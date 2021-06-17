<?php

namespace App;

class HttpResponse
{
    protected $header;
    protected $content;
    protected $xvarniship;

    public function __construct($response, $headerSize) {
        $this->header = substr($response, 0, $headerSize);
        $this->content = substr($response, $headerSize);
        $this->xvarniship = $this->grabXVarnishIp($this->header);
    }

    public function getHeader() {
        return $this->header;
    }

    public function getContent() {
        return $this->content;
    }

    public function getXVarnishIp() {
        return $this->xvarniship;
    }

    protected function grabXVarnishIp() {
        preg_match('/X-Varnish-IP:[ \t](?:[0-9]{1,3}\.){3}[0-9]{1,3}/', $this->header, $matches );
        $keywords = preg_split("/X-Varnish-IP:[ \t]/", $matches[0]);
        return $keywords[1];
    }
}