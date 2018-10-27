<?php

require_once 'Item.php';

class Product extends Item
{
    /**
     * @var array
     */
    protected $_category_ids = array(1,6,7);

    /**
     * @param string $search
     * @param int $limit
     * @return array
     */
    public function get_search_suggestions($search = '', $limit = 25)
    {
        $this->db->select('items.*, manufacturers.name as manufacturer, units.name as unit, categories.name as category');
        $this->db->from('items');
        $this->db->join('manufacturers', 'manufacturers.id = items.manufacturer_id');
        $this->db->join('categories', 'items.category_id = categories.id');
        $this->db->join('units', 'units.id = items.unit_id');
        $this->db->where_in('items.category_id', $this->_category_ids);
        $this->db->where('items.deleted != 1');
        $this->db->limit($limit);
        if(!empty($search)) {
            $this->db->where("(items.name LIKE '%". $search ."%' OR items.product_id LIKE '%". $search ."%' OR items.item_number LIKE '%". $search ."%')");
        }
        $query = $this->db->get();
        if ($query) {
            $suggestions = !empty($query) ? $query->result_array() : [];
            $suggestions = array_map(function($suggestion){
                $suggestion['label'] = $suggestion['name'];
                $suggestion['value'] = $suggestion['item_id'];
                return $suggestion;
            }, $suggestions);
            return $suggestions;
        }
        return false;
    }
    
    /**
     * @param int $id
     * @return bool
     */
    function exists($id)
    {
        $this->db->from('items');
        $this->db->where('item_id', $id);
        $this->db->where('items.deleted != 1');
        $query = $this->db->get();
        return ($query->num_rows() == 1);
    }
}