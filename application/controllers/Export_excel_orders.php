<?php
use Models\Mrp\Orders\Mrp_Order;

require_once ("Secure_area.php");
require_once (APPPATH . 'libraries/PHPExcel/PHPExcel.php');
require_once (APPPATH . 'libraries/PHPExcel/PHPExcel/IOFactory.php');

class Export_excel_orders extends Secure_area
{

    protected $dataExcel = [];

    protected $dataMergeCell = [];

    protected $dataMonth = '';

    protected $oPHPExcel;

    protected $colRange;

    function __construct()
    {
        parent::__construct();
        $this->colName = $this->setDefaultListColName();
    }

    public function export($option, $sale_monthly_id)
    {
        $salesOrder = new Mrp_Order();
        $viewData = $salesOrder->get_saved_view($sale_monthly_id);
        $this->oPHPExcel = PHPExcel_IOFactory::createReader('Excel2007');
        $this->oPHPExcel = $this->oPHPExcel->load(FCPATH . '/storage/excelTemplate.xlsx');
        $this->dataExcel = json_decode($viewData['data']);
        $this->dataMergeCell = json_decode($viewData['merge_cell']);
        $this->dataMonth = $viewData['month'];
        switch ($option) {
            case 'forPrint':
                $this->exportExcelForPrint();
                break;
            default:
                exportExcelDefault();
        }
        
        $objWriter = PHPExcel_IOFactory::createWriter($this->oPHPExcel, 'Excel2007');
        $objWriter->setPreCalculateFormulas(true);
        ob_start();
        $objWriter->save('php://output');
        $excelOutput = ob_get_clean();
        $this->load->helper('download');
        force_download("Đơn hàng $this->dataMonth.xlsx", $excelOutput);
    }

    private function exportExcelForPrint()
    {
        $listTotalCol = [];
        $rowTotal = 0;
        $isGetTotal = false;
        $oPHPExcel1 = PHPExcel_IOFactory::createReader('Excel2007')->load(FCPATH . '/storage/excelTemplate.xlsx');
        foreach ($this->dataExcel as $index => $listRowVal) {
            $colStart = 'A';
            foreach ($listRowVal as $colValue) {
                $oPHPExcel1->getActiveSheet()->setCellValue($colStart . '' . ($index + 1), $colValue);
                $colStart ++;
            }
        }
        foreach ($this->dataExcel as $index => $listRowVal) {
            foreach ($listRowVal as $col => $colValue) {
                if ($colValue == 'Total' || $colValue =='Tổng') {
                    $isGetTotal = true;
                    $rowTotal = $index;
                    $listTotalCol[] = $col;
                }
            }
            if ($isGetTotal) {
                break;
            }
        }
        foreach ($this->dataExcel as $index => $listRowVal) {
            $colStart = 'A';
            foreach ($listRowVal as $col => $colValue) {
                if ($index < $rowTotal && ! empty($colValue)) {
                    $this->oPHPExcel->getActiveSheet()->setCellValue($colStart . '' . ($index + 1), $colValue);
                    continue;
                }
                if (in_array($col, $listTotalCol) || $col == 0) {
                    if ($index > $rowTotal) {
                        if (! empty($colValue)) {
                            $colValue = PHPExcel_Calculation::getInstance($oPHPExcel1)->calculateFormula($colValue, 'A1', $oPHPExcel1->getActiveSheet()
                                ->getCell('A1'));
                        }
                        $this->oPHPExcel->getActiveSheet()->setCellValue($colStart . '' . ($index + 1), $colValue);
                    }
                    $colStart ++;
                }
            }
        }
    }

    private function exportExcelDefault()
    {
        foreach ($this->dataExcel as $index => $listRowVal) {
            $colStart = 'A';
            foreach ($listRowVal as $colValue) {
                $this->oPHPExcel->getActiveSheet()->setCellValue($colStart . '' . ($index + 1), $colValue);
                $colStart ++;
            }
        }
        
        foreach ($this->dataMergeCell as $cellMerge) {
            $start = $this->colName[$cellMerge->col] . ($cellMerge->row + 1);
            $end = $this->colName[$cellMerge->col + $cellMerge->colspan - 1] . ($cellMerge->row + 1 + $cellMerge->rowspan - 1);
            $mergeCellRange = $start . ':' . $end;
            $this->oPHPExcel->getActiveSheet()->mergeCells($mergeCellRange);
        }
    }

    private function setDefaultListColName()
    {
        $iCol = 'A';
        $colName = [];
        do {
            $colName[] = $iCol;
        } while ($iCol ++ != 'ZZZZ');
        return $colName;
    }
}
?>