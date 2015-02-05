<?php

use jerryhsia\ExcelExporter;

require_once __DIR__.'/../vendor/autoload.php';


class ExcelExporterTest extends PHPUnit_Framework_TestCase {

    public function testCreateFile()
    {
        $source = [
            ['name' => 'Jerry Hsia', 'gender' => 'male', 'age' => 23],
            ['name' => 'Jerry 9916', 'gender' => 'female', 'age' => 24]
        ];

        $fields = [
            'name' => 'Name',
            'age' => 'Age',
            'gender' => 'Gender',
        ];

        $exporter = new ExcelExporter([
            'filename' => 'test.xls',
            'fields' => $fields,
            'data' => $source,
            'title' => 'Title',
            'leftBar' => 'Left Bar Text',
            'rightBar' => 'Right Bar Text'
        ]);

        $file = $exporter->createFile();

        $this->assertTrue(file_exists($file));

        @unlink($file);
    }
}
