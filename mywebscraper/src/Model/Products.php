<?php

namespace Model;

class Products implements ResourceInterface {

    const CLASS_NAME = 'products';

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

    public function getDownloadFolder()
    {
        return $this->config->download_folder;
    }

    public function getIterationCount()
    {
        return $this->config->iteration_count;
    }

    public function getProductName($url)
    {
        $parsedUrl = parse_url($url);
        return ltrim($parsedUrl['path'],'/');
    }

}