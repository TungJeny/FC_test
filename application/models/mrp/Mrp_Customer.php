<?php
namespace Models\Mrp;

import('model', 'Customer.php');

class Mrp_Customer extends \Customer
{

    public function get_id_by_code($code = '')
    {
        $this->db->from('customers');
        $this->db->where('customers.code', $code);
        $query = $this->db->get();
        $result = ! empty($query) ? $query->row_array() : [];
        return ! empty($result) ? $result['id'] : 0;
    }

    public function get_by_id($customer_id)
    {
        $this->db->from('customers');
        $this->db->join('people', 'people.person_id = customers.person_id');
        $this->db->where('customers.id', $customer_id);
        $query = $this->db->get();
        
        return ! empty($query) ? $query->row_array() : [];
    }
}
