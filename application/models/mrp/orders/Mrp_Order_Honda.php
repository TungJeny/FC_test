<?php
namespace mrp_order_model;

use Models\Mrp\Mrp_Customer;
use Models\Mrp\Orders\Mrp_Order;

class Mrp_Order_Honda extends Mrp_Order
{

    protected $excel_data = [];

    protected $mrp_customer;

    private $col_alphas;

    private $handle_table = [];

    private $merge_cell = [];

    private $total_row_val_each_col = 0;

    private $cell_item = [];

    private $cell_port = [];

    private $cell_type = [];

    private $cell_value = [];

    public function __construct()
    {
        parent::__construct();
        $this->mrp_customer = new Mrp_Customer();
    }

    public function set_excel_calculated_data($data = [])
    {
        $this->excel_data = $data;
        return $this;
    }

    public function upload()
    {
        $i_col = 'A';
        do {
            $this->col_alphas[] = $i_col;
        } while ($i_col ++ != 'ZZZ');
        $end_date = '';
        $msg = '';
        $item_col_excel = 2;
        $port_col_excel = 6;
        $type_col_excel = 7;
        $date_start_col_excel = 10;
        $num_row = count($this->excel_data[0]) + 10;
        $this->handle_table = array_fill(0, $num_row, []);
        foreach ($this->excel_data[1] as $key => $row_header) {
            switch (trim($row_header)) {
                case 'PS_CD':
                    $port_col_excel = $key;
                    break;
                case 'ODR_TYP':
                    $type_col_excel = $key;
                    break;
                case 'PT_NO':
                    $item_col_excel = $key;
                    break;
            }
            if (ctype_digit(trim($row_header)) && (int) $row_header > 0) {
                $date_start_col_excel = $key;
                break;
            }
        }
        $data_excel_row_one = $this->excel_data[1];
        end($data_excel_row_one);
        $end_date = prev($data_excel_row_one);
        $month_before = ((int)substr($end_date, 4, 2) - 1) > 9 ? (int)substr($end_date, 4, 2) - 1: '0'.((int)substr($end_date, 4, 2) - 1);
        for ($row_num = 2; $row_num < count($this->excel_data); $row_num ++) {
            if (! empty($this->excel_data[$row_num][$item_col_excel])) {
                $item = $this->CI->Item->get_item_by_product_id_or_name($this->excel_data[$row_num][$item_col_excel]);
                if (empty($item) || empty($item['product_id'])) {
                    $status = 'error';
                    if (strpos($msg, $this->excel_data[$row_num][$item_col_excel]) == false) {
                        $msg .= 'Không có thông tin sản phẩm ' . $this->excel_data[$row_num][$item_col_excel] . ".<br>\n";
                    }
                    $item['product_id'] = $this->excel_data[$row_num][$item_col_excel];
                }
                for ($col_key = $date_start_col_excel; $col_key < count($this->excel_data[1]); $col_key ++) {
                    if ($this->excel_data[1][$col_key] < (int)substr($end_date, 0, 4).$month_before. '26') {
                        $date_start_col_excel = $col_key+1;
                        continue;
                    }
                    if ($this->excel_data[1][$col_key] == 'TOTAL') {
                        $this->total_row_val_each_col = $col_key - $date_start_col_excel;
                        break;
                    }
                    if (! empty($item['product_id'])) {
                        if (array_key_exists(str_replace(' ', '', $item['product_id']) . '_' . $this->excel_data[$row_num][$port_col_excel] . '_' . $this->excel_data[$row_num][$type_col_excel]. '_' . $this->excel_data[1][$col_key], $this->cell_value)) {
                            $this->cell_value[str_replace(' ', '', $item['product_id']) . '_' . $this->excel_data[$row_num][$port_col_excel] . '_' . $this->excel_data[$row_num][$type_col_excel] . '_' . $this->excel_data[1][$col_key]] += $this->excel_data[$row_num][$col_key];
                        } else {
                            $this->cell_value[str_replace(' ', '', $item['product_id']) . '_' . $this->excel_data[$row_num][$port_col_excel] . '_' . $this->excel_data[$row_num][$type_col_excel] . '_' . $this->excel_data[1][$col_key]] = $this->excel_data[$row_num][$col_key];
                        }
                    }
                }
                
                $key = '' . $item['product_id'];
                $this->cell_item[$key][$this->excel_data[$row_num][$port_col_excel] . '_' . $this->excel_data[$row_num][$type_col_excel]] = $item['product_id'];
                $this->cell_port[str_replace(' ', '', $item['product_id']) . '_' . $this->excel_data[$row_num][$port_col_excel]][$this->excel_data[$row_num][$type_col_excel]] = '1';
                $this->cell_type[str_replace(' ', '', $item['product_id']) . '_' . $this->excel_data[$row_num][$port_col_excel] . '_' . $this->excel_data[$row_num][$type_col_excel]] = $this->excel_data[$row_num][$type_col_excel];
            }
        }
        $handle_table_row = 4;
        for ($col_key = $date_start_col_excel; $col_key < count($this->excel_data[1]); $col_key ++) {
            if ($this->excel_data[1][$col_key] == 'TOTAL') {
                break;
            }
            if ($this->excel_data[1][$col_key] < (int)substr($end_date, 0, 4).$month_before. '26') {
                continue;
            }
            $this->handle_table[$handle_table_row ++][0] = date('d/m/Y', strtotime($this->excel_data[1][$col_key]));
        }
        $total_qty_row_num = $handle_table_row;
        $this->handle_table[$handle_table_row ++][0] = 'Tổng';
        $this->handle_table[$handle_table_row ++][0] = 'Đơn giá';
        $this->handle_table[$handle_table_row ++][0] = 'Thành Tiền';
        $this->handle_table[$handle_table_row ++][0] = 'Revise';
        $this->handle_table[$handle_table_row ++][0] = 'Chênh lệch';
        $next_month_number = (int) date('m', strtotime($end_date)) + 1;
        $next_2month_number = $next_month_number + 1;
        $next_3month_number = $next_2month_number + 1;
        
        $current_year = (int) date('Y', strtotime($end_date));
        $current_year_next_month = (int) date('Y', strtotime($end_date));
        $current_year_next_2month = (int) date('Y', strtotime($end_date));
        $current_year_next_3month = (int) date('Y', strtotime($end_date));
        if ($next_month_number > 12) {
            $next_month_number = 1;
            $current_year_next_month += 1;
            $current_year_next_2month += 1;
            $current_year_next_3month += 1;
            $next_2month_number = $next_month_number + 1;
            $next_3month_number = $next_2month_number + 1;
        }
        if ($next_2month_number > 12) {
            $next_2month_number = 1;
            $current_year_next_2month += 1;
            $current_year_next_3month += 1;
            $next_3month_number = $next_2month_number + 1;
        }
        if ($next_3month_number > 12) {
            $next_3month_number = 1;
            $current_year_next_3month += 1;
        }
        $this->handle_table[$handle_table_row ++][0] = 'T' . $next_month_number . '/' . $current_year_next_month;
        $this->handle_table[$handle_table_row ++][0] = 'T' . $next_2month_number . '/' . $current_year_next_2month;
        $this->handle_table[$handle_table_row ++][0] = 'T' . $next_3month_number . '/' . $current_year_next_3month;
        
        $this->cell_item = array_map(function ($value) {
            return count($value) + 2;
        }, $this->cell_item);
        $this->cell_port = array_map(function ($value1) {
            return count($value1);
        }, $this->cell_port);
        ksort($this->cell_item);
        ksort($this->cell_port);
        ksort($this->cell_type);
        ksort($this->cell_value);
        
        // handle table
        $col_item = 1;
        $list_col_total = [];
        $list_col_sp = [];
        // merge cell fo item
        foreach ($this->cell_item as $colspan_item) {
            $cell_merge = new \stdClass();
            $cell_merge->row = 1;
            $cell_merge->col = $col_item;
            $cell_merge->rowspan = 1;
            $cell_merge->colspan = $colspan_item;
            $this->merge_cell[] = clone $cell_merge;
            $cell_merge->row = 2;
            $cell_merge->col = $col_item + $colspan_item - 2;
            $cell_merge->rowspan = 2;
            $cell_merge->colspan = 1;
            $this->merge_cell[] = clone $cell_merge;
            $list_col_sp[] = $col_item + $colspan_item - 2;
            $cell_merge->row = 2;
            $cell_merge->col = $col_item + $colspan_item - 1;
            $cell_merge->rowspan = 2;
            $cell_merge->colspan = 1;
            $this->merge_cell[] = $cell_merge;
            $list_col_total[] = $col_item + $colspan_item - 1;
            $col_item += $colspan_item;
        }
        $col_port = 1;
        // merge cell for port
        foreach ($this->cell_port as $colspan) {
            if ($colspan != 1) {
                $cell_merge = new \stdClass();
                $cell_merge->row = 2;
                $cell_merge->col = $col_port;
                $cell_merge->rowspan = 1;
                $cell_merge->colspan = $colspan;
                $this->merge_cell[] = $cell_merge;
            }
            if (in_array(($col_port + $colspan), $list_col_sp)) {
                $col_port += $colspan + 2;
            } else {
                $col_port += $colspan;
            }
        }
        $col_item = 1;
        $this->handle_table[0][] = null;
        $this->handle_table[1][0] = null;
        
        foreach ($this->cell_item as $item_name => $colspan) {
            $this->handle_table[1][$col_item] = str_replace(' ', '', $item_name);
            for ($i = 1; $i <= $colspan; $i ++) {
                $this->handle_table[0][] = null;
                $this->handle_table[1][$col_item + $i] = null;
            }
            $col_item += $colspan;
        }
        $col_port = 1;
        
        foreach ($this->cell_port as $port => $port_colspan) {
            $this->handle_table[2][0] = null;
            $this->handle_table[2][$col_port] = explode('_', $port)[1];
            for ($i = 1; $i <= $port_colspan; $i ++) {
                $this->handle_table[2][$col_port + $i] = null;
            }
            if (in_array(($col_port + $port_colspan), $list_col_sp)) {
                $this->handle_table[2][$col_port + $port_colspan] = 'SP';
                $this->handle_table[2][$col_port + $port_colspan + 1] = 'Total';
                $col_port += $port_colspan + 2;
                continue;
            }
            $col_port += $port_colspan;
        }
        $col_type = 1;
        $this->handle_table[3][0] = null;
        foreach ($this->cell_type as $type) {
            $this->handle_table[3][$col_type] = 'Type ' . $type;
            if (in_array(($col_type + 1), $list_col_sp)) {
                $this->handle_table[3][$col_type + 1] = null;
                $this->handle_table[3][$col_type + 2] = null;
                $col_type += 3;
                continue;
            }
            $col_type ++;
        }
        $handle_table_row = 4;
        $col_value = 1;
        $first = $this->cell_item;
        $loop_count = 0;
        foreach ($this->cell_value as $value) {
            $has_total = false;
            if (in_array(($col_value + 1), $list_col_sp)) {
                $has_total = true;
                $this->handle_table[$handle_table_row][$col_value + 1] = 0;
                $this->handle_table[$handle_table_row][$col_value + 2] = '=SUM(' . $this->col_alphas[$col_value + 3 - current($first)] . ($handle_table_row + 1) . ':' . $this->col_alphas[$col_value + 1] . ($handle_table_row + 1) . ')';
            }
            $this->handle_table[$handle_table_row][$col_value] = 0;
            if (! empty($value)) {
                $this->handle_table[$handle_table_row][$col_value] = $value;
            }
            $handle_table_row ++;
            $loop_count ++;
            if (count($this->cell_value) == $loop_count) {
                for ($i = 4; $i < $handle_table_row; $i ++) {
                    ksort($this->handle_table[$i]);
                }
            }
            if (($loop_count % $this->total_row_val_each_col) == 0) {
                if ($has_total) {
                    $col_value += 3;
                    next($first);
                } else {
                    $col_value ++;
                }
                $handle_table_row = 4;
            }
        }
        $col_letter = 'B';
        for ($i = 1; $i <= count($this->handle_table[0]); $i ++) {
            $sumRowStart = 5;
            $this->handle_table[$total_qty_row_num + 3][$i] = 0;
            for ($sumRowStart = 5; $sumRowStart <= ($this->total_row_val_each_col + 4); $sumRowStart ++) {
                if (empty($this->handle_table[$sumRowStart][$i])) {
                    $this->handle_table[$total_qty_row_num + 3][$i] += 0;
                } elseif (!is_numeric($this->handle_table[$sumRowStart][$i])) {
                    $this->handle_table[$total_qty_row_num + 3][$i] += 0;
                } else {
                    $this->handle_table[$total_qty_row_num + 3][$i] += $this->handle_table[$sumRowStart][$i];
                }
            }
            $this->handle_table[$total_qty_row_num][$i] = '=SUM(' . $col_letter . '5:' . $col_letter . ($this->total_row_val_each_col + 4) . ')';
            $this->handle_table[$total_qty_row_num + 2][$i] = '=' . $col_letter . ($total_qty_row_num + 1) . '*' . $col_letter . ($total_qty_row_num + 2);
            $this->handle_table[$total_qty_row_num + 4][$i] = '=' . $col_letter . ($total_qty_row_num + 1) . '-' . $col_letter . ($total_qty_row_num + 4);
            $col_letter ++;
        }
        
        echo json_encode([
            'data' => $this->handle_table,
            'mergeCells' => $this->merge_cell,
            'end_date' => date('Y-m-d', strtotime($end_date)),
            'total_qty_row_num' => $total_qty_row_num
        ]);
        die();
    }

    public function save($data)
    {
        $this->set_customer('honda');
        $_SESSION['order_for'] = $data['order_for'];
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
        $status = 'success';
        $msg = '';
        $month = $data['month'];
        $merge_cells = json_decode($data['merge_cell']);
        $date_col = 0;
        $port_row = 2;
        $type_row = 3;
        $product_id_row = 1;
        $product_id_col = 1;
        $forecast_row = $data['end_row_body'] + 6;
        $sales_monthly = $this->correct_monthly_data($month);
        $sales_daily = [];
        $sales_forecast = [];
        $sale_forecast_items = [];
        for ($row = 4; $row <= $data['end_row_body']; $row ++) {
            $sales_items = [];
            $product_id_col = 1;
            while ($product_id_col < count($data['data_save'][$row])) {
                $colspan = 1;
                $item = $this->CI->Item->get_item_by_product_id_or_name($data['data_save'][$product_id_row][$product_id_col]);
                if (empty($item) || empty($item['product_id'])) {
                    $status = 'error';
                    if (strpos($msg, $data['data_save'][$product_id_row][$product_id_col]) == false) {
                        $msg .= 'Không có thông tin mã sản phẩm ' . $data['data_save'][$product_id_row][$product_id_col] . " Vui lòng cập nhật. <br>\n";
                    }
                }
                foreach ($merge_cells as $merge_cell) {
                    if ($product_id_col == $merge_cell->col && $product_id_row == $merge_cell->row) {
                        $colspan = $merge_cell->colspan;
                        break;
                    }
                }
                $end_col = $date_col + $colspan + $product_id_col;
                $port = '';
                for ($col = $date_col + $product_id_col; $col < $end_col; $col ++) {
                    if (! empty($data['data_save'][$port_row][$col]) && $data['data_save'][$port_row][$col] == 'SP') {
                        $data['data_save'][$type_row][$col] = 'sp';
                    }
                    if (! empty($data['data_save'][$row][$col]) && ! empty($item_id) && ! empty($data['data_save'][$type_row][$col])) {
                        if (! empty($data['data_save'][$port_row][$col])) {
                            $port = $data['data_save'][$port_row][$col];
                        }
                        $sales_items[$item_id][$port] = [
                            'qty' => $data['data_save'][$row][$col],
                            'type' => $data['data_save'][$type_row][$col]
                        ];
                    }
                }
                $product_id_col = $product_id_col + $colspan;
            }
            $sales_daily[date('Y-m-d', strtotime(str_replace('/', '-', $data['data_save'][$row][$date_col])))] = $sales_items;
        }
        $forecast_time = str_replace('/', '-', trim($data['data_save'][$forecast_row][$date_col], 'T'));
        if ($status == 'error') {
            echo json_encode([
                'status' => $status,
                'msg' => $msg
            ]);
            die();
        }
        
        if (empty($forecast_time)) {
            echo json_encode([
                'status' => 'error',
                'msg' => 'Không có thông tin forecast'
            ]);
            die();
        }
        $time_parts = explode('-', $forecast_time);
        $forecast_time = $time_parts[1] . '-' . str_pad($time_parts[0], 2, '0', STR_PAD_LEFT);
        
        while (! empty($forecast_time)) {
            $sales_forecast_items = [];
            $product_id_col = 1;
            while ($product_id_col < count($data['data_save'][$forecast_row])) {
                $colspan = 1;
                $item = $this->CI->Item->get_item_by_product_id_or_name($data['data_save'][$product_id_row][$product_id_col]);
                if (empty($item['item_id'])) {
                    continue;
                }
                $item_id = $item['item_id'];
                foreach ($merge_cells as $merge_cell) {
                    if ($product_id_col == $merge_cell->col && $product_id_row == $merge_cell->row) {
                        $colspan = $merge_cell->colspan;
                        break;
                    }
                }
                $end_col = $colspan + $product_id_col;
                
                for ($col = $product_id_col; $col <= $end_col; $col ++) {
                    
                    if (! empty($data['data_save'][$forecast_row][$col])) {
                        if (! empty($data['data_save'][$port_row][$col])) {
                            $port = $data['data_save'][$port_row][$col];
                        }
                        $type = $data['data_save'][$type_row][$col];
                        $sales_forecast_items[$item_id][$port] = [
                            'qty' => $data['data_save'][$forecast_row][$col],
                            'type' => $type
                        ];
                    }
                }
                $product_id_col = $product_id_col + $colspan;
            }
            $sales_forecast[$forecast_time] = $sales_forecast_items;
            $forecast_time = '';
            if ($data['data_save'][++ $forecast_row]) {
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
            'sales_forecast' => $sales_forecast,
            'order_for' => $data['order_for']
        ];
    }

    private function convert_data_to_array_view(array $data)
    {
        return [
            'sale_monthly_id' => $data['sale_monthly_id'],
            'data' => json_encode($data['data_save']),
            'merge_cell' => $data['merge_cell'],
            'cell' => '',
            'end_row_body' => $data['end_row_body']
        
        ];
    }
}
