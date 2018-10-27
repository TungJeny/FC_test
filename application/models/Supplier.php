<?php

class Supplier extends Person
{
    /**
     * Save suppliers for item
     * 
     * @param int $item_id
     * @param array $suppliers: list of person_ids
     * @return boolean
     */
    public function save_suppliers_for_item($item_id, $suppliers)
    {
        $this->db->delete('items_suppliers', array(
            'item_id' => $item_id
        ));
        $suppliers = explode(',', $suppliers);
        foreach ($suppliers as $supplier) {
            if ($supplier != '') {
                $supplier = trim($supplier);
                if (is_numeric($supplier) && $this->exists($supplier)) {
                    $this->db->insert('items_suppliers', array(
                        'item_id' => $item_id,
                        'person_id' => $supplier
                    ));
                }
            }
        }
        return TRUE;
    }
    
    /**
     * get name suppliers item 
     * @param  [type] $item_id [description]
     * @return [type]          [description]
     */
    function get_suppliers_for_item_name($item_id)
    {
        $this->db->select('suppliers.company_name, suppliers.person_id');
        $this->db->from('items_suppliers');
        $this->db->join('suppliers', 'items_suppliers.person_id=suppliers.person_id');
        $this->db->where('items_suppliers.item_id', $item_id);
        $return = array();
        foreach ($this->db->get()->result_array() as $result) {
            $return[] = trim($result['company_name']);
        }
        return $return;
    }
    
    function get_suppliers_for_item($item_id)
    {
        $this->db->select('suppliers.company_name, suppliers.person_id');
        $this->db->from('items_suppliers');
        $this->db->join('suppliers', 'items_suppliers.person_id=suppliers.person_id');
        $this->db->where('items_suppliers.item_id', $item_id);
        $return = array();
        foreach ($this->db->get()->result_array() as $result) {
            $return[] = trim($result['person_id']);
        }
        return $return;
    }

    /*
     * Determines if a given person_id is a customer
     */
    function exists($person_id)
    {
        $this->db->from('suppliers');
        $this->db->join('people', 'people.person_id = suppliers.person_id');
        $this->db->where('suppliers.person_id', $person_id);
        $query = $this->db->get();
        
        return ($query->num_rows() == 1);
    }

    /*
     * Returns all the suppliers
     */
    function get_all($limit = 10000, $offset = 0, $col = 'company_name', $order = 'asc')
    {
        $order_by = '';
        if (! $this->config->item('speed_up_search_queries')) {
            $order_by = "ORDER BY " . $col . " " . $order;
        }
        
        $people = $this->db->dbprefix('people');
        $suppliers = $this->db->dbprefix('suppliers');
        $data = $this->db->query("SELECT *,${people}.person_id as pid 
						FROM " . $people . "
						JOIN " . $suppliers . " ON 										                       
						" . $people . ".person_id = " . $suppliers . ".person_id
						WHERE deleted =0 $order_by
						LIMIT  " . $offset . "," . $limit);
        
        return $data;
    }

    function account_number_exists($account_number)
    {
        $this->db->from('suppliers');
        $this->db->where('account_number', $account_number);
        $query = $this->db->get();
        
        return ($query->num_rows() == 1);
    }

    function supplier_id_from_account_number($account_number)
    {
        $this->db->from('suppliers');
        $this->db->where('account_number', $account_number);
        $query = $this->db->get();
        
        if ($query->num_rows() == 1) {
            return $query->row()->person_id;
        }
        
        return false;
    }

    function count_all()
    {
        $this->db->from('suppliers');
        $this->db->where('deleted', 0);
        return $this->db->count_all_results();
    }

    /*
     * Gets information about a particular supplier
     */
    function get_info($supplier_id, $can_cache = FALSE)
    {
        if ($can_cache) {
            static $cache = array();
            
            if (isset($cache[$supplier_id])) {
                return $cache[$supplier_id];
            }
        } else {
            $cache = array();
        }
        
        $this->db->from('suppliers');
        $this->db->join('people', 'people.person_id = suppliers.person_id');
        $this->db->where('suppliers.person_id', $supplier_id);
        $query = $this->db->get();
        
        if ($query->num_rows() == 1) {
            $cache[$supplier_id] = $query->row();
            return $cache[$supplier_id];
        } else {
            // Get empty base parent object, as $supplier_id is NOT an supplier
            $person_obj = parent::get_info(- 1);
            
            // Get all the fields from supplier table
            $fields = $this->db->list_fields('suppliers');
            
            // append those fields to base parent object, we we have a complete empty object
            foreach ($fields as $field) {
                $person_obj->$field = '';
            }
            
            return $person_obj;
        }
    }

    /*
     * Gets information about multiple suppliers
     */
    function get_multiple_info($suppliers_ids)
    {
        $this->db->from('suppliers');
        $this->db->join('people', 'people.person_id = suppliers.person_id');
        $this->db->where_in('suppliers.person_id', $suppliers_ids);
        $this->db->order_by("last_name", "asc");
        return $this->db->get();
    }

    /*
     * Inserts or updates a suppliers
     */
    function save_supplier(&$person_data, &$supplier_data, $supplier_id = false)
    {
        $success = false;
        
        if (parent::save($person_data, $supplier_id)) {
            
            if ($supplier_id && $this->exists($supplier_id)) {
                $supplier_info = $this->get_info($supplier_id);
                
                $current_balance = $supplier_info->balance;
                
                // Insert store balance transaction when manually editing
                if (isset($supplier_data['balance']) && $supplier_data['balance'] != $current_balance) {
                    $store_account_transaction = array(
                        'supplier_id' => $supplier_id,
                        'receiving_id' => NULL,
                        'comment' => lang('common_manual_edit_of_balance'),
                        'transaction_amount' => $supplier_data['balance'] - $current_balance,
                        'balance' => $supplier_data['balance'],
                        'date' => date('Y-m-d H:i:s')
                    );
                    
                    $this->db->insert('supplier_store_accounts', $store_account_transaction);
                }
            }
            
            if (! $supplier_id or ! $this->exists($supplier_id)) {
                $supplier_data['person_id'] = $person_data['person_id'];
                $success = $this->db->insert('suppliers', $supplier_data);
            } else {
                $this->db->where('person_id', $supplier_id);
                $success = $this->db->update('suppliers', $supplier_data);
            }
        }
        
        return $success;
    }

    /*
     * Deletes one supplier
     */
    function delete($supplier_id)
    {
        $supplier_info = $this->Supplier->get_info($supplier_id);
        
        if ($supplier_info->image_id !== NULL) {
            $this->load->model('Appfile');
            $this->Person->update_image(NULL, $supplier_id);
            $this->Appfile->delete($supplier_info->image_id);
        }
        
        $this->db->where('person_id', $supplier_id);
        return $this->db->update('suppliers', array(
            'deleted' => 1
        ));
    }

    /*
     * Deletes a list of suppliers
     */
    function delete_list($supplier_ids)
    {
        foreach ($supplier_ids as $supplier_id) {
            $supplier_info = $this->Supplier->get_info($supplier_id);
            
            if ($supplier_info->image_id !== NULL) {
                $this->load->model('Appfile');
                $this->Person->update_image(NULL, $supplier_id);
                $this->Appfile->delete($supplier_info->image_id);
            }
        }
        
        $this->db->where_in('person_id', $supplier_ids);
        return $this->db->update('suppliers', array(
            'deleted' => 1
        ));
    }

    /*
     * Get search suggestions to find suppliers
     */
    function get_supplier_search_suggestions($search, $limit = 25)
    {
        if (! trim($search)) {
            return array();
        }
        
        $suggestions = array();
        
        $this->db->select("company_name,email,image_id,suppliers.person_id", false);
        $this->db->from('suppliers');
        $this->db->join('people', 'suppliers.person_id=people.person_id');
        $this->db->where('deleted', 0);
        $this->db->like("company_name", $search, 'both');
        $this->db->limit($limit);
        
        $by_company_name = $this->db->get();
        
        $temp_suggestions = array();
        foreach ($by_company_name->result() as $row) {
            $data = array(
                'name' => $row->company_name,
                'email' => $row->email,
                'avatar' => $row->image_id ? app_file_url($row->image_id) : base_url() . "assets/img/user.png"
            );
            
            $temp_suggestions[$row->person_id] = $data;
        }
        
        $this->load->helper('array');
        uasort($temp_suggestions, 'sort_assoc_array_by_name');
        foreach ($temp_suggestions as $key => $value) {
            $suggestions[] = array(
                'value' => $key,
                'label' => $value['name'],
                'avatar' => $value['avatar'],
                'subtitle' => $value['email']
            );
        }
        
        $this->db->select("first_name,last_name,email,image_id,suppliers.person_id", false);
        $this->db->from('suppliers');
        $this->db->join('people', 'suppliers.person_id=people.person_id');
        
        $this->db->where("(first_name LIKE '%" . $this->db->escape_like_str($search) . "%' or 
			last_name LIKE '%" . $this->db->escape_like_str($search) . "%' or 
			full_name LIKE '%" . $this->db->escape_like_str($search) . "%') and deleted=0");
        
        $this->db->limit($limit);
        
        $by_name = $this->db->get();
        
        $temp_suggestions = array();
        foreach ($by_name->result() as $row) {
            $data = array(
                'name' => $row->first_name . ' ' . $row->last_name,
                'email' => $row->email,
                'avatar' => $row->image_id ? app_file_url($row->image_id) : base_url() . "assets/img/user.png"
            );
            
            $temp_suggestions[$row->person_id] = $data;
        }
        
        uasort($temp_suggestions, 'sort_assoc_array_by_name');
        foreach ($temp_suggestions as $key => $value) {
            $suggestions[] = array(
                'value' => $key,
                'label' => $value['name'],
                'avatar' => $value['avatar'],
                'subtitle' => $value['email']
            );
        }
        
        $this->db->select("first_name, last_name, email,image_id,suppliers.person_id", false);
        $this->db->from('suppliers');
        $this->db->join('people', 'suppliers.person_id=people.person_id');
        $this->db->where('deleted', 0);
        $this->db->like('email', $search, 'both');
        $this->db->limit($limit);
        
        $by_email = $this->db->get();
        
        $temp_suggestions = array();
        foreach ($by_email->result() as $row) {
            $data = array(
                'name' => $row->first_name . ' ' . $row->last_name,
                'email' => $row->email,
                'avatar' => $row->image_id ? app_file_url($row->image_id) : base_url() . "assets/img/user.png"
            );
            
            $temp_suggestions[$row->person_id] = $data;
        }
        
        uasort($temp_suggestions, 'sort_assoc_array_by_name');
        
        foreach ($temp_suggestions as $key => $value) {
            $suggestions[] = array(
                'value' => $key,
                'label' => $value['name'],
                'avatar' => $value['avatar'],
                'subtitle' => $value['email']
            );
        }
        
        $this->db->select("phone_number,email,image_id,suppliers.person_id", false);
        $this->db->from('suppliers');
        $this->db->join('people', 'suppliers.person_id=people.person_id');
        $this->db->where('deleted', 0);
        $this->db->like('phone_number', $search, 'both');
        $this->db->limit($limit);
        
        $by_phone = $this->db->get();
        
        $temp_suggestions = array();
        foreach ($by_phone->result() as $row) {
            $data = array(
                'name' => $row->phone_number,
                'email' => $row->email,
                'avatar' => $row->image_id ? app_file_url($row->image_id) : base_url() . "assets/img/user.png"
            );
            
            $temp_suggestions[$row->person_id] = $data;
        }
        
        uasort($temp_suggestions, 'sort_assoc_array_by_name');
        foreach ($temp_suggestions as $key => $value) {
            $suggestions[] = array(
                'value' => $key,
                'label' => $value['name'],
                'avatar' => $value['avatar'],
                'subtitle' => $value['email']
            );
        }
        
        $this->db->select("account_number,email,image_id,suppliers.person_id", false);
        $this->db->from('suppliers');
        $this->db->join('people', 'suppliers.person_id=people.person_id');
        $this->db->where('deleted', 0);
        $this->db->like('account_number', $search, 'both');
        $this->db->limit($limit);
        
        $by_account_number = $this->db->get();
        
        $temp_suggestions = array();
        foreach ($by_account_number->result() as $row) {
            $data = array(
                'name' => $row->account_number,
                'email' => $row->email,
                'avatar' => $row->image_id ? app_file_url($row->image_id) : base_url() . "assets/img/user.png"
            );
            
            $temp_suggestions[$row->person_id] = $data;
        }
        
        uasort($temp_suggestions, 'sort_assoc_array_by_name');
        foreach ($temp_suggestions as $key => $value) {
            $suggestions[] = array(
                'value' => $key,
                'label' => $value['name'],
                'avatar' => $value['avatar'],
                'subtitle' => $value['email']
            );
        }
        
        // only return $limit suggestions
        if (count($suggestions > $limit)) {
            $suggestions = array_slice($suggestions, 0, $limit);
        }
        return $suggestions;
    }

    /*
     * Preform a search on suppliers
     */
    function search($search, $limit = 20, $offset = 0, $column = 'last_name', $orderby = 'asc')
    {
        // The queries are done as 2 unions to speed up searches to use indexes.
        // When doing OR WHERE across 2 tables; performance is not good
        $this->db->select('*,people.person_id as pid');
        $this->db->from('suppliers');
        $this->db->join('people', 'suppliers.person_id=people.person_id');
        
        if ($search) {
            $this->db->where("(first_name LIKE '%" . $this->db->escape_like_str($search) . "%' or 
					last_name LIKE '%" . $this->db->escape_like_str($search) . "%' or 
					email LIKE '%" . $this->db->escape_like_str($search) . "%' or 
					phone_number LIKE '%" . $this->db->escape_like_str($search) . "%' or 
					full_name LIKE '%" . $this->db->escape_like_str($search) . "%') and deleted=0");
        } else {
            $this->db->where('deleted', 0);
        }
        
        $people_search = $this->db->get_compiled_select();
        $this->db->select('*,people.person_id as pid');
        $this->db->from('suppliers');
        $this->db->join('people', 'suppliers.person_id=people.person_id');
        
        if ($search) {
            $this->db->where("(account_number LIKE '%" . $this->db->escape_like_str($search) . "%' or 
					company_name LIKE '%" . $this->db->escape_like_str($search) . "%') and deleted=0");
        } else {
            $this->db->where('deleted', 0);
        }
        
        $supplier_search = $this->db->get_compiled_select();
        
        $order_by = '';
        if (! $this->config->item('speed_up_search_queries')) {
            $order_by = " ORDER BY $column $orderby ";
        }
        
        return $this->db->query($people_search . " UNION " . $supplier_search . " $order_by LIMIT $limit OFFSET $offset");
    }

    function search_count_all($search, $limit = 10000)
    {
        // The queries are done as 2 unions to speed up searches to use indexes.
        // When doing OR WHERE across 2 tables; performance is not good
        $this->db->from('suppliers');
        $this->db->join('people', 'suppliers.person_id=people.person_id');
        
        if ($search) {
            $this->db->where("(first_name LIKE '%" . $this->db->escape_like_str($search) . "%' or 
				last_name LIKE '%" . $this->db->escape_like_str($search) . "%' or 
				email LIKE '%" . $this->db->escape_like_str($search) . "%' or 
				phone_number LIKE '%" . $this->db->escape_like_str($search) . "%' or 
				full_name LIKE '%" . $this->db->escape_like_str($search) . "%') and deleted=0");
        } else {
            $this->db->where('deleted', 0);
        }
        
        $people_search = $this->db->get_compiled_select();
        
        $this->db->from('suppliers');
        $this->db->join('people', 'suppliers.person_id=people.person_id');
        
        if ($search) {
            $this->db->where("(account_number LIKE '%" . $this->db->escape_like_str($search) . "%' or 
				company_name LIKE '%" . $this->db->escape_like_str($search) . "%') and deleted=0");
        } else {
            $this->db->where('deleted', 0);
        }
        
        $supplier_search = $this->db->get_compiled_select();
        
        $result = $this->db->query($people_search . " UNION " . $supplier_search);
        return $result->num_rows();
    }

    function find_supplier_id($search)
    {
        if ($search) {
            $this->db->select("suppliers.person_id");
            $this->db->from('suppliers');
            $this->db->join('people', 'suppliers.person_id=people.person_id');
            
            // Can't use full text index due to transactions not being able to use this info
            $this->db->where("(first_name LIKE '%" . $this->db->escape_like_str($search) . "%' or 
			last_name LIKE '%" . $this->db->escape_like_str($search) . "%' or 
			full_name LIKE '%" . $this->db->escape_like_str($search) . "%' or
			company_name LIKE '%" . $this->db->escape_like_str($search) . "%' or 
			email LIKE '%" . $this->db->escape_like_str($search) . "%') and deleted=0");
            
            if (! $this->config->item('speed_up_search_queries')) {
                $this->db->order_by("last_name", "asc");
            }
            $query = $this->db->get();
            
            if ($query->num_rows() > 0) {
                return $query->row()->person_id;
            }
        }
        
        return null;
    }

    function cleanup()
    {
        $supplier_data = array(
            'account_number' => null
        );
        $this->db->where('deleted', 1);
        return $this->db->update('suppliers', $supplier_data);
    }
}
?>
