<?php

namespace jerryhsia;
/**
 * Class BaseExporter
 *
 * @property string $filename The exported file name
 * @property string $outputPath The directory path to exported file
 * @property boolean $keepFile Whether to keep the file after export
 *
 * @author Jerry Hsia<xiajie9916@gmail.com>
 */
abstract class BaseExporter {

    protected $_config = [];

    protected static $_default_output_dir = 'output';

    public function __construct(array $config)
    {
        $this->_config = $config;
        if (!$this->filename) {
            throw new \Exception('The \'filename\' param required');
        }

        $this->fixFilename();
    }

    function __get($name)
    {
        return isset($this->_config[$name]) ? $this->_config[$name] : null;
    }

    function __set($name, $value)
    {
        $this->_config[$name] = $value;
    }

    /**
     * Fix file name
     */
    protected function fixFilename()
    {
        $this->filename = str_replace("/", "-", $this->filename);
        $this->filename = str_replace("\\", "-", $this->filename);
        $this->filename = str_replace(' ', '-', $this->filename);
    }

    /**
     * Create a dir
     *
     * @param $dir
     * @return mixed
     * @throws \Exception
     */
    protected function createDir($dir)
    {
        $success = true;
        if (!is_dir($dir)) {
            $success = mkdir($dir);
        }

        if (!$success || !is_writable($dir)) {
            throw new \Exception('Default dir is not writable');
        }

        return $dir;
    }

    /**
     * The directory path to exported file
     *
     * @return mixed
     * @throws \Exception
     */
    public function getOutputPath()
    {
        $dir = $this->fileDir ? $this->fileDir : __DIR__.DIRECTORY_SEPARATOR.self::$_default_output_dir;
        return $this->createDir($dir);
    }

    /**
     * The full path to exported file
     *
     * @return mixed
     * @throws \Exception
     */
    public function getFilePath()
    {
        return $this->getOutputPath().DIRECTORY_SEPARATOR.$this->filename;
    }

    /**
     * Get the file extension
     *
     * @return mixed|string
     */
    public function getFileExtension()
    {
        if (($ext = pathinfo($this->filename, PATHINFO_EXTENSION)) !== '') {
            return strtolower($ext);
        } else {
            $fileInfo = explode('.', $this->filename);
            return strtolower(end($fileInfo));
        }
    }

    /**
     * Create the exporting file
     *
     * @return string The path to the exported file
     */
    abstract function createFile();

    /**
     * Send file to browser
     *
     * @throws \Exception
     */
    public function send()
    {
        $createdFile = $this->createFile();
        if (file_exists($createdFile)) {

            $headers = [
                'Content-Type' => 'application/octet-stream',
                'Content-Disposition' => 'attachment;filename='.$this->filename,
                'Last-Modified' => gmdate('D, d M Y H:i:s') . ' GMT',
                'Cache-Control' => 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0',
                'Pragma' => 'no-cache'
            ];
            foreach ($headers as $key => $value) {
                Header("{$key}: {$value}");
            }

            $handle = fopen($createdFile, 'rb');
            echo fread($handle, filesize($createdFile));
            fclose($handle);
            if (!$this->keepFile) {
                @unlink($createdFile);
            }
            exit;
        } else {
            throw new \Exception('Failed to create file');
        }
    }
}
