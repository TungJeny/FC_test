<?php

class Attribute_group extends CI_Model
{

    protected $all_related_objects;

    public function __construct()
    {
        $this->all_related_objects = array(
            'customer' => lang('module_customer'),
            'item' => lang('module_item'),
            'sale' => lang('module_sale')
        );
    }

    public function save($data = [])
    {
        if ($this->exists_by_id( $data['id'])) {
            if ($this->update($data)) {
                return ! empty($data['id']) ? $data['id'] : false;
            }
        } else {
            if ($this->db->insert('attribute_groups', $data)) {
                return $this->db->insert_id();
            }
        }
        return false;
    }

    public function getAll($options = [])
    {
        $this->db->from('attribute_groups');
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
        
        if (!empty($options['related_object'])) {
            $this->db->like('related_object', $options['related_object']);
        }
        
        $query = $this->db->get();
        $results = ! empty($query) ? $query->result_array() : [];
//         foreach ($results as &$result) {
//             $related_object = explode(',', $result['related_object']);
//             $related_object = array_map(function ($module_id) {
//                 return lang('module_' . $module_id);
//             }, $related_object);
//             $result['related_object'] = implode(', ', $related_object);
//         }
        return $results;
    }

    public function countAll($options = [])
    {
        $this->db->from('attribute_groups');
        $query = $this->db->get();
        return ! empty($query) ? $query->num_rows() : 0;
    }

    public function get_by_code($code = '')
    {
        $this->db->from('attribute_groups');
        $this->db->where('code', $code);
        $query = $this->db->get();
        if ($query->num_rows() == 1) {
            return $query->row_array();
        }
        return false;
    }
    
    

    public function get($id = '')
    {
        $this->db->from('attribute_groups');
        $this->db->where('id', $id);
        $query = $this->db->get();
        if ($query->num_rows() == 1) {
            return $query->row_array();
        }
        return null;
    }

    public function exists_by_id($id)
    {
        $this->db->from('attribute_groups');
        $this->db->where('id', $id);
        $query = $this->db->get();
        return ($query->num_rows() == 1);
    }

    public function exists_by_code($code)
    {
        $this->db->from('attribute_groups');
        $this->db->where('code', $code);
        $query = $this->db->get();
        return ($query->num_rows() >= 1);
    }

    private function update(array $data)
    {
        $this->db->where('id', $data['id']);
        return $this->db->update('attribute_groups', $data);
    }

    public function delete($ids = [])
    {
        $this->db->where_in('attr_group_id', $ids);
        $this->db->delete('attribute_groups_combine');
        $this->db->where_in('id', $ids);
        $this->db->delete('attribute_groups');
    }

    public function get_all_related_obj()
    {
        return $this->all_related_objects;
    }

    public function get_related_attr($id)
    {
        $this->db->select('a.id, a.name');
        $this->db->from('attribute_groups_combine agc');
        $this->db->join('attributes a', 'a.id = agc.attr_id');
        $this->db->where('agc.attr_group_id', $id);
        $results = $this->db->get()->result_array();
        $results = array_map(function ($value) {
            $return['id'] = $value['id'];
            $return['label'] = $value['name'];
            return $return;
        }, $results);
        return $results;
    }
    
    public function get_attributes_by_group($group_id)
    {
        $this->db->select('attributes.*');
        $this->db->from('attribute_groups_combine');
        $this->db->join('attributes', 'attributes.id = attribute_groups_combine.attr_id');
        $this->db->where('attribute_groups_combine.attr_group_id', $group_id);
        $query = $this->db->get();
        return !empty($query) ? $query->result_array() : [];
    }

    public function save_attr_combine($attr_group_id, array $selected_attr)
    {
        $data_selected_attr = array_map(function ($value) use ($attr_group_id) {
            $return['attr_id'] = $value['id'];
            $return['attr_group_id'] = $attr_group_id;
            return $return;
        }, $selected_attr);
        $this->db->trans_start();
        $this->db->where('attr_group_id', $attr_group_id);
        $this->db->delete('attribute_groups_combine');
        if (! empty($data_selected_attr)) {
            $this->db->insert_batch('attribute_groups_combine', $data_selected_attr);
        }
        $this->db->trans_complete();
    }
}
