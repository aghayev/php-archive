<?php

namespace Model;

use DateTime;
use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;

class Logger extends AbstractLogger implements LoggerInterface
{

    public $filePath;
    public $dateFormat = DateTime::RFC2822;

    public $template = "{date} {level} {message} {context}";

    public function __construct($filename = 'default.log')
    {
        $this->filePath = 'log/'. $filename.'.log';

        if (!file_exists($this->filePath))
        {
            touch($this->filePath);
        }
    }

    public function log($level, $message, array $context = [])
    {
        file_put_contents($this->filePath, trim(strtr($this->template, [
                '{date}' => $this->getDate(),
                '{level}' => $level,
                '{message}' => $message,
                '{context}' => $this->contextStringify($context),
            ])) . PHP_EOL, FILE_APPEND);
    }

    public function getDate()
    {
        return (new DateTime())->format($this->dateFormat);
    }

    public function contextStringify(array $context = [])
    {
        return !empty($context) ? json_encode($context) : null;
    }
}