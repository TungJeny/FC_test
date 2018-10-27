<?php

require_once("Secure_area.php");

use Models\Stock;
class Stock_out extends Secure_area
{
    public $view_data = [];

    private $stock_out;

    function __construct()
    {
        parent::__construct('stock_out');
        $this->load->library('stock_lib');
        $this->lang->load('stock_out');
        $this->load->model('Product');
        $this->lang->load('module');
        $this->load->helper('items');
        $this->load->helper('format');
        $this->load->model('Supplier');
        $this->load->model('Sale_daily');
        $this->load->model('Sale_daily_item');
        $this->load->model('Customer');
        $this->load->model('Category');
        $this->load->model('Tag');
        $this->load->model('Item');
        $this->load->model('Item_location');
        $this->load->model('Item_kit');
        $this->load->model('Appfile');
        $this->load->model('Receiver_location');
        $this->stock_out = new Models\Stock_out();
        
        $this->modes = $this->stock_out->get_modes();
        $this->view_data = [
            Models\Stock_out::STOCK_TYPE_DIRECT => [
                'placeholder' => 'Mã hàng',
                'button_change_mode' => get_data($this->modes, \Models\Stock_out::STOCK_TYPE_DIRECT)
            ],
            Models\Stock_out::STOCK_TYPE_ORDER => [
                'placeholder' => 'Mã hàng',
                'button_change_mode' => get_data($this->modes, \Models\Stock_out::STOCK_TYPE_ORDER)
            ],
            Models\Stock_out::STOCK_TYPE_PACKAGE => [
                'placeholder' => 'mã vạch/ Tên VT/ mã lô VT',
                'button_change_mode' => get_data($this->modes, \Models\Stock_out::STOCK_TYPE_PACKAGE)
            ]
        ];
        if (empty($this->stock_lib->get_stock_out_type())) {
            $this->stock_lib->set_stock_out_type('direct');
        }
    }

    /**
     * Index
     */
    function index()
    {
        $this->_reload(array(), FALSE);
    }
    /**
     * Search Item
     */
    function search_item()
    {
        session_write_close();
        $data_type = $this->stock_lib->get_stock_out_type();
        switch ($data_type) {
            case Models\Stock_out::STOCK_TYPE_ORDER:
                $suggestions = $this->Product->get_search_suggestions($this->input->get('term'), 100);
                break;
            case Models\Stock_out::STOCK_TYPE_PACKAGE:
                $material = new Models\Material();
                $suggestions = $material->get_search_suggestions($this->input->get('term'), 100);
                break;
            default:
                $suggestions = $this->Item->get_item_search_suggestions($this->input->get('term'), 'cost_price', 100);
                $suggestions = array_merge($suggestions, $this->Item_kit->get_item_kit_search_suggestions_sales_stock_in($this->input->get('term'), 'cost_price', 100));
        }
        echo json_encode($suggestions);
    }

    /**
     * Add Item To Cart
     */
    function add_item()
    {

        $data = array();
        $id = trim($this->input->post("item"));
        if ($id == NULL || $id == '') {
            $data['error'] = lang('stock_in_unable_to_add_item');
            $this->_reload($data);
            return;
        }
        $stock_type = $this->stock_lib->get_stock_out_type();
        switch ($stock_type) {
            case Models\Stock_out::STOCK_TYPE_PACKAGE:
                $result = $this->add_package_item($id);
                if ($result['status'] == 'error') {
                    $data['error'] = $result['msg'];
                }
                break;
            default:
                $this->add_item_default($id);
        }
        $this->_reload($data);
    }

    private function add_package_item($id)
    {
        $this->stock_lib->empty_cart();
        $stock_package = new Models\Stock_package();
        $list_stock = $stock_package->get_item_for_stock_out($id);
        foreach ($list_stock as $stock_package) {
            $item_id = $stock_package['item_id'];
            $package_id = $stock_package['package_id'];
            $package_code = $stock_package['package_code'];
            $total_quantity = $stock_package['total_quantity'];
            $total_quantity_stock = $stock_package['total_stock_qty'];
            $unit_name = $stock_package['unit_name'];
            $this->stock_lib->add_item_stock_out_package($item_id, $package_id, $package_code, $total_quantity, $total_quantity_stock, $unit_name);
        }
        
    }

    
    private function add_item_default($item_or_kit_id) {
        $data = array();
        $quantity = 1;
        if ($this->stock_lib->is_valid_item_kit($item_or_kit_id)) {
            if ($this->Item_kit->get_info($item_or_kit_id)->deleted || $this->Item_kit->get_info($this->Item_kit->get_item_kit_id($item_or_kit_id))->deleted) {
                $data['error'] = lang('stock_out_unable_to_add_item');
            } else {
                $this->stock_lib->add_item_kit($item_or_kit_id);
            }
        } elseif ($this->Item_location->get_location_quantity($item_or_kit_id) <= 0) {
            $data['error'] = 'Không thể xuất kho, số lương trong kho đang bằng 0';
        } elseif ($this->Item->get_info($item_or_kit_id)->deleted || $this->Item->get_info($this->Item->get_item_id($item_or_kit_id))->deleted || !$this->stock_lib->add_item($item_or_kit_id, $quantity)) {
            if (!$this->config->item('enable_scale') || !$this->stock_lib->add_scale_item($item_or_kit_id)) {
                $data['error'] = lang('stock_out_unable_to_add_item');
            }
        }
    }
    /**
     * Update Item In Cart
     * @param $item_id
     */
    function update_item($item_id)
    {
        $data = array();
        $this->form_validation->set_rules('price', 'lang:common_price', 'numeric');
        $this->form_validation->set_rules('quantity', 'lang:common_quantity', 'numeric');
        $this->form_validation->set_rules('quantity_received', 'lang:stock_out_qty_received', 'numeric');
        $this->form_validation->set_rules('discount', 'lang:common_discount_percent', 'numeric');
        $description = NULL;
        $serial_number = NULL;
        $price = NULL;
        $quantity = NULL;
        $selling_price = NULL;
        $discount = NULL;
        $expire_date = NULL;
        $quantity_received = NULL;
        if ($this->input->post("name")) {
            $variable = $this->input->post("name");
            $$variable = $this->input->post("value");
        }
        if ($selling_price !== NULL && $selling_price == '') {
            $selling_price = NULL;
        }
        if ($discount !== NULL && $discount == '') {
            $discount = 0;
        }
        if ($quantity !== NULL && $quantity == '') {
            $quantity = 0;
        }
        if ($quantity_received !== NULL && $quantity_received == '') {
            $quantity_received = 0;
        }
        if ($this->form_validation->run() != FALSE) {
            if ($quantity > $this->Item_location->get_location_quantity($item_id)) {
                $data['error'] = 'Số lượng xuất lớn hơn số lượng đang có';
            } elseif ($quantity < $this->Item_location->get_location_quantity($item_id)) {
                $this->stock_lib->update_item($item_id, $description, $serial_number, $expire_date, $quantity, $quantity_received, $discount, $price, $selling_price);
            } else {
                $data['error'] = lang('stock_out_error_editing_item');
            }
        }
        $this->_reload($data);
    }

    function update_item_package($line)
    {
        $data = array();
        $cart = $this->stock_lib->get_cart();
        $this->form_validation->set_rules('quantity', 'lang:common_quantity', 'numeric');
        $quantity = NULL;
        $note = NULL;
        if ($this->input->post("name")) {
            $variable = $this->input->post("name");
            $$variable = $this->input->post("value");
        }
        if ($quantity !== NULL && $quantity == '') {
            $quantity = 0;
        }
        if ($note !== NULL && $note == '') {
            $note = 0;
        }
        if ($this->form_validation->run() != FALSE) {
            if ($quantity > ($cart[$line]['total_quantity']-$cart[$line]['total_stock_qty'])) {
                $data['error'] = 'Số lượng xuất vượt quá số lượng cần xuất';
            } elseif ($quantity < ($cart[$line]['total_quantity']-$cart[$line]['total_stock_qty'])) {
                $this->stock_lib->update_item_stock_out_package($line, $quantity, $note);
            } else {
                $data['error'] = lang('stock_out_error_editing_item');
            }
        }
        $this->_reload($data);
    }
    /**
     * Delete Item From Cart
     * @param $item_id
     */
    function delete_item($item_id)
    {
        $this->stock_lib->delete_item($item_id);
        if (count($this->stock_lib->get_cart()) == 0) {
            $this->stock_lib->reset_data();
        }
        $this->_reload();
    }

    /**
     * Search Supplier
     */
    function search_receiver_location()
    {
        // allow parallel searchs to improve performance.
        session_write_close();
        $suggestions = $this->Receiver_location->get_search_suggestions($this->input->get('term'), 100);
        echo json_encode($suggestions);
    }

    /**
     * Select Supplier
     */
    function select_receiver_location()
    {
        $data = array();
        $id = $this->input->post("receiver_location");
        if ($this->Receiver_location->exists($id)) {
            $this->stock_lib->set_receiver_location($id);
        } else {
            $data['error'] = 'Không thể thêm đơn vị nhận';
        }
        $this->_reload($data);
    }
    
    /**
     * Select Supplier
     */
    function add_new_receiver_location()
    {
        $data = array();
        if ($this->input->post("location") != '') {
            $id = $this->Receiver_location->save($this->input->post("location"));
            $this->stock_lib->set_receiver_location($id);
        } else {
            $data['error'] = 'Không thể thêm đơn vị nhận';
        }
        $this->_reload($data);
    }

    function search_item_tp()
    {
        // allow parallel searchs to improve performance.
        session_write_close();
        $suggestions = $this->Product->get_search_suggestions($this->input->get('term'), 1,100);
        echo json_encode($suggestions);
    }

    function select_item_tp()
    {
        $data = array();
        $id = $this->input->post("item_tp");
        if ($this->Product->exists($id)) {
            $this->stock_lib->set_stock_out_package_item_tp($id);
        } else {
            $data['error'] = 'Không thể thêm thành phẩm';
        }
        $this->_reload($data);
    }

    /**
     * Search Employee
     */
    function search_employee()
    {
        // allow parallel searchs to improve performance.
        session_write_close();
        $stock_type = $this->stock_lib->get_stock_out_type();
        if ($stock_type == Models\Stock_out::STOCK_TYPE_ORDER) {
            $suggestions = $this->Employee->get_shipper_suggestions($this->input->get('term'), 100);
        } else {
            $suggestions = $this->Employee->get_employee_search_suggestions($this->input->get('term'), 100);
        }
        echo json_encode($suggestions);
    }

    /**
     * Select Employee
     */
    function select_employee()
    {
        $data = array();
        $employee_id = $this->input->post("employee");
        if ($this->Employee->exists($employee_id)) {
            $this->stock_lib->set_employee($employee_id);
        } else {
            $data['error'] = lang('stock_out_unable_to_add_employee');
        }
        $this->stock_lib->clear_all_paid_store_account_stock_in();
        $this->_reload($data);
    }

    /**
     * Search Sale Order By Date
     */
    function search_sale_daily() {
        $this->load->model('Sale_daily');
        $term = $this->input->get('term');
        if (empty($term)) {
            return;
        }
        $parts = explode('/', $term);
        $term = $parts[2] . '-' . $parts[1] . '-' . $parts[0];
        $customer = $this->stock_lib->get_stock_out_customer();
        if (!empty($customer)) {
            $customer = $this->Customer->get_info_by_code($customer);
            $suggestions = $this->Sale_daily->get_search_suggestions($term, get_data($customer, 'id'), 100);
            echo json_encode($suggestions);
        }
    }

    /**
     * Select Sale Daily
     */
    function select_sale_daily()
    {
        $data = array();
        $sale_daily_id = $this->input->post("sale_daily");
        if ($this->Sale_daily->exists($sale_daily_id)) {
            $this->stock_lib->set_stock_out_sale_daily($sale_daily_id);
            // Reset Selected Port
            $this->stock_lib->reset_stock_out_port();
        } else {
            $data['error'] = 'Không thể chọn đơn hàng xuất kho';
        }
        $this->_reload($data);
    }

    /**
     * Get Ports From Items Of Selected Order
     */
    function search_receiver_port() {
        session_write_close();
        $term = $this->input->get('term');
        $sale_daily_id = $this->stock_lib->get_stock_out_sale_daily();
        $suggestions = $this->Sale_daily_item->get_search_port_suggestions($term, $sale_daily_id, 100);
        //print_r ($suggestions);
        echo json_encode($suggestions);
    }

    /**
     * Select receiver port
     */
    function select_receiver_port() {
        $data = array();
        $port = $this->input->post('port');
        $this->stock_lib->set_stock_out_port($port);
        $this->_reload($data);
    }

    /**
     * Set Comment
     */
    function set_comment()
    {
        $this->stock_lib->set_comment($this->input->post('comment'));
    }

    /**
     * Complete
     */
    function complete()
    {
        $data['cart'] = $this->stock_lib->get_cart();
        if (empty($data['cart'])) {
            return redirect('stock_out');
        }
        $row['sub_total'] = $data['sub_total'] = $this->stock_lib->get_subtotal();
        $row['receiver_location_id'] = $data['receiver_location'] = $this->stock_lib->get_receiver_location();
        $row['total'] = $data['total'] = $this->stock_lib->get_total();
        $row['quantity'] = $data['quantity'] = $this->stock_lib->get_total_quantity();
        $row['receipt_title'] = $data['receipt_title'] = lang('stock_out_receipt');
        $data['payments'] = $this->stock_lib->get_payments();
        $row['supplier_id'] = $data['supplier_id'] = $this->stock_lib->get_supplier();
        $data['employee_id'] = $this->stock_lib->get_employee();
        if (empty($data['employee_id'])) {
            $data['employee_id'] = $this->Employee->get_logged_in_employee_info()->person_id;
        }
        if ($this->stock_lib->get_receiver_location() != -1) {
            $location = $this->Receiver_location->get_info($this->stock_lib->get_receiver_location());
            $data['receiver_location_name'] = $location->name;
        }
        $row['employee_id'] = $data['employee_id'];
        $row['location_id'] = $this->Employee->get_logged_in_employee_current_location_id();
        $employee = $this->Employee->get_info($data['employee_id']);
        $data['employee'] = $employee->first_name . ' ' . $employee->last_name;
        $row['comment'] = $data['comment'] = $this->stock_lib->get_comment();
        $data['payment_type'] = $this->stock_lib->get_payments();
        $data['mode'] = $this->stock_lib->get_mode();
        $data['change_stock_out_date'] = $this->stock_lib->get_change_stock_in_date_enable() ? $this->stock_lib->get_change_stock_out_date() : false;
        $old_date = $this->stock_lib->get_change_recv_id() ? $this->Receiving->get_info($this->stock_lib->get_change_recv_id())->row_array() : false;
        $old_date = $old_date ? date(get_date_format() . ' ' . get_time_format(), strtotime($old_date['stock_out_time'])) : date(get_date_format() . ' ' . get_time_format());
        $data['transaction_time'] = $this->stock_lib->get_change_stock_in_date_enable() ? date(get_date_format() . ' ' . get_time_format(), strtotime($this->stock_lib->get_change_stock_out_date())) : $old_date;
        $data['suspended'] = 0;
        $data['is_po'] = 0;
        $row['items'] = $data['cart'];
        $row['deleted'] = 0;
        $row['deleted_by'] = 0;
        $row['created_at'] = $row['updated_at'] = time();
        $row['type'] = Stock::TYPE_OUT;
        // Save stock in to database
        $data['stock_out_id'] = $this->stock_out->add($row);
        if ($data['supplier_id'] > 0) {
            $supplier = $this->Supplier->get_info($data['supplier_id']);
        }
        if (empty($data['stock_out_id'])) {
            $data['error_message'] = '';
            $data['error_message'] .= '<span class="text-danger">' . lang('stock_out_transaction_failed') . '</span>';
            $data['error_message'] .= '<br /><br />' . anchor('stock_out', '&laquo; ' . lang('stock_out_register'));
            $data['error_message'] .= '<br /><br />' . anchor('stock_out/complete', lang('common_try_again') . ' &raquo;');
        } else {
            if ($this->stock_lib->get_email_receipt() && !empty($supplier->email)) {
                $this->load->library('email');
                $config['mailtype'] = 'html';
                $this->email->initialize($config);
                $this->email->from($this->Location->get_info_for_key('email') ? $this->Location->get_info_for_key('email') : 'no-reply@mg.phppointofsale.com', $this->config->item('company'));
                $this->email->to($supplier->email);
                $this->email->subject(lang('stock_out_receipt'));
                $this->email->message($this->load->view("stock_out/receipt_email", $data, true));
                $this->email->send();
            }
        }
        $this->load->view("stock_out/receipt", $data);
        if (!empty($data['stock_out_id'])) {
            $this->stock_lib->clear_all();
        }
    }

    /**
     * Reload Data, Need For Index
     * @param array $data
     */
    function _reload($data = array())
    {
        $is_ajax = $this->input->is_ajax_request();
        // Get Logged Employee Account
        $person_info = $this->Employee->get_logged_in_employee_info();
        $data['stock_type'] = $this->stock_lib->get_stock_out_type();
        $data['view_data'] = $this->view_data[$data['stock_type']];
        $data['view_data']['customer'] = $this->stock_lib->get_stock_out_customer();
        $data['modes'] = $this->stock_out->get_modes();
        $data['customers'] = $this->Customer->get_collection();
        $data['customer'] = $this->stock_lib->get_stock_out_customer();
        $data['port'] = $this->stock_lib->get_stock_out_port();
        // Get Cart
        $data['cart'] = $this->stock_lib->get_cart();
        // Get Sale daily
        $sale_daily = $this->stock_lib->get_stock_out_sale_daily();
        if (!empty($sale_daily)) {
            $data['sale_daily'] = $this->Sale_daily->get_info($sale_daily);
            $data['ports'] = $this->Sale_daily_item->get_ports($sale_daily);
        }
        // Get Employee
        $employee_id = $this->stock_lib->get_employee();
        if (!empty($employee_id)) {
            $employee = $this->Employee->get_info($employee_id);
            $data['employee'] = $employee->first_name . ' ' . $employee->last_name;
            $data['employee_email'] = $employee->email;
            $data['employee_avatar'] = $employee->image_id ? app_file_url($employee->image_id) : base_url() . "assets/img/user.png";
            $data['employee_id'] = $employee_id;
        }
        // Get Supplier
        $receiver_location = $this->stock_lib->get_receiver_location();
        if ($receiver_location != -1) {
            $location = $this->Receiver_location->get_info($receiver_location);
            $data['receiver_location'] = $receiver_location;
            $data['receiver_location_name'] = $location->name;
        }
        // Get Supplier
        $item_tp = $this->stock_lib->get_stock_out_package_item_tp();
        if ($item_tp != -1) {
            $item_tp_info = $this->Item->get_info($item_tp);
            $data['item_tp'] = $item_tp;
            $data['item_tp_name'] = $item_tp_info->name;
        }
        // Get Comment
        $data['comment'] = $this->stock_lib->get_comment();
        // Get Total
        $data['total'] = $this->stock_lib->get_total();
        // Check Permission
        $data['items_module_allowed'] = $this->Employee->has_module_permission('items', $person_info->person_id);
        if ($is_ajax) {
            $this->load->view("stock_out/index/form", $data);
        } else {
            $this->load->view("stock_out/index", $data);
        }
    }

    /**
     * Change Stock Type
     */
    public function change_stock_type()
    {
        $type = $this->input->post('stock_type');
        $this->stock_lib->clear_all();
        $this->stock_lib->set_stock_out_type($type);
        $this->_reload();
    }

    /**
     * Change Customer
     */
    public function change_customer()
    {
        $this->stock_lib->reset_data();
        $customer = $this->input->post('customer');
        $this->stock_lib->set_stock_out_customer($customer);
        $this->_reload();
    }
}