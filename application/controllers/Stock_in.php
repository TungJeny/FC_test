<?php
require_once("Secure_area.php");

use Models\Stock;

class Stock_in extends Secure_area
{

    protected $created_categorie_ids = [
        2
    ];

    private $location_id;

    private $stock;

    private $stock_in;

    public $view_data = [
        'free_style' => [
            'placeholder' => 'mã hàng',
            'button_change_mode' => 'Trực tiếp'
        ],
        'purchase_order' => [
            'placeholder' => 'đơn hàng po',
            'button_change_mode' => 'Đơn hàng PO'
        ],
        'product' => [
            'placeholder' => 'mã hàng',
            'button_change_mode' => 'Thành phẩm'
        ]
    ];

    const STOCK_TYPE_PO = 'purchase_order';

    const STOCK_TYPE_PR = 'product';

    function __construct()
    {
        parent::__construct('stock_in');
        $this->load->library('stock_lib');
        $this->lang->load('stock_in');
        $this->lang->load('module');
        $this->load->helper('items');
        $this->load->helper('format');
        $this->stock = new Stock();
        $this->stock_package = new Models\Stock_package();
        $this->stock_in = new Models\Stock_in();
        $this->load->model('Supplier');
        $this->load->model('Category');
        $this->load->model('Tag');
        $this->load->model('Item');
        $this->load->model('Item_location');
        $this->load->model('Item_kit');
        $this->load->model('Appfile');
        $this->load->model('Purchase_order');
        $this->load->model('Receiver_location');
        if (empty($this->stock_lib->get_stock_type())) {
            $this->stock_lib->set_stock_type('free_style');
        }
        $this->location_id = $this->Employee->get_logged_in_employee_current_location_id();
    }

    /**
     * Index
     */
    function index()
    {
        $this->_reload(array(), FALSE);
    }

    /**
     *
     * @param
     *            $id
     */
    public function view($id)
    {
        $stock_type = $this->stock_lib->get_stock_type();
        if ($stock_type != self::STOCK_TYPE_PR) {
            return redirect('/stock_in');
        }
        // Get Back Data
        $this->Stock = new Models\Stock();
        $data = array();
        $data['stock_request'] = $this->Stock->get_stock($id);
        if (empty($data['stock_request'])) {
            return redirect('stock_in');
        }

        $this->_reload($data, FALSE);
    }

    /**
     * Search Item
     */
    function search_item()
    {
        session_write_close();
        $data_type = $this->stock_lib->get_stock_type();
        switch ($data_type) {
            case 'purchase_order':
                $suggestions = $this->Purchase_order->get_item_search_suggestions($this->input->get('term'));
                break;
            case 'product':
                $this->load->model('Product');
                $suggestions = $this->Product->get_search_suggestions($this->input->get('term'), 100);
                break;
            default:
                $suggestions = $this->Item->get_item_search_suggestions($this->input->get('term'), 'cost_price', 100, [
                    2,
                    3,
                    5
                ]);
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
        $stock_type = $this->stock_lib->get_stock_type();
        switch ($stock_type) {
            case STOCK_TYPE_PO:
                $result = $this->add_purchase_order_item($id);
                if ($result['status'] == 'error') {
                    $data['error'] = $result['msg'];
                }
                break;
            default:
                $this->add_free_style_item($id);
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

    private function add_free_style_item($id)
    {
        $quantity = 1;
        if ($this->stock_lib->is_valid_item_kit($id)) {
            if ($this->Item_kit->get_info($id)->deleted || $this->Item_kit->get_info($this->Item_kit->get_item_kit_id($id))->deleted) {
                $data['error'] = lang('stock_in_unable_to_add_item');
            } else {
                $this->stock_lib->add_item_kit($id);
            }
        } elseif ($this->Item->get_info($id)->deleted || $this->Item->get_info($this->Item->get_item_id($id))->deleted || !$this->stock_lib->add_item($id, $quantity)) {
            if (!$this->config->item('enable_scale') || !$this->stock_lib->add_scale_item($id)) {
                $data['error'] = lang('stock_in_unable_to_add_item');
            }
        }
    }

    private function add_purchase_order_item($id)
    {
        $po = $this->Purchase_order->get_info($id);
        $msg = '';
        $status = 'success';
        if (empty($po)) {
            return [
                'msg' => 'Đơn hàng không tồn tại',
                'status' => 'error'
            ];
        }
        $list_item = $this->Purchase_order->get_purchase_order_items($id);
        $this->stock_lib->set_stock_type_id($id);
        $this->stock_lib->empty_cart();
        $finish_stock = true;
        $quantity_stock = [];
        $quantity_total = [];
        $quantity_remain = [];
        foreach ($list_item as $item) {
            $package_code = '';
            $item_id = $item['item_id'];
            $quantity_total[$item['item_id']] = $item['quantity'];
            $quantity = $item['quantity'] - $this->stock_in->sumQuantityStockById($id, 'purchase_order', $item['item_id']);
            $quantity_remain[$item['item_id']] = $quantity;
            if (in_array($this->Item->get_info($item['item_id'])->category_id, $this->created_categorie_ids)) {
                $package_code = $this->create_package_code($item['item_id']);
            }
            $quantity_stock[$item['item_id']] = $this->stock_in->sumQuantityStockById($id, 'purchase_order', $item['item_id']) ? $this->stock_in->sumQuantityStockById($id, 'purchase_order', $item['item_id']) : 0;
            if ($quantity_remain[$item['item_id']] > 0) {
                $finish_stock = false;
            } else {
                continue;
            }
            $is_delete = $this->Item->get_info($item_id)->deleted || $this->Item->get_info($this->Item->get_item_id($item_id))->deleted;
            if ($is_delete) {
                $msg .= 'Vật tư ' . $this->Item->get_info($item_id)->name . ' đã xóa, không thể thêm;' . "\n";
                $status = 'error';
            } elseif (!$this->stock_lib->add_item($item_id, $quantity, NULL, 0, null, null, null, null, FALSE, FALSE, 0, 0, 0, $package_code, $item['unit_id'], $item['unit_name'])) {
                $msg .= 'Không thêm mới được mặt hàng';
                $status = 'error';
            }
        }
        if ($finish_stock) {
            $this->stock_lib->empty_cart();
            return [
                'msg' => 'Đơn hàng đã nhập đủ số lượng. Bạn chọn đơn hàng khác',
                'status' => 'error'
            ];
        }
        $this->stock_lib->set_qty_stock($quantity_stock);
        $this->stock_lib->set_qty_remain($quantity_remain);
        $this->stock_lib->set_qty_total($quantity_total);
        $this->stock_lib->set_supplier($po->supplier_id);
        $this->stock_lib->set_po_code($po->po_code);
        return [
            'msg' => $msg,
            'status' => $status
        ];
    }

    /**
     * Update Item In Cart
     *
     * @param
     *            $line
     */
    function update_item($line)
    {
        $data = array();
        $id = $this->input->get('stock_request_id');
        if (!empty($id)) {
            $data['stock_request'] = $this->stock->get_stock($id);
        }
        $self_validation = true;
        $cart = $this->stock_lib->get_cart();
        if (isset($cart[$line])) {
            $item_id = $cart[$line]['item_id'];
        } else {
            $item_id = $line;
        }
        $message = lang('stock_in_error_editing_item');
        $this->form_validation->set_rules('price', 'lang:common_price', 'numeric');
        $this->form_validation->set_rules('quantity', 'lang:common_quantity', 'numeric');
        $this->form_validation->set_rules('quantity_received', 'lang:stock_in_qty_received', 'numeric');
        $this->form_validation->set_rules('discount', 'lang:common_discount_percent', 'numeric');
        $description = NULL;
        $serial_number = NULL;
        $price = NULL;
        $quantity = NULL;
        $selling_price = NULL;
        $discount = NULL;
        $expire_date = NULL;
        $quantity_received = NULL;
        $overflow_quantity = NULL;
        $status = NULL;
        $note = NULL;
        if ($this->input->post("name")) {
            $variable = $this->input->post("name");
            $$variable = $this->input->post("value");
        }

        $quantity_remain = [];
        if ('purchase_order' == $this->stock_lib->get_stock_type()) {
            $quantity_remain = $this->stock_lib->get_qty_remain();
        }

        if ((isset($quantity_remain[$item_id]) && (int)$quantity > $quantity_remain[$item_id])) {
            $self_validation = false;
            $message = 'Không thể nhập quá số lượng đơn hàng';
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
        if ($overflow_quantity !== NULL && $overflow_quantity == '') {
            $overflow_quantity = 0;
        }
        if ($note !== NULL && $note == '') {
            $note = "";
        }
        if ($status !== NULL && $status == '') {
            $status = 0;
        } else {
            foreach ($cart as &$item) {
                $item['status'] = 0;
                $item['number_of_pakage'] = $this->stock_in->getNumberOfPackage($item['item_id']);
            }
            unset($item);
            $this->stock_lib->set_cart($cart);
        }

        if ($this->form_validation->run() != FALSE && $self_validation) {
            // If Update Stock Request Item
            if (!empty($data['stock_request'])) {
                $data_update = array(
                    $this->input->post('name') => $this->input->post('value')
                );
                $this->stock = new Models\Stock();
                $this->stock->update_item($item_id, $data_update);
            } else {
                $this->stock_lib->update_item($line, $description, $serial_number, $expire_date, $quantity, $quantity_received, $discount, $price, $selling_price, $overflow_quantity, $note, $status);
            }
        } else {
            $data['error'] = $message;
        }
        if (!empty($data['stock_request'])) {
            redirect(site_url('/stock_in/view/' . get_data($data['stock_request'], 'stock_id')));
        } else {
            $this->_reload($data);
        }
    }

    /**
     * Delete Item From Cart
     *
     * @param
     *            $item_id
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
    function search_supplier()
    {
        // allow parallel searchs to improve performance.
        session_write_close();
        $suggestions = $this->Supplier->get_supplier_search_suggestions($this->input->get('term'), 100);
        echo json_encode($suggestions);
    }

    /**
     * Select Supplier
     */
    function select_supplier()
    {
        $data = array();
        $supplier_id = $this->input->post("supplier");
        if ($this->Supplier->account_number_exists($supplier_id)) {
            $supplier_id = $this->Supplier->supplier_id_from_account_number($supplier_id);
        }
        if ($this->Supplier->exists($supplier_id)) {
            $this->stock_lib->set_supplier($supplier_id);
        } else {
            $data['error'] = lang('stock_in_unable_to_add_supplier');
        }
        $this->stock_lib->clear_all_paid_store_account_stock_in();
        $this->_reload($data);
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
        $location_id = $this->input->post("receiver_location");
        if ($this->Location->exists($location_id)) {
            $this->stock_lib->set_receiver_location($location_id);
        } else {
            $data['error'] = 'Không thể thêm đơn vị nhận';
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
        $stock_type = $this->stock_lib->get_stock_type();
        if ($stock_type == self::STOCK_TYPE_PR) {
            $suggestions = $this->Employee->get_importer_suggestions($this->input->get('term'), 100);
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
            $data['error'] = lang('stock_in_unable_to_add_employee');
        }
        $this->stock_lib->clear_all_paid_store_account_stock_in();
        $this->_reload($data);
    }

    /**
     * Search Package
     */
    function search_package()
    {
        session_write_close();
        $model = new Models\Stock_package();
        $suggestions = $model->get_search_suggestions($this->input->get('term'), 100, array(
            'package_type' => Models\Stock_package::TYPE_ITEM
        ));
        echo json_encode($suggestions);
    }

    /**
     * Select Package
     */
    function select_package()
    {
        $data = array();
        $package_id = $this->input->post("package");
        $model = new Models\Stock_package();
        if ($model->exists($package_id)) {
            $this->stock_lib->set_package($package_id);
        } else {
            $data['error'] = 'Không thể thiết lập mã vật tư';
        }
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
        // Handle Stock Request
        $stock_request_id = $this->input->post('stock_request_id');
        if (!empty($stock_request_id)) {
            switch ($this->stock_lib->get_stock_type()) {
                case self::STOCK_TYPE_PR:
                    $this->load->model('Employee');
                    $this->Stock = new Models\Stock();
                    $data['stock_request'] = $this->Stock->get_stock($stock_request_id);
                    // Update status of request is complete
                    $this->Stock->update_stock($stock_request_id, array(
                        'status' => Stock::STATUS_ACCEPTED,
                        'updated_at' => time()
                    ));
                    // Update items of request
                    $selected_items = $this->stock_lib->get_selected_items($stock_request_id);
                    if (!empty($selected_items)) {
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
                    }
                    // Stock In Items
                    if (!empty($data['stock_request']->items)) {
                        foreach ($selected_items as $item_id => $selected_item) {
                            foreach ($data['stock_request']->items as $item) {
                                if (get_data($item, 'item_id') == $item_id) {
                                    $this->db->trans_start();
                                    // Update Inventory
                                    $row = array(
                                        'trans_items' => $item['item_id'],
                                        'trans_user' => get_data($data['stock_request'], 'employee_id'),
                                        'trans_comment' => 'STOCK IN ' . $stock_request_id . ': ' . get_data($data['stock_request'], 'comment'),
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
                    $data['invoice_title'] = 'Phiếu nhập kho TP';
                    $data['employee'] = $this->Employee->get_info(get_data($data['stock_request'], 'employee_id'));
                    $data['receiver_location'] = $this->Receiver_location->get_info(get_data($data['stock_request'], 'receiver_location_id'));
                    $data['location'] = $this->Location->get_info(get_data($data['stock_request'], 'location_id'));
                    $this->load->view('stock_in/receipt_pr', $data);
                    break;
                default:
                    break;
            }
            return false;
        }
        $data['cart'] = $this->stock_lib->get_cart();
        if (empty($data['cart'])) {
            return redirect('stock_in');
        }
        $po_code = $this->stock_lib->get_po_code();
        $po_created_time = '';
        if (!empty($po_code)) {
            $po_created_time = date('d-m-Y', $this->Purchase_order->get_info_by_code($po_code)->created_at);
        }

        $row['sub_total'] = $data['sub_total'] = $this->stock_lib->get_subtotal();
        $row['total'] = $data['total'] = $this->stock_lib->get_total();
        $row['quantity'] = $data['quantity'] = $this->stock_lib->get_total_quantity();
        $row['receipt_title'] = $data['receipt_title'] = lang('stock_in_receipt');
        $data['payments'] = $this->stock_lib->get_payments();
        $data['quantity_stock'] = $this->stock_lib->get_qty_stock();
        $data['quantity_total'] = $this->stock_lib->get_qty_total();
        $row['supplier_id'] = $data['supplier_id'] = $this->stock_lib->get_supplier();
        $data['employee_id'] = $this->stock_lib->get_employee();
        if (empty($data['employee_id'])) {
            $data['employee_id'] = $this->Employee->get_logged_in_employee_info()->person_id;
        }
        $row['employee_id'] = $data['employee_id'];
        $row['location_id'] = $this->location_id;
        $data['location_name'] = $this->Location->get_info($this->location_id)->name;
        $employee = $this->Employee->get_info($data['employee_id']);
        $data['employee'] = $employee->first_name . ' ' . $employee->last_name;
        $row['comment'] = $data['comment'] = $this->stock_lib->get_comment();
        $data['payment_type'] = $this->stock_lib->get_payments();
        $data['mode'] = $this->stock_lib->get_mode();
        $data['change_stock_in_date'] = $this->stock_lib->get_change_stock_in_date_enable() ? $this->stock_lib->get_change_stock_in_date() : false;
        $old_date = $this->stock_lib->get_change_recv_id() ? $this->Receiving->get_info($this->stock_lib->get_change_recv_id())
            ->row_array() : false;
        $old_date = $old_date ? date(get_date_format() . ' ' . get_time_format(), strtotime($old_date['stock_in_time'])) : date(get_date_format() . ' ' . get_time_format());
        $data['transaction_time'] = $this->stock_lib->get_change_stock_in_date_enable() ? date(get_date_format() . ' ' . get_time_format(), strtotime($this->stock_lib->get_change_stock_in_date())) : $old_date;
        $data['po_created_time'] = $po_created_time;
        $data['suspended'] = 0;
        $data['is_po'] = 0;
        $row['items'] = $data['cart'];
        $row['deleted'] = 0;
        $row['deleted_by'] = 0;
        $data['created_at'] = $row['created_at'] = $row['updated_at'] = time();
        $row['type'] = Stock::TYPE_IN;
        $row['stock_in_by_id'] = $this->stock_lib->get_stock_type_id();
        $row['stock_in_by_type'] = $this->stock_lib->get_stock_type();
        $row['receiver_location_id'] = $this->stock_lib->get_receiver_location();
        $row['package_id'] = $this->stock_lib->get_package();
        // Append Data For Stock In Type Product
        if ($row['stock_in_by_type'] == Stock_in::STOCK_TYPE_PR) {
            $row['status'] = Stock::STATUS_PENDING;
            $data['package_code'] = $row['package_code'] = $this->stock->get_product_package_code();
        }
        // Save stock in to database
        $data['stock_in_id'] = $this->stock->add($row);
        if ($data['supplier_id'] > 0) {
            $supplier = $this->Supplier->get_info($data['supplier_id']);
            $data['supplier_name'] = $supplier->company_name;
            $data['supplier_address_1'] = $supplier->address_1;
        }
        if (empty($data['stock_in_id'])) {
            $data['error_message'] = '';
            $data['error_message'] .= '<span class="text-danger">' . lang('stock_in_transaction_failed') . '</span>';
            $data['error_message'] .= '<br /><br />' . anchor('stock_in', '&laquo; ' . lang('stock_in_register'));
            $data['error_message'] .= '<br /><br />' . anchor('stock_in/complete', lang('common_try_again') . ' &raquo;');
        } else {
            if ($this->stock_lib->get_email_receipt() && !empty($supplier->email)) {
                $this->load->library('email');
                $config['mailtype'] = 'html';
                $this->email->initialize($config);
                $this->email->from($this->Location->get_info_for_key('email') ? $this->Location->get_info_for_key('email') : 'no-reply@mg.phppointofsale.com', $this->config->item('company'));
                $this->email->to($supplier->email);
                $this->email->subject(lang('stock_in_receipt'));
                $this->email->message($this->load->view("stock_in/receipt_email", $data, true));
                $this->email->send();
            }
        }
        switch ($this->stock_lib->get_stock_type()) {
            case self::STOCK_TYPE_PO:
                $this->load->view("stock_in/receipt_po", $data);
                break;
            case self::STOCK_TYPE_PR:
                redirect(site_url('stock_in/view/' . $data['stock_in_id']));
                break;
            default:
                $this->load->view("stock_in/receipt", $data);
        }

        if (!empty($data['stock_in_id'])) {
            $this->stock_lib->clear_all();
        }
    }

    /**
     * Get Stock Requests
     */
    public function get_list_stock_request()
    {
        $this->Stock = new Models\Stock_in();
        $data['collection'] = $this->Stock->get_stock_requests();
        $this->load->view("stock_in/index/form/pr/list", $data);
    }


    /**
     * @param $stock_id , type : string
     * @return mix
     */
    public function view_stock_stamp($stock_id, $offset = "", $per_page = "")
    {

        $stock_id = explode(',', $stock_id);
        $stock_type = $this->stock_lib->get_stock_type();
        $this->Stock = new Models\Stock();
        $data = array();

        $data['stock_request'] = $this->Stock->get_stock_stamp_view_limit($stock_id, $offset, $per_page);
        if (!($data['stock_request'])) {
            return redirect('stock_in');
        }

        return $data['stock_request'];
    }

    /**
     * [print_stamp description]
     * @return [type] [description]
     */
    function print_stamp()
    {
        //get_stock_request_print
        $this->Stock = new Models\Stock();
        $stock_type = $this->stock_lib->get_stock_type();
        $data['collection'] = $this->Stock->get_stock_requests();
        $stock_id = '';
        foreach ($data['collection'] as $item) {
            $stock_id .= $item->stock_id . ',';
        }
        $stock_id = rtrim($stock_id, ",");

        $offset = $this->uri->segment(4) ? $this->uri->segment(4) : 0;

        $config['base_url'] = site_url('stock_in/print_stamp/page');
        $config['per_page'] = 10;
        //$data['per_page'] = $config['per_page'];
        $data['all_data'] = $this->view_stock_stamp($stock_id, $offset, $config['per_page']);
        $config['total_rows'] = count($data['collection']);
        $data['total_rows'] = $config['total_rows'];
        $this->load->library('pagination');
        $this->pagination->initialize($config);
        $data['pagination'] = $this->pagination->create_links();
        $this->load->view('stock_in/print_stamps/print_stamps', $data);
    }

    /**
     * view html print container ( in lô )
     * @return [type] [description]
     */
    public function view_print_container()
    {
        if (!empty($this->uri->segment(4)) && !empty($this->uri->segment(3)) ){
            $stock_id = $this->uri->segment(3);
            $item_id = $this->uri->segment(4);
            $data['stock_id'] =  $stock_id;
            if ($this->uri->segment(5)) {
                $data['number_pr'] = $this->uri->segment(5);
            } else {
                $data['number_pr'] = 1;
            }
            $data['data_consignment'] = $this->view_stock_stamp($stock_id);
           
            $data['ltp'] = $data['data_consignment'][0]['package_code'];
            
            $data['item_id'] = $item_id;
            $data['get_item'] = $this->Item->get_info($item_id);
            $this->load->view("stock_in/print_stamps/print_template/print_container", $data);
        }
    }

    /**
     * view html print container AJAX NUMBER_PR ( in lô )
     * @return [type] [description]
     */
    public function view_print_container_ajax()
    {
        if (!empty($this->uri->segment(4)) && !empty($this->uri->segment(3)) ){
            $stock_id = $this->uri->segment(3);
            $item_id = $this->uri->segment(4);
            if ($this->uri->segment(5)) {
                $data['number_pr'] = $this->uri->segment(5);
            } else {
                $data['number_pr'] = 1;
            }
            $data['data_consignment'] = $this->view_stock_stamp($stock_id);

            $data['ltp'] = $data['data_consignment'][0]['package_code'];

            $data['item_id'] = $item_id;
            $data['get_item'] = $this->Item->get_info($item_id);
            $this->load->view("stock_in/print_stamps/index/view_container_pr", $data);
        }
    }

    /**
     * view Stock print_stamps
     * @param $stock_id , $item_id
     */
    public function view_print_barcode()
    {
        $this->load->helper('unit');
        if (!empty($this->uri->segment(3))) {
            $item_id = $this->uri->segment(3);
            if ($this->uri->segment(4)) {
                $data['number_pr'] = $this->uri->segment(4);
            } else {
                $data['number_pr'] = 1;
            }
            $this->load->model('Unit');
            $this->load->model('appconfig');
            $this->load->model('Item_taxes');
            $this->load->model('Item_location');
            $this->load->model('Item_location_taxes');
            $this->load->model('Item_taxes_finder');

            
            $data['company'] = $this->appconfig->get('company');
            $this->load->helper('items');
            $data['items'] = get_items_barcode_data($item_id);
            $data['scale'] = 1;
            $data['item_id'] = $item_id;
            $data['amount_unit'] = '';
            $data['get_item'] = $this->Item->get_info($item_id);

            $data_formula = json_decode($data['get_item']->type_unit_formula);

            if (is_array($data_formula) || is_object($data_formula))
            {
                foreach ($data_formula as $id_unit => $number_type_unit) {
                    if($this->Unit->get_unit_name($id_unit) == 'Túi' || $this->Unit->get_unit_name($id_unit) == 'túi' ) {
                       $data['amount_unit'] = $number_type_unit.'/'.'Túi';
                    }
                }
            }

            $data['amount_unit'] ? $data['amount_unit'] : '';
            $this->load->view("stock_in/print_stamps/print_template/stamp_package", $data);
        }
    }

    /**
     * ajax view print Barcode A4
     * @return [type] [description]
     */
    public function view_print_barcode_ajax()
    {
        if (!empty($this->uri->segment(3))) {
            $item_id = $this->uri->segment(3);
            if ($this->uri->segment(4)) {
                $data['number_pr'] = $this->uri->segment(4);
            } else {
                $data['number_pr'] = 1;
            }

            $this->load->model('Unit');
            $this->load->model('appconfig');
            $this->load->model('Item_taxes');
            $this->load->model('Item_location');
            $this->load->model('Item_location_taxes');
            $this->load->model('Item_taxes_finder');

            $data['company'] = $this->appconfig->get('company');
            $this->load->helper('items');
            $data['items'] = get_items_barcode_data($item_id);
            $data['scale'] = 1;
            $data['item_id'] = $item_id;
            $data['amount_unit'] = '';

            $data['get_item'] = $this->Item->get_info($item_id);

            $data_formula = json_decode($data['get_item']->type_unit_formula);

            if (is_array($data_formula) || is_object($data_formula))
            {
                foreach ($data_formula as $id_unit => $number_type_unit) {
                    if($this->Unit->get_unit_name($id_unit) == 'Túi' || $this->Unit->get_unit_name($id_unit) == 'túi' ) {
                       $data['amount_unit'] = $number_type_unit.'/'.'Túi';
                    }
                }
            }

            $this->load->view("stock_in/print_stamps/index/view_barcode_pr", $data);
        }
    }
    /**
     * view in lô: phiếu in lô bị thay đổi
     * 
     * @return [type] [description]
     */
    public function view_print_container_change()
    {
        if (! empty($this->uri->segment(3)) && !empty($this->uri->segment(4)) ) {
            $stock_id = $this->uri->segment(3);
            $item_id = $this->uri->segment(4);
            $data['stock_id'] = $stock_id;
            if ($this->uri->segment(5)) {
                $data['number_pr'] = $this->uri->segment(5);
            } else {
                $data['number_pr'] = 1;
            }
  
            $this->load->model('Unit');
            $this->load->model('appconfig');
            $this->load->model('Item_taxes');
            $this->load->model('Item_location');
            $this->load->model('Item_location_taxes');
            $this->load->model('Item_taxes_finder');

            //$data['get_item'] = $this->Item->get_info($item_id);

            $this->load->helper('items');
            $data['items'] = get_items_barcode_data($item_id);
            $data['scale'] = 1;
            $data['item_id'] = $item_id;
            $data['amount_unit'] = '';

            $data['get_item'] = $this->Item->get_info_and_quantity($item_id);
            //$data['get_item'] = $this->Item->get_info($item_id);
             //$data['get_item'] = $this->Item->get_info($item_id);
            $data['company'] = $this->appconfig->get('company');
            $data['website'] = $this->appconfig->get('website');

            $data['data_consignment'] = $this->view_stock_stamp($stock_id);
            $this->load->helper('qrcode');

            $data_formula = json_decode($data['get_item'][0]['type_unit_formula']);

            if (is_array($data_formula) || is_object($data_formula))
            {
                foreach ($data_formula as $id_unit => $number_type_unit) {
                    //echo $this->Unit->get_unit_name($id_unit).'<br>';
                    if($this->Unit->get_unit_name($id_unit) == 'Thùng' || $this->Unit->get_unit_name($id_unit) == 'thùng' ) {
                       $data['amount_unit'] = $number_type_unit.' '.'Pcs';
                    }
                }
            }
            $data['amount_unit'] ? $data['amount_unit'] : '';

            $data['data_qrcode'] = $this->view_stock_stamp($stock_id);
             foreach ($data['data_qrcode'][0]['items'] as $row) {
                if ($row['item_id'] == $item_id) {
                    $data['id_product'] = $row['product_id'];
                }
            }

            $data['lvt'] = $data['data_qrcode'][0]['consignment'];
            $data['ltp'] = $data['data_qrcode'][0]['package_type'] == 2 ? $data['data_qrcode'][0]['package_code'] : " ";
            $data['items'] = get_items_qrcode_data($item_id);
            
            $this->load->view("stock_in/print_stamps/print_template/print_container_change", $data);
             
        }
    }

    /**
     * view in lô theo ajax
     * @return [type] [description]
     */
    public function view_print_container_change_ajax()
    {
        if (! empty($this->uri->segment(3)) && !empty($this->uri->segment(4)) ) {
            $stock_id = $this->uri->segment(3);
            $item_id = $this->uri->segment(4);
            $data['stock_id'] = $stock_id;
            if ($this->uri->segment(5)) {
                $data['number_pr'] = $this->uri->segment(5);
            } else {
                $data['number_pr'] = 1;
            }
  
            $this->load->model('Unit');
            $this->load->model('appconfig');
            $this->load->model('Item_taxes');
            $this->load->model('Item_location');
            $this->load->model('Item_location_taxes');
            $this->load->model('Item_taxes_finder');

           //$data['get_item'] = $this->Item->get_info($item_id);

            $this->load->helper('items');
            $data['items'] = get_items_barcode_data($item_id);
            $data['scale'] = 1;
            $data['item_id'] = $item_id;
            $data['amount_unit'] = '';

            $data['get_item'] = $this->Item->get_info_and_quantity($item_id);
            /*$data['get_item'] = $this->Item->get_info($item_id);*/
            $data['company'] = $this->appconfig->get('company');
            $data['website'] = $this->appconfig->get('website');

            $data['data_consignment'] = $this->view_stock_stamp($stock_id);
            $this->load->helper('qrcode');

           /* $data_formula = json_decode($data['get_item']->type_unit_formula);*/
            $data_formula = json_decode($data['get_item'][0]['type_unit_formula']);

            if (is_array($data_formula) || is_object($data_formula))
            {
                foreach ($data_formula as $id_unit => $number_type_unit) {
                    //echo $this->Unit->get_unit_name($id_unit).'<br>';
                    if($this->Unit->get_unit_name($id_unit) == 'Thùng' || $this->Unit->get_unit_name($id_unit) == 'thùng' ) {
                       $data['amount_unit'] = $number_type_unit.' '.'Pcs';
                    }
                }
            }
            $data['amount_unit'] ? $data['amount_unit'] : '';

            $data['data_qrcode'] = $this->view_stock_stamp($stock_id);
             foreach ($data['data_qrcode'][0]['items'] as $row) {
                if ($row['item_id'] == $item_id) {
                    $data['id_product'] = $row['product_id'];
                }
            }
            $data['lvt'] = $data['data_qrcode'][0]['consignment'];
            $data['ltp'] = $data['data_qrcode'][0]['package_type'] == 2 ? $data['data_qrcode'][0]['package_code'] : " ";
            $data['items'] = get_items_qrcode_data($item_id);
            
            $this->load->view("stock_in/print_stamps/index/view_container_change", $data);  
        }
    }


    /**
     * view Stock print_stamps
     * @param : $stock_id, $item_id
     */
    public function view_print_qrcode()
    {

        if (!empty($this->uri->segment(3)) && !empty($this->uri->segment(4))) {
            $stock_id = $this->uri->segment(3);
            $item_id = $this->uri->segment(4);
            $data['stock_id'] = $stock_id;
            if ($this->uri->segment(5)) {
                $data['number_pr'] = $this->uri->segment(5);
            } else {
                $data['number_pr'] = 1;
            }

            $this->load->model('Unit');
            $this->load->helper('qrcode');
            $this->load->model('appconfig');
            $this->load->model('Manufacturer');
            $data['data_qrcode'] = $this->view_stock_stamp($stock_id);
           
            $data['item_info'] = $this->Item->get_info($item_id);
            $data['amount_unit'] = '';

            $data_formula = json_decode($data['item_info']->type_unit_formula);

            if (is_array($data_formula) || is_object($data_formula))
            {
                foreach ($data_formula as $id_unit => $number_type_unit) {
                    //echo $this->Unit->get_unit_name($id_unit).'<br>';
                    if($this->Unit->get_unit_name($id_unit) == 'Thùng' || $this->Unit->get_unit_name($id_unit) == 'thùng' ) {
                       $data['amount_unit'] = $number_type_unit.'/'.'thùng';
                    }
                }
            }
            $data['amount_unit'] ? $data['amount_unit'] : '';

            $data['manufacturer'] = $this->Manufacturer->get_info($data['item_info']->manufacturer_id)->name;

            foreach ($data['data_qrcode'][0]['items'] as $row) {
                if ($row['item_id'] == $item_id) {
                    $data['id_product'] = $row['product_id'];
                }
            }
            $data['lvt'] = $data['data_qrcode'][0]['consignment'];
            $data['ltp'] = $data['data_qrcode'][0]['package_type'] == 2 ? $data['data_qrcode'][0]['package_code'] : " ";
            $data['company'] = $this->appconfig->get('company');
            $data['website'] = $this->appconfig->get('website');
            $data['get_item'] = $this->Item->get_info($item_id);
            $this->load->helper('qrcode');
            $data['items'] = get_items_qrcode_data($item_id);
            $this->load->view("stock_in/print_stamps/print_template/print_parcel", $data);
        }
    }

    /**
     * view print QRcode A4
     * @return [type] [description]
     */
    public function view_print_qrcode_ajax()
    {

        if (!empty($this->uri->segment(3)) && !empty($this->uri->segment(4))) {
            $stock_id = $this->uri->segment(3);
            $item_id = $this->uri->segment(4);

            if ($this->uri->segment(5)) {
                $data['number_pr'] = $this->uri->segment(5);
            } else {
                $data['number_pr'] = 1;
            }
            
            $this->load->model('Unit');
            $this->load->helper('qrcode');
            $this->load->model('appconfig');
            $this->load->model('Manufacturer');
            $data['data_qrcode'] = $this->view_stock_stamp($stock_id);

            $data['item_info'] = $this->Item->get_info($item_id);

            $data['amount_unit'] = '';
            
            $data_formula = json_decode($data['item_info']->type_unit_formula);

            if (is_array($data_formula) || is_object($data_formula))
            {
                foreach ($data_formula as $id_unit => $number_type_unit) {
                    //echo $this->Unit->get_unit_name($id_unit).'<br>';
                    if($this->Unit->get_unit_name($id_unit) == 'Thùng' || $this->Unit->get_unit_name($id_unit) == 'thùng' ) {
                       $data['amount_unit'] = $number_type_unit.'/'.'thùng';
                    }
                }
            }
            $data['amount_unit'] ? $data['amount_unit'] : '';

            $data['manufacturer'] = $this->Manufacturer->get_info($data['item_info']->manufacturer_id)->name;

            foreach ($data['data_qrcode'][0]['items'] as $row) {
                if ($row['item_id'] == $item_id) {
                    $data['id_product'] = $row['product_id'];
                }
            }
            $data['lvt'] = $data['data_qrcode'][0]['consignment'];
            $data['ltp'] = $data['data_qrcode'][0]['package_type'] == 2 ? $data['data_qrcode'][0]['package_code'] : " ";
            $data['company'] = $this->appconfig->get('company');
            $data['website'] = $this->appconfig->get('website');
            $data['get_item'] = $this->Item->get_info($item_id);
            $this->load->helper('qrcode');
            $data['items'] = get_items_qrcode_data($item_id);
            $this->load->view("stock_in/print_stamps/index/view_qrcode_pr", $data);
        }
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
        $data['stock_type'] = $this->stock_lib->get_stock_type();
        $data['view_data'] = $this->view_data[$data['stock_type']];
        $data['quantity_stock'] = $this->stock_lib->get_qty_stock();
        $data['quantity_total'] = $this->stock_lib->get_qty_total();
        $data['po_code'] = $this->stock_lib->get_po_code();
        // Get Cart
        if (!empty($data['stock_request'])) {
            $data['cart'] = get_data($data['stock_request'], 'items');
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
            // Get Product Package
            $data['product_package'] = $this->stock->get_product_package(get_data($data['stock_request'], 'stock_id'));
            // Get Selected Item When Search
            $data['selected_items'] = $this->stock_lib->get_selected_items(get_data($data['stock_request'], 'stock_id'));
        } else {
            $data['cart'] = $this->stock_lib->get_cart();
            // Get Supplier
            $supplier_id = $this->stock_lib->get_supplier();
            // Get Receiver Location
            $receiver_location = $this->stock_lib->get_receiver_location();
            // Get Employee
            $employee_id = $this->stock_lib->get_employee();
            // Get Package
            $package_id = $this->stock_lib->get_package();
            // Get Comment
            $data['comment'] = $this->stock_lib->get_comment();
            // Get Total
            $data['total'] = $this->stock_lib->get_total();
        }
        // Get Supplier
        if ($supplier_id != -1) {
            $supplier = $this->Supplier->get_info($supplier_id);
            if (!empty($supplier)) {
                $data['supplier'] = $supplier->company_name;
                $data['supplier_balance'] = $supplier->balance;
                $data['has_balance'] = $supplier->balance > 0;
                if ($supplier->first_name || $supplier->last_name) {
                    $data['supplier'] .= ' (' . $supplier->first_name . ' ' . $supplier->last_name . ')';
                }
                $data['supplier_email'] = $supplier->email;
                $data['avatar'] = $supplier->image_id ? app_file_url($supplier->image_id) : base_url() . "assets/img/user.png";
                $data['supplier_id'] = $supplier_id;
            }
        }
        // Get Receiver Location
        if ($receiver_location != -1) {
            $location = $this->Receiver_location->get_info($receiver_location);
            if (!empty($location)) {
                $data['receiver_location'] = $receiver_location;
                $data['receiver_location_name'] = $location->name;
            }
        }
        // Get Employee
        if ($employee_id != -1) {
            $employee = $this->Employee->get_info($employee_id);
            if (!empty($employee)) {
                $data['employee'] = $employee->first_name . ' ' . $employee->last_name;
                $data['employee_email'] = $employee->email;
                $data['employee_avatar'] = $employee->image_id ? app_file_url($employee->image_id) : base_url() . "assets/img/user.png";
                $data['employee_id'] = $employee_id;
            }
        }
        // Get Package
        if ($package_id != -1) {
            $model = new Models\Stock_package();
            $package = $model->get_info($package_id);
            if (!empty($package) && isset($package->package_code)) {
                $data['package'] = $package->package_code;
            }
            $data['package_id'] = $package_id;
        }
        // Check Permission
        $data['items_module_allowed'] = $this->Employee->has_module_permission('items', $person_info->person_id);
        if ($is_ajax) {
            $this->load->view("stock_in/index/form", $data);
        } else {
            $this->load->view("stock_in/index", $data);
        }
    }

    public function change_stock_type()
    {
        $type = $this->input->post('stock_type');
        $this->stock_lib->clear_all();
        $this->stock_lib->set_stock_type($type);
        $this->_reload();
    }

    /**
     */
    public function cancel_stock_in()
    {
        $this->stock_lib->reset_data();
        redirect('/stock_in');
    }

    /**
     *
     * @param
     *            $id
     */
    public function cancel_stock_request($id)
    {
        $this->Stock = new Models\Stock();
        $this->Stock->delete($id);
        $this->stock_lib->reset_data();
        redirect('/stock_in');
    }

    private function create_package_code($item_id)
    {
        $number_of_package = $this->stock_in->getNumberOfPackage($item_id) < 10 ? '0' . $this->stock_in->getNumberOfPackage($item_id) : $this->stock_in->getNumberOfPackage($item_id);
        return $number_of_package . '-' . substr(date('Y'), 2, 2) . '-' . $this->Item->get_info($item_id)->name;
    }
}
