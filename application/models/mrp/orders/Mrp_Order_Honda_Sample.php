<?php
namespace Models\Mrp\Orders;

class Mrp_Order_Honda_Sample extends Mrp_Order
{

    public function __construct()
    {
        parent::__construct();
    }

    public function save($data)
    {
        $this->set_customer('honda');
        $_SESSION['order_for'] = 'sample';
        $dataSave = $this->prepareSave($data);
        $this->CI->db->trans_start();
        if ($data['sale_monthly_id']) {
            $result = $this->update($data['sale_monthly_id'], $dataSave, false);
        } else {
            $result = $this->insert($dataSave, false);
        }
        $this->CI->db->trans_complete();
        return $result;
    }

    private function prepareSave($data)
    {
        $row = 3;
        $sales_daily = [];
        $sales_monthly = $this->correct_monthly_data(date('Y-m', strtotime(explode('T', $data['month'])[0])));
        for ($row = 3; $row < count($data['data_save']); $row ++) {
            $startMergeCol = 6;
            if (empty($data['data_save'][$row][1])) {
                continue;
            }
            foreach ($data['merge_cell'] as $mergeCell) {
                if (empty($data['data_save'][1][$startMergeCol])) {
                    break;
                }
                if ($mergeCell->col == $startMergeCol && $mergeCell->row == 1) {
                    for ($i = $startMergeCol; $i < $mergeCell->colspan + $startMergeCol; $i ++) {
                        preg_match('#\((.*?)\)#', $data['data_save'][2][$i], $match);
                        if (empty($data['data_save'][2][$i]) || empty($data['data_save'][$row][$i]) || empty($match[1])) {
                            continue;
                        }
                        $item = $this->CI->Item->get_item_by_product_id_or_name($data['data_save'][$row][1]);
                        if (empty($item['item_id'])) {
                            continue;
                        }
                        $sales_items[$item['item_id']][$data['data_save'][1][$startMergeCol]] = [
                            'qty' => $data['data_save'][$row][$i],
                            'type' => preg_replace("/\(([^()]*+|(?R))*\)/", "", $data['data_save'][2][$i])
                        ];
                        $listDate = explode(',' ,str_replace([
                            'giao',
                            ' '
                        ], [
                            '',
                            ''
                        ], $match[1]));
                        foreach ($listDate as $date) {
                            $sales_daily[date('Y-m-d', strtotime(str_replace('/', '-', $date)))] = $sales_items;
                        }
                    }
                    $startMergeCol += $startMergeCol;
                }
            }
        }
        
        $sales_view = $this->convert_data_to_array_view($data);
        return [
            'sales_monthly' => $sales_monthly,
            'sales_daily' => $sales_daily,
            'sales_view' => $sales_view,
            'sales_forecast' => []
        ];
    }

    private function convert_data_to_array_view(array $data)
    {
        return [
            'sale_monthly_id' => $data['sale_monthly_id'],
            'data' => json_encode($data['data_save']),
            'merge_cell' => json_encode($data['merge_cell']),
        ];
    }
}