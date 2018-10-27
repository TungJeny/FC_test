<?php

class Customer extends Person
{

    /*
     * Determines if a given person_id is a customer
     */
    function exists($person_id)
    {
        $this->db->from('customers');
        $this->db->join('people', 'people.person_id = customers.person_id');
        $this->db->where('customers.person_id', $person_id);
        $query = $this->db->get();
        
        return ($query->num_rows() == 1);
    }

    function account_number_exists($account_number)
    {
        $this->db->from('customers');
        $this->db->where('account_number', $account_number);
        $query = $this->db->get();
        
        return ($query->num_rows() == 1);
    }

    function customer_id_from_account_number($account_number)
    {
        $this->db->from('customers');
        $this->db->where('account_number', $account_number);
        $query = $this->db->get();
        
        if ($query->num_rows() == 1) {
            return $query->row()->person_id;
        }
        
        return false;
    }

    /*
     * Returns all the customers
     */
    function get_all($limit = 10000, $offset = 0, $col = 'last_name', $order = 'asc')
    {
        $order_by = '';
        
        if (! $this->config->item('speed_up_search_queries')) {
            $order_by = "ORDER BY " . $col . " " . $order;
        }
        
        $people = $this->db->dbprefix('people');
        $customers = $this->db->dbprefix('customers');
        $data = $this->db->query("SELECT *,${people}.person_id as pid 
						FROM " . $people . "
						STRAIGHT_JOIN " . $customers . " ON 										                       
						" . $people . ".person_id = " . $customers . ".person_id
						WHERE deleted =0 $order_by 
						LIMIT  " . $offset . "," . $limit);
        
        return $data;
    }

    /**
     * @param int $start
     * @param int $limit
     * @param null $conditions
     * @param null $orders
     * @return mixed
     */
    function get_collection($start = 0, $limit = 100, $conditions = null, $orders = null) {
        $this->db->from('customers');
        if (!empty($conditions)) {
            $this->db->where($conditions);
        }
        $this->db->where('deleted <> 1');
        if (!empty($orders)) {
            $this->db->order_by($orders);
        }
        $this->db->limit($limit, $start);
        $query = $this->db->get();
        if ($query) {
            $result = $query->result();
            $query->free_result();
            return $result;
        }
        return $query;
    }

    function count_all()
    {
        $this->db->from('customers');
        $this->db->where('deleted', 0);
        return $this->db->count_all_results();
    }

    /*
     * Gets information about a particular customer
     */
    function get_info($customer_id, $can_cache = FALSE)
    {
        if ($can_cache) {
            static $cache = array();
            
            if (isset($cache[$customer_id])) {
                return $cache[$customer_id];
            }
        } else {
            $cache = array();
        }
        $this->db->from('customers');
        $this->db->join('people', 'people.person_id = customers.person_id');
        $this->db->where('customers.person_id', $customer_id);
        $query = $this->db->get();
        
        if ($query->num_rows() == 1) {
            $cache[$customer_id] = $query->row();
            return $cache[$customer_id];
        } else {
            // Get empty base parent object, as $customer_id is NOT an customer
            $person_obj = parent::get_info(- 1);
            
            // Get all the fields from customer table
            $fields = $this->db->list_fields('customers');
            
            // append those fields to base parent object, we we have a complete empty object
            foreach ($fields as $field) {
                $person_obj->$field = '';
            }
            
            return $person_obj;
        }
    }

    /**
     * @param $id
     * @return bool
     */
    function get_info_by_id($id)
    {
        $this->db->from('customers');
        $this->db->join('people', 'people.person_id = customers.person_id');
        $this->db->where('customers.id', $id);
        $query = $this->db->get();
        if (!$query) {
            return false;
        }
        $result = $query->row();
        $query->free_result();
        return $result;
    }

    /**
     * @param $code
     * @return bool
     */
    function get_info_by_code($code)
    {
        $this->db->from('customers');
        $this->db->join('people', 'people.person_id = customers.person_id');
        $this->db->where('customers.code', $code);
        $query = $this->db->get();
        if (!$query) {
            return false;
        }
        $result = $query->row();
        $query->free_result();
        return $result;
    }

    /*
     * Gets information about multiple customers
     */
    function get_multiple_info($customer_ids)
    {
        $this->db->from('customers');
        $this->db->join('people', 'people.person_id = customers.person_id');
        $this->db->where_in('customers.person_id', $customer_ids);
        $this->db->order_by("last_name", "asc");
        return $this->db->get();
    }

    /*
     * Inserts or updates a customer
     */
    function save_customer(&$person_data, &$customer_data, $customer_id = false)
    {
        $success = false;
        // Run these queries as a transaction, we want to make sure we do all or nothing
        $this->db->trans_start();
        if (parent::save($person_data, $customer_id)) {
            if ($customer_id && $this->exists($customer_id)) {
                $cust_info = $this->get_info($customer_id);
                
                $current_balance = $cust_info->balance;
                
                // Insert store balance transaction when manually editing
                if (isset($customer_data['balance']) && $customer_data['balance'] != $current_balance) {
                    $store_account_transaction = array(
                        'customer_id' => $customer_id,
                        'sale_id' => NULL,
                        'comment' => lang('common_manual_edit_of_balance'),
                        'transaction_amount' => $customer_data['balance'] - $current_balance,
                        'balance' => $customer_data['balance'],
                        'date' => date('Y-m-d H:i:s')
                    );
                    
                    $this->db->insert('store_accounts', $store_account_transaction);
                }
            }
            
            if (! $customer_id or ! $this->exists($customer_id)) {
                $customer_data['person_id'] = $person_data['person_id'];
                $success = $this->db->insert('customers', $customer_data);
                if (! $success) {
                    unset($customer_data['person_id']);
                    unset($person_data['person_id']);
                }
            } else {
                $this->db->where('person_id', $customer_id);
                $success = $this->db->update('customers', $customer_data);
            }
        }
        
        $this->db->trans_complete();
        return $success;
    }

    /*
     * Deletes one customer
     */
    function delete($customer_id)
    {
        $customer_info = $this->Customer->get_info($customer_id);
        
        if ($customer_info->image_id !== NULL) {
            $this->load->model('Appfile');
            $this->Person->update_image(NULL, $customer_id);
            $this->Appfile->delete($customer_info->image_id);
        }
        
        $this->db->where('person_id', $customer_id);
        return $this->db->update('customers', array(
            'deleted' => 1
        ));
    }

    /*
     * Deletes a list of customers
     */
    function delete_list($customer_ids)
    {
        foreach ($customer_ids as $customer_id) {
            $customer_info = $this->Customer->get_info($customer_id);
            
            if ($customer_info->image_id !== NULL) {
                $this->Person->update_image(NULL, $customer_id);
                $this->load->model('Appfile');
                $this->Appfile->delete($customer_info->image_id);
            }
        }
        
        $this->db->where_in('person_id', $customer_ids);
        return $this->db->update('customers', array(
            'deleted' => 1
        ));
    }

    function check_duplicate($name, $email, $phone_number)
    {
        if (! $email) {
            // Set to an email no one would have
            $email = 'no-reply@mg.phppointofsale.com';
        }
        
        if (! $phone_number) {
            // Set to phone number no one would have
            $phone_number = '555-555-5555';
        }
        
        $this->db->from('customers');
        $this->db->join('people', 'customers.person_id=people.person_id');
        $this->db->where('deleted', 0);
        $this->db->where("full_name = " . $this->db->escape($name) . ' or email=' . $this->db->escape($email) . ' or phone_number=' . $this->db->escape($phone_number));
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return true;
        }
        
        return false;
    }

    function get_customer_search_suggestions($search, $limit = 25)
    {
        if (! trim($search)) {
            return array();
        }
        
        $suggestions = array();
        
        $this->db->from('customers');
        $this->db->join('people', 'customers.person_id=people.person_id');
        
        $this->db->where("(first_name LIKE '%" . $this->db->escape_like_str($search) . "%' or 
			last_name LIKE '%" . $this->db->escape_like_str($search) . "%' or 
			full_name LIKE '%" . $this->db->escape_like_str($search) . "%') and deleted=0");
        
        $this->db->limit($limit);
        $by_name = $this->db->get();
        $temp_suggestions = array();
        
        foreach ($by_name->result() as $row) {
            $name_label = $row->first_name . ' ' . $row->last_name . ' (' . $row->person_id . ')';
            
            if ($row->phone_number) {
                $name_label .= ' (' . $row->phone_number . ')';
            }
            
            $data = array(
                'name' => $name_label,
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
        
        $this->db->from('customers');
        $this->db->join('people', 'customers.person_id=people.person_id');
        $this->db->where('deleted', 0);
        $this->db->like("account_number", $search, 'both');
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
        
        $this->db->from('customers');
        $this->db->join('people', 'customers.person_id=people.person_id');
        $this->db->where('deleted', 0);
        $this->db->like("email", $search, 'both');
        $this->db->limit($limit);
        $by_email = $this->db->get();
        
        $temp_suggestions = array();
        
        foreach ($by_email->result() as $row) {
            $data = array(
                'name' => $row->first_name . '&nbsp;' . $row->last_name,
                'email' => $row->email,
                'avatar' => $row->image_id ? app_file_url($row->image_id) : base_url() . "assets/img/user.png"
            );
            
            $temp_suggestions[$row->person_id] = $data;
        }
        
        uasort($temp_suggestions, 'sort_assoc_array_by_name');
        
        foreach ($temp_suggestions as $key => $value) {
            $suggestions[] = array(
                'value' => $key,
                'label' => $value['email'],
                'avatar' => $value['avatar'],
                'subtitle' => $value['email']
            );
        }
        
        $this->db->from('customers');
        $this->db->join('people', 'customers.person_id=people.person_id');
        $this->db->where('deleted', 0);
        $this->db->like("phone_number", $search, 'both');
        $this->db->limit($limit);
        $by_phone_number = $this->db->get();
        
        $temp_suggestions = array();
        
        foreach ($by_phone_number->result() as $row) {
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
        
        $this->db->from('customers');
        $this->db->join('people', 'customers.person_id=people.person_id');
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
        
        uasort($temp_suggestions, 'sort_assoc_array_by_name');
        
        foreach ($temp_suggestions as $key => $value) {
            $suggestions[] = array(
                'value' => $key,
                'label' => $value['name'],
                'avatar' => $value['avatar'],
                'subtitle' => $value['email']
            );
        }
        
        // Cleanup blank entries
        for ($k = count($suggestions) - 1; $k >= 0; $k --) {
            if (! $suggestions[$k]['label']) {
                unset($suggestions[$k]);
            }
        }
        
        // Probably not needed; but doesn't hurt
        $suggestions = array_values($suggestions);
        
        // only return $limit suggestions
        if (count($suggestions > $limit)) {
            $suggestions = array_slice($suggestions, 0, $limit);
        }
        return $suggestions;
    }

    /*
     * Preform a search on customers
     */
    function search($search, $limit = 20, $offset = 0, $column = 'last_name', $orderby = 'asc')
    {
        // The queries are done as 2 unions to speed up searches to use indexes.
        // When doing OR WHERE across 2 tables; performance is not good
        $this->db->select('*,people.person_id as pid');
        $this->db->from('customers');
        $this->db->join('people', 'customers.person_id=people.person_id');
        
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
        $this->db->from('customers');
        $this->db->join('people', 'customers.person_id=people.person_id');
        
        if ($search) {
            $this->db->where("(account_number LIKE '%" . $this->db->escape_like_str($search) . "%' or 
					company_name LIKE '%" . $this->db->escape_like_str($search) . "%') and deleted=0");
        } else {
            $this->db->where('deleted', 0);
        }
        
        $customer_search = $this->db->get_compiled_select();
        
        $order_by = '';
        if (! $this->config->item('speed_up_search_queries')) {
            $order_by = " ORDER BY $column $orderby ";
        }
        
        return $this->db->query($people_search . " UNION " . $customer_search . " $order_by LIMIT $limit OFFSET $offset");
    }

    function search_count_all($search, $limit = 10000)
    {
        // The queries are done as 2 unions to speed up searches to use indexes.
        // When doing OR WHERE across 2 tables; performance is not good
        $this->db->from('customers');
        $this->db->join('people', 'customers.person_id=people.person_id');
        
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
        
        $this->db->from('customers');
        $this->db->join('people', 'customers.person_id=people.person_id');
        
        if ($search) {
            $this->db->where("(account_number LIKE '%" . $this->db->escape_like_str($search) . "%' or 
				company_name LIKE '%" . $this->db->escape_like_str($search) . "%') and deleted=0");
        } else {
            $this->db->where('deleted', 0);
        }
        
        $customer_search = $this->db->get_compiled_select();
        
        $result = $this->db->query($people_search . " UNION " . $customer_search);
        return $result->num_rows();
    }

    function cleanup()
    {
        $customer_data = array(
            'account_number' => null
        );
        $this->db->where('deleted', 1);
        return $this->db->update('customers', $customer_data);
    }

    /**
     * get_customer_for_item
     * param
     * 
     */
    function get_customers_for_item_task($item_id)
    {
        $this->db->select('customers.code, customers.person_id');
        $this->db->from('items_customers');
        $this->db->join('customers', 'items_customers.person_id=customers.person_id');
        $this->db->where('items_customers.item_id', $item_id);
        $return = array();
        foreach ($this->db->get()->result_array() as $result) {
            $return[] = trim($result['person_id']);
        }
        return $return;
    }


    /**
     * [get_suppliers_for_item description]
     * @param  $item_id
     * @return [type]  [description]
     * erro function
     */
    function get_customers_for_item($item_id)
    {
        $this->db->select('customers.code, customers.person_id');
        $this->db->from('items_customers');
        $this->db->join('customers', 'items_customers.person_id=customers.person_id');
        $this->db->where('items_customers.item_id', $item_id);
        $return = array();
        $result = $this->db->get();
        if ($result) {
            foreach ($this->db->get()->result_array() as $result) {
                $return[] = trim($result['person_id']);
            }
        }
        return $return;
    }

    /**
     * Save suppliers for item
     * 
     * @param int $item_id
     * @param array $suppliers: list of person_ids
     * @return boolean
     */
    public function save_customers_for_item($item_id, $customers)
    {
        $this->db->delete('items_customers', array(
            'item_id' => $item_id
        ));
        $customers = explode(',', $customers);
        foreach ($customers as $customer) {
            if ($customer != '') {
                $customer = trim($customer);
                if (is_numeric($customer) && $this->exists($customer)) {
                    $this->db->insert('items_customers', array(
                        'item_id' => $item_id,
                        'person_id' => $customer
                    ));
                }
            }
        }
        return TRUE;
    }

}
?>
