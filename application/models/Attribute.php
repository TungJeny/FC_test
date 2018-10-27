<?php

class Attribute extends CI_Model
{

    public function save($data = [])
    {
        if ($this->exists_by_id($data['id'])) {
            if ($this->update($data)) {
                return ! empty($data['id']) ? $data['id'] : false;
            }
        } else {
            if ($this->db->insert('attributes', $data)) {
                return $this->db->insert_id();
            }
        }
        
        return false;
    }

    public function get_all($options = [])
    {
        $this->db->from('attributes');
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

    public function get_all_for_attr_gr_combine(array $selected_attrs)
    {
        $this->db->select('id, name');
        $this->db->from('attributes');
        $list_attr = $this->db->get()->result_array();
        $results = [];
        foreach ($list_attr as $attr) {
            $results[$attr['id']] = $attr;
        }
        foreach ($selected_attrs as $attr ) {
            unset($results[$attr['id']]);
        }
        $results = array_map(function ($value) {
            $return['id'] = $value['id'];
            $return['label'] = $value['name'];
            return $return;
        }, $results);
        return $results;
    }

    public function countAll($options = [])
    {
        $this->db->from('attributes');
        if (! empty($options['query'])) {
            $this->db->like('name', $options['query']);
            $this->db->or_like('code', $options['query']);
            $this->db->or_like('description', $options['query']);
        }
        $query = $this->db->get();
        return ! empty($query) ? $query->num_rows() : 0;
    }

    public function get_by_code($code = '')
    {
        $this->db->from('attributes');
        $this->db->where('code', $code);
        $query = $this->db->get();
        if ($query->num_rows() == 1) {
            return $query->row_array();
        }
        return false;
    }

    public function get($id = '')
    {
        $this->db->from('attributes');
        $this->db->where('id', $id);
        $query = $this->db->get();
        if ($query->num_rows() == 1) {
            return $query->row_array();
        }
        return null;
    }

    public function exists_by_id($id)
    {
        $this->db->from('attributes');
        $this->db->where('id', $id);
        $query = $this->db->get();
        return ($query->num_rows() == 1);
    }

    public function exists_by_code($code)
    {
        $this->db->from('attributes');
        $this->db->where('code', $code);
        $query = $this->db->get();
        return ($query->num_rows() >= 1);
    }

    private function update(array $data)
    {
        $this->db->where('id', $data['id']);
        return $this->db->update('attributes', $data);
    }

    public function delete($ids = [])
    {
        $this->db->where_in('id', $ids);
        $this->db->delete('attributes');
    }
}
