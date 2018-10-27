<?php
require_once (APPPATH . 'libraries/PHPExcel/PHPExcel.php');

class ExcelProcessor
{

    protected $oPHPExcel;

    protected $file;

    public function __construct($file = '')
    {
        $this->file = FCPATH . '/storage/' . $file;
        if (is_file($this->file)) {
            $this->oPHPExcel = PHPExcel_IOFactory::createReader('Excel2007');
            $this->oPHPExcel = $this->oPHPExcel->load($this->file);
        }
    }

    public function convert_2_array($option = '')
    {
        $allData = [];
        $worksheet = $this->oPHPExcel->getActiveSheet();
        foreach ($worksheet->getRowIterator() AS $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(FALSE); // This loops through all cells,
            $cells = [];
            foreach ($cellIterator as $cell) {
                if ($option == 'formula') {
                    $cells[] = $cell->getValue();
                } else {
                    $cells[] = $cell->getCalculatedValue();
                }
                
            }
            $allData[] = $cells;
        }
        return $allData;
    }
}
?>