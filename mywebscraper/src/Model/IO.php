<?php

namespace Model;

class IO {

    protected $path;

    public function __construct()
    {
        $this->path  = '../../output';
    }

    public function save($folderName) {

        try {
            $pathToFolder = $this->path . DIRECTORY_SEPARATOR . $folderName;

            if (is_dir($pathToFolder)) {
                if ($handler = opendir($pathToFolder)) {
                    closedir($handler);
                } else {
                    throw new \Exception('Unable to open directory: ' . $this->basePath);
                }
            } else {
                echo $pathToFolder;exit;
                mkdir($pathToFolder);
                $this->save($pathToFolder);
            }
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
}