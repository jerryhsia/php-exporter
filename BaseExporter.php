<?php

/**
 * Class BaseExporter
 *
 * @property string $filename
 * @property string $fileDir
 *
 * @author Jerry Hsia<xiajie9916@gmail.com>
 */
abstract class BaseExporter {

    protected $_config = [];

    public function __construct(array $config)
    {
        $this->_config = $config;
        if (empty($this->filename)) {
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

    protected function fixFilename()
    {
        $this->filename = str_replace("/", "-", $this->filename);
        $this->filename = str_replace("\\", "-", $this->filename);
        $this->filename = str_replace(' ', '-', $this->filename);
    }

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

    public function getFileDir()
    {
        $dir = $this->fileDir ? $this->fileDir : __DIR__.DIRECTORY_SEPARATOR.'_dir';
        return $this->createDir($dir);
    }

    public function getFilePath()
    {
        return $this->getFileDir().DIRECTORY_SEPARATOR.$this->filename;
    }

    public function getFileExtension()
    {
        if (($ext = pathinfo($this->filename, PATHINFO_EXTENSION)) !== '') {
            return strtolower($ext);
        } else {
            $fileInfo = explode('.', $this->filename);
            return end($fileInfo);
        }
    }

    abstract function createFile();

    protected function send()
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
            @unlink($createdFile);
        } else {
            throw new \Exception('Failed to create file');
        }
    }
}
