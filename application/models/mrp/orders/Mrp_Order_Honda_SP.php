<?php
namespace Models\Mrp\Orders;

class Mrp_Order_Honda_SP extends Mrp_Order
{

    public function __construct()
    {
        parent::__construct();
        $this->CI->load->model('Item');
    }

    public function save(array $data)
    {
        $this->set_customer('honda');
        $_SESSION['order_for'] = 'sp';
        $data_save = $this->convert_data_to_array($data);
        $this->CI->db->trans_start();
        if ($data['sale_monthly_id']) {
            $result = $this->update($data['sale_monthly_id'], $data_save, false);
        } else {
            $result = $this->insert($data_save, false);
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
        $sales_monthly = $this->correct_monthly_data($month);
        $sales_daily = [];
        $sales_forecast = [];

        for ($row = 4; $row <= $data['end_row_body']; $row ++) {
            $sales_items = [];
            $product_id_col = 1;
            while ($product_id_col < count($data['data_save'][$row])) {
                
                $item = $this->CI->Item->get_item_by_product_id_or_name($data['data_save'][$product_id_row][$product_id_col]);
                if (empty($item['item_id'])) {
                    $product_id_col++;
                    continue;
                }
                if (!empty($data['data_save'][$row][$product_id_col])) {
                    $sales_items[$item['item_id']]['no_port'] = [
                        'qty' => $data['data_save'][$row][$product_id_col],
                        'type' => 'no_type'
                    ];
                }
                $product_id_col++;
            }
            $sales_daily[date('Y-m-d', strtotime(str_replace('/', '-', $data['data_save'][$row][$date_col])))] = $sales_items;
        }
        $sales_view = $this->convert_data_to_array_view($data);
        return [
            'sales_monthly' => $sales_monthly,
            'sales_daily' => $sales_daily,
            'sales_view' => $sales_view,
            'sales_forecast' => $sales_forecast
        ];
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