<?php
namespace Models;
class Stock_in extends Stock
{
    /**
     * Stock constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Inventory');
        $this->load->model('Employee');
        $this->load->model('Purchase_order');
    }

    /**
     *
     * @param
     *            $stock_id
     * @return mixed
     */
    public function sumQuantityStockById($stock_by_id, $stock_by_type, $item_id)
    {
        $this->db->select('SUM(quantity) AS total_qty');
        $this->db->from('stock_in');
        $this->db->where('stock_in_by_id', $stock_by_id);
        $this->db->where('stock_in_by_type', $stock_by_type);
        $this->db->where('item_id',$item_id);
        return $this->db->get()->row_array()['total_qty'];
    }
    
    public function getNumberOfPackage($item_id)
    {
        $item_name_slug = create_slug($this->Item->get_info($item_id)->name,'-');
        $this->db->select('MAX(package_slug) as package_slug');
        $this->db->from('stock_package');
        $this->db->like('package_slug',$item_name_slug);
        $this->db->where('item_id',$item_id);
        $package_slug = $this->db->get();
        if (!empty($package_slug)) {
            return (int)explode('-',$package_slug->row_array()['package_slug'])[0]+1;
        }
        return '1';
    }
    /**
     *
     * @param
     *            $stock_id
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
     *
     * @param
     *            $stock_id
     * @param
     *            $data
     * @return mixed
     */
    function update($stock_id, $data)
    {
        $this->db->where('stock_in_id', $stock_id);
        $success = $this->db->update('stock_in', $data);
        return $success;
    }
    
    public function get_suppliers()
    {
        $this->db->from('suppliers');
        $this->db->where('deleted <> 1');
        $query = $this->db->get();
        $result = $query->result_array();
        $query->free_result();
        return $result;
    }
    
    public function get_list_po()
    {
        $this->db->select('DISTINCT sti.stock_in_by_id, po.po_code', false);
        $this->db->from('stock_in sti');
        $this->db->join('purchase_orders po', 'po.id = sti.stock_in_by_id');
        $this->db->where('sti.overflow_quantity >', 0);
        $this->db->where('sti.stock_in_by_type', STOCK_TYPE_PO);
        $query = $this->db->get();
        $result = $query->result_array();
        $query->free_result();
        return $result;
    }

    public function get_list_package_code()
    {
        $this->db->select('DISTINCT stp.package_slug, stp.package_code', false);
        $this->db->from('stock_in sti');
        $this->db->join('stock_package stp', 'sti.stock_id = stp.package_by_id');
        $this->db->where('sti.overflow_quantity >', 0);
        $this->db->where('sti.stock_in_by_type', STOCK_TYPE_PO);
        $this->db->where('stp.package_by_type', STOCK_PACKAGE_STOCK_IN);
        $query = $this->db->get();
        $result = $query->result_array();
        $query->free_result();
        return $result;
    }

    public function search_stock_temp_item($params)
    {
        $this->db->select('DISTINCT it.name, sti.stock_id, sti.item_id, sppl.company_name, po.po_code, stp.package_slug, stp.package_code,sti.overflow_quantity, sti.note, sti.status, st.location_id', false);
        $this->db->from('stock_in sti');
        $this->db->join('stock_package stp', '(sti.stock_id = stp.package_by_id) AND (sti.item_id = stp.item_id)');
        $this->db->join('purchase_orders po', 'po.id = sti.stock_in_by_id');
        $this->db->join('items it', 'it.item_id = sti.item_id');
        $this->db->join('stock st', 'st.stock_id = sti.stock_id');
        $this->db->join('suppliers sppl', 'st.supplier_id = sppl.person_id');
        $this->db->where('sti.stock_in_by_type', STOCK_TYPE_PO);
        $this->db->where('stp.package_by_type', STOCK_PACKAGE_STOCK_IN);
        $this->db->where('sti.overflow_quantity >', 0);
        if (empty($params['supplier_id']) && empty($params['po_id']) && empty($params['package_slug'])) {
            return [];
        }
        if (! empty($params['supplier_id'])) {
            $this->db->where('st.supplier_id', $params['supplier_id']);
        }
        
        if (! empty($params['po_id'])) {
            $this->db->where('sti.stock_in_by_id', $params['po_id']);
        }
        
        if (! empty($params['package_slug'])) {
            $this->db->where('stp.package_slug', $params['package_slug']);
        }
        $query = $this->db->get();
        $result = $query->result_array();
        $query->free_result();
        return $result;
    }
    
    public function transfer_stock_in_temp($items) {
        $row_inventory= [];
        $employee_id = $this->Employee->get_logged_in_employee_info()->person_id;
        if (empty($items)) {
            return false;
        }
        foreach ($items as $item) {
            $row_inventory[] = array(
                'trans_items' => $item['item_id'],
                'trans_user' => $employee_id,
                'trans_comment' => 'STOCK IN TEMP ' . $item['stock_id'],
                'trans_inventory' => $item['overflow_quantity'],
                'location_id' => $item['location_id']
            );
           if ( $this->update_qty_location_items(array(
                'location_id' => $item['location_id'],
                'item_id' => $item['item_id'],
                'quantity' => $item['overflow_quantity']
           )) ) {
               $this->db->where('item_id', $item['item_id']);
               $this->db->where('stock_id', $item['stock_id']);
               $this->db->update('stock_in', ['is_finish_stock_in' =>  1]);
           }
        }
        if (!empty($row_inventory)) {
            $this->db->insert_batch('inventory', $row_inventory);
        }
        return true;

    }
    
    function update_qty_location_items($data)
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
}
