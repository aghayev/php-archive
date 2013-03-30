<?php

/**
 * The mySmsView class
 *
 * This class is sms templating engine
 *
 * @author     Imran Aghayev
 * @version   $Id$
 */
abstract class mySmsView
{
    protected $command = null;
    protected $responseType = null;
    protected $parameters = array();
    protected $values = array();
    protected $template = null;
    protected $responseMessage = null;
    protected $attachments = null;
    protected $responseObj = null;

    /**
     * Show a default view.
     */
    const VIEW = 'View';

    /**
     * Show an error view.
     */
    const ERROR = 'Error';

    /**
     * Show a success view.
     */
    const SUCCESS = 'Success';

    /**
     *  Tag used in templating
     */
    const TAG = '%';

    /**
     * Class constructor
     *
     */
    public function __construct($responseType = null, $parameters = array())
    {
        $this->responseType = ucfirst(strtolower($responseType));
        $this->parameters = $parameters;
    }

    /**
     * Response
     *
     * @return string
     */
    abstract public function handleView($content);

    /**
     * Sets the command parameter
     *
     * @return mixed
     */
    public function setParameter($name, $value)
    {
        $this->parameters[$name] = $value;
    }

    /**
     * Gets the command parameter
     *
     * @return mixed
     */
    public function getParameter($name)
    {
        return $this->parameters[$name];
    }

    /**
     * Indicates whether or not a parameter exists
     *
     * @return true, if the parameter exists, otherwise false
     */
    public function hasParameter($name)
    {
        return array_key_exists($name, $this->parameters);
    }

    /**
     * Gets the command parameter
     *
     * @return mixed
     */
    public function getContext()
    {
        return $this->parameters['context'];
    }

    /**
     * Get responseMessage
     *
     * @return null
     */
    public function getResponseMessage()
    {
        return $this->responseMessage;
    }

    /**
     * Set command
     *
     * @return null
     */
    public function setCommand($command)
    {
        $this->command = $command;
    }

    /**
     * Set presentation
     *
     *
     * @return null
     */
    public function setPresentation($renderMode, $content)
    {
        csVasLogger::doLog('Executing view "setPresentation"');

        if ($renderMode == self::VIEW) {
            // simple non db based template file

            $viewFile = $this->command . self::VIEW . '.php';

            if (sfConfig::get('app_vas_debug')) {
                csVasLogger::doLog($viewFile);
            }
            
            $file = sfFinder::type('file')->name($viewFile)->in(sfConfig::get('sf_csvas_templates'));
            if (is_readable($file[0])) {
                // view template can be included many times during rendering
                require ($file[0]);
            }
        } else {
            $commandFile = $this->command . $this->responseType . $renderMode . '.php';

            if (sfConfig::get('app_vas_debug')) {
                csVasLogger::doLog($commandFile);
            }

            $files = sfFinder::type('file')->name($commandFile)->in(sfConfig::get('sf_csvas_templates'));

            if (is_readable($files[0])) {
                // db template can be included once because is called from csSyncFilter or csAsyncFilter
                require_once ($files[0]);
            } else {
                $defaultFile = sfConfig::get('sf_csvas_template') . '/' . $this->responseType . $renderMode . '.php';

                if (is_readable($defaultFile)) {
                    require_once ($defaultFile);
                }
            }
        }
    }

    /**
     * Persist Template
     *
     *
     * @return null
     */
    public function persistTemplate(CsvasRequest $requestObj, $renderMode,
                                    $content, $templateFromDb = false)
    {
        csVasLogger::doLog('Executing view "persistTemplate"');

        if ($content['response']) {

            $this->responseObj = new CsvasResponse();
            $this->responseObj->setCsvasRequestId($requestObj->getId());

            if ($templateFromDb) {

            $subCode = $content['code'];

            $this->template = $this->responseObj->getCsvasResponseType()->getResponseTextForRequestWithI18n($requestObj,
                                                                                                            $subCode);
                
            }

            // View :: Decorate
            switch ($renderMode) {
                case self::SUCCESS:
                    if ($content['response']) {
                        $requestObj->setCsvasRequestStateId(CsvasRequestStatePeer::ID_PROCESSED);
                    }
                    break;

                case self::ERROR:
                    $requestObj->setCsvasRequestStateId(CsvasRequestStatePeer::ID_ERROR_PROCESSING);
                    break;

                default:
                    //nothing
                    break;
            }

            // View :: Get template file
            $this->setPresentation($renderMode, $content);

            //persist to db
            $requestObj->setProcessedAt(stdTime::utcTime(), 'UTC');
            $requestObj->save();

            //for multimedia messaging service
            if (isset($this->attachments)) {

                if (is_array($this->attachments)) {
                    $attach = '';

                    foreach ($this->attachments as $attachment) {
                        if (isset($attachment['filelocation']) && $attachment['filelocation']) {
                            // An image attachment
                            if ($attach) {
                                $attach .= ',';
                            }
                            $attach .= $attachment['filelocation'];
                        }
                    }
                } else {
                    $attach = $this->attachments;
                }

                $this->responseObj->setAttachments($attach);
            }

            $this->responseObj->setText($this->responseMessage);
            $this->responseObj->setProcessedAt(stdTime::utcTime(), 'UTC');
            $this->responseObj->save();
        }
    }

    /**
     * Set template
     *
     * @return null
     */
    public function setTemplate($renderMode, $content)
    {
        csVasLogger::doLog('Executing view "setTemplate"');

        //Save request response
        if ($content['response']) {

            if ($this->getParameter('event') == 'vas.acklocation') {
                $this->command =
                    csVasUtils::getCamelCase($this->getContext()->getLocateRequest()->getCsvasRequest()->getCsvasRequestType()->getCode());
                $requestObj = $this->getContext()->getLocateRequest()->getCsvasRequest();
            } elseif ($this->getParameter('event') == 'vas.cmd_gps') {
                $this->command =
                    csVasUtils::getCamelCase($this->getContext()->getCsvasRequest()->getCsvasRequestType()->getCode());
                $requestObj = $this->getContext()->getCsvasRequest();
            } else {
                $this->command = csVasUtils::getCamelCase($this->getContext()->getParameter('commandEvent'));

                $requestObj = $this->getParameter('requestObj');
            }

            self::persistTemplate($requestObj, $renderMode, $content, true);
        }
    }

    /**
     * Include Partial Helper Method
     */
    public function includePartial($templateName, $content)
    {
        csVasLogger::doLog('Executing view "includePartial"');

        $partialFile = '_' . $templateName . '.php';

        $files = sfFinder::type('file')->name($partialFile)->in(sfConfig::get('sf_csvas_templates'));

        // partial template can be included many times during rendering
        if (is_readable($files[0])) {
            require ($files[0]);
        }
    }

    /**
     * Assigns a value for replacing a specific tag
     *
     * @param string $key the name of the tag to replace
     * @param string $value the value to replace
     *
     * @return null
     */
    public function assign($key, $value)
    {
        $this->values[self::TAG . $key . self::TAG] = $value;
    }

    /**
     * Fetchs the content of the template, replacing the keys for its respective values.
     *
     * @return string
     */
    public function fetch()
    {
        return strtr($this->template, $this->values);
    }

}
