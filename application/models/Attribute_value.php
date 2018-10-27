<?php

class Attribute_value extends CI_Model
{

    public function save_batch($records = [])
    {
        return $this->db->insert_batch('attribute_values', $records);
    }
    
    public function get_all_values($entity_id = '', $entity_type = '') {
        $this->db->select('attributes.*, attribute_values.value');
        $this->db->from('attribute_values');
        $this->db->join('attributes', 'attributes.id = attribute_values.attribute_id');
        $this->db->where('attribute_values.entity_id', $entity_id);
        $this->db->where('attribute_values.entity_type', $entity_type);
        $query = $this->db->get();
        return !empty($query) ? $query->result_array() : [];
    }
    
    public function delete_by_entity($entity_id = '', $entity_type = '') {
        $this->db->where('entity_id', $entity_id);
        $this->db->where('entity_type', $entity_type);
        $this->db->delete('attribute_values');
    }
}
