<?php

/**
 * This is mySmsRequest class
 *
 * This class manages sms requests. It parses input from the request and store them as parameters.
 *
 * @author     Imran Aghayev
 * @version    $Id$
 */
class mySmsRequest extends sfRequest
{
    protected $smsParameters = array();
    public $command = null;
    public $commandTypeId = null;
    public $commandEvent = null;
    public $commandPin = null;
    public $commandParams = null;
    public $commandType = null;
    public $commandValue = null;
    public $commandArr = array();
    public $senderAddress = null;
    public $serviceActivationNumber = null;
    public $usrId = null;
    public $mmsCapable = null;

    /**
     * Initializes this sfRequest.
     *
     * @param  sfEventDispatcher $dispatcher  An sfEventDispatcher instance
     * @param  array             $parameters  An associative array of initialization parameters
     * @param  array             $attributes  An associative array of initialization attributes
     * @param  array             $options     An associative array of options
     *
     * @return bool true, if initialization completes successfully, otherwise false
     * @throws <b>sfInitializationException</b> If an error occurs while initializing this sfRequest
     */
    public function initialize(sfEventDispatcher $dispatcher,
                               $parameters = array(), $attributes = array(),
                               $options = array())
    {
        //here we store SmsMessage object to parameterHolder so no need to reassign
        parent::initialize($dispatcher, $parameters, $attributes, $options);

        //SMS parameters
        $this->smsParameters = $parameters;
        $this->parameterHolder->add($this->smsParameters);

        //step 1.1 :: Remove any embedded separator characters
        //Convert any separator chars to spaces
        $message = csRequestHelper::removeSeparatorCharacters($this->getData());

        //step 1.2 :: getCommand and getCommandTypeId by smsCommand
        // Work out the request type, i.e. the command, by selecting the first two
        // words and if that fails the last two words (if there any Farsi chars detected)
        // Looking them up in the request_type_i18n table then
        // If we are still unable to get a request type then if we have been given a
        // valid image url then we take it to be an IMAGE command, else we set the
        // type to UNKNOWN
        $requestTypeResult = CsvasRequestTypePeer::deriveRequestType($message,
                                                                     false);
        $this->command = $requestTypeResult->command;
        $this->commandTypeId = $requestTypeResult->commandTypeId;
        $this->setData($requestTypeResult->message);

        if (!$this->commandTypeId) {
            if (csRequestHelper::hasNonLatinCharacters($message)) {
                if (sfConfig::get('app_vas_allow_right_to_left_commands')) {
                    // Try again, this time right to left
                    $requestTypeResult = CsvasRequestTypePeer::deriveRequestType($message,
                                                                                 true);
                    $this->command = $requestTypeResult->command;
                    $this->commandTypeId = $requestTypeResult->commandTypeId;
                    $this->setData($requestTypeResult->message);
                }
            }
        }

        if (!$this->commandTypeId) {
            $requestTypeResult = CsvasRequestTypePeer::deriveImageRequestType($message);

            $this->command = $requestTypeResult->command;
            $this->commandTypeId = $requestTypeResult->commandTypeId;
            $this->setData($requestTypeResult->message);
        }

        //step 1.4 :: If there are command parameters then we examine them and convert
        // any Farsi MSISDNs to latin characters, because the application stores
        // them all as latin.  Note that the user must supply Nicknames, Group names
        // and Community names in latin.        
        if (sfConfig::get('app_vas_convert_farsi_msisdns')) {
            // Convert any Farsi MSISDNs to latin
            $this->setData(csFarsiHelper::checkForFarsiMsisdns($this->getData()));
        }

        //step 2 :: Short Code Management :: Taken from Buddywise
        // Now we check to see if the user has sent the command to a command
        // short code.  If so then that must be the command or else it's an error. The
        // need not supply the command if they target such a short code, so we
        // may recover from a previous error having set the type to UNKNOWN.
        $this->serviceActivationNumber = $this->getServiceActivationNumber();

        // Here we are checking the msisdn number to which the user sent the SMS command.
        // If it isn't the default number then it may be a number with which we associate a particular command.
        $defaultServiceActivationNumber = sfConfig::get('app_vas_default_service_activation_number');

        if ($this->serviceActivationNumber != '' && $this->serviceActivationNumber != $defaultServiceActivationNumber) {
            $requestType = CsvasRequestTypePeer::getRequestTypeFromShortCode($this->serviceActivationNumber);

            if ($requestType) {
                // We are expecting it to be this particular request type, which means we expect there
                // to be no command or one which matches the expected one.
                if ($this->commandTypeId != $requestType->getId()) {
                    // The command we expect it to be does not match.  Insert the expected command
                    $this->command = $requestType->getCode();
                    $this->getParameterHolder()->set('message',
                                                     $this->command . ' ' . trim($this->getData()));
                    $this->commandTypeId = $requestType->getId();
                }
            }
        }

        //step 2.5 :: senderAddress
        $this->senderAddress = $this->smsParameters['senderAddress'];

        //step 2.6 :: usrId and Auto Registration :: At the moment used in Socialwise
        $this->usrId = csCommonHelper::getUserId($this->senderAddress, false);

        if (!$this->usrId) {
            if ($this->command == CsvasRequestTypePeer::REQUEST_TYPE_COMMAND_REGISTER
                || sfConfig::get('app_vas_auto_registration')
                || $this->serviceActivationNumber == $defaultServiceActivationNumber) {
                // Invite them to join the system.
                // Create a new Usr/Contact/Target for this usr.
                //RegistrationHelper class belongs to Socialwise
                $errors = RegistrationHelper::handleRegistrationRequestWithMinimalData($this->senderAddress);

                if (count($errors) > 0) {
                    $errMsgs = implode(', ', $errors);
                    csVasLogger::doLog('Error during auto-registration: ' . $errMsgs);
                } else {
                    // Default to the register command
                    $this->command = CsvasRequestTypePeer::REQUEST_TYPE_COMMAND_REGISTER;
                    $this->getParameterHolder()->set('message',
                                                     $this->command . ' ' . trim($this->getData()));
                    $this->commandTypeId = CsvasRequestTypePeer::REQUEST_TYPE_REGISTER_ID;
                }
            }
        }

        // step 2.7 :: Set userId after auto-registration
        if (!$this->usrId) {
            $this->usrId = csCommonHelper::getUserId($this->senderAddress);
        }

        if (sfConfig::get('app_vas_auto_registration')) {
            $requestingUsr = UsrPeer::retrieveByPK($this->usrId);

            // Check the usr state
            if ($this->usrId && $requestingUsr->getUsrStateId() == UsrPeer::USR_STATE_REGISTRATION_FAILED) {
                // They previously contacted us and failed the registration process
                // Switch to AutoRegistrant now that they have contacted us again
                $requestingUsr->setUsrStateId(UsrPeer::USR_STATE_AUTO_REGISTRATION);
                $requestingUsr->save();
            }
        }

        //step 3 :: getEvent
        if (!isset($this->commandTypeId) || $this->commandTypeId == '') {
            $this->commandEvent = strtoupper('error');
        } else {
            $requestType = CsvasRequestTypePeer::retrieveByPK($this->commandTypeId);

            $this->commandEvent = $requestType->getCode();
        }

        //step 4 :: getCommandParams
        $withConcatenation = false;
        if (!isset($this->commandParams) || $this->commandParams == '') {
            $this->commandParams = null;
            // Capture the command parameters.
            $words = explode(' ', trim($this->getData()));
            if (isset($words) && is_array($words) && count($words) > 1) {
                $concatChar = ' ';
                if ($withConcatenation) {
                    // Concatenate all remaining words, after the command, with underscore.
                    $concatChar = '_';
                }
                $this->commandParams = trim(implode($concatChar,
                                                    array_slice($words, 1)));
            }
        }

        //step 5 :: preProcessPin
        // If a PIN is required then extract it from the request.
        if (sfConfig::get('app_request_requires_pin')) {
            csRequestHelper::preProcessPin($this);
        }

        //step 6 :: preProcessCommand
        // If supporting command types, abbreviated cmds,
        // then preprocess the command to ensure it is in an expected format.
        if (sfConfig::get('app_request_use_preprocessor')) {
            csRequestHelper::preProcessCommand($this);
        }

        //step 7 :: commandType and commandValue
        csRequestHelper::preProcessCommandType($this);

        //step 8 :: commandArr
        //talked to Zhan :: We agreed that this is just parsing function
        //not check or validation function. Throwing error can be replaced
        //by writing to log file
        $this->commandArr = CsvasRequestTypePatternPeer::getCommandArr($this->commandTypeId,
                                                                       $this->commandParams);

        //step 9 :: mmsCapable
        $this->mmsCapable = $this->getMmsCapable();
        
        $this->getParameterHolder()->set('command', $this->command);
        $this->getParameterHolder()->set('commandTypeId', $this->commandTypeId);
        $this->getParameterHolder()->set('commandEvent', $this->commandEvent);
        $this->getParameterHolder()->set('commandPin', $this->commandPin);
        $this->getParameterHolder()->set('commandParams', $this->commandParams);

        $this->getParameterHolder()->set('commandType', $this->commandType);
        $this->getParameterHolder()->set('commandValue', $this->commandValue);

        $this->getParameterHolder()->set('commandArr', $this->commandArr);

        $this->getParameterHolder()->set('senderAddress', $this->senderAddress);

        $this->getParameterHolder()->set('serviceActivationNumber',
                                         $this->serviceActivationNumber);

        $this->getParameterHolder()->set('usrId', $this->usrId);

        $this->getParameterHolder()->set('mmsCapable', $this->mmsCapable);

        if (sfConfig::get('app_vas_debug')) {
            csVasLogger::doLog(print_r($this->getParameterHolder()->getAll(),
                                       true));
        }
    }

    /**
     * Gets the Short Code. Here is has sms prefix
     *
     * @return <type>
     */
    public function getServiceActivationNumber()
    {
        if (isset($this->smsParameters['smsServiceActivationNumber'])) {
            return $this->smsParameters['smsServiceActivationNumber'];
        }
    }

    /**
     * Gets MmsCapable
     *
     * @return <type>
     */
    public function getMmsCapable()
    {
        if (isset($this->smsParameters['mmsCapable'])) {
            return $this->smsParameters['mmsCapable'];
        }
    }

    /**
     * Sets the message
     *
     * @return <type>
     */
    public function setData($message)
    {
        if (isset($this->smsParameters['message'])) {
            $this->smsParameters['message'] = $message;
        }

        $this->getParameterHolder()->set('message', $message);
    }

    /**
     * Gets the message
     *
     * @return <type>
     */
    public function getData()
    {
        if (isset($this->smsParameters['message'])) {
            return $this->smsParameters['message'];
        }
    }

    /**
     * Gets the command
     *
     * @return <type>
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * Gets the command
     *
     * @return <type>
     */
    public function getCommandEvent()
    {
        return $this->commandEvent;
    }

    /**
     * Gets the command params
     *
     * @return <type>
     */
    public function getCommandParams()
    {
        return $this->commandParams;
    }

}