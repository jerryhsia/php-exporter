# php-exporter
A library for exporting data to file

## Installation

`composer require --prefer-dist "jerryhsia/php-exporter" "dev-master"`

## JsonExporter

Field|Type|Description
-----------|----------|---------------
**filename**|String|REQUIRED - The exported file name
**data**|Array|REQUIRED - The source data
**fileDir**|String|OPTIONAL - The directory path to exported file
**fields**|Array|OPTIONAL - The fields allowed to export

### Usage

```php
<?php
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

$file = $exporter->createFile(); // Save file to the disk
$exporter->send(); // Send file to the broswer
```

## ExcelExporter

Field|Type|Description
-----------|----------|---------------
**filename**|String|REQUIRED - The exported file name
**data**|Array|REQUIRED - The source data
**fileDir**|String|OPTIONAL - The directory path to exported file
**fields**|Array|OPTIONAL - The fields allowed to export
**title**|String|OPTIONAL - The title of table
**leftBar**|String|OPTIONAL - The left bar text
**rightBar**|String|OPTIONAL - The right bar text

### Usage

```php
<?php
$source = [
    ['name' => 'Jerry Hsia', 'gender' => 'male', 'age' => 23],
    ['name' => 'Jerry 9916', 'gender' => 'female', 'age' => 24]
];

$fields = [
    'name' => 'Name',
    'age' => 'Age'
];

$exporter = new ExcelExporter([
    'filename' => 'test.xls',
    'fields' => $fields,
    'data' => $source
]);

$file = $exporter->createFile(); // Save file to the disk
$exporter->send(); // Send file to the broswer
```

## TODO

- PdfExporter
- ZipExporter
- ActiveRecordExporter(For yii2 framework)

## Thanks

This library will continue to develop, and if you like it, please star it, Thanks.

