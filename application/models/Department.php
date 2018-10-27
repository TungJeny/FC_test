<?php

class Department extends CI_Model
{
    public function save($data = [])
    {
        $data['location_id'] = !empty($data['location_id']) ? $data['location_id'] : $this->Employee->get_logged_in_employee_current_location_id() ;
        $old_department = !empty($data['id']) ? $this->get($data['id']) : null;
        if (empty($old_department)) {
            if (!$this->existsByCode($data['code']) && $this->db->insert('departments', $data)) {
                return $this->db->insert_id();
            }
        } else {
            if ($old_department['code'] == $data['code']) {
                $this->db->where('id', $old_department['id']);
                return $this->db->update('departments', $data);
            } elseif (!$this->existsByCode($data['code'])) {
                $this->db->where('id', $old_department['id']);
                return $this->db->update('departments', $data);
            }
        }
        return false;
    }
    
    public function getAll($options = [])
    {
        $this->db->from('departments');
        if (empty($options['location_id'])) {
            $this->db->where('location_id', $this->Employee->get_logged_in_employee_current_location_id());
        }
        
        if (!empty($options['limit'])) {
            $this->db->limit($options['limit']);
        }
        if (!empty($options['offset'])) {
            $this->db->offset($options['offset']);
        }
        
        $this->db->where('deleted != 1');
        
        if(!empty($options['query'])) {
            $this->db->like('name', $options['query']);
            $this->db->or_like('code', $options['query']);
            $this->db->or_like('description', $options['query']);
        }
        
        if(!empty($options['order_by']) && !empty($options['order_field'])) {
            $this->db->order_by($options['order_field'], $options['order_by']);
        }
        
        $query = $this->db->get();
        return !empty($query) ? $query->result_array() : [];
    }
    
    public function countAll($options = [])
    {
        $this->db->from('departments');
        if (empty($options['location_id'])) {
            $this->db->where('location_id', $this->Employee->get_logged_in_employee_current_location_id());
        }
        
        if(!empty($options['query'])) {
            $this->db->like('name', $options['query']);
            $this->db->or_like('code', $options['query']);
            $this->db->or_like('description', $options['query']);
        }
        
        $this->db->where('deleted != 1');
        $query = $this->db->get();
        return !empty($query) ? $query->num_rows() : 0;
    }
    
    public function getByCode($code = '') 
    {
        $this->db->from('departments');
        $this->db->where('code', $code);
        $this->db->where('location_id', $this->Employee->get_logged_in_employee_current_location_id());
        $query = $this->db->get();
        if ($query->num_rows() == 1) {
            return $query->row_array();
        }
        return false;
    }
    
    public function get($id = '')
    {
        $this->db->from('departments');
        $this->db->where('id', $id);
        $this->db->where('location_id', $this->Employee->get_logged_in_employee_current_location_id());
        $query = $this->db->get();
        if ($query->num_rows() == 1) {
            return $query->row_array();
        }
        return null;
    }
    
    function existsById($id)
    {
        $this->db->from('departments');
        $this->db->where('id', $id);
        $this->db->where('location_id', $this->Employee->get_logged_in_employee_current_location_id());
        $query = $this->db->get();
        return ($query->num_rows() == 1);
    }
    
    function existsByCode($code)
    {
        $this->db->from('departments');
        $this->db->where('code', $code);
        $this->db->where('location_id', $this->Employee->get_logged_in_employee_current_location_id());
        $query = $this->db->get();
        return ($query->num_rows() >= 1);
    }
    
    public function delete($ids = [])
    {
        $this->db->where_in('id', $ids);
        $this->db->where('location_id', $this->Employee->get_logged_in_employee_current_location_id());
        return $this->db->update('departments', array(
            'deleted' => 1,
            'code' => NULL
        ));
    }
}
