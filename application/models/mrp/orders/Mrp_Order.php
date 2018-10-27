<?php
namespace Models\Mrp\Orders;

use Models\Mrp\Mrp_Model;
use Models\Mrp\Mrp_Sales_Daily;
use Models\Mrp\Mrp_Sales_Monthly;
use Models\Mrp\Mrp_Sales_Forecast_Items;
use Models\Mrp\Mrp_Sales_View;
use Models\Mrp\Mrp_Customer;
use Models\Mrp\Mrp_Item_Bom;
use Models\Mrp\Mrp_Sales_Items;
use Models\Mrp\Mrp_Production_Planning;

class Mrp_Order extends Mrp_Model
{

    private $customer;

    protected $mrp_sales_daily;

    protected $mrp_sales_monthly;

    protected $mrp_sales_items;

    protected $mrp_sales_customer;

    protected $mrp_sales_forecast_items;

    protected $mrp_sales_view;

    protected $logged_in_employee;

    public function __construct()
    {
        parent::__construct();
        $this->CI->load->model('Employee');
        $this->CI->load->model('Item');
        $this->mrp_sales_daily = new Mrp_Sales_Daily();
        $this->mrp_sales_monthly = new Mrp_Sales_Monthly();
        $this->mrp_sales_items = new Mrp_Sales_Items();
        $this->mrp_sales_customer = new Mrp_Customer();
        $this->mrp_sales_forecast_items = new Mrp_Sales_Forecast_Items();
        $this->mrp_sales_view = new Mrp_Sales_View();
        $this->logged_in_employee = $this->CI->Employee->get_logged_in_employee_info();
    }

    public function get_all_materials_by_month($months = [], $categories = [])
    {
        $order_items = (new Mrp_Sales_Items())->get_by_month($months);
        
        $items_planning = (new Mrp_Production_Planning())->get_detail(reset($months))['detail'];
        
        foreach ($items_planning as $item_planning) {
            $total = 0;
            foreach ($item_planning as $key => $val) {
                $total += $val;
            }
            $item_planning['total_qty'] = $total;
        }
        
        $bom_model = new Mrp_Item_Bom();
        $materials_raw = [];
        foreach ($order_items as $item) {
            $materials_raw = array_merge($materials_raw, $bom_model->calculate_materials($item['item_id'], $item['qty'], $categories));
        }
        
        $materials = [];
        foreach ($materials_raw as $material_raw) {
            if (array_key_exists($material_raw['material_id'], $materials)) {
                $materials[$material_raw['material_id']]['calculated_by_qty'] += $material_raw['calculated_by_qty'];
                $materials[$material_raw['material_id']]['calculated_by_unit'] += $material_raw['calculated_by_unit'];
            } else {
                unset($material_raw['rate_of_qty']);
                unset($material_raw['rate_of_unit']);
                $materials[$material_raw['material_id']] = $material_raw;
            }
        }
        return $materials;
    }

    public function get_forecast_materials_by_months($est_months = [], $categories = [])
    {
        $forecast_items = (new Mrp_Sales_Items())->get_forecast_item($est_months);
        $forecasts = [];
        foreach ($forecast_items as $item) {
            $forecasts[$item['month']][] = $item;
        }
        $bom_model = new Mrp_Item_Bom();
        
        $forecast_materials = [];
        foreach ($forecasts as $month => $items) {
            $materials = [];
            $materials_raw = [];
            foreach ($items as $item) {
                if (! empty($item['item_id'])) {
                    $materials_raw = array_merge($materials_raw, $bom_model->calculate_materials($item['item_id'], $item['qty'], $categories));
                }
            }
            foreach ($materials_raw as $material_raw) {
                if (array_key_exists($material_raw['material_id'], $materials)) {
                    $materials[$material_raw['material_id']]['calculated_by_qty'] += $material_raw['calculated_by_qty'];
                    $materials[$material_raw['material_id']]['calculated_by_unit'] += $material_raw['calculated_by_unit'];
                } else {
                    unset($material_raw['rate_of_qty']);
                    unset($material_raw['rate_of_unit']);
                    $materials[$material_raw['material_id']] = $material_raw;
                }
            }
            $forecast_materials[$month] = $materials;
        }
        return $forecast_materials;
    }

    public function get_all_forecast_materials_by_months($months = [], $categories = [])
    {
        $forecast_items = (new Mrp_Sales_Items())->get_forecast_item($months);
        $bom_model = new Mrp_Item_Bom();
        $materials_raw = [];
        foreach ($forecast_items as $item) {
            $materials_raw = array_merge($materials_raw, $bom_model->calculate_materials($item['item_id'], $item['qty'], $categories));
        }
        
        $materials = [];
        foreach ($materials_raw as $material_raw) {
            if (array_key_exists($material_raw['material_id'], $materials)) {
                $materials[$material_raw['material_id']]['calculated_by_qty'] += $material_raw['calculated_by_qty'];
                $materials[$material_raw['material_id']]['calculated_by_unit'] += $material_raw['calculated_by_unit'];
            } else {
                unset($material_raw['rate_of_qty']);
                unset($material_raw['rate_of_unit']);
                $materials[$material_raw['material_id']] = $material_raw;
            }
        }
        
        return $materials;
    }

    protected function set_customer($customer = '')
    {
        $this->customer = $customer;
    }

    public function get_saved_view($sale_monthly_id)
    {
        return $this->CI->db->select('sale_monthly_id, data, merge_cell,mrp_sales_monthly.month, cell, end_row_body')
            ->from('mrp_sales_view')
            ->join('mrp_sales_monthly', 'mrp_sales_monthly.id = mrp_sales_view.sale_monthly_id')
            ->where('sale_monthly_id', $sale_monthly_id)
            ->get()
            ->row_array();
    }

    protected function correct_monthly_data($month = '')
    {
        $order_for = '';
        if ($this->customer == 'honda') {
            $order_for = $this->session->userdata('order_for');
            $this->session->unset_userdata('order_for');
        }
        return [
            'month' => $month,
            'employee_id' => $this->logged_in_employee->id,
            'type' => 'po',
            'order_for' => $order_for,
            'customer_id' => $this->mrp_sales_customer->get_id_by_code($this->customer),
            'status' => '',
            'po_monthly_id' => '',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        
        ];
    }

    protected function insert(array $data, $forecast_check = true)
    {
        $converted_data = $this->convert_data($data, $forecast_check);
        if ($sales_po_monthly_id = $this->mrp_sales_monthly->save($converted_data['sales_monthly'])) {
            
            $this->mrp_sales_monthly->delete_sales_monthly_by_month($converted_data['sales_monthly']['month'], $sales_po_monthly_id, $converted_data['sales_monthly']['customer_id'], $converted_data['sales_monthly']['order_for']);
            // sale daily, items
            $sales_daily = $this->add_sales_monthly_to_sales_daily($converted_data['sales_daily'], $sales_po_monthly_id);
            $list_id_sales_daily = $this->mrp_sales_daily->save_batch($sales_daily);
            $sales_items = $this->add_sales_daily_to_sales_items($converted_data['sales_items'], $list_id_sales_daily);
            $this->mrp_sales_items->save_batch($sales_items);
            // view
            $this->mrp_sales_view->save($this->mrp_sales_view->add_sale_monthly_id_to_sale_view($sales_po_monthly_id, $converted_data['sales_view']));
            // forecast, forecast items
            if ($forecast_check) {
                $sale_forecast_monthly = $this->add_sales_po_monthly_id_to_sale_forecast($converted_data['sales_forecast_monthly'], $sales_po_monthly_id);
                $list_id_sales_forecast = $this->mrp_sales_monthly->save_batch($sale_forecast_monthly);
                $sales_forecast_items = $this->add_sales_forecast_monthly_to_sales_forecast_items($converted_data['sales_forecast_items'], $list_id_sales_forecast);
                $this->mrp_sales_forecast_items->save_batch($sales_forecast_items);
            }
            return true;
        }
        return false;
    }

    protected function update($sales_po_monthly_id, array $data, $forecast_check = true)
    {
        $converted_data = $this->convert_data($data, $forecast_check);
        if ($sales_po_monthly_id) {
            $this->delete_sale_daily_and_item_by_month($sales_po_monthly_id);
            // insert sale daily, items
            $sales_daily = $this->add_sales_monthly_to_sales_daily($converted_data['sales_daily'], $sales_po_monthly_id);
            $list_id_sales_daily = $this->mrp_sales_daily->save_batch($sales_daily);
            $sales_items = $this->add_sales_daily_to_sales_items($converted_data['sales_items'], $list_id_sales_daily);
            $this->mrp_sales_items->save_batch($sales_items);
            $sales_view = $this->mrp_sales_view->add_sale_monthly_id_to_sale_view($sales_po_monthly_id, $converted_data['sales_view']);
            $this->mrp_sales_view->save($sales_view);
            // insert forecast, forecast items
            if ($forecast_check) {
                $sale_forecast_monthly = $this->add_sales_po_monthly_id_to_sale_forecast($converted_data['sales_forecast_monthly'], $sales_po_monthly_id);
                $list_id_sales_forecast = $this->mrp_sales_monthly->save_batch($sale_forecast_monthly);
                $sales_forecast_items = $this->add_sales_forecast_monthly_to_sales_forecast_items($converted_data['sales_forecast_items'], $list_id_sales_forecast);
                $this->mrp_sales_forecast_items->save_batch($sales_forecast_items);
            }
            return true;
        }
        return false;
    }

    protected function delete_sale_daily_and_item_by_month($sale_monthly_id)
    {
        $this->mrp_sales_view->delete_by_sale_monthly($sale_monthly_id);
        
        $list_sales_daily_id = $this->get_list_sales_daily_id_by_sale_monthly($sale_monthly_id);
        if (! empty($list_sales_daily_id)) {
            $this->CI->db->where_in('sale_daily_id', $list_sales_daily_id);
            $this->CI->db->delete('mrp_sales_items');
        }
        $list_sales_forecast_id = $this->get_list_sales_forecast_id_by_sale_monthly($sale_monthly_id);
        if (! empty($list_sales_forecast_id)) {
            $this->CI->db->where_in('sale_monthly_id', $list_sales_forecast_id);
            $this->CI->db->delete('mrp_sales_forecast_items');
        }
        
        $this->mrp_sales_monthly->delete_by_po_sale_monthly($sale_monthly_id);
        $this->mrp_sales_daily->delete_by_po_sale_monthly($sale_monthly_id);
    }

    private function get_list_sales_daily_id_by_sale_monthly($sale_monthly_id)
    {
        $list_sales_daily_id = $this->CI->db->select('id')
            ->from('mrp_sales_daily')
            ->where('sale_monthly_id', $sale_monthly_id)
            ->get()
            ->result_array();
        $list_id = array_map(function ($value) {
            return $value['id'];
        }, $list_sales_daily_id);
        return $list_id;
    }

    private function get_list_sales_forecast_id_by_sale_monthly($sale_monthly_id)
    {
        $list_sales_forecast_id = $this->CI->db->select('id')
            ->from('mrp_sales_monthly')
            ->where('po_monthly_id', $sale_monthly_id)
            ->get()
            ->result_array();
        $list_id = array_map(function ($value) {
            return $value['id'];
        }, $list_sales_forecast_id);
        return $list_id;
    }

    protected function convert_data(array $data, $forecast_check)
    {
        $sales_monthly = $data['sales_monthly'];
        $sales_view = $data['sales_view'];
        $sales_daily_and_items = $this->get_sales_daily_and_items($data['sales_daily']);
        $sales_forecast_monthly_and_items = $this->get_sales_forecast_and_forecast_items($data['sales_forecast']);
        if (empty($sales_monthly['month']) && ! $data['sales_view']['sale_monthly_id']) {
            echo json_encode([
                'status' => 'error',
                'msg' => 'Không có thông tin tháng'
            ]);
            die();
        }
        if (empty($sales_daily_and_items['sales_items'])) {
            echo json_encode([
                'status' => 'error',
                'msg' => 'Không có thông tin sản phẩm số hoặc số lượng sản phẩm'
            ]);
            die();
        }
        if (empty($sales_daily_and_items['sales_daily']) || empty($sales_daily_and_items['sales_items'])) {
            echo json_encode([
                'status' => 'error',
                'msg' => 'Không có thông tin ngày xuất hàng, hoặc số lượng sản phẩm cần xuất'
            ]);
            die();
        }
        if (empty($sales_forecast_monthly_and_items['sales_forecast_items']) && $forecast_check) {
            echo json_encode([
                'status' => 'error',
                'msg' => 'Không có thông tin số lượng forecast'
            ]);
            die();
        }
        
        if ($sales_monthly) {
            return [
                'sales_monthly' => $sales_monthly,
                'sales_daily' => $sales_daily_and_items['sales_daily'],
                'sales_items' => $sales_daily_and_items['sales_items'],
                'sales_view' => $sales_view,
                'sales_forecast_monthly' => $sales_forecast_monthly_and_items['sales_forecast_monthly'],
                'sales_forecast_items' => $sales_forecast_monthly_and_items['sales_forecast_items']
            ];
        }
    }

    private function get_sales_daily_and_items($data_sales_daily)
    {
        $sales_daily = [];
        $sales_items = [];
        
        foreach ($data_sales_daily as $date => $item_port_item_type) {
            $sales_daily[date('Y-m-d', strtotime($date))] = [
                'date' => date('Y-m-d', strtotime($date)),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            foreach ($item_port_item_type as $item_id => $list_port_or_item_type) {
                foreach ($list_port_or_item_type as $port_or_item_type => $value) {
                    $item_type_name = $this->get_item_type_name($value['type'], $port_or_item_type);
                    $sales_items[$date][] = [
                        'item_id' => $item_id,
                        'qty' => $value['qty'],
                        'port' => $item_type_name['port'],
                        'export' => $item_type_name['export'],
                        'product_type' => $item_type_name['product_type'],
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ];
                }
            }
        }
        return [
            'sales_daily' => $sales_daily,
            'sales_items' => $sales_items
        ];
    }

    private function get_sales_forecast_and_forecast_items($sales_forecast)
    {
        $sales_forecast_monthly = [];
        $sales_forecast_items = [];
        $order_for = $this->session->userdata('order_for');
        foreach ($sales_forecast as $date => $item_port_item_type) {
            $sales_forecast_monthly[$date] = [
                'month' => $date,
                'employee_id' => $this->logged_in_employee->id,
                'type' => 'forecast',
                'order_for' => $order_for,
                'customer_id' => $this->mrp_sales_customer->get_id_by_code($this->customer),
                'status' => '',
                'po_monthly_id' => '',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            foreach ($item_port_item_type as $item_id => $list_port_or_item_type) {
                foreach ($list_port_or_item_type as $port_or_item_type => $value) {
                    $item_type_name = $this->get_item_type_name($value['type'], $port_or_item_type);
                    $sales_forecast_items[$date][] = [
                        'item_id' => $item_id,
                        'qty' => $value['qty'],
                        'port' => $item_type_name['port'],
                        'export' => $item_type_name['export'],
                        'product_type' => $item_type_name['product_type'],
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ];
                }
            }
        }
        return [
            'sales_forecast_monthly' => $sales_forecast_monthly,
            'sales_forecast_items' => $sales_forecast_items
        ];
    }

    private function add_sales_monthly_to_sales_daily(array $sales_daily, $sales_monthly_id)
    {
        $sales_daily = array_map(function ($sale) use ($sales_monthly_id) {
            $sale['sale_monthly_id'] = $sales_monthly_id;
            return $sale;
        }, $sales_daily);
        return $sales_daily;
    }

    private function add_sales_daily_to_sales_items(array $sales_items, array $sales_daily_ids)
    {
        $result = [];
        foreach ($sales_items as $key => &$sale_items) {
            $sale_items = array_map(function ($sale_item) use ($key, $sales_daily_ids) {
                $sale_item['sale_daily_id'] = $sales_daily_ids[$key];
                return $sale_item;
            }, $sale_items);
            $result = array_merge($result, $sale_items);
        }
        unset($sale_items);
        return $result;
    }

    private function add_sales_po_monthly_id_to_sale_forecast(array $sales_forecast_monthly, $sales_po_monthly_id)
    {
        $sales_forecast_monthly = array_map(function ($sale) use ($sales_po_monthly_id) {
            $sale['po_monthly_id'] = $sales_po_monthly_id;
            return $sale;
        }, $sales_forecast_monthly);
        return $sales_forecast_monthly;
    }

    private function add_sales_forecast_monthly_to_sales_forecast_items(array $sales_forecast_items, array $sales_forecast_monthly_ids)
    {
        $result = [];
        foreach ($sales_forecast_items as $key => &$sale_items) {
            $sale_items = array_map(function ($sale_item) use ($key, $sales_forecast_monthly_ids) {
                $sale_item['sale_monthly_id'] = $sales_forecast_monthly_ids[$key];
                return $sale_item;
            }, $sale_items);
            $result = array_merge($result, $sale_items);
        }
        unset($sale_items);
        return $result;
    }

    private function save_sales_view(array $sales_view, $sales_monthly_id)
    {
        $sales_view['sale_monthly_id'] = $sales_monthly_id;
        return $this->CI->db->insert('mrp_sales_view', $sales_view);
    }

    public function delete($sale_monthly_id)
    {
        $this->CI->db->trans_start();
        $this->mrp_sales_monthly->delete_by_po_sale_monthly($sale_monthly_id);
        $this->mrp_sales_monthly->delete($sale_monthly_id);
        $this->mrp_sales_view->delete_by_sale_monthly($sale_monthly_id);
        $list_sales_daily_id = $this->get_list_sales_daily_id($sale_monthly_id);
        if (! empty($list_sales_daily_id)) {
            $this->CI->db->where_in('sale_daily_id', $list_sales_daily_id);
            $this->CI->db->delete('mrp_sales_items');
        }
        $this->mrp_sales_daily->delete_by_po_sale_monthly($sale_monthly_id);
        $this->CI->db->trans_complete();
        if ($this->CI->db->trans_status()) {
            return true;
        }
        return false;
    }

    private function get_item_type_name($type, $port_name)
    {
        $port = $export = $product_type = '';
        if ($type === 'export') {
            $export = $port_name;
        } else {
            $port = $port_name;
            $product_type = $type;
        }
        
        return [
            'port' => $port,
            'export' => $export,
            'product_type' => $product_type
        ];
    }
}
