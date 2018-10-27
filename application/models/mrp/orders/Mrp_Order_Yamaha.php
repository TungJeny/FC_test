<?php
namespace Models\Mrp\Orders;

class Mrp_Order_Yamaha extends Mrp_Order
{

    public function __construct()
    {
        parent::__construct();
        $this->CI->load->model('Item');
    }

    public function save(array $data)
    {
        $this->set_customer('yamaha');
        $data_save = $this->convert_data_to_array($data);
        $this->CI->db->trans_start();
        if ($data['sale_monthly_id']) {
            $result = $this->update($data['sale_monthly_id'], $data_save);
        } else {
            $result = $this->insert($data_save);
        }
        $this->CI->db->trans_complete();
        return $result;
    }

    private function convert_data_to_array(array $data)
    {
        $month = $data['month'];
        $merge_cells = json_decode($data['merge_cell']);
        $date_col = 0;
        $port_row = 3;
        $product_id_row = 2;
        $product_id_col = 1;
        $forecast_row = $data['end_row_body'] + 7;
        $sales_monthly = $this->correct_monthly_data($month);
        $sales_daily = [];
        $sales_forecast = [];
        $sale_forecast_items = [];
        
        for ($row = 4; $row <= $data['end_row_body']; $row ++) {
            $sales_items = [];
            $product_id_col = 1;
            while ($product_id_col < count($data['data_save'][$row])) {
                $colspan = 1;
                foreach ($merge_cells as $merge_cell) {
                    if ($product_id_col == $merge_cell->col && $product_id_row == $merge_cell->row) {
                        $colspan = $merge_cell->colspan;
                        break;
                    }
                }
                $item = $this->CI->Item->get_item_by_product_id_or_name($data['data_save'][$product_id_row][$product_id_col]);
                if (empty($item['item_id'])) {
                    $product_id_col = $product_id_col + $colspan;
                    continue;
                }
                $item_id = $item['item_id'];
                $end_col = $date_col + $colspan + $product_id_col;
                for ($col = $date_col + $product_id_col; $col < $end_col; $col ++) {
                    if (! empty($data['data_save'][$row][$col]) && $data['data_save'][$port_row][$col] != 'Tổng') {
                        $type = $this->get_item_type_by_value($data['data_save'][$port_row][$col]);
                        $sales_items[$item_id][$data['data_save'][$port_row][$col]] = [
                            'qty' => $data['data_save'][$row][$col],
                            'type' => $type
                        ];
                    }
                }
                $product_id_col = $product_id_col + $colspan;
            }
            $sales_daily[date('Y-m-d', strtotime(str_replace('/', '-', $data['data_save'][$row][$date_col])))] = $sales_items;
        }
        $forecast_time = str_replace('/', '-', trim($data['data_save'][$forecast_row][$date_col], 'T'));
        $time_parts = explode('-', $forecast_time);
        if (! empty($time_parts) && count($time_parts) == 2) {
            $forecast_time = $time_parts[1] . '-' . str_pad($time_parts[0], 2, '0', STR_PAD_LEFT);
        }
        
        while (! empty($forecast_time) && date_create_from_format('Y-m', $forecast_time)) {
            $sales_forecast_items = [];
            $product_id_col = 1;
            while ($product_id_col < count($data['data_save'][$forecast_row])) {
                $colspan = 1;
                foreach ($merge_cells as $merge_cell) {
                    if ($product_id_col == $merge_cell->col && $product_id_row == $merge_cell->row) {
                        $colspan = $merge_cell->colspan;
                        break;
                    }
                }
                $item = $this->CI->Item->get_item_by_product_id_or_name($data['data_save'][$product_id_row][$product_id_col]);
                if (empty($item['item_id'])) {
                    $product_id_col = $product_id_col + $colspan;
                    continue;
                }
                $item_id = $item['item_id'];
                $end_col = $date_col + $colspan + $product_id_col;
                for ($col = $date_col + $product_id_col; $col <= $end_col; $col ++) {
                    
                    if (! empty($data['data_save'][$forecast_row][$col])) {
                        $type = $this->get_item_type_by_value($data['data_save'][$port_row][$col]);
                        $sales_forecast_items[$item_id][$data['data_save'][$port_row][$col]] = [
                            'qty' => $data['data_save'][$forecast_row][$col],
                            'type' => $type
                        ];
                    }
                }
                $product_id_col = $product_id_col + $colspan;
            }
            $sales_forecast[$forecast_time] = $sales_forecast_items;
            if (! empty($data['data_save'][++ $forecast_row][$date_col])) {
                $forecast_time = str_replace('/', '-', trim($data['data_save'][$forecast_row][$date_col], 'T'));
            }
            if (! empty($forecast_time)) {
                $time_parts = explode('-', $forecast_time);
                if (! empty($time_parts)) {
                    $forecast_time = $time_parts[1] . '-' . str_pad($time_parts[0], 2, '0', STR_PAD_LEFT);
                }
            }
        }
        $sales_view = $this->convert_data_to_array_view($data);
        if (empty($sales_forecast)) {
            echo json_encode([
                'status' => 'error',
                'msg' => 'Không có thông tin forecast'
            ]);
            die();
        }
        return [
            'sales_monthly' => $sales_monthly,
            'sales_daily' => $sales_daily,
            'sales_view' => $sales_view,
            'sales_forecast' => $sales_forecast
        ];
    }

    protected function get_item_type_by_value($value)
    {
        if (create_slug($value) == 'xuat_khau') {
            return 'export';
        }
        if (strtolower(substr($value, 0, 4)) === 'type' || $value == 'SP') {
            return $value;
        }
        return 'port';
    }

    private function convert_data_to_array_view(array $data)
    {
        return [
            'sale_monthly_id' => $data['sale_monthly_id'],
            'data' => json_encode($data['data_save']),
            'merge_cell' => $data['merge_cell'],
            'cell' => $data['cell'],
            'end_row_body' => $data['end_row_body']
        
        ];
    }
}