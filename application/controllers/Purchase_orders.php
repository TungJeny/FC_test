<?php
require_once ("Secure_area.php");

class Purchase_orders extends Secure_area
{

    const ITEM_PER_PAGE = 20;

    const ITEM_PAGE_RANGE = 3;

    /**
     *
     * @var array
     */
    public $rpp = array(
        5,
        15,
        20,
        25,
        30,
        35,
        40,
        45,
        50,
        55,
        60,
        65,
        70,
        75,
        80,
        90,
        100
    );

    /**
     *
     * @var array
     */
    public $mass_actions = array(
        'mass_active' => array(
            'label' => 'Kích hoạt',
            'action' => 'purchase_orders/mass_active',
            'type' => 'redirect'
        ),
        'mass_deactive' => array(
            'label' => 'Khóa loại',
            'action' => 'purchase_orders/mass_deactive',
            'type' => 'redirect'
        )
    );

    /**
     * Purchase_orders constructor.
     */
    function __construct()
    {
        parent::__construct();
        $this->load->model('Purchase_order');
        $this->load->model('Category');
        $this->load->model('Supplier');
        $this->load->helper('format');
        $this->load->model('Appfile');
    }

    /**
     * Index
     */
    function index()
    {
        $data = $this->_init_data();
        $this->session->set_userdata('redirect_url',current_url());
        $this->load->view('purchase_order/manage', $data);
    }

    /**
     *
     * @return mixed
     */
    protected function _init_data()
    {
        $data['selected_ids'] = explode(',', $this->input->get_param('selected_ids'));
        $sorter = $this->input->get_param('sorter');
        $sort_str = 'receive_date DESC';
        // Render Sorting List
        if (! empty($sorter)) {
            if (! empty($sorter['name']) && ! empty($sorter['value'])) {
                $sort_str = $sorter['name'] . ' ' . $sorter['value'];
            }
        }
        $data['current_page'] = $this->input->get_param('current_page');
        if (empty($data['current_page'])) {
            $data['current_page'] = 1;
        }
        $data['record_per_page'] = $this->input->get_param('record_per_page');
        if (empty($data['record_per_page'])) {
            $data['record_per_page'] = self::ITEM_PER_PAGE;
        }
        // Analyze Filters
        $conditions = null;
        $filters = $this->input->get_param('filters');
        if ($filters) {
            $conditions = analyze_filters($filters);
        }
        $data['action'] = 'Danh sách';
        $data['index_url'] = $this->Purchase_order->get_index_url();
        $data['create_url'] = $this->Purchase_order->get_create_url();
        $data['total'] = $this->Purchase_order->count_by();
        $data['count_total'] = $this->Purchase_order->count_by($conditions);
        // Paginate
        $data['rpp'] = $this->rpp;
        $data['mass_actions'] = $this->mass_actions;
        $data['pager'] = $this->Purchase_order->paginate($data['current_page'], $data['record_per_page'], $data['count_total'], self::ITEM_PAGE_RANGE);
        $data['sorter'] = $sorter;
        $data['filters'] = $filters;
        $data['collection'] = $this->Purchase_order->get_collection($data['pager']['start'], $data['record_per_page'], $conditions, $sort_str);
        return $data;
    }

    /**
     *
     * @param int $id
     */
    public function view($id = -1)
    {
        $this->session->set_userdata('purchase_order/selected_items', null);
        $data['categories'][''] = lang('common_select_category');
        $data['purchase_order'] = new stdClass();
        $data['purchase_order']->supplier = new stdClass();
        if ($id > 0) {
            
            $data['purchase_order'] = $this->Purchase_order->get_info($id);
            $data['purchase_order']->supplier = $this->Supplier->get_info(get_data($data['purchase_order'], 'supplier_id'));
            $data['purchase_order']->items = $this->Purchase_order->get_purchase_order_items(get_data($data['purchase_order'], 'id'));
            $data['items'] = get_data($data['purchase_order'], 'items');            
            // add items staged
            $data['json_staged'] = $this->Purchase_order->get_staged_purchase_oder_item($id);
            foreach ($data['items'] as $key => $item) {
                foreach ($data['json_staged'] as $json_key => $json_item) {
                    if (!empty($json_item['json_staged'])) {
                        if (!empty($json_item) && (get_data($item, 'item_id') == get_data($json_item, 'item_id'))) {
                            $data['items'][$key]['staged'] = json_decode(get_data($json_item, 'json_staged'));
                        }                        
                    }                    
                }    
            }

            // end items staged

            $data['filter_category_id'] = $this->Purchase_order->get_category_of_items($data['items']);
            $this->_store_session_data($data['purchase_order']);

        } else {
            $data['filter_category_id'] = 0;
        }
        $categories = $this->Category->sort_categories_and_sub_categories($this->Category->get_all_categories_and_sub_categories());
        foreach ($categories as $key => $value) {
            $name = str_repeat('&nbsp;&nbsp;', $value['depth']) . $value['name'];
            $data['categories'][$key] = $name;
        }
       
        $data['suppliers'] = $this->Purchase_order->get_suppliers();
        $data['status_list'] = $this->Purchase_order->get_status_list();
        $this->load->view('purchase_order/view', $data);
    }

    /**
     * Store current purchase data view
     *
     * @param
     *            $purchase_order
     * @return $this
     */
    private function _store_session_data($purchase_order)
    {
        $this->session->set_userdata('purchase_order/id', get_data($purchase_order, 'id'));
        $this->session->set_userdata('purchase_order/comment', get_data($purchase_order, 'comment'));
        $this->session->set_userdata('purchase_order/status', get_data($purchase_order, 'status'));
        $this->session->set_userdata('purchase_order/receive_date', date('d/m/Y', get_data($purchase_order, 'receive_date', time())));
        $this->session->set_userdata('purchase_order/selected_items', serialize($purchase_order->items));
        return $purchase_order;
    }

    /**
     * Search Item
     */
    public function search_item()
    {
        $this->load->helper('format');
        $category_id = $this->input->post('category_id');
        $supplier_id = $this->input->post('supplier_id');
        $date = $this->input->post('date');
        $dates = explode(' - ', $date);
        $conditions = array();
        if (! empty($category_id)) {
            $conditions['category_id'] = $category_id;
        }
        if (! empty($supplier_id)) {
            $conditions['supplier_id'] = $supplier_id;
            $this->session->set_userdata('purchase_order/supplier_id', $supplier_id);
        }
        if (! empty($dates)) {
            $dates[0] = format_begin_month($dates[0]);
            $dates[1] = format_end_month($dates[1]);
            $conditions['dates'] = $dates;
        }
        $data['plan_items'] = $this->Purchase_order->get_plan_items($conditions);
        $this->unset_selected_items();
        $this->load->view('purchase_order/view/result', $data);
    }

    /**
     *
     * @return $this
     */
    public function select_item()
    {
        $item = $this->input->post('data');
        $selected_items = $this->session->userdata('purchase_order/selected_items');
        if (! empty($selected_items)) {
            $selected_items = @unserialize($selected_items);
        }
        if (empty($selected_items)) {
            $selected_items = array();
        }
        if (isset($selected_items[$item['item_id']])) {
            $selected_items[$item['item_id']]['quantity'] += 1;
        } else {
            $item['comment'] = '';
            $selected_items[$item['item_id']] = $item;
        }
        $this->session->set_userdata('purchase_order/selected_items', serialize($selected_items));
        return $this;
    }

    /**
     *
     * @return $this
     */
    public function unselect_item()
    {
        $item_id = $this->input->post('item_id');
        $selected_items = $this->session->userdata('purchase_order/selected_items');
        if (! empty($selected_items)) {
            $selected_items = @unserialize($selected_items);
        }
        if (empty($selected_items)) {
            return $this;
        } else {
            unset($selected_items[$item_id]);
        }
        $this->session->set_userdata('purchase_order/selected_items', serialize($selected_items));
        return $this;
    }

    /**
     *
     * @return $this
     */
    public function update_item()
    {
        $item_id = $this->input->post('item_id');
        $field = $this->input->post('field');
        $value = $this->input->post('value');
        $selected_items = $this->session->userdata('purchase_order/selected_items');

        if (! empty($selected_items)) {
            $selected_items = @unserialize($selected_items);
        }
        if (empty($selected_items)) {
            return $this;
        } else {
            if (isset($selected_items[$item_id])) {
                $selected_items[$item_id][$field] = $value;
            }
        }
        $this->session->set_userdata('purchase_order/selected_items', serialize($selected_items));
        return $this;
    }

    /**
     *
     * @return $this
     */
    public function update_field()
    {
        $field = $this->input->post('field');
        $value = $this->input->post('value');
        $this->session->set_userdata('purchase_order/' . $field, $value);
        return $this;
    }

    /**
     */
    public function validate_field()
    {
        $field = $this->input->post('field');
        $value = $this->session->userdata('purchase_order/' . $field);
        $result = array(
            'success' => true,
            'message' => ''
        );
        if (empty($value)) {
            $result = array(
                'success' => false,
                'message' => 'Bạn chưa chọn ' . $field
            );
        }
        echo json_encode($result);
    }

    /**
     * Reload
     */
    public function reload()
    {
        $refresh = $this->input->get('refresh');
        if (! empty($refresh)) {
            $this->session->set_userdata('purchase_order/selected_items', null);
        }
        $data['items'] = $this->session->userdata('purchase_order/selected_items');
        if (! empty($data['items'])) {
            $data['items'] = @unserialize($data['items']);
        }
        if (empty($data['items'])) {
            $data['items'] = array();
        }
        $this->load->view('purchase_order/view/items', $data);
    }
    /**
     * add array item_staged
     */
    public function save()
    {
        $id = $this->input->post('id');
        $data = $this->input->post('data');
        
        foreach ($data['items'] as $items => $item) {
            if (!empty($item['json_staged'])) {
                $i = 0;
                foreach ($item['json_staged']['quantity'] as $quantity =>$val_quantity) {
                        if (is_numeric($val_quantity)) {
                            foreach ($item['json_staged']['month'] as $month => $val_month) { 
                                if($month == $i){
                                    $staged = [
                                        'quantity' => trim($val_quantity),
                                        'month' => trim($val_month)
                                    ];
                                }
                                else{

                                } 
                            }
                        }
                      
                $data['items'][$items]['staged'][] = $staged;
                $i++;
                }
            }     
        }


        foreach ($data['items'] as $items => $item) {
            if (!empty($item['json_staged'])) {
                $i = 0;
                foreach ($item['json_staged']['quantity'] as $quantity =>$val_quantity) {
                    if (is_numeric($val_quantity)) {
                        foreach ($item['json_staged']['month'] as $month => $val_month) {
                            if($month == $i){
                             
                                $staged_item = [
                                    'quantity' => trim($val_quantity),
                                    'month' => trim($val_month),
                                    'item_id' => trim($items),
                                    'Purchase_order_id' => trim($id)
                                ];
                            }
                            else{

                            }
                        } 
                    }          
                $data['items'][$items]['staged_item'][] = $staged_item;
                $i++;
                }
            }     
        }

        if (empty($id)) {
            $id = $this->Purchase_order->add($data);
        } else {    
    
        $this->Purchase_order->update($id, $data);
        }
        return redirect($this->Purchase_order->get_invoice_url($id));
    }

    /**
     *
     * @param int $id
     */
    public function invoice($id = -1)
    {
        $this->session->set_userdata('purchase_order/selected_items', null);
        $data['categories'][''] = lang('common_select_category');
        $data['supplier'] = new stdClass();
        $data['purchase_order'] = new stdClass();
        if ($id > 0) {
            $data['purchase_order'] = $this->Purchase_order->get_info($id);
            $data['purchase_order']->supplier = $this->Supplier->get_info(get_data($data['purchase_order'], 'supplier_id'));
            $data['purchase_order']->items = $this->Purchase_order->get_purchase_order_items($id);
            $data['purchase_order']->person = $this->Employee->get_info(get_data($data['purchase_order'], 'person_id'));
            $data['purchase_order']->supplier_name =  $data['purchase_order']->supplier->company_name;
            $data['purchase_order']->created_date =  $data['purchase_order']->created_at;
        }
        $categories = $this->Category->sort_categories_and_sub_categories($this->Category->get_all_categories_and_sub_categories());
        foreach ($categories as $key => $value) {
            $name = str_repeat('&nbsp;&nbsp;', $value['depth']) . $value['name'];
            $data['categories'][$key] = $name;
        }
        $data['filter_category_id'] = 0;
        $data['suppliers'] = $this->Purchase_order->get_suppliers();
        $this->load->view('purchase_order/invoice', $data);
    }

    private function unset_selected_items()
    {
        $this->session->unset_userdata('purchase_order/selected_items');
    }

    public function delete($id)
    {
        $ids =[];
        if (!is_array($id)) {
            $ids[] = $id;
        } else {
            $ids = $id;
        }
        $this->Purchase_order->delete($ids);
        $this->Purchase_order->delete_pruchase_oder_id_staged($ids);
        redirect($this->get_redirect_url());
    }
    
    private function get_redirect_url() {
        return $this->session->userdata('redirect_url')? $this->session->userdata('redirect_url'): 'purchase_orders';
    }
}
