<?php

namespace Model;

class Products implements ResourceInterface {

    private $config;

    public function __construct()
    {
        $contents = file_get_contents("src/etc/products.json");
        $this->config = json_decode($contents);
    }

    /**
     * @return array[]|null;
     */
    public function getUrls() {
        return $this->config->urls;
    }

    public function __toString()
    {
        return 'products';
    }
}