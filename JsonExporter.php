<?php

namespace jerryhsia;
/**
 * Class JsonExporter
 *
 * @property array $fields The fields allowed to export
 * @property array $data The source data
 *
 * @author Jerry Hsia<xiajie9916@gmail.com>
 */
class JsonExporter extends BaseExporter {


    public function __construct($config)
    {
        $this->_config = $config;

        if (!is_array($this->data) || !$this->data) {
            throw new \Exception('Data invalid');
        }

        parent::__construct($config);
    }

    public function createFile()
    {
        $data = [];
        if (is_array($this->fields) && $this->fields) {
            $i = 0;
            foreach ($this->data as $row) {
                foreach ($this->fields as $field) {
                    if (isset($row[$field])) {
                        $data[$i][$field] = $row[$field];
                    } else {
                        $data[$i][$field] = '';
                    }
                }
                $i++;
            }
            $data = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        } else {
            $data = json_encode($this->data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        $handle = fopen($this->getFilePath(), 'w+');

        fwrite($handle, $data);
        fclose($handle);

        return $this->getFilePath();
    }

}
