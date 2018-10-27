<?php

namespace Models;
class Stock extends \CI_Model
{

    const TYPE_IN = 1;
    const TYPE_OUT = 2;
    const STATUS_PENDING = 1;
    const STATUS_ACCEPTED = 2;
    const STOCK_TYPE_PO = 'purchase_order';
    const STOCK_TYPE_PR = 'product';

    /**
     * Stock constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Inventory');
        $this->load->model('Employee');
    }

    /**
     * @param $id
     * @return mixed
     */
    public function get_stock($id)
    {
        // Get Stock
        $this->db->from('stock');
        $this->db->where('stock_id', $id);
        $result = $this->db->get();
        $stock = false;
        if ($result) {
            $stock = $result->row();
            $result->free_result();
            if (empty($stock)) {
                $stock = new \stdClass();
            }
            $stock->items = $this->get_items($id);
        }
        return $stock;
    }

    /**
     * [get_stock_stamp_view description]
     * @param  [type]  $id     [description]
     * @param  integer $offset [description]
     * @param  integer $limit  [description]
     * @return [type]          [description]
     */
    function get_stock_stamp_view_limit($id, $offset =0 ,$limit=100){
        $this->db->select('stock.*,stock_package.*
            ,people.first_name,
                            people.last_name,locations.name AS location');
        $this->db->from('stock');
        $this->db->join('stock_package', 'stock.stock_id = stock_package.package_by_id','left');
        $this->db->join('locations', 'stock.receiver_location_id = locations.location_id','left');
        $this->db->join('people', 'stock.employee_id = people.person_id','left');
        $this->db->where_in('stock_id', $id);
        $this->db->order_by('stock_id', 'DESC');
        $this->db->limit($limit, $offset);   
        $result = $this->db->get();

        if (!$result) {
            return false;
        }
        $query = $result->result_array();

        $stock = [];
        foreach ($query as $k => $v) {
                $stock[$k] = $v;
                $stock[$k]['items'] = $this->get_items($v['stock_id']);
                $stock[$k]['consignment'] = $this->get_consignment_package_name($v['package_id']);
            }
        return $stock;
    }
    /**
     * [get_consignment_package_name description]
     * @param  [type] $stock_package_id [description]
     * @return [type]                   [description]
     */
    function get_consignment_package_name($stock_package_id){
        $this->db->select('package_code');
        $this->db->from('stock_package');
        $this->db->where('id', $stock_package_id);
        $query = $this->db->get();
        if (!$query) {
            return false;
        }
        $row = $query->row_array();
        $query->free_result();
        return $row['package_code'];
    }

    /**
     * @param $stock_id
     * @param $data
     * @return mixed
     */
    function update_stock($stock_id, $data)
    {
        $this->db->where('stock_id', $stock_id);
        $success = $this->db->update('stock', $data);
        return $success;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function get_items($id)
    {
        // Get Items
        $this->db->from('stock_items');
        $this->db->where('stock_id', $id);
        $result = $this->db->get();
        $collection = array();
        if ($result) {
            $collection = $result->result_array();
            $result->free_result();
        }
        return $collection;
    }

    /**
     * @param $stock_id
     * @return mixed
     */
    public function get_info($stock_id)
    {
        $this->db->from('stock_in');
        $this->db->where('stock_in_id', $stock_id);
        return $this->db->get();
    }

    /**
     * @param $stock_id
     * @return bool
     */
    function exists($stock_id)
    {
        $this->db->from('stock_in');
        $this->db->where('stock_in_id', $stock_id);
        $query = $this->db->get();
        return ($query->num_rows() == 1);
    }

    /**
     * @param $stock_id
     * @param $data
     * @return mixed
     */
    function update($stock_id, $data)
    {
        $this->db->where('stock_in_id', $stock_id);
        $success = $this->db->update('stock_in', $data);
        return $success;
    }

    /**
     * @param $data
     * @return bool
     */
    function add($data)
    {
        $is_success = false;
        if (empty($data['items'])) {
            return $is_success;
        }
        $items = $data['items'];
        $stock_in_by_id = !empty($data['stock_in_by_id']) ? $data['stock_in_by_id'] : null;
        $stock_in_by_type = !empty($data['stock_in_by_type']) ? $data['stock_in_by_type'] : null;
        $data_stock = $data;
        unset($data_stock['items'], $data_stock['stock_in_by_id'], $data_stock['stock_in_by_type'], $data_stock['package_code']);
        $this->load->library('stock_lib');
        $this->db->insert('stock', $data_stock);
        $stock_id = $this->db->insert_id();
        if (!empty($stock_id)) {
            $this->db->trans_start();
            $data_stock_package = [];
            $stock_package = new Stock_package();
            if (!empty($data['package_code'])) {
                // Set Package Type (1: Element Package, 2: Product Package)
                if ($this->stock_lib->get_stock_type() == self::STOCK_TYPE_PR) {
                    $package_type = \Models\Stock_package::TYPE_PRODUCT;
                    $data_package = [
                        'package_by_id' => $stock_id,
                        'package_by_type' => STOCK_PACKAGE_STOCK_IN,
                        'package_code' => $data['package_code'],
                        'package_slug' => create_slug($data['package_code'], '-'),
                        'package_type'=> $package_type
                    ];
                    $stock_package->save($data_package);
                }
            }
            foreach ($items as $item) {
                // Create Item
                $row = array(
                    'stock_id' => $stock_id,
                    'item_id' => $item['item_id'],
                    'line' => $item['line'],
                    'name' => trim($item['name']),
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'quantity_received' => $item['quantity_received'],
                    'price' => $item['price'],
                    'default_cost_price' => $item['default_cost_price'],
                    'selling_price' => $item['selling_price'],
                    'note' => get_data($item, 'note')
                );
                $this->db->insert('stock_items', $row);
                // Update Inventory
                if ($data['type'] == self::TYPE_OUT) {
                    $item['quantity'] *= -1;
                }
                // Not Request
                if ($this->stock_lib->get_stock_type() != self::STOCK_TYPE_PR) {
                    $row = array(
                        'trans_items' => $item['item_id'],
                        'trans_user' => $data['employee_id'],
                        'trans_comment' => 'STOCK IN ' . $stock_id . ' ' . $data['comment'] . ($stock_in_by_id ? $stock_in_by_type . 'id' . $stock_in_by_id : ''),
                        'trans_inventory' => $item['quantity'],
                        'location_id' => $data['location_id']
                    );
                    $this->db->insert('inventory', $row);
                    $this->update_qty_location_items(array(
                        'location_id' => $data['location_id'],
                        'item_id' => $item['item_id'],
                        'quantity' => $item['quantity']
                    ));
                }
                if (!empty($stock_in_by_id)) {
                    $row = array(
                        'item_id' => $item['item_id'],
                        'stock_id' => $stock_id,
                        'stock_in_by_id' => $stock_in_by_id,
                        'quantity' => $item['quantity'],
                        'overflow_quantity' => $item['overflow_quantity'],
                        'status' => $item['status'],
                        'note' => $item['note'],
                        'stock_in_by_type' => $stock_in_by_type
                    );
                    $this->db->insert('stock_in', $row);
                }
                if (!empty($item['package_code'])) {
                    // Set Package Type (1: Element Package, 2: Product Package)
                    if ($this->stock_lib->get_stock_type() != self::STOCK_TYPE_PR) {
                        $package_type = \Models\Stock_package::TYPE_ITEM;
                        $data_stock_package[] = [
                            'package_by_id' => $stock_id,
                            'package_by_type' => STOCK_PACKAGE_STOCK_IN,
                            'package_code' => $item['package_code'],
                            'package_slug' => create_slug($item['package_code'], '-'),
                            'item_id' => $item['item_id'],
                            'package_type'=> $package_type
                        ];
                    }
                }
            }
            if (!empty($data_stock_package)) {
                $stock_package->save_batch($data_stock_package);
            }
            $this->db->trans_complete();
        }
        return $stock_id;
    }

    /**
     * @param $data
     * @return $this
     */
    public function update_qty_location_items($data)
    {
        // Get Current Location Quantity
        $tbl = 'location_items';
        $this->db->reset_query();
        $this->db->where('location_id', $data['location_id']);
        $this->db->where('item_id', $data['item_id']);
        $row = $this->db->get($tbl)->row();
        if ($row) {
            $data['quantity'] = intval($row->quantity) + intval($data['quantity']);
            $is_success = $this->db->update($tbl, array(
                'quantity' => $data['quantity']
            ), array(
                'location_id' => $data['location_id'],
                'item_id' => $data['item_id']
            ));
        } else {
            $is_success = $this->db->insert($tbl, $data);
        }
        return $is_success;
    }

    /**
     * @param $stock_id
     * @return mixed
     */
    function delete($stock_id)
    {
        $employee_id = $this->Employee->get_logged_in_employee_info()->person_id;
        $this->db->delete('stock_items', array(
            'stock_id' => $stock_id
        ));
        $this->db->where('stock_id', $stock_id);
        $this->db->update('stock_in', array(
            'deleted' => 1,
            'deleted_by' => $employee_id
        ));
        $this->db->where('stock_id', $stock_id);
        return $this->db->delete('stock', array(
            'stock_id' => $stock_id
        ));
    }

    /**
     * @param $stock_id
     * @return mixed
     */
    function get_stock_in_items($stock_id)
    {
        $this->db->from('stock_items');
        $this->db->where('stock_id', $stock_id);
        return $this->db->get();
    }

    /**
     * @param $stock_id
     * @return mixed
     */
    function get_supplier($stock_id)
    {
        $this->db->from('stock');
        $this->db->where('stock_id', $stock_id);
        $query = $this->db->get();
        if ($query) {
            $row = $query->row()->supplier_id;
            if (!empty($row->supplier_id)) {
                return $this->Supplier->get_info($row->supplier_id);
            }
        }
        return false;
    }

    /**
     * @param array $conditions
     * @return mixed
     */
    function get_stock_requests($conditions = array(), $limit = 1000) {
        $this->db->select('stock.*,people.first_name,people.last_name,locations.name AS location');
        $this->db->from('stock');
        $this->db->where('stock.deleted <> 1');
        if (!empty($conditions)) {
            $this->db->where($conditions);
        }
        $this->db->where('status', self::STATUS_PENDING);
        $this->db->where('type', self::TYPE_IN);
        $this->db->join('people', 'stock.employee_id = people.person_id');
        $this->db->join('locations', 'stock.location_id = locations.location_id');
        $this->db->limit($limit);
        $query = $this->db->get();
        if ($query) {
            $result = $query->result();
            $query->free_result();
            return $result;
        }
        return $query;
    }  

    /**
     * @return string
     */
    function get_product_package_code() {
        return ($this->count_stock_in_year() + 1) . '-' . date('m') . '-' . date('y');
    }

    public function count_stock_in_year() {
        $this->db->select('COUNT(stock_id) AS `count_stock_in`');
        $this->db->where('type', self::TYPE_IN);
        $this->db->where('created_at >= ', strtotime('first day of this year'));
        $this->db->where('created_at <= ', strtotime('last day of this year'));
        $this->db->from('stock');
        $query = $this->db->get();
        if (!$query) {
            return 0;
        }
        $row = $query->row();
        $query->free_result();
        return get_data($row, 'count_stock_in');
    }

    /**
     * @param $stock_id
     * @return mixed
     */
    function get_product_package($stock_id) {
        $this->db->from('stock_package');
        $this->db->where('package_by_id', $stock_id);
        $this->db->limit(1);
        $query = $this->db->get();
        if ($query) {
            $result = $query->row();
            $query->free_result();
            return $result;
        }
        return $query;
    }

    /**
     * @param $item_id
     * @param $data
     * @return $this
     */
    function update_item($item_id, $data) {
        $this->db->set($data);
        $this->db->where('item_id', $item_id);
        $this->db->update('stock_items');
        return $this;
    }

    /**
     * @param $stock_id
     * @param $item_id
     * @return $this
     */
    function remove_item($stock_id, $item_id) {
        $this->db->where('stock_id', $stock_id);
        $this->db->where('item_id', $item_id);
        $this->db->delete('stock_items');
        return $this;
    }
}