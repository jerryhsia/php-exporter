<?php

require_once __DIR__.'/../vendor/autoload.php';

use jerryhsia\JsonExporter;

class JsonExporterTest extends PHPUnit_Framework_TestCase {

    public function testCreateFile()
    {
        $source = [
            ['name' => 'Jerry Hsia', 'gender' => 'male', 'age' => 23],
            ['name' => 'Jerry 9916', 'gender' => 'female', 'age' => 24]
        ];

        $fields = ['name', 'age'];

        $exporter = new JsonExporter([
            'filename' => 'test.json',
            'fields' => $fields,
            'data' => $source
        ]);

        $file = $exporter->createFile();

        $this->assertTrue(file_exists($file));

        $data = file_get_contents($file);
        $data = json_decode($data, true);

        foreach ($source as $index => $row) {
            foreach ($row as $key => $value) {
                if (!in_array($key, $fields)) continue;
                $this->assertEquals($data[$index][$key], $value);
            }
        }

        @unlink($file);
    }
}
