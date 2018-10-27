<?php
namespace Models;

class Stock_package extends \CI_Model
{

    const TYPE_ITEM = 1;

    const TYPE_PRODUCT = 2;

    /**
     *
     * @param
     *            $data
     */
    public function save($data)
    {
        $this->db->insert('stock_package', $data);
    }
    
    public function save_batch($data)
    {
        $this->db->insert_batch('stock_package', $data);
    }

    /**
     *
     * @param
     *            $search
     * @param int $limit
     * @return array
     */
    function get_search_suggestions($search, $limit = 25, $conditions = null)
    {
        if (! trim($search)) {
            return array();
        }
        $suggestions = array();
        $this->db->from('stock_package');
        $this->db->like('package_code', $search, 'both');
        if (! empty($conditions)) {
            $this->db->where($conditions);
        }
        $this->db->limit($limit);
        $result = $this->db->get();
        if (! empty($result)) {
            foreach ($result->result() as $row) {
                $data = array(
                    'value' => $row->id,
                    'label' => $row->package_code
                );
                $suggestions[$row->id] = $data;
            }
        }
        return $suggestions;
    }

    /**
     *
     * @param
     *            $id
     * @return mixed
     */
    public function get_info($id)
    {
        $this->db->from('stock_package');
        $this->db->where('id', $id);
        $query = $this->db->get();
        if ($query) {
            $row = $query->row();
            $query->free_result();
            return $row;
        }
        return null;
    }

    /**
     *
     * @param
     *            $id
     * @return bool
     */
    function exists($id)
    {
        $this->db->from('stock_package');
        $this->db->where('id', $id);
        $query = $this->db->get();
        return ($query->num_rows() == 1);
    }

    public function get_item_for_stock_out($item_id)
    {
        $this->db->select(' stkp.id, 
                            stkp.item_id,
                            unit.name as unit_name, 
                            it.name as item_name, 
                            stki.stock_id,
                            stkp.id as package_id,
                            stkp.package_code,  
                            (stki.quantity+stki.overflow_quantity) as total_quantity');
        $this->db->from('stock_package stkp');
        $this->db->join('stock_in stki', 'stki.stock_id = stkp.package_by_id');
        $this->db->join('items it', 'stkp.item_id = it.item_id');
        $this->db->join('units unit', 'unit.id = it.unit_id');
        $this->db->where('stkp.package_type', 1);
        $this->db->where('stkp.item_id', $item_id);
        $this->db->where('stki.item_id', $item_id);
        $this->db->where(' (stki.quantity+stki.overflow_quantity) >', 0);
       
        $stock_out_package = $this->db->get()->result_array();
        $list_package_id = array_column($stock_out_package, 'id');
        $qty_stock_out = $this->get_qty_stock_out_package($item_id, $list_package_id);
        foreach ($stock_out_package as &$stock_out) {
            $stock_out['total_stock_qty'] = 0;
            if (! empty($qty_stock_out[$stock_out['id']])) {
                $stock_out['total_stock_qty'] = $qty_stock_out[$stock_out['id']];
            }
        }
        return $stock_out_package;
    }

    public function get_qty_stock_out_package($item_id, $list_package_id)
    {
        $result = [];
        if (! empty($list_package_id)) {
            $this->db->select('stko.stock_out_by_id, SUM(stko.quantity) as quantity');
            $this->db->from('stock_out stko');
            $this->db->where('stko.item_id', $item_id);
            $this->db->where_in('stko.stock_out_by_id', $list_package_id);
            $this->db->where('stko.stock_out_by_type', Stock_out::STOCK_TYPE_PACKAGE);
            $this->db->group_by('stko.stock_out_by_id');
            $qty_stock_out = $this->db->get()->result_array();
            foreach ($qty_stock_out as $stock_out) {
                $result[$stock_out['stock_out_by_id']] = $stock_out['quantity'];
            }
        } 
        return $result;
    }
}