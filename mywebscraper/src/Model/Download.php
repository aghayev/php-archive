<?php

namespace Model;

class Download {

    private $basePath = null;
    private $newDirPermission = 0777;
    private $newDirRecursive = true;

    private static $instance;

    private function __construct() {}
    private function __clone() {}

    public static function getInstance() {

        if (empty(self::$instance)) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    public function save($downloadPath, $response) {

        try {
            if (!is_dir($downloadPath)) {
                $this->mkdir($downloadPath);
            }
            // Saving Header
            file_put_contents($downloadPath. 'headers.txt', $response->getHeader());

            // Saving Content
            file_put_contents($downloadPath. 'content.html', gzdecode($response->getContent()));
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * Create a new directory
     *
     * @param string $dir
     */
    public function mkdir($dir) {

        try {

            $newDir = $this->formatBasePath($dir);
            if (file_exists($newDir)) {
                throw new \Exception('Directory already exists:' . $dir);
            }

            if (!mkdir($newDir, $this->newDirPermission, $this->newDirRecursive)) {
                throw new \Exception('Unable to make directory: ' . $newDir);
            }

        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    public function buildPath($downloadPath) {
        return implode(DIRECTORY_SEPARATOR, $downloadPath). DIRECTORY_SEPARATOR;
    }

    private function formatBasePath($dir) {
        return ($this->basePath !== null) ? $this->basePath . '/' . $dir : $dir;
    }
}