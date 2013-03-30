<?php

/**
 * The myVasTestHelper Adapter
 *
 * @author     Imran Aghayev
 * @version    $Id$
 */
class csVasTestHelper
{
    public static $channelId = ChannelPeer::CHANNEL_SMS;

    /**
     * Creates and registers a user
     * msisdn because it is non-blackbox method
     * 
     */
    public static function registerUser($msisdn, $nickname = null)
    {
        try {
            self::SoapInit();

            RegistrationHelper::handleRegistrationRequestWithMinimalData($msisdn,
                                                                         null,
                                                                         $nickname);

            // Update status
            $fromUsr = UsrPeer::getUsrIdFromMobile($msisdn);
            $fromUsr->setUsrStateId(UsrPeer::USR_STATE_PRE_REGISTRATION);
            $fromUsr->save();

            // Get middleware to register the usr
            $un = new Unify($fromUsr->getUsername(), csVasTestHelper::$channelId);
            
            // locale to localee migration
            // Once migration to localee has occurred for all projects clean up 
            // by removing following two lines and calling $fromUsr->getContact()->getLocaleeId()
            // in call to registerUser()
            $contact = $fromUsr->getContact();
            $locId = (class_exists('LocaleePeer')) ? $contact->getLocaleeId() : $contact->getLocaleId();
            
            return $un->registerUser($locId);
        } catch (Exception $e) {
            return Unify::WS_ACTION_STATE_CALL_FAILED;
        }
    }

    /**
     * Sends Sms Message then fetches MT-SMS Response
     * mobile is because it is blackbox method
     *  
     * @param int $mobile
     * @return Usr
     */
    public static function sendControlledSms($mobile, $message)
    {
        // Setup SoapClient
        $soapClient = self::setupSoapClient();

        //setup smsMessage
        $msg = new SmsMessage();
        $msg->senderAddress = $mobile;
        $msg->message = $message;
        $msg->smsServiceActivationNumber = null;
        $msg->mmsCapable = null;
        $msg->channelId = self::$channelId;
        $msg->dateTime = date('Y-m-d H:i:s');

        //randomly generator corellator
        $correlator = mt_rand();

        // Send PRICE SMS Command
        $result = $soapClient->notifySmsReception($correlator, $msg);

        return csTestHelper::getSmsOutByCorrelator($correlator);
    }

    /**
     * Sends Sms Message then fetches MT-SMS Response
     * mobile is because it is blackbox method
     *  
     * @param int $mobile
     * @return Usr
     */
    public static function sendControlledMms($mobile, $message, $mmsAttachment)
    {
        // Setup SoapClient
        $soapClient = self::setupSoapClient();

        //setup smsMessage
        $msg = new MmsMessage();
        $msg->senderAddress = $mobile;
        $msg->subject = $message;
        $msg->messageServiceActivationNumber = null;
        $msg->attachments = $mmsAttachment;
        $msg->dateTime = date('Y-m-d H:i:s');

        //randomly generator corellator
        $correlator = mt_rand();

        // Send PRICE SMS Command
        $result = $soapClient->notifyMessageReception($correlator, $msg);

        return csTestHelper::getSmsOutByCorrelator($correlator);
    }

    /**
     * App host
     */
    public static function SoapInit()
    {
        $wsdldef = sfConfig::get('app_application_wsdl_default');
        $_SERVER['HTTP_HOST'] = $wsdldef['HOSTNAME'];
        $_SERVER['SERVER_PORT'] = 80;
    }

    /**
     * Setup SoapClient
     */
    private static function setupSoapClient()
    {
        self::SoapInit();

        $options = array();
        $options['trace'] = sfConfig::get('app_trace_soap');
        $wsdl = Unify::getLocalWsdl();

        return new SoapClient($wsdl, $options);
    }

    /**
     * Create a Group or Community
     * msisdn because it is non-blackbox method
     *
     */
    public static function createLocatorGroup($msisdn, $name, $community = false)
    {
        $fromUsr = UsrPeer::getUsrIdFromMobile($msisdn);

        $group = new LocatorGroup();
        $group->setUsrId($fromUsr->getId());
        $group->setName($name);
        $group->setIsCommunity($community);
        $form = new LocatorGroupForm($group);
        $form->bind(array('name' => $name));

        if ($form->isValid()) {
            $form->save();

            if ($community) {
                // Add new Community to CommunityGroup Table
                $ucg = new UsrCommunityGroup();
                $ucg->setCommunityUsrId($fromUsr->getId());
                $ucg->setLocatorGroupId($form->getObject()->getId());
                $ucg->save();
            }

            return true;
        }

        return false;
    }

    /**
     * Add Friend To a Group
     */
    public static function addFriendToGroup($msisdn, $name, $friendMsisdn)
    {
        $fromUsr = UsrPeer::getUsrIdFromMobile($msisdn);

        $friendUsr = UsrPeer::getUsrIdFromMobile($friendMsisdn);

        $frnd = UsrLocateePeer::doSelectOne(UsrLocateePeer::getFriendCriteria($fromUsr->getId(),
                                                                              $friendUsr->getId(),
                                                                              false));

        $grp = LocatorGroupPeer::findGroupByName($name, $fromUsr->getId());

        $entry = new UsrLocateeLocatorGroup();
        $entry->setUsrLocateeId($frnd->getId());
        $entry->setLocatorGroupId($grp->getId());
        $entry->save();
    }

    /**
     * Add Friend To a Community
     */
    public static function addFriendToCommunity($msisdn, $name, $friendMsisdn)
    {
        self::addFriendToGroup($msisdn, $name, $friendMsisdn);
    }

    /**
     * Update Contact CanUpdateStatus 
     */
    public static function SetContactCanUpdateStatus($msisdn,
                                                     $permission = false)
    {
        $fromUsr = UsrPeer::getUsrIdFromMobile($msisdn);
        $contact = $fromUsr->getContact();

        $contact->setCanUpdateStatus($permission);
        $contact->save();
    }

    /**
     * Update Contact CanUpdateImage 
     */
    public static function SetContactCanUpdateImage($msisdn, $permission = false)
    {
        $fromUsr = UsrPeer::getUsrIdFromMobile($msisdn);
        $contact = $fromUsr->getContact();

        $contact->setCanUpdateImage($permission);
        $contact->save();
    }

}
