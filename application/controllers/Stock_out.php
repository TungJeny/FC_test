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
        $this->lang->load('module');
        $this->load->model('Product');
        $this->load->helper('items');
        $this->load->helper('format');
        $this->load->helper('unit');
        $this->load->model('Supplier');
        $this->load->model('Sale_daily');
        $this->load->model('Sale_daily_item');
        $this->load->model('Port');
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
        $stock_request_id = $this->input->post("stock_request_id");
        if (!empty($stock_request_id)) {
            if (!is_numeric($id)) {
                $this->load->model('Product');
                $suggestions = $this->Product->get_search_suggestions($id);
                if (!empty($suggestions)) {
                    $item = array_shift($suggestions);
                    $id = get_data($item, 'item_id');
                }
            }
            $this->Stock = new Models\Stock();
            $data['stock_request'] = $this->Stock->get_stock($stock_request_id);
            $this->stock_lib->select_item($stock_request_id, $id);
        }
        $this->_reload($data);
    }

    private function add_package_item($id)
    {
        $finish_stock = true;
        $this->stock_lib->empty_cart();
        $stock_package = new Models\Stock_package();
        $list_stock = $stock_package->get_item_for_stock_out($id);
        if (empty($list_stock)) {
            return [
                'msg' => 'Không có thông tin vật tư',
                'status' => 'error'
            ];
        }
        foreach ($list_stock as $stock_package) {
            if ($stock_package['total_quantity'] - $stock_package['total_stock_qty'] > 0) {
                $finish_stock = false;
            } else {
                continue;
            }
            $item_id = $stock_package['item_id'];
            $package_id = $stock_package['package_id'];
            $package_code = $stock_package['package_code'];
            $total_quantity = $stock_package['total_quantity'];
            $total_quantity_stock = $stock_package['total_stock_qty'];
            $unit_name = $stock_package['unit_name'];
            $this->stock_lib->add_item_stock_out_package($item_id, $package_id, $package_code, $total_quantity, $total_quantity_stock, $unit_name);
        }
        if ($finish_stock) {
            $this->stock_lib->empty_cart();
            return [
                'msg' => 'Đơn hàng đã nhập đủ số lượng. Bạn chọn đơn hàng khác',
                'status' => 'error'
            ];
        }
        return [
            'msg' => '',
            'status' => 'success'
        ];

    }

    private function add_item_default($item_or_kit_id)
    {
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
            $note = '';
        }
        if ($this->form_validation->run() != FALSE) {
            $this->stock_lib->update_item_stock_out_package($line, null, $note);
            if ($quantity > ($cart[$line]['total_quantity'] - $cart[$line]['total_stock_qty'])) {
                $data['error'] = 'Số lượng xuất vượt quá số lượng cần xuất';
            } elseif ($quantity < ($cart[$line]['total_quantity'] - $cart[$line]['total_stock_qty'])) {
                $this->stock_lib->update_item_stock_out_package($line, $quantity, $note);
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
    function delete_package_item($item_id)
    {
        $this->stock_lib->delete_stock_out_package_item($item_id);
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
            $data['error'] = 'Vui lòng nhập tên đơn vị nhận vào  để tạo mới';
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
        $suggestions = $this->Product->get_search_suggestions($this->input->get('term'), 1, 100);
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
    function search_sale_daily()
    {
        $this->load->model('Sale_daily');
        $term = $this->input->get('term');
        if (empty($term)) {
            return;
        }
        $parts = explode('/', $term);
        if (is_array($parts)) {
            $parts = array_filter($parts);
        }
        $term = $parts[1] . '-' . $parts[0];
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
            // Reset Cart
            $this->stock_lib->empty_cart();
        } else {
            $data['error'] = 'Không thể chọn đơn hàng xuất kho';
        }
        $this->stock_lib->set_stock_out_is_reset(true);
        $this->_reload($data);
    }

    /**
     * Get Ports From Items Of Selected Order
     */
    function search_receiver_port()
    {
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
    function select_receiver_port()
    {
        $data = array();
        $port = $this->input->post('port');
        $this->stock_lib->set_stock_out_port($port);
        $this->_reload($data);
    }

    /**
     * Set Port
     */
    function set_port()
    {
        $port = $this->input->post('port');
        $ports = explode(' - ', $port);
        if (is_array($ports)) {
            $ports = array_filter($ports);
        }
        $selected = $this->input->post('selected');
        if ($selected == \Models\Stock_out::VALUE_YES) {
            if (!empty($ports)) {
                foreach ($ports as $port) {
                    $this->stock_lib->select_stock_out_port($port);
                }
            } else {
                $this->stock_lib->select_stock_out_port($port);
            }
        } else {
            if (!empty($ports)) {
                foreach ($ports as $port) {
                    $this->stock_lib->unselect_stock_out_port($port);
                }
            } else {
                $this->stock_lib->unselect_stock_out_port($port);
            }
        }
        // Reset Cart
        $this->stock_lib->empty_cart();
        // Select Port Items
        $sale_daily = $this->stock_lib->get_stock_out_sale_daily();
        $ports = $this->stock_lib->get_stock_out_ports();
        if (!empty($ports)) {
            $items = $this->Sale_daily->get_port_items($sale_daily, $ports);
        } else {
            $items = $this->Sale_daily->get_items($sale_daily);
        }
        if (!empty($items)) {
            foreach ($items as $item) {
                $this->stock_lib->add_item(get_data($item, 'item_id'), get_data($item, 'quantity'), get_data($item, 'quantity'));
            }
        }
        $this->_reload();
    }

    /**
     * Set Comment
     */
    function set_comment()
    {
        $this->stock_lib->set_comment($this->input->post('comment'));
    }

    /**
     * Set License Plate
     */
    function set_license_plate()
    {
        $this->stock_lib->set_stock_out_license_plate($this->input->post('license_plate'));
    }

    /**
     * Set Received Day
     */
    function set_received_day()
    {
        $this->stock_lib->set_stock_out_received_day($this->input->post('received_day'));
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
        $stock_type = $this->stock_lib->get_stock_out_type();
        switch ($stock_type) {
            case Models\Stock_out::STOCK_TYPE_ORDER:
                $this->complete_stock_out_order();
                break;
            case Models\Stock_out::STOCK_TYPE_PACKAGE:
                $this->complete_stock_out_package();
                break;
            default:
                $this->complete_stock_out();
        }
    }

    /**
     * Complete Stock Out Order
     */
    public function complete_stock_out_order()
    {
        // Handle Stock Request
        $stock_request_id = $this->input->post('stock_request_id');
        if (!empty($stock_request_id)) {
            $selected_items = $this->stock_lib->get_selected_items($stock_request_id);
            if (empty($selected_items)) {
                return redirect('/stock_out');
            }
            $this->load->model('Employee');
            $this->load->model('Location');
            $this->Stock = new Models\Stock();
            $data['stock_request'] = $this->Stock->get_stock($stock_request_id);
            // Update status of request is complete
            $this->Stock->update_stock($stock_request_id, array(
                'status' => Stock::STATUS_ACCEPTED,
                'updated_at' => time()
            ));
            // Update items of request
            foreach ($data['stock_request']->items as $item) {
                $is_selected = false;
                foreach ($selected_items as $item_id => $selected_item) {
                    if (get_data($item, 'item_id') == $item_id) {
                        $is_selected = true;
                    }
                }
                if (!$is_selected) {
                    $this->Stock->remove_item($stock_request_id, get_data($item, 'item_id'));
                }
            }
            // Stock Out Item
            if (!empty($data['stock_request']->items)) {
                foreach ($selected_items as $item_id => $selected_item) {
                    foreach ($data['stock_request']->items as $item) {
                        if (get_data($item, 'item_id') == $item_id) {
                            $this->db->trans_start();
                            // Update Inventory
                            $item['quantity'] *= -1;
                            $row = array(
                                'trans_items' => $item['item_id'],
                                'trans_user' => get_data($data['stock_request'], 'employee_id'),
                                'trans_comment' => 'STOCK OUT ' . $stock_request_id . ': ' . get_data($data['stock_request'], 'comment'),
                                'trans_inventory' => $item['quantity'],
                                'location_id' => get_data($data['stock_request'], 'location_id')
                            );
                            $this->db->insert('inventory', $row);
                            // Update Quantity By Location
                            $this->Stock->update_qty_location_items(array(
                                'location_id' => get_data($data['stock_request'], 'location_id'),
                                'item_id' => $item['item_id'],
                                'quantity' => $item['quantity']
                            ));
                            $this->db->trans_complete();
                        }
                    }
                }
            }
            // Reload Data
            $this->Stock = new Models\Stock();
            $data['stock_request'] = $this->Stock->get_stock($stock_request_id);
            $data['customer'] = $this->Customer->get_info_by_id(get_data($data['stock_request'], 'customer_id'));
            $data['employee'] = $this->Employee->get_info(get_data($data['stock_request'], 'employee_id'));
            $data['receiver_location'] = $this->Location->get_info(get_data($data['stock_request'], 'receiver_location_id'));
            $this->load->view('stock_out/receipt_order', $data);
            return;
        }
        return redirect('/stock_out');
    }

    private function complete_stock_out()
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
        $old_date = $this->stock_lib->get_change_recv_id() ? $this->Receiving->get_info($this->stock_lib->get_change_recv_id())
            ->row_array() : false;
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
     *
     */
    private function complete_stock_out_package()
    {
        $data['cart'] = $this->stock_lib->get_cart();
        if (empty($data['cart'])) {
            return redirect('stock_out');
        }
        $row['sub_total'] = $data['sub_total'] = $this->stock_lib->get_subtotal();
        $row['receiver_location_id'] = $data['receiver_location'] = $this->stock_lib->get_receiver_location();
        $row['total'] = $data['total'] = $this->stock_lib->get_total();
        $row['quantity'] = $data['quantity'] = $this->stock_lib->get_total_quantity();
        $row['item_product'] = $this->stock_lib->get_stock_out_package_item_tp();
        $row['receipt_title'] = $data['receipt_title'] = lang('stock_out_receipt');
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
        $data['mode'] = $this->stock_lib->get_mode();
        $data['transaction_time'] = date(get_date_format(), time());
        $row['stock_out_by_type'] = Models\Stock_out::STOCK_TYPE_PACKAGE;
        $row['items'] = $data['cart'];
        $row['deleted'] = 0;
        $row['deleted_by'] = 0;
        $row['created_at'] = $row['updated_at'] = time();
        $row['type'] = Stock::TYPE_OUT;
        // Save stock in to database
        $data['stock_out_id'] = $this->stock_out->save_stock_out_package($row);
        if (empty($data['stock_out_id'])) {
            $data['error_message'] = '';
            $data['error_message'] .= '<span class="text-danger">' . lang('stock_out_transaction_failed') . '</span>';
            $data['error_message'] .= '<br /><br />' . anchor('stock_out', '&laquo; ' . lang('stock_out_register'));
            $data['error_message'] .= '<br /><br />' . anchor('stock_out/complete', lang('common_try_again') . ' &raquo;');
        }
        $this->load->view("stock_out/receipt_package", $data);
        if (!empty($data['stock_out_id'])) {
            $this->stock_lib->clear_all();
            $this->stock_lib->set_stock_out_type('package');
        }
    }

    /**
     * @param $id
     */
    public function view($id)
    {
        // Get Back Data
        $this->Stock = new Models\Stock();
        $data = array();
        $data['stock_request'] = $this->Stock->get_stock($id);
        if (empty($data['stock_request'])) {
            return redirect('/stock_out');
        }
        $this->_reload($data, FALSE);
    }

    /**
     * Save Stock Out Request
     */
    public function save_stock_out_request()
    {
        $data['cart'] = $this->stock_lib->get_cart();
        if (empty($data['cart'])) {
            return redirect('/stock_out');
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
        $row['status'] = Stock::STATUS_PENDING;
        $row['customer_id'] = get_data($this->Customer->get_info_by_code($this->stock_lib->get_stock_out_customer()), 'id');
        $row['sale_daily_id'] = $this->stock_lib->get_stock_out_sale_daily();
        $row['ports'] = implode(',', $this->stock_lib->get_stock_out_ports());
        $row['license_plate'] = $this->stock_lib->get_stock_out_license_plate();
        $row['received_at'] = date_to_timestamp($this->stock_lib->get_stock_out_received_day());
        // Save stock in to database
        $data['stock_out_id'] = $this->stock_out->save_stock_out_request($row);
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
        if (!empty($data['stock_out_id'])) {
            $this->stock_lib->reset_data();
            return redirect(site_url('stock_out/view/' . $data['stock_out_id']));
        }
        return redirect('/stock_out');
    }

    /**
     * Reload Data, Need For Index
     *
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
        $data['license_plates'] = $this->stock_out->get_license_plates();;
        if (!empty($data['stock_request'])) {
            // Get Customer
            $data['customer'] = get_data($data['stock_request'], 'customer_id');
            $data['customer'] = get_data($this->Customer->get_info_by_id($data['customer']), 'code');
            $this->stock_lib->set_stock_out_customer($data['customer']);
            // Get Port
            $data['selected_ports'] = explode(',', get_data($data['stock_request'], 'ports'));
            if (is_array($data['selected_ports'])) {
                $data['selected_ports'] = array_filter($data['selected_ports']);
            }
            // Get Cart
            $sale_daily = get_data($data['stock_request'], 'sale_daily_id');
            // Reset Cart Data
            if (!empty($sale_daily)) {
                $data['sale_daily'] = $this->Sale_daily->get_info($sale_daily);
                $data['cart'] = get_data($data['stock_request'], 'items');
            }
            $data['ports'] = $this->Port->get_customer_ports($data['customer']);
            // Get Supplier
            $supplier_id = get_data($data['stock_request'], 'supplier_id');
            // Get Receiver Location
            $receiver_location = get_data($data['stock_request'], 'receiver_location_id');
            // Get Employee
            $employee_id = get_data($data['stock_request'], 'employee_id');
            // Get Package
            $package_id = get_data($data['stock_request'], 'package_id');
            // Get Comment
            $data['comment'] = get_data($data['stock_request'], 'comment');
            // Get Total
            $data['total'] = get_data($data['stock_request'], 'total');
            // Get Selected License Plate
            $data['selected_license_plate'] = get_data($data['stock_request'], 'license_plate');
            // Get Received Day
            $data['received_day'] = date('d/m/Y', get_data($data['stock_request'], 'received_at', time()));
            // Get Selected Item When Search
            $data['selected_items'] = $this->stock_lib->get_selected_items(get_data($data['stock_request'], 'stock_id'));
        } else {
            // Get Customers
            $data['customer'] = $this->stock_lib->get_stock_out_customer();
            // Get Ports
            $data['selected_ports'] = $this->stock_lib->get_stock_out_ports();
            if (is_array($data['selected_ports'])) {
                $data['selected_ports'] = array_filter($data['selected_ports']);
            }
            // Get Cart
            $data['cart'] = $this->stock_lib->get_cart();
            $sale_daily = $this->stock_lib->get_stock_out_sale_daily();
            // Reset Cart Data
            if (!empty($sale_daily)) {
                $data['sale_daily'] = $this->Sale_daily->get_info($sale_daily);
                $items = $this->Sale_daily->get_items($sale_daily);
                if (!empty($data['sale_daily']) && empty($data['cart'])) {
                    $this->stock_lib->empty_cart();
                    if (!empty($items)) {
                        foreach ($items as $item) {
                            $this->stock_lib->add_item(get_data($item, 'item_id'), get_data($item, 'quantity'), get_data($item, 'quantity'));
                        }
                    }
                }
                // Get Ports From Sale Daily Order
                // $data['ports'] = $this->Sale_daily_item->get_ports($sale_daily);
                // If Empty Selected Ports
                if (empty($data['selected_ports'])) {
                    if (!empty($items)) {
                        foreach ($items as $item) {
                            if (!empty($item['ports'])) {
                                $ports = explode(',', $item['ports']);
                                foreach ($ports as $port) {
                                    $this->stock_lib->select_stock_out_port($port, true);
                                }
                            }
                        }
                    }
                    // Re-Get Ports
                    $data['selected_ports'] = $this->stock_lib->get_stock_out_ports();
                }
            }
            if (is_array($data['selected_ports'])) {
                $data['selected_ports'] = array_filter($data['selected_ports']);
            }
            if (!empty($data['customer'])) {
                $data['ports'] = $this->Port->get_customer_ports($data['customer']);
            }
            // Get Employee
            $employee_id = $this->stock_lib->get_employee();
            // Get Package
            $package_id = $this->stock_lib->get_package();
            // Get Comment
            $data['comment'] = $this->stock_lib->get_comment();
            // Get Total
            $data['total'] = $this->stock_lib->get_total();
            // Get Selected License Plate
            $data['selected_license_plate'] = $this->stock_lib->get_stock_out_license_plate();
            // Get Received Day
            $data['received_day'] = $this->stock_lib->get_stock_out_received_day();
        }
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
            $data['receiver_location_name'] = get_data($location, 'name');
        }
        // Get Supplier
        $item_tp = $this->stock_lib->get_stock_out_package_item_tp();
        if ($item_tp != -1) {
            $item_tp_info = $this->Item->get_info($item_tp);
            $data['item_tp'] = $item_tp;
            $data['item_tp_name'] = $item_tp_info->name;
        }
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

    /**
     * Get Stock Requests
     */
    public function get_list_stock_request()
    {
        $this->Stock = new Models\Stock_out();
        $data['collection'] = $this->Stock->get_stock_requests();
        $this->load->view("stock_out/index/form/order/list", $data);
    }

    /**
     * Select Item
     */
    function select_item()
    {
        $id = trim($this->input->post('item_id'));
        $stock_request_id = $this->input->get('stock_request_id');
        if (!empty($stock_request_id) && !empty($id)) {
            $this->stock_lib->select_item($stock_request_id, $id);
        }
    }

    /**
     * Un-select Item
     */
    function unselect_item()
    {
        $id = trim($this->input->post('item_id'));
        $stock_request_id = $this->input->get('stock_request_id');
        if (!empty($stock_request_id) && !empty($id)) {
            $this->stock_lib->unselect_item($stock_request_id, $id);
        }
    }

    /**
     * Cancel Stock Out
     */
    public function cancel_stock_out()
    {
        $this->stock_lib->reset_data();
        redirect('/stock_out');
    }

    /**
     * @param $id
     */
    public function cancel_stock_request($id)
    {
        $this->Stock = new Models\Stock();
        $this->Stock->delete($id);
        $this->stock_lib->reset_data();
        redirect('/stock_out');
    }

    public function test_unit()
    {
        var_dump($this->Customer->get_info_by_code('yamaha'));
    }
}