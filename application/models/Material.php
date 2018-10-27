<?php
namespace Models;
require_once 'Item.php';

class Material extends \Item
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
        $this->db->select('items.*, manufacturers.name as manufacturer, units.name as unit');
        $this->db->from('items');
        $this->db->join('manufacturers', 'manufacturers.id = items.manufacturer_id', 'left');
        $this->db->join('units', 'units.id = items.unit_id', 'left');
        $this->db->where('items.category_id',2);
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
}