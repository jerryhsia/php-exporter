<?php

namespace jerryhsia;
use jerryhsia\BaseExporter;

/**
 * Class ExcelExporter
 *
 * @property array $fields The fields allowed to export
 * @property array $data The source data
 * @property string $title The title of table
 * @property string $leftBar The left bar text
 * @property string $rightBar The right bar text
 *
 * @package jerryhsia
 * @author Jerry Hsia<xiajie9916@gmail.com>
 */
class ExcelExporter extends BaseExporter {

    public function __construct($config)
    {
        $this->_config = $config;

        if (!is_array($this->data) || !$this->data) {
            throw new \Exception('Data invalid');
        }

        if (!in_array($this->getFileExtension(), ['xls', 'xlsx'])) {
            throw new \Exception('The file extension should be one of xls, xlsx');
        }

        parent::__construct($config);
    }

    protected static $typeMap = [
        'xls' => 'Excel5',
        'xlsx' => 'Excel2007',
        'pdf' => 'PDF'
    ];

    protected $currentRow = 1;

    protected function getPHPExcel()
    {
        if (!$this->phpExcel) {
            $this->phpExcel = new \PHPExcel();
        }
        return $this->phpExcel;
    }

    protected function getActiveSheet()
    {
        return $this->getPHPExcel()->setActiveSheetIndex(0);
    }

    protected function getFieldsMap()
    {
        if (!is_array($this->fieldsMap)) {
            if ($this->fields) {
                $index = 65;
                $map = [];
                foreach ($this->fields as $k => $v) {
                    if ($index >= 91) {
                        $index = 97;
                    }
                    $c = '';
                    if ($index >= 97) {
                        $c = 'A'.strtoupper(chr($index));
                    } else {
                        $c = chr($index);
                    }
                    $map[$k] = $c;
                    $index++;
                }
                $this->fieldsMap = $map;
            }
        }
        return $this->fieldsMap;
    }


    protected function getColNum() {
        if (!$this->colNum) {
            $this->colNum = count($this->fields);
        }
        return $this->colNum;
    }

    protected function writeRow($row, $data)
    {
        foreach ($data as $key => $value) {
            $this->getActiveSheet()->setCellValue($this->getFieldsMap()[$key].$row, $value);
        }
    }

    protected function writeTitle()
    {
        $colNum = $this->getColNum();
        if ($colNum && $this->title) {
            $sheet = $this->getActiveSheet();
            $startAscii = 65; //A
            $firstLineCell = chr($startAscii) . $this->currentRow . ':' . chr($startAscii + $colNum - 1) . $this->currentRow;
            $firstCell = chr($startAscii) . $this->currentRow;

            $sheet->setCellValue($firstCell, $this->title);
            $sheet->mergeCells($firstLineCell);
            $sheet->getStyle($firstCell)->getFont()->setBold(true)->setSize(18);
            $sheet->getStyle($firstCell)->getAlignment()
                ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
                ->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $sheet->getRowDimension($this->currentRow)->setRowHeight(40);
            $this->currentRow++;
        }
    }

    protected function writeBar()
    {
        $colNum = $this->getColNum();
        if ($colNum && ($this->leftBar || $this->rightBar)) {
            $startAscii = 65; //A
            $endAscii = $startAscii + $colNum - 1;
            $sheet = $this->getActiveSheet();

            $middleNum = ($endAscii - ($startAscii + 1))/2;
            $sheet->getRowDimension($this->currentRow)->setRowHeight(25);
            if ($this->leftBar) {
                $secondLeftLineCell = chr($startAscii).($this->currentRow).':'.chr($startAscii + $middleNum).($this->currentRow);
                $secondLeftCell = chr($startAscii).($this->currentRow);

                $sheet->setCellValueExplicit($secondLeftCell, $this->leftBar, \PHPExcel_Cell_DataType::TYPE_STRING);
                $sheet->mergeCells($secondLeftLineCell);
                $sheet->getStyle($secondLeftCell)->getAlignment()
                    ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT)
                    ->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
            }
            if ($this->rightBar) {
                $secondRightLineCell = chr($startAscii + $middleNum + 1).$this->currentRow.':'.chr($endAscii).($this->currentRow);
                $secondRightCell = chr($startAscii + $middleNum + 1).$this->currentRow;
                $sheet->setCellValueExplicit($secondRightCell, $this->rightBar, \PHPExcel_Cell_DataType::TYPE_STRING);
                $sheet->mergeCells($secondRightLineCell);
                $sheet->getStyle($secondRightCell)->getAlignment()
                    ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT)
                    ->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
            }
            $this->currentRow++;
        }
    }

    protected function writeHead()
    {
        if ($this->fields) {
            $this->writeRow($this->currentRow++, $this->fields);
        }
    }

    protected function parseRow($data)
    {
        $row = $data;
        if ($this->filter) {
            $row = call_user_func($this->filter, $data, $row);
        }
        if ($this->fields) {
            $arr = [];
            foreach ($this->fields as $k => $v) {
                $arr[$k] = isset($row[$k]) ? $row[$k] : '';
            }
            $row = $arr;
        }
        return $row;
    }

    protected function writeBody()
    {
        foreach ($this->data as $row) {
            $this->writeRow($this->currentRow++, $this->parseRow($row));
        }
    }

    protected function writeData()
    {
        $this->writeTitle();
        $this->writeBar();
        $this->writeHead();
        $this->writeBody();
    }

    protected function getWriter()
    {
        $title = 'php-exporter';
        $this->getPHPExcel()->getProperties()->setCreator($title)
            ->setLastModifiedBy($title)
            ->setTitle($title)
            ->setSubject($title)
            ->setDescription($title)
            ->setKeywords($title)
            ->setCategory($title);
        $this->getPHPExcel()->getActiveSheet()->setTitle($title);
        $this->getPHPExcel()->setActiveSheetIndex(0);
        $this->writeData();

        return \PHPExcel_IOFactory::createWriter($this->getPHPExcel(), self::$typeMap[$this->getFileExtension()]);
    }

    public function createFile()
    {
        $createdFile = $this->getFilePath();
        @unlink($createdFile);
        $writer = $this->getWriter();
        $writer->save($createdFile);
        return $createdFile;
    }
}
