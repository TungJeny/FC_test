<?php

class Purchase_order extends MY_Model
{

    const STATUS_PENDING = 1;

    const STATUS_APPROVED = 2;

    const STATUS_ACTION = 3;

    const STATUS_CANCEL = 4;

    const STATUS_COMPLETED = 5;

    /**
     *
     * @var array
     */
    protected $_instance = array(
        'prefix_key' => 'phppos/purchase_orders',
        'table' => 'phppos_purchase_orders',
        'primary_key' => 'id',
        'collection' => array(
            'fields' => '*'
        )
    );

    public function __construct()
    {
        $this->load->helper('format');
    }

    /**
     *
     * @param
     *            $id
     * @return mixed
     */
    public function get_info($id)
    {
        if ($id <= 0) {
            return false;
        }
        $this->db->from('purchase_orders');
        $this->db->where('id', $id);
        $query = $this->db->get();
       
        if ($query) {
            $entity = $query->row();
            $query->free_result();
            return $entity;
        }
        return false;
    }

    public function get_info_by_code($po_code)
    {
        if (empty($po_code)) {
            return false;
        }
        $this->db->from('purchase_orders');
        $this->db->where('po_code', $po_code);
        $query = $this->db->get();
        if ($query) {
            $entity = $query->row();
            $query->free_result();
            return $entity;
        }
        return false;
    }

    /**
     *
     * @param array $data
     * @return bool|mixed
     */
    public function save($data = [])
    {
        if ($this->exists_by_id($data['id'])) {
            if ($this->update($data)) {
                return ! empty($data['id']) ? $data['id'] : false;
            }
        } else {
            if ($this->db->insert('purchase_order', $data)) {
                return $this->db->insert_id();
            }
        }
        
        return false;
    }

    /**
     *
     * @param array $options
     * @return array
     */
    public function get_all($options = [])
    {
        $this->db->from('purchase_orders');
        if (! empty($options['limit'])) {
            $this->db->limit($options['limit']);
        }
        if (! empty($options['offset'])) {
            $this->db->offset($options['offset']);
        }
        
        if (! empty($options['query'])) {
            $this->db->like('name', $options['query']);
            $this->db->or_like('code', $options['query']);
            $this->db->or_like('description', $options['query']);
        }
        
        if (! empty($options['order_by']) && ! empty($options['order_field'])) {
            $this->db->order_by($options['order_field'], $options['order_by']);
        }
        
        $query = $this->db->get();
        return ! empty($query) ? $query->result_array() : [];
    }

    /**
     *
     * @param array $options
     * @return int
     */
    public function count_all()
    {
        $this->db->from('purchase_orders');
        $query = $this->db->get();
        return ! empty($query) ? $query->num_rows() : 0;
    }

    /**
     *
     * @param string $id
     * @return null
     */
    public function get($id = '')
    {
        $this->db->from('purchase_orders');
        $this->db->where('id', $id);
        $query = $this->db->get();
        if ($query->num_rows() == 1) {
            return $query->row_array();
        }
        return null;
    }

    /**
     *
     * @param
     *            $id
     * @return bool
     */
    public function exists_by_id($id)
    {
        $this->db->from('purchase_orders');
        $this->db->where('id', $id);
        $query = $this->db->get();
        return ($query->num_rows() == 1);
    }

    /**
     *   fix
     * @param
     *            $id
     * @param
     *            $data
     * @return mixed
     */
    public function update($id, $data)
    {
        $CI = & get_instance();
        $CI->load->helper('format');
        $CI->load->model('Employee');
        $items = $data['items'];
        unset($data['items']);
        $data['updated_at'] = time();
        if (! empty($data['receive_date'])) {
            $data['receive_date'] = date_to_timestamp($data['receive_date']);
        }
        $this->db->trans_start();
        $this->db->where('id', $id);
        $this->db->update('purchase_orders', $data);
        if (! empty($id) && ! empty($items)) {
            $this->db->delete('purchase_order_items', array(
                'purchase_order_id' => $id
            ));

            $this->db->delete('purchase_order_item_staged', array(
                'purchase_order_id' => $id
            ));
            // delete pruchase oder id staged
            // if ($id) {
            //     $this->db->where('purchase_order_id', $id);
            //     $this->db->delete('purchase_order_item_staged');
            // }
            // end
            foreach ($items as $item) {
                $row = array(
                    'purchase_order_id' => $id,
                    'item_id' => $item['item_id'],
                    'quantity' => $item['quantity'],
                    'month' => $item['month'],
                    'comment' => $item['comment'],
                    'json_staged' => json_encode($item['staged'])
                );
                $this->db->insert('purchase_order_items', $row);
                

                if (!empty($item['staged_item'][0]) ) {
                   foreach ($item['staged_item'] as $key) {
                         $row_staged  = array(
                            'purchase_order_id' =>  $id,
                            'item_id'  => $key['item_id'],
                            'quantity'  => $key['quantity'],
                            'month_staged'  => $key['month']
                        );
                                
                    $this->db->insert('purchase_order_item_staged', $row_staged); 
                    } /*END FOR*/
                } /*end if*/
            }
        }
        $this->db->trans_complete();
        return $id;
    }

    /**
     *
     * @param array $ids
     */
    public function delete($ids = [])
    {
        $this->db->where_in('id', $ids);
        $this->db->delete('purchase_orders');
    }

    /**
     * delete on table purchase oder item staged
     * @param  array  $ids [description]
     * @return [type]      [description]
     */
    public function delete_pruchase_oder_id_staged($ids = [])
    {
        $this->db->where_in('purchase_order_id', $ids);
        $this->db->delete('purchase_order_item_staged');
    }

    /**
     *
     * @return mixed
     */
    public function get_units()
    {
        $this->db->from('units');
        $this->db->where('deleted <> 1');
        $query = $this->db->get();
        $result = $query->result();
        $query->free_result();
        return $result;
    }

    /**
     *
     * @return mixed
     */
    public function get_suppliers()
    {
        $this->db->from('suppliers');
        $this->db->where('deleted <> 1');
        $query = $this->db->get();
        $result = $query->result();
        $query->free_result();
        return $result;
    }

    /**
     *
     * @param
     *            $conditions
     * @return array
     */
    public function get_items($conditions)
    {
        $this->db->from('items');
        $this->db->where('items.deleted != ', 1);
        if (! empty($conditions['supplier_id'])) {
            $this->db->join('items_suppliers', 'items_suppliers.item_id = items.item_id');
            $this->db->where('items_suppliers.person_id ', $conditions['supplier_id']);
            unset($conditions['supplier_id']);
        }
        if (! empty($conditions['dates'])) {
            unset($conditions['dates']);
        }
        $this->db->where($conditions);
        $query = $this->db->get();
        $result = $query->result();
        if (! empty($result)) {
            $query->free_result();
            $units = $this->get_units();
            $suppliers = $this->get_suppliers();
            $item_ids = [];
            if (! empty($units)) {
                $default_unit = $units[0];
                foreach ($result as $key => $row) {
                    foreach ($units as $unit) {
                        if (intval($row->unit_id) == intval($unit->id)) {
                            $result[$key]->unit = $unit;
                        } else {
                            $result[$key]->unit = $default_unit;
                        }
                    }
                    $item_ids[] = $row->item_id;
                }
            }
            $this->db->reset_query();
            $this->db->from('items_suppliers');
            $this->db->where_in('item_id', $item_ids);
            $query = $this->db->get();
            if ($query) {
                $items_suppliers = $query->result();
                $query->free_result();
                if (! empty($items_suppliers)) {
                    foreach ($result as $key => $row) {
                        foreach ($items_suppliers as $items_supplier) {
                            if (intval($items_supplier->item_id) == intval($row->item_id)) {
                                foreach ($suppliers as $supplier) {
                                    if ($supplier->person_id == $items_supplier->person_id) {
                                        $result[$key]->suppliers[$items_supplier->person_id] = $supplier;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        return $result;
    }

    /**
     *
     * @param array $dates
     * @return array
     */
    public function get_materials_plans($dates = array())
    {
        $this->db->from('mrp_materials_plans');
        $this->db->where( 'deleted !=', 1);
        $query = $this->db->get();
        $result = $query->result();
        $query->free_result();
        if (! empty($dates)) {
            $filter_result = array();
            foreach ($result as $key => $row) {
                $month = strtotime($row->month . '-01');
                $from = strtotime($dates[0]);
                $to = strtotime($dates[1]);
                if ($month >= $from && $month <= $to) {
                    $filter_result[] = $row;
                }
            }
            return $filter_result;
        }
        return $result;
    }

    /**
     *
     * @param
     *            $conditions
     * @return array
     */
    public function get_plan_items($conditions)
    {
        $this->load->helper('array');
        $this->load->helper('format');
        $items = $this->get_items($conditions);
        $item_ids = array();
        if (! empty($items)) {
            foreach ($items as $item) {
                $item_ids[] = get_data($item, 'item_id');
            }
        }
        if (! empty($item_ids)) {
            $item_quantities = $this->get_quantity_items($item_ids, get_data($conditions, 'dates'));
        }
        $materials_plans = $this->get_materials_plans($conditions['dates']);
        $plan_items = array();
        foreach ($materials_plans as $materials_plan) {
            if (! empty($items)) {
                foreach ($items as $item) {
                    if (! empty($item_quantities) && isset($item_quantities[get_data($item, 'item_id')])) {
                        $item->quantity_in_stock = $item_quantities[get_data($item, 'item_id')];
                    }
                    $detail = json_decode($materials_plan->detail);
                    $materials_plan->info = $detail;
                    if (is_object($detail)) {
                        $detail = object_to_array($detail);
                        foreach ($detail as $item_id => $item_detail) {
                            if (intval($item->item_id) == intval($item_id)) {
                                $item->material_plan = $materials_plan;
                                $plan_items[$item->item_id] = $item;
                            }
                        }
                    }
                }
            }
        }
        return $plan_items;
    }

    /**
     *
     * @param
     *            $data
     * @return $this
     */
    public function add($data)
    {
        $CI = & get_instance();
        $CI->load->helper('format');
        $CI->load->model('Employee');
        $items = $data['items'];
        unset($data['items']);
        $data['deleted'] = '0';
        $data['person_id'] = $CI->Employee->get_logged_in_employee_info()->person_id;
        $data['po_code'] = $this->generate_purchase_order_code($data['supplier_id']);
        $data['created_at'] = $data['updated_at'] = time();
        if (! empty($data['receive_date'])) {
            $data['receive_date'] = date_to_timestamp($data['receive_date']);
        }
        $this->db->trans_start();
        $this->db->insert('purchase_orders', $data);
        $id = $this->db->insert_id();
        if (! empty($id) && ! empty($items)) {
            foreach ($items as $item) {
                $row = array(
                    'purchase_order_id' => $id,
                    'item_id' => $item['item_id'],
                    'quantity' => $item['quantity'],
                    'month' => $item['month'],
                    'comment' => $item['comment'],
                    'json_staged' => json_encode($item['staged'])
                );

                $this->db->insert('purchase_order_items', $row);
                if ( !empty($item['staged_item'][0])) {
                   foreach ($item['staged_item'] as $key) {
                         $row_staged  = array(
                            'purchase_order_id' =>  $id,
                            'item_id'  => $key['item_id'],
                            'quantity'  => $key['quantity'],
                            'month_staged'  => $key['month']
                        );

                        $this->db->insert('phppos_purchase_order_item_staged', $row_staged);
                    }
                } /*end if*/
            }
        }

        $this->db->trans_complete();
        return $id;
    }

    /**
     *
     * @param
     *            $person_id
     * @return bool|string
     */
    public function generate_purchase_order_code($person_id)
    {
        $CI = & get_instance();
        $CI->load->model('Supplier');
        // Get Supplier
        $supplier = $CI->Supplier->get_info($person_id);
        if (empty($supplier)) {
            return false;
        }
        $purchase_order_code = ! empty($supplier->company_code) ? strtoupper($supplier->company_code) : strtoupper(create_slug($supplier->company_name));
        $begin = strtotime('first day of this month');
        $end = strtotime('last day of this month');
        $this->db->select('po_code');
        $this->db->where('supplier_id', $person_id);
        $this->db->where('(`receive_date` >= ' . $begin . ' AND `receive_date` <= ' . $end . ')');
        $this->db->from('purchase_orders');
        $list_po = $this->db->get()->result_array();
        if (! empty($list_po)) {
            $list_po_id = array_column($list_po, 'po_code');
            
            $list_po_id = array_map(function ($value) {
                return (int) explode('/', $value)[0];
            }, $list_po_id);
            $max_id = max($list_po_id);
        } else {
            $max_id = 0;
        }
        $purchase_order_code = ($max_id + 1) . '/' . date('m', time()) . '-' . $purchase_order_code;
        return $purchase_order_code;
    }


    /**
     *
     * @param
     *            $purchase_order_id
     * @return mixed
     */
    public function get_purchase_order_items($purchase_order_id)
    {
        $this->db->where('purchase_order_id', $purchase_order_id);
        $this->db->from('purchase_order_items');
        $this->db->join('items', 'items.item_id = purchase_order_items.item_id');
        $this->db->where('items.deleted !=', 1);
        $query = $this->db->get();
        if ($query) {
            $purchase_order_items = $query->result();
            $query->free_result();
            $items = array();
            $units = $this->get_units();
            foreach ($purchase_order_items as $row) {
                $default_unit = $units[0];
                $item = array(
                    'item_id' => get_data($row, 'item_id'),
                    'item_name' => get_data($row, 'name'),
                    'unit_id' => get_data($row, 'unit_id'),
                    'cost_price' => get_data($row, 'cost_price'),
                    'category_id' => get_data($row, 'category_id'),
                    'comment' => get_data($row, 'comment'),
                    'quantity' => get_data($row, 'quantity'),
                    'month' => get_data($row, 'month')
                );
                foreach ($units as $unit) {
                    if (intval($row->unit_id) == intval($unit->id)) {
                        $item['unit_name'] = get_data($unit, 'name');
                    } else {
                        $item['unit_name'] = get_data($default_unit, 'name');
                    }
                }
                $items[get_data($row, 'item_id')] = $item;
            }

            return $items;
        }
        return false;
    }

   
    /**
     * get total number of month (12 month)
     * @param  string $first_date  / timestamp  [start month]
     * @param  string $end_date / timestamp [end month]
     * @return [type]              [mix]
     */
    public function get_collection_for_item($item_id="", $first_date, $end_date )
    {
        //$this->db->select('purchase_orders.*, purchase_order_items.*');
        $this->db->select('sum(quantity) as total_month');
        $this->db->from('purchase_orders');
        $this->db->join('purchase_order_items', 'purchase_orders.id = purchase_order_items.purchase_order_id');
        $this->db->where('item_id', $item_id);
        $this->db->where_in('status', array('2','3'));
        $this->db->where('created_at >=', $first_date);
        $this->db->where('created_at <=', $end_date);
        $this->db->group_by('item_id');
        $query = $this->db->get();
        // echo $this->db->last_query();
        // echo "<br>";
        //die();
        if ($query) {
            $result = $query->row_array();
            $query->free_result();
            
            return $result;
        }
        return false;
    }
    /**
     * $first_date, $end_date
     */
    public function get_collection_staged_table($item_id="", $month)
    {
        $this->db->select('sum(quantity) as total_month_staged');
        $this->db->from('purchase_orders');
       
        $this->db->join('phppos_purchase_order_item_staged', 'purchase_orders.id = phppos_purchase_order_item_staged.purchase_order_id');
        $this->db->where('item_id', $item_id);
        $this->db->where_in('status', array('2','3'));
        $this->db->where('month_staged', $month);
        // $this->db->where('created_at >=', $first_date);
        // $this->db->where('created_at <=', $end_date);
        $this->db->group_by('item_id');
        $query = $this->db->get();
        //echo $this->db->last_query();
        if ($query) {
            $result = $query->row_array();
            $query->free_result();
            
            return $result;
        }
        return false;
    }

    /**
     * $first_date, $end_date // code test lấy bằng json 
     * [get_collection_for_item_staged description]
     * @param  string $item_id [description]
     * @return [type]          [description]
     */
    public function get_collection_for_item_staged($item_id="",$month){
        $this->db->select('*');
        $this->db->from('purchase_orders');
        $this->db->join('purchase_order_items', 'purchase_orders.id = purchase_order_items.purchase_order_id');
        $this->db->where('item_id', $item_id);
        $this->db->where('month', $month);
        $this->db->where_in('status', array('2','3'));
        //$this->db->where('created_at >=', $first_date);
        //$this->db->where('created_at <=', $end_date);
        //$this->db->group_by('item_id');
        $query = $this->db->get();
        // echo $this->db->last_query();
        // echo "<br>";
        $array_megre = [];
        if ($query) {
            $result = $query->result_array();
            $query->free_result();
            if (!empty($result)) {
                return $result;
            }
        }  
        return false;
    }

    /**
     *
     * @param null $start
     * @param null $limit
     * @param null $conditions
     * @param null $orders
     * @return bool
     */
    public function get_collection($start = null, $limit = null, $conditions = null, $orders = null)
    {
        $this->db->select('purchase_orders.*, people.last_name, people.first_name, people.full_name, suppliers.company_name');
        $this->db->from('purchase_orders');
        $this->db->join('suppliers', 'purchase_orders.supplier_id = suppliers.person_id');
        $this->db->join('people', 'people.person_id = purchase_orders.person_id');
        if (! empty($conditions)) {
            $this->db->where($conditions);
        }
        $this->db->where('purchase_orders.deleted', 0);
        $this->db->order_by($orders);
        $this->db->limit($limit, $start);
        $query = $this->db->get();
        if ($query) {
            $result = $query->result();
            $query->free_result();
            return $result;
        }
        return false;
    }

    /**
     * [sumQuantityStockById description]
     * stock_by_type = purchase_order,
     * stock_by_id = array();
     * item_id = id_item
     * @param  [type] $stock_by_id   [description]
     * @param  [type] $stock_by_type [description]
     * @param  [type] $item_id       [description]
     * @return [type]                [description]
     */
    public function sumQuantityStockById_all_of_item($stock_by_id, $stock_by_type, $item_id, $first_date=null, $end_date=null)
    {
        //$this->db->select('*');
        $this->db->select('SUM(quantity) AS total_qty');
        $this->db->from('stock_in');
        $this->db->join('purchase_orders', 'stock_in.stock_in_by_id = purchase_orders.id');
        $this->db->where_in('stock_in_by_id', $stock_by_id);
        $this->db->where('stock_in_by_type', $stock_by_type);
        $this->db->where('item_id',$item_id);
        $this->db->where('created_at >=', $first_date);
        $this->db->where('created_at <=', $end_date);
        $query = $this->db->get();
        //echo $this->db->last_query();
        //die(); 
        if ($query) {
            $result = $query->row_array();
            return $result['total_qty'];
            
        }
        return false;
    }

    /**
     * get id purchase_order of month date time
     * @param  [type] $start_date [description]
     * @param  [type] $end_date   [description]
     * @return [type]             [description]
     */
    function get_collection_of_date($start_date = null, $end_date = null){
        $this->db->select('*');
        $this->db->from('purchase_orders');   
        $this->db->where('deleted', 0);
        $this->db->where('created_at', $start_date);
        $this->db->where('created_at', $end_date);
        $query = $this->db->get();

        if ($query) {
            $result = $query->result();
            $query->free_result();
            return $result;
        }
        return false;
    }

    /**
     *
     * @return array
     */
    public function get_status_list()
    {
        $status_list = array();
        $status_list[self::STATUS_PENDING] = 'Chờ duyệt';
        $status_list[self::STATUS_APPROVED] = 'Đã duyệt';
        $status_list[self::STATUS_ACTION] = 'Thực hiện';
        $status_list[self::STATUS_COMPLETED] = 'Hoàn thành';
        $status_list[self::STATUS_CANCEL] = 'Hủy';
        return $status_list;
    }

    /**
     *
     * @param
     *            $id
     * @return string
     */
    public function get_invoice_url($id)
    {
        return site_url('purchase_orders/invoice/' . $id);
    }

    /**
     *
     * @param
     *            $item_ids
     * @return mixed
     */
    public function get_quantity_items($item_ids, $dates)
    {
        if (! empty($dates)) {
            $dates[0] = substr($dates[0], 0, 7);
            $dates[1] = substr($dates[1], 0, 7);
        }
        $this->db->reset_query();
        $this->db->select('item_id, SUM(quantity) AS total_quantity');
        $this->db->from('purchase_order_items');
        $this->db->where_in('item_id', $item_ids);
        $this->db->where_in('month', $dates);
        $this->db->group_by('item_id');
        $query = $this->db->get();
        $result = $query->result();
        $rendered_result = array();
        foreach ($result as $item) {
            $rendered_result[$item->item_id] = $item->total_quantity;
        }
        return $rendered_result;
    }

    public function get_category_of_items($data)
    {
        return reset($data)['category_id'];
    }

    function get_item_search_suggestions($search)
    {
        if (! trim($search)) {
            return array();
        }
        
        $suggestions = array();
        
        $this->db->select("id, po_code", false);
        $this->db->from('purchase_orders');
        $this->db->like('po_code', $search, 'both');
        $this->db->group_start();
        $this->db->where('status', self::STATUS_APPROVED);
        $this->db->or_where('status', self::STATUS_ACTION);
        $this->db->group_end();
        $this->db->limit('25');
        $by_additional_item_numbers = $this->db->get();
        $temp_suggestions = array();
        foreach ($by_additional_item_numbers->result() as $row) {
            $data = array(
                'label' => $row->po_code,
                'image' => base_url() . "assets/img/item.png",
                'category' => '',
                'item_number' => ''
            );
            
            $temp_suggestions[$row->id] = $data;
        }
        
        foreach ($temp_suggestions as $key => $value) {
            $suggestions[] = array(
                'value' => $key,
                'label' => $value['label'],
                'image' => $value['image'],
                'category' => $value['category'],
                'item_number' => $value['item_number']
            );
        }
        
        return $suggestions;
    }
    /**
     * get staged in table pruchase_oder_item
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    function get_staged_purchase_oder_item($id)
    {
        $this->db->select('item_id, json_staged');
        $this->db->from('purchase_order_items');
        $this->db->where('purchase_order_id', $id);

        $query = $this->db->get();
        
        if ($query) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        }
        return false;
    }
}
