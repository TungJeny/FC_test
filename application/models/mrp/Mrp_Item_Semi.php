<?php
namespace Models\Mrp;

class Mrp_Item_Semi extends \CI_Model
{

    public function save($item_id = 0, $records = [])
    {
        $this->delete_by_item($item_id);
        foreach ($records as $record) {
            $this->db->insert('mrp_items_semi', $record);
        }
        return true;
    }

    public function delete_by_item($item_id = 0)
    {
        $this->db->where_in('item_id', $item_id);
        $this->db->delete('mrp_items_semi');
    }

    public function get_all_semis_of_item($item_id = 0)
    {
        $this->db->select('items.*, units.name as unit, manufacturers.name as manufacturer, mrp_items_semi.qty as qty');
        $this->db->from('mrp_items_semi');
        $this->db->join('items', 'items.item_id = mrp_items_semi.semi_id');
        $this->db->join('units', 'units.id = items.unit_id', 'left');
        $this->db->join('manufacturers', 'items.manufacturer_id = manufacturers.id', 'left');
        $this->db->where('mrp_items_semi.item_id', $item_id);
        $query = $this->db->get();
        return ! empty($query) ? $query->result_array() : [];
    }
    
    public function list_item_semi_by_item_id($list_item_id) {
        if (!empty($list_item_id)) {
            $this->db->select('items.*,mrp_items_semi.item_id as parent_item, units.name as unit, manufacturers.name as manufacturer, mrp_items_semi.qty as qty');
            $this->db->from('mrp_items_semi');
            $this->db->join('items', 'items.item_id = mrp_items_semi.semi_id');
            $this->db->join('units', 'units.id = items.unit_id', 'left');
            $this->db->join('manufacturers', 'items.manufacturer_id = manufacturers.id', 'left');
            $this->db->where_in('mrp_items_semi.item_id', $list_item_id);
            $query = $this->db->get();
        }
        $results = [];
        if (! empty($query)) {
            $query_result = $query->result_array();
            foreach ($query_result as $result) {
                $results[$result['parent_item']][] = $result;
            }
        }
        return $results;
    }
}
