<?php

namespace Lib;

/**
 * This class can manage files/directories in my environment
 * 
 * @author Imran Aghayev<imran.aghayev@hotmail.co.uk>
 */
class IO {

    private $basePath = null;
    private $newDirPermission = 0777;
    private $newDirRecursive = true;

    /**
     * Constructor
     * 
     * @param string $basePath
     */
    public function __construct($basePath) {
        $this->cd($basePath);
    }

    /**
     * Helper function to construct path folder
     * 
     * @param string $dir
     */
    private function formatBasePath($dir) {
        return ($this->basePath !== null) ? $this->basePath . '/' . $dir : $dir;
    }

    /**
     * Helper function to get path folder
     */
    private function getBasePath() {
        return $this->basePath . '/';
    }

    /**
     * List files and directories
     */
    public function ls() {

        $ls = '';

        try {
            
            if (is_dir($this->basePath)) {
                if ($handler = opendir($this->basePath)) {
                    while (($fileName = readdir($handler)) !== false) {

                        if ($fileName == '..') {
                            continue;
                        }

                        $ls .= '# ' . $fileName . "\n";
                    }
                    closedir($handler);
                } else {
                    throw new \Exception('Unable to open directory: ' . $this->basePath);
                }
            } else {
                throw new \Exception('No directory found: ' . $this->basePath);
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
        }

        return $ls;
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

    /**
     * Change directory
     * 
     * @param string $dir
     */
    public function cd($dir) {

        try {

            $this->basePath = $this->formatBasePath($dir);

            if (getcwd() == $this->basePath) {
                throw new \Exception('Given directory is already:' . $this->basePath);
            }

            if (!chdir($this->basePath)) {
                throw new \Exception('Unable to change directory: ' . $newDir);
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * Insert content in a new file
     * 
     * @param string $content
     * @param string $filename
     */
    public function fecho($content, $fileName) {

        try {

            $fileNamePath = $this->getBasePath() . $fileName;

            if (file_exists($fileNamePath)) {
                throw new \Exception('File already exists:' . $fileNamePath);
            }

            if (!file_put_contents($fileNamePath, $content)) {
                throw new \Exception('Unable to content the file:' . $fileNamePath);
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * Display content of the file
     * 
     * @param string $fileName
     */
    public function cat($fileName) {

        try {

            $fileNamePath = $this->getBasePath() . $fileName;

            if (!file_exists($fileNamePath)) {
                throw new \Exception('File does not exist:' . $fileNamePath);
            }

            echo file_get_contents($fileNamePath);
        } catch (\Exception $e) {
            echo $e->getMessage();
        }    
    }

    /**
     * Delete the file
     * 
     * @param string $fileName
     */
    public function rm($fileName) {

        try {

            $fileNamePath = $this->getBasePath() . $fileName;

            if (!file_exists($fileNamePath)) {
                throw new \Exception('File does not exist already:' . $fileNamePath);
            }

            if (!unlink($fileNamePath)) {
                throw new \Exception('Unable to delete the file:' . $fileNamePath);
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
        }        
    }

}
