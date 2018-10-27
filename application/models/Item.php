<?php

class Item extends CI_Model
{

    /*
     * Determines if a given item_id is an item
     */
    function exists($item_id)
    {
        $this->db->from('items');
        $this->db->where('item_id', $item_id);
        $query = $this->db->get();
        
        return ($query->num_rows() == 1);
    }

    /*
     * Returns all the items
     */
    function get_all($limit = 10000, $offset = 0, $col = 'item_id', $order = 'desc')
    {
        $current_location = $this->Employee->get_logged_in_employee_current_location_id();
        $this->db->select('location_items.location as location, items.*, item_images.image_id as image_id, GROUP_CONCAT(' . $this->db->dbprefix('tags') . '.name) as tags, categories.id as category_id,categories.name as category,
        location_items.quantity as quantity, 
        location_items.reorder_level as location_reorder_level,
        location_items.cost_price as location_cost_price,
        location_items.unit_price as location_unit_price');
        
        $this->db->from('items');
        $this->db->join('location_items', 'location_items.item_id = items.item_id and location_id = ' . $current_location, 'left');
        $this->db->join('categories', 'categories.id = items.category_id', 'left');
        $this->db->join('items_tags', 'items_tags.item_id = items.item_id', 'left');
        $this->db->join('tags', 'tags.id = items_tags.tag_id', 'left');
        $this->db->join('item_images', 'items.item_id = item_images.item_id', 'left');
        $this->db->group_by('items.item_id');
        $this->db->where('items.deleted', 0);
        if (! $this->config->item('speed_up_search_queries')) {
            $this->db->order_by($col, $order);
        }

        $this->db->limit($limit);
        $this->db->offset($offset);    
        return $this->db->get();
    }

    /**
     * get total quantity or emploey 
     * @param  [type] $item_id [description]
     * @return [type]          [description]
     */
    function get_info_and_quantity($item_id)
    {
        $current_location = $this->Employee->get_logged_in_employee_current_location_id();
        $this->db->select('location_items.location as location, items.*, item_images.image_id as image_id, GROUP_CONCAT(' . $this->db->dbprefix('tags') . '.name) as tags, categories.id as category_id,categories.name as category,
        location_items.quantity as quantity, 
        location_items.reorder_level as location_reorder_level,
        location_items.cost_price as location_cost_price,
        location_items.unit_price as location_unit_price');

        $this->db->from('items');
        $this->db->join('location_items', 'location_items.item_id = items.item_id and location_id = ' . $current_location, 'left');
        $this->db->join('categories', 'categories.id = items.category_id', 'left');
        $this->db->join('items_tags', 'items_tags.item_id = items.item_id', 'left');
        $this->db->join('tags', 'tags.id = items_tags.tag_id', 'left');
        $this->db->join('item_images', 'items.item_id = item_images.item_id', 'left');
        $this->db->where('items.item_id', $item_id);
        $this->db->group_by('items.item_id');
        $this->db->where('items.deleted', 0);
        $this->db->group_by('items.item_id');
        $this->db->where('items.deleted', 0);
        
        $query = $this->db->get();
        if (!$query) {
            return false;
        }
        $result = $query->result_array();
        $query->free_result();
        return $result;
    }

    function get_all_by_supplier($supplier_id)
    {
        $this->db->from('items');
        $this->db->where('supplier_id', $supplier_id);
        $this->db->where('items.deleted', 0);
        $this->db->order_by('name');
        
        return $this->db->get()->result_array();
    }

    function get_all_by_category($category_id, $hide_out_of_stock_grid = FALSE, $offset = 0, $limit = 14)
    {
        $items_table = $this->db->dbprefix('items');
        $item_kits_table = $this->db->dbprefix('item_kits');
        $item_images_table = $this->db->dbprefix('item_images');
        if (! $hide_out_of_stock_grid) {
            $result = $this->db->query("(SELECT item_id, unit_price, name, size, image_id FROM $items_table LEFT JOIN $item_images_table USING (item_id)
                WHERE deleted = 0 and category_id = $category_id GROUP BY item_id ORDER BY name) UNION ALL (SELECT CONCAT('KIT ',item_kit_id), unit_price, name, '', 'no_image' as image_id FROM $item_kits_table 
            WHERE deleted = 0 and category_id = $category_id ORDER BY name) ORDER BY name LIMIT $offset, $limit");
        } else {
            $location_items_table = $this->db->dbprefix('location_items ');
            $current_location = $this->Employee->get_logged_in_employee_current_location_id();
            $result = $this->db->query("(SELECT i.item_id, i.unit_price, name,size, image_id FROM $items_table as i LEFT JOIN $item_images_table USING(item_id) LEFT JOIN $location_items_table as li ON i.item_id = li.item_id and li.location_id = $current_location
            WHERE (quantity > 0 or quantity IS NULL) and deleted = 0 and category_id = $category_id GROUP BY item_id ORDER BY name) UNION ALL (SELECT CONCAT('KIT ',item_kit_id), unit_price, name, '', 'no_image' as image_id FROM $item_kits_table 
            WHERE deleted = 0 and category_id = $category_id ORDER BY name) ORDER BY name LIMIT $offset, $limit");
        }
        return $result;
    }

    function count_all_by_category($category_id)
    {
        $this->db->from('items');
        $this->db->where('deleted', 0);
        $this->db->where('category_id', $category_id);
        $items_count = $this->db->count_all_results();
        
        $this->db->from('item_kits');
        $this->db->where('deleted', 0);
        $this->db->where('category_id', $category_id);
        $item_kits_count = $this->db->count_all_results();
        
        return $items_count + $item_kits_count;
    }

    function get_all_by_tag($tag_id, $hide_out_of_stock_grid = FALSE, $offset = 0, $limit = 14)
    {
        $items_table = $this->db->dbprefix('items');
        $items_images_table = $this->db->dbprefix('item_images');
        $items_tags_table = $this->db->dbprefix('items_tags');
        
        $item_kits_table = $this->db->dbprefix('item_kits');
        $item_kits_tags_table = $this->db->dbprefix('item_kits_tags');
        
        if (! $hide_out_of_stock_grid) {
            $result = $this->db->query("(SELECT item_id, unit_price,name, image_id FROM $items_table LEFT JOIN $items_images_table USING (item_id) INNER JOIN $items_tags_table USING (item_id)
            WHERE deleted = 0 and $items_tags_table.tag_id = $tag_id GROUP BY item_id ORDER BY name) UNION ALL (SELECT CONCAT('KIT ',item_kit_id), unit_price, name, 'no_image' as image_id FROM $item_kits_table INNER JOIN $item_kits_tags_table USING (item_kit_id)
            WHERE deleted = 0 and $item_kits_tags_table.tag_id = $tag_id ORDER BY name) ORDER BY name LIMIT $offset, $limit");
        } else {
            $location_items_table = $this->db->dbprefix('location_items ');
            $current_location = $this->Employee->get_logged_in_employee_current_location_id();
            $result = $this->db->query("(SELECT i.item_id, i.unit_price, name,size, image_id FROM $items_table as i LEFT JOIN $items_images_table USING (item_id) INNER JOIN $items_tags_table USING (item_id) LEFT JOIN $location_items_table as li ON i.item_id = li.item_id and li.location_id = $current_location
            WHERE (quantity > 0 or quantity IS NULL) and deleted = 0 and $items_tags_table.tag_id = $tag_id GROUP BY i.item_id ORDER BY name) UNION ALL (SELECT CONCAT('KIT ',item_kit_id), unit_price, name, '', 'no_image' as image_id FROM $item_kits_table INNER JOIN $item_kits_tags_table USING (item_kit_id)
            WHERE deleted = 0 and $item_kits_tags_table.tag_id = $tag_id ORDER BY name) ORDER BY name LIMIT $offset, $limit");
        }
        
        return $result;
    }

    function count_all_by_tag($tag_id)
    {
        $this->db->from('items_tags');
        $this->db->where('tag_id', $tag_id);
        $items_count = $this->db->count_all_results();
        
        $this->db->from('item_kits_tags');
        $this->db->where('tag_id', $tag_id);
        $item_kits_count = $this->db->count_all_results();
        
        return $items_count + $item_kits_count;
    }

    function get_next_id($item_id)
    {
        $items_table = $this->db->dbprefix('items');
        $result = $this->db->query("SELECT item_id FROM $items_table WHERE item_id = (select min(item_id) from $items_table where deleted = 0 and item_id > " . $this->db->escape($item_id) . ")");
        
        if ($result->num_rows() > 0) {
            $row = $result->result();
            return $row[0]->item_id;
        }
        
        return FALSE;
    }

    function get_prev_id($item_id)
    {
        $items_table = $this->db->dbprefix('items');
        $result = $this->db->query("SELECT item_id FROM $items_table WHERE item_id = (select max(item_id) from $items_table where deleted = 0 and item_id <" . $this->db->escape($item_id) . ")");
        
        if ($result->num_rows() > 0) {
            $row = $result->result();
            return $row[0]->item_id;
        }
        
        return FALSE;
    }
    

    function get_all_tiers_prices()
    {
        $this->db->from('items_tier_prices');
        $result = $this->db->get()->result_array();
        
        $return = array();
        
        foreach ($result as $row) {
            $return[$row['item_id']][$row['tier_id']] = array(
                'unit_price' => $row['unit_price'],
                'percent_off' => $row['percent_off'],
                'cost_plus_percent' => $row['cost_plus_percent']
            );
        }
        
        return $return;
    }

    function get_tier_price_row($tier_id, $item_id)
    {
        $this->db->from('items_tier_prices');
        $this->db->where('tier_id', $tier_id);
        $this->db->where('item_id ', $item_id);
        return $this->db->get()->row();
    }

    function delete_tier_price($tier_id, $item_id)
    {
        $this->db->where('tier_id', $tier_id);
        $this->db->where('item_id', $item_id);
        return $this->db->delete('items_tier_prices');
    }

    function tier_exists($tier_id, $item_id)
    {
        $this->db->from('items_tier_prices');
        $this->db->where('tier_id', $tier_id);
        $this->db->where('item_id', $item_id);
        $query = $this->db->get();
        
        return ($query->num_rows() >= 1);
    }

    function save_item_tiers($tier_data, $item_id)
    {
        if ($this->tier_exists($tier_data['tier_id'], $item_id)) {
            $this->db->where('tier_id', $tier_data['tier_id']);
            $this->db->where('item_id', $item_id);
            
            return $this->db->update('items_tier_prices', $tier_data);
        }
        
        return $this->db->insert('items_tier_prices', $tier_data);
    }

    function account_number_exists($item_number)
    {
        $this->db->from('items');
        $this->db->where('item_number', $item_number);
        $query = $this->db->get();
        //check if 
        if ($query->num_rows() == 1) {
            return ($query->num_rows() == 1);
        }
        return false;
    }

    function product_id_exists($product_id)
    {
        $this->db->from('items');
        $this->db->where('product_id', $product_id);
        $query = $this->db->get();
        
        return ($query->num_rows() == 1);
    }
    function get_item_by_product_id_or_name($product_id)
    {
        static $cache = [];
        $product_id = str_replace(' ', '', $product_id);
        $cache_key= str_replace('-', '', $product_id);
        if (!empty($cache[$cache_key])) {
            return $cache[$cache_key];
        }
        $this->db->select('item_id, product_id');
        $this->db->from('items');
        $this->db->where('product_id', $product_id);
        $this->db->or_where('name', $product_id);
        $this->db->or_where('(REPLACE (name, "-","")) ="'. $product_id. '"');
        $this->db->or_where('(REPLACE (product_id, "-","")) ="'. $product_id. '"');
        $this->db->or_where('(REPLACE (product_id, " ","")) ="'. $product_id. '"');
        $query = $this->db->get();
        $cache[$cache_key] = !empty($query->row_array())? $query->row_array(): [];
        return $cache[$cache_key];
    }

    function get_main_image($item_id)
    {
        $item_images = $this->get_item_images($item_id);
        
        if (isset($item_images[0])) {
            return $item_images[0];
        }
        
        return FALSE;
    }

    function get_item_images($item_id)
    {
        $this->db->from('item_images');
        $this->db->where('item_id', $item_id);
        $this->db->order_by('id');
        return $this->db->get()->result_array();
    }

    function count_all()
    {
        $this->db->from('items');
        $this->db->where('deleted', 0);
        return $this->db->count_all_results();
    }
    
    /*
     * Gets information about a particular item
     */
    function get_info($item_id, $can_cache = TRUE)
    {
        if ($can_cache) {
            static $cache = array();
        } else {
            $cache = array();
        }
        
        if (is_array($item_id)) {
            $items = $this->get_multiple_info($item_id)->result();
            foreach ($items as $item) {
                // We don't need image in the case we are passing in array; adding images would slow performance
                $item->image_id = '';
                $cache[$item->item_id] = $item;
            }
            
            return $items;
        } else {
            if (isset($cache[$item_id])) {
                return $cache[$item_id];
            }
        }
        
        // If we are NOT an int return empty item
        if (! is_numeric($item_id)) {
            // Get empty base parent object, as $item_id is NOT an item
            $item_obj = new stdClass();
            
            // Get all the fields from items table
            $fields = $this->db->list_fields('items');
            
            foreach ($fields as $field) {
                $item_obj->$field = '';
            }
            
            $item_obj->image_id = '';
            
            return $item_obj;
        }
        
        $this->db->from('items');
        $this->db->where('item_id', $item_id);
        
        $query = $this->db->get();
        
        if ($query->num_rows() == 1) {
            $cache[$item_id] = $query->row();
            $main_image = $this->get_main_image($item_id);
            $cache[$item_id]->image_id = $main_image['image_id'];
            return $cache[$item_id];
        } else {
            // Get empty base parent object, as $item_id is NOT an item
            $item_obj = new stdClass();
            
            // Get all the fields from items table
            $fields = $this->db->list_fields('items');
            
            foreach ($fields as $field) {
                $item_obj->$field = '';
            }
            $item_obj->image_id = '';
            
            return $item_obj;
        }
    }

    function get_category($category_id)
    {
        $this->db->from('categories');
        $this->db->where('id', $category_id);
        
        $query = $this->db->get();
        
        if ($query->num_rows() >= 1) {
            return $query->row()->name;
        }
        $this->lang->load('error');
        return lang('error_unknown');
    }

    /*
     * Get an item id given an item number or product_id or additional item number
     */
    function get_item_id($item_number)
    {
        if (! $item_number) {
            return false;
        }
        
        $this->db->from('items');
        $this->db->where('item_number', $item_number);
        $this->db->or_where('product_id', $item_number);
        
        $query = $this->db->get();
        
        if ($query->num_rows() >= 1) {
            return $query->row()->item_id;
        }
        
        $this->load->model('Additional_item_numbers');
        if ($additional_item_id = $this->Additional_item_numbers->get_item_id($item_number)) {
            return $additional_item_id;
        }
        
        $this->load->model('Item_serial_number');
        
        if ($item_id_from_serial_number = $this->Item_serial_number->get_item_id($item_number)) {
            return $item_id_from_serial_number;
        }
        
        return false;
    }

    /*
     * Gets information about multiple items
     */
    function get_multiple_info($item_ids)
    {
        $this->db->from('items');
        if (! empty($item_ids)) {
            $this->db->group_start();
            $item_ids_chunk = array_chunk($item_ids, 25);
            foreach ($item_ids_chunk as $item_ids) {
                $this->db->or_where_in('item_id', $item_ids);
            }
            $this->db->group_end();
        } else {
            $this->db->where('1', '2', FALSE);
        }
        
        $this->db->order_by("item_id", "asc");
        return $this->db->get();
    }

    /*
     * Inserts or updates a item
     */
    function save(&$item_data, $item_id = false)
    {
        $item_data['last_modified'] = date('Y-m-d H:i:s');
        
        if (! $item_id or ! $this->exists($item_id)) {
            if ($this->db->insert('items', $item_data)) {
                $item_data['item_id'] = $this->db->insert_id();
                return true;
            }
            return false;
        }
        
        $this->db->where('item_id', $item_id);
        
        return $this->db->update('items', $item_data);
    }

    /*
     * Updates multiple items at once
     */
    function update_multiple($item_data, $item_ids, $select_inventory = 0)
    {
        if ($select_inventory) {
            if ($this->is_empty_search()) {
                return $this->db->update('items', $item_data);
            } else {
                $item_ids = array();
                
                $total_items = $this->count_all();
                $params = $this->session->userdata('item_search_data') ? $this->session->userdata('item_search_data') : array(
                    'offset' => 0,
                    'order_col' => 'item_id',
                    'order_dir' => 'asc',
                    'search' => FALSE,
                    'category_id' => FALSE,
                    'fields' => 'all'
                );
                $result = $this->search(isset($params['search']) ? $params['search'] : '', isset($params['category_id']) ? $params['category_id'] : '', $total_items, 0, 'name', 'asc', isset($params['fields']) ? $params['fields'] : 'all');
                
                foreach ($result->result() as $row) {
                    $item_ids[] = $row->item_id;
                }
                $this->load->helper('database');
                return create_and_execute_large_update_query_items($item_ids, $item_data);
            }
        } else {
            $this->load->helper('database');
            return create_and_execute_large_update_query_items($item_ids, $item_data);
        }
    }

    function update_multiple_percent($item_ids, $select_inventory = 0, $cost_price_percent = FALSE, $unit_price_percent = FALSE, $promo_price_percent = FALSE, $promo_price_use_selling_price = FALSE)
    {
        if ($select_inventory) {
            if ($this->is_empty_search()) {
                if ($cost_price_percent) {
                    $this->db->set('cost_price', "cost_price * (1+($cost_price_percent/100))", FALSE);
                }
                
                if ($unit_price_percent) {
                    $this->db->set('unit_price', "unit_price * (1+($unit_price_percent/100))", FALSE);
                }
                
                if ($promo_price_percent) {
                    if ($promo_price_use_selling_price) {
                        $this->db->set('promo_price', "unit_price * (1+($promo_price_percent/100))", FALSE);
                    } else {
                        $this->db->set('promo_price', "promo_price * (1+($promo_price_percent/100))", FALSE);
                    }
                }
                
                return $this->db->update('items');
            } else {
                $item_ids = array();
                
                $total_items = $this->count_all();
                $params = $this->session->userdata('item_search_data') ? $this->session->userdata('item_search_data') : array(
                    'offset' => 0,
                    'order_col' => 'item_id',
                    'order_dir' => 'asc',
                    'search' => FALSE,
                    'category_id' => FALSE,
                    'fields' => 'all'
                );
                $result = $this->search(isset($params['search']) ? $params['search'] : '', isset($params['category_id']) ? $params['category_id'] : '', $total_items, 0, 'name', 'asc', isset($params['fields']) ? $params['fields'] : 'all');
                
                foreach ($result->result() as $row) {
                    $item_ids[] = $row->item_id;
                }
                $this->load->helper('database');
                return create_and_execute_large_update_query_location_items_percent($item_ids, $cost_price_percent, $unit_price_percent, $promo_price_percent, $promo_price_percent);
            }
        } else {
            $this->load->helper('database');
            return create_and_execute_large_update_query_location_items_percent($item_ids, $cost_price_percent, $unit_price_percent, $promo_price_percent);
        }
    }

    function update_tiers($item_ids, $select_inventory, $tier_types, $tier_values)
    {
        if (! $tier_types) {
            $tier_types = array();
        }
        
        if (! $tier_values) {
            $tier_values = array();
        }
        if ($select_inventory) {
            $item_ids = array();
            
            $total_items = $this->count_all();
            $params = $this->session->userdata('item_search_data') ? $this->session->userdata('item_search_data') : array(
                'offset' => 0,
                'order_col' => 'item_id',
                'order_dir' => 'asc',
                'search' => FALSE,
                'category_id' => FALSE,
                'fields' => 'all'
            );
            $result = $this->search(isset($params['search']) ? $params['search'] : '', isset($params['category_id']) ? $params['category_id'] : '', $total_items, 0, 'name', 'asc', isset($params['fields']) ? $params['fields'] : 'all');
            
            foreach ($result->result() as $row) {
                $item_ids[] = $row->item_id;
            }
        }
        
        foreach ($item_ids as $item_id) {
            // Save price tiers
            foreach ($tier_types as $tier_id => $tier_type_value) {
                $tier_data = array(
                    'tier_id' => $tier_id
                );
                $tier_data['item_id'] = $item_id;
                $tier_value = $tier_values[$tier_id];
                
                if ($tier_value) {
                    $tier_data[$tier_type_value] = (float) $tier_value;
                    
                    if ($tier_type_value == 'unit_price') {
                        
                        $tier_data['percent_off'] = NULL;
                    } else {
                        $tier_data['unit_price'] = NULL;
                    }
                    
                    $this->Item->save_item_tiers($tier_data, $item_id);
                }
            }
        }
    }

    function is_empty_search()
    {
        $params = $this->session->userdata('item_search_data') ? $this->session->userdata('item_search_data') : array(
            'offset' => 0,
            'order_col' => 'item_id',
            'order_dir' => 'asc',
            'search' => FALSE,
            'category_id' => FALSE,
            'fields' => 'all'
        );
        
        if (! $params['search'] && ! $params['category_id']) {
            return TRUE;
        }
        
        return FALSE;
    }

    /*
     * Deletes one item
     */
    function delete($item_id)
    {
        $item_info = $this->Item->get_info($item_id);
        
        if ($item_info->image_id !== NULL) {
            $this->load->model('Appfile');
            
            foreach ($this->get_item_images($item_id) as $image) {
                $this->Item->delete_image($image['image_id']);
            }
        }
        
        $this->db->where('item_id', $item_id);
        $delete_success = $this->db->update('items', array(
            'deleted' => 1
        ));
        
        if ($this->config->item("ecommerce_platform")) {
            require_once (APPPATH . "models/interfaces/Ecom.php");
            $ecom_model = Ecom::get_ecom_model();
            $ecom_model->unlink_item($item_id);
        }
        
        return $delete_success;
    }

    /*
     * Deletes a list of items
     */
    function delete_list($item_ids, $select_inventory)
    {
        foreach ($item_ids as $item_id) {
            $item_images = $this->Item->get_item_images($item_id);
            foreach ($item_images as $image) {
                $this->delete_image($image['image_id']);
            }
        }
        
        if ($select_inventory) {
            if ($this->is_empty_search()) {
                if ($this->config->item("ecommerce_platform")) {
                    require_once (APPPATH . "models/interfaces/Ecom.php");
                    $ecom_model = Ecom::get_ecom_model();
                    $ecom_model->unlink_all();
                }
                return $this->db->update('items', array(
                    'deleted' => 1
                ));
            } else {
                $item_ids = array();
                $total_items = $this->count_all();
                
                $params = $this->session->userdata('item_search_data') ? $this->session->userdata('item_search_data') : array(
                    'offset' => 0,
                    'order_col' => 'item_id',
                    'order_dir' => 'asc',
                    'search' => FALSE,
                    'category_id' => FALSE,
                    'fields' => 'all'
                );
                $result = $this->search(isset($params['search']) ? $params['search'] : '', isset($params['category_id']) ? $params['category_id'] : '', $total_items, 0, 'name', 'asc', isset($params['fields']) ? $params['fields'] : 'all');
                
                foreach ($result->result() as $row) {
                    $item_ids[] = $row->item_id;
                }
                
                if ($this->config->item("ecommerce_platform")) {
                    require_once (APPPATH . "models/interfaces/Ecom.php");
                    $ecom_model = Ecom::get_ecom_model();
                    $ecom_model->unlink_items($item_ids);
                }
                
                $this->load->helper('database');
                return create_and_execute_large_update_query_items($item_ids, array(
                    'deleted' => 1
                ));
            }
        } else {
            if ($this->config->item("ecommerce_platform")) {
                require_once (APPPATH . "models/interfaces/Ecom.php");
                $ecom_model = Ecom::get_ecom_model();
                $ecom_model->unlink_items($item_ids);
            }
            $this->load->helper('database');
            return create_and_execute_large_update_query_items($item_ids, array(
                'deleted' => 1
            ));
        }
    }

    /*
     * Get search suggestions to find items on the manage screen
     */
    function get_manage_items_search_suggestions($search, $limit = 25)
    {
        if (! trim($search)) {
            return array();
        }
        
        $suggestions = array();
        
        if ($this->config->item('supports_full_text') && ! $this->config->item('legacy_search_method')) {
            $this->db->select("items.name,item_number,size, item_images.image_id,items.item_id, categories.name as category, MATCH (" . $this->db->dbprefix('items') . ".name) AGAINST (" . $this->db->escape(escape_full_text_boolean_search($search) . '*') . " IN BOOLEAN MODE) as rel, CASE WHEN " . $this->db->dbprefix('items') . ".name = " . $this->db->escape($search) . " THEN 1 ELSE 0 END AS exact_score", false);
            $this->db->from('items');
            $this->db->join('item_images', 'items.item_id = item_images.item_id', 'left');
            $this->db->join('categories', 'categories.id = items.category_id', 'left');
            $this->db->where("MATCH (" . $this->db->dbprefix('items') . ".name) AGAINST (" . $this->db->escape(escape_full_text_boolean_search($search) . '*') . " IN BOOLEAN MODE)", NULL, FALSE);
            $this->db->where('items.deleted', 0);
            $this->db->group_by('items.item_id');
            $this->db->limit($limit);
            $this->db->order_by('rel DESC, exact_score DESC');
            $by_name = $this->db->get();
            
            $temp_suggestions = array();
            foreach ($by_name->result() as $row) {
                $data = array(
                    'avatar' => $row->image_id ? app_file_url($row->image_id) : base_url() . "assets/img/item.png",
                    'category' => $row->category,
                    'item_number' => $row->item_number
                );
                
                $data['label'] = $row->name;
                $temp_suggestions[$row->item_id] = $data;
            }
            
            foreach ($temp_suggestions as $key => $value) {
                $suggestions[] = array(
                    'value' => $key,
                    'label' => $value['label'],
                    'avatar' => $value['avatar'],
                    'subtitle' => $value['category'] ? $value['category'] : lang('common_none')
                );
            }
            
            $this->db->select('items.*, item_images.image_id, items.name as item_name, categories.name as category');
            $this->db->from('categories');
            $this->db->like('categories.name', $search, 'both');
            $this->db->join('items', 'items.category_id=categories.id');
            $this->db->join('item_images', 'items.item_id = item_images.item_id', 'left');
            $this->db->group_by('items.item_id');
            $this->db->limit($limit);
            $by_category = $this->db->get();
            
            $temp_suggestions = array();
            foreach ($by_category->result() as $row) {
                $data = array(
                    'avatar' => $row->image_id ? app_file_url($row->image_id) : base_url() . "assets/img/item.png",
                    'label' => $row->item_name,
                    'category' => $row->category
                );
                $temp_suggestions[$row->item_id] = $data;
            }
            
            foreach ($temp_suggestions as $key => $value) {
                $suggestions[] = array(
                    'value' => $key,
                    'label' => $value['label'],
                    'avatar' => $value['avatar'],
                    'subtitle' => $value['category'] ? $value['category'] : lang('common_none')
                );
            }
            
            $this->db->select("item_number,items.item_id,item_images.image_id,items.name, categories.name as category, size, MATCH (item_number) AGAINST (" . $this->db->escape(escape_full_text_boolean_search($search) . '*') . " IN BOOLEAN MODE) as rel, CASE WHEN " . $this->db->dbprefix('items') . ".item_number = " . $this->db->escape($search) . " THEN 1 ELSE 0 END AS exact_score", false);
            $this->db->from('items');
            $this->db->join('item_images', 'items.item_id = item_images.item_id', 'left');
            $this->db->join('categories', 'categories.id = items.category_id', 'left');
            $this->db->where("MATCH (item_number) AGAINST (" . $this->db->escape(escape_full_text_boolean_search($search) . '*') . " IN BOOLEAN MODE)", NULL, FALSE);
            $this->db->where('items.deleted', 0);
            $this->db->group_by('items.item_id');
            $this->db->limit($limit);
            $this->db->order_by('rel DESC, exact_score DESC');
            
            $by_item_number = $this->db->get();
            
            $temp_suggestions = array();
            foreach ($by_item_number->result() as $row) {
                $data = array(
                    'avatar' => $row->image_id ? app_file_url($row->image_id) : base_url() . "assets/img/item.png",
                    'label' => $row->item_number,
                    'category' => $row->category
                );
                
                $temp_suggestions[$row->item_id] = $data;
            }
            
            foreach ($temp_suggestions as $key => $value) {
                $suggestions[] = array(
                    'value' => $key,
                    'label' => $value['label'],
                    'avatar' => $value['avatar'],
                    'subtitle' => $value['category'] ? $value['category'] : lang('common_none')
                );
            }
            
            $this->db->select("item_number,items.item_id,product_id,item_images.image_id,items.name, categories.name as category, size, MATCH (item_number) AGAINST (" . $this->db->escape(escape_full_text_boolean_search($search) . '*') . " IN BOOLEAN MODE) as rel, CASE WHEN " . $this->db->dbprefix('items') . ".product_id = " . $this->db->escape($search) . " THEN 1 ELSE 0 END AS exact_score", false);
            $this->db->from('items');
            $this->db->join('item_images', 'items.item_id = item_images.item_id', 'left');
            $this->db->join('categories', 'categories.id = items.category_id', 'left');
            $this->db->where("MATCH (product_id) AGAINST (" . $this->db->escape(escape_full_text_boolean_search($search) . '*') . " IN BOOLEAN MODE)", NULL, FALSE);
            $this->db->where('items.deleted', 0);
            $this->db->limit($limit);
            $this->db->order_by('rel DESC, exact_score DESC');
            $this->db->group_by('items.item_id');
            $by_product_id = $this->db->get();
            $temp_suggestions = array();
            foreach ($by_product_id->result() as $row) {
                $data = array(
                    'avatar' => $row->image_id ? app_file_url($row->image_id) : base_url() . "assets/img/item.png",
                    'label' => $row->product_id,
                    'category' => $row->category
                );
                
                $temp_suggestions[$row->item_id] = $data;
            }
            
            foreach ($temp_suggestions as $key => $value) {
                $suggestions[] = array(
                    'value' => $key,
                    'label' => $value['label'],
                    'avatar' => $value['avatar'],
                    'subtitle' => $value['category'] ? $value['category'] : lang('common_none')
                );
            }
            
            $this->db->select("items.item_id,item_images.image_id,items.name, categories.name as category, size, MATCH (item_number) AGAINST (" . $this->db->escape(escape_full_text_boolean_search($search) . '*') . " IN BOOLEAN MODE) as rel", false);
            $this->db->from('items');
            $this->db->join('item_images', 'items.item_id = item_images.item_id', 'left');
            $this->db->join('categories', 'categories.id = items.category_id', 'left');
            $this->db->where('items.item_id', $search);
            $this->db->where('items.deleted', 0);
            $this->db->limit($limit);
            $this->db->order_by('rel DESC');
            $this->db->group_by('items.item_id');
            $by_item_id = $this->db->get();
            $temp_suggestions = array();
            foreach ($by_item_id->result() as $row) {
                $data = array(
                    'avatar' => $row->image_id ? app_file_url($row->image_id) : base_url() . "assets/img/item.png",
                    'label' => $row->item_id,
                    'category' => $row->category
                );
                
                $temp_suggestions[$row->item_id] = $data;
            }
            
            foreach ($temp_suggestions as $key => $value) {
                $suggestions[] = array(
                    'value' => $key,
                    'label' => $value['label'],
                    'avatar' => $value['avatar'],
                    'subtitle' => $value['category'] ? $value['category'] : lang('common_none')
                );
            }
            
            $this->db->select('items.item_id,item_images.image_id, name, additional_item_numbers.item_number');
            $this->db->from('additional_item_numbers');
            $this->db->join('items', 'items.item_id = additional_item_numbers.item_id', 'left');
            $this->db->join('item_images', 'items.item_id = item_images.item_id', 'left');
            
            $this->db->like('additional_item_numbers.item_number', $search, 'both');
            $this->db->group_by('items.item_id');
            $this->db->limit($limit);
            $by_additional_item_numbers = $this->db->get();
            
            $temp_suggestions = array();
            foreach ($by_additional_item_numbers->result() as $row) {
                $data = array(
                    'avatar' => $row->image_id ? app_file_url($row->image_id) : base_url() . "assets/img/item.png",
                    'label' => $row->name,
                    'category' => $row->item_number
                );
                
                $temp_suggestions[$row->item_id] = $data;
            }
            
            foreach ($temp_suggestions as $key => $value) {
                $suggestions[] = array(
                    'value' => $key,
                    'label' => $value['label'],
                    'avatar' => $value['avatar'],
                    'subtitle' => $value['category'] ? $value['category'] : lang('common_none')
                );
            }
            
            $this->db->select('items.item_id, items.name as item_name, item_images.image_id, tags.name as tag_name, ');
            $this->db->from('items_tags');
            $this->db->join('tags', 'items_tags.tag_id=tags.id');
            $this->db->join('items', 'items_tags.item_id=items.item_id');
            $this->db->join('item_images', 'items.item_id = item_images.item_id', 'left');
            $this->db->like('tags.name', $search, 'both');
            $this->db->group_by('items.item_id');
            $this->db->limit($limit);
            
            $by_tags = $this->db->get();
            $temp_suggestions = array();
            
            foreach ($by_tags->result() as $row) {
                $data = array(
                    'avatar' => $row->image_id ? app_file_url($row->image_id) : base_url() . "assets/img/item.png",
                    'label' => $row->name,
                    'category' => $row->tag_name
                );
                
                $temp_suggestions[$row->item_id] = $data;
            }
            
            foreach ($temp_suggestions as $key => $value) {
                $suggestions[] = array(
                    'value' => $key,
                    'label' => $value['label'],
                    'avatar' => $value['avatar'],
                    'subtitle' => $value['category'] ? $value['category'] : lang('common_none')
                );
            }
        } else {
            
            $this->db->select('items.*, item_images.image_id,categories.name as category');
            $this->db->from('items');
            $this->db->join('item_images', 'items.item_id = item_images.item_id', 'left');
            $this->db->join('categories', 'categories.id = items.category_id', 'left');
            $this->db->like('items.name', $search, 'both');
            $this->db->where('items.deleted', 0);
            $this->db->group_by('items.item_id');
            $this->db->limit($limit);
            $by_name = $this->db->get();
            $temp_suggestions = array();
            
            foreach ($by_name->result() as $row) {
                $data = array(
                    'name' => $row->name,
                    'subtitle' => $row->category,
                    'avatar' => $row->image_id ? app_file_url($row->image_id) : base_url() . "assets/img/item.png"
                );
                $temp_suggestions[$row->item_id] = $data;
            }
            
            foreach ($temp_suggestions as $key => $value) {
                $suggestions[] = array(
                    'value' => $key,
                    'label' => $value['name'],
                    'avatar' => $value['avatar'],
                    'subtitle' => $value['subtitle'] ? $value['subtitle'] : lang('common_none')
                );
            }
            
            $this->db->select('items.*, item_images.image_id, categories.name as category');
            $this->db->from('categories');
            $this->db->like('categories.name', $search, 'both');
            $this->db->join('items', 'items.category_id=categories.id');
            $this->db->join('item_images', 'items.item_id = item_images.item_id', 'left');
            $this->db->limit($limit);
            $this->db->group_by('items.item_id');
            $by_category = $this->db->get();
            
            $temp_suggestions = array();
            foreach ($by_category->result() as $row) {
                $data = array(
                    'name' => $row->name,
                    'subtitle' => $row->category,
                    'avatar' => $row->image_id ? app_file_url($row->image_id) : base_url() . "assets/img/item.png"
                );
                $temp_suggestions[$row->item_id] = $data;
            }
            
            foreach ($temp_suggestions as $key => $value) {
                $suggestions[] = array(
                    'value' => $key,
                    'label' => $value['name'],
                    'avatar' => $value['avatar'],
                    'subtitle' => $value['subtitle'] ? $value['subtitle'] : lang('common_none')
                );
            }
            
            $this->db->select('items.*, item_images.image_id, categories.name as category');
            $this->db->from('items');
            $this->db->join('item_images', 'items.item_id = item_images.item_id', 'left');
            $this->db->join('categories', 'categories.id = items.category_id', 'left');
            $this->db->like('item_number', $search, 'both');
            $this->db->where('items.deleted', 0);
            $this->db->group_by('items.item_id');
            $this->db->limit($limit);
            $by_item_number = $this->db->get();
            
            $temp_suggestions = array();
            foreach ($by_item_number->result() as $row) {
                $data = array(
                    'name' => $row->item_number,
                    'subtitle' => $row->category,
                    'avatar' => $row->image_id ? app_file_url($row->image_id) : base_url() . "assets/img/item.png"
                );
                $temp_suggestions[$row->item_id] = $data;
            }
            
            foreach ($temp_suggestions as $key => $value) {
                $suggestions[] = array(
                    'value' => $key,
                    'label' => $value['name'],
                    'avatar' => $value['avatar'],
                    'subtitle' => $value['subtitle'] ? $value['subtitle'] : lang('common_none')
                );
            }
            
            $this->db->select('items.*, item_images.image_id as image_id, categories.name as category');
            $this->db->from('items');
            $this->db->join('item_images', 'items.item_id = item_images.item_id', 'left');
            $this->db->join('categories', 'categories.id = items.category_id', 'left');
            $this->db->like('product_id', $search, 'both');
            $this->db->where('items.deleted', 0);
            $this->db->group_by('items.item_id');
            $this->db->limit($limit);
            $by_product_id = $this->db->get();
            $temp_suggestions = array();
            foreach ($by_product_id->result() as $row) {
                $data = array(
                    'name' => $row->product_id,
                    'subtitle' => $row->category,
                    'avatar' => $row->image_id ? app_file_url($row->image_id) : base_url() . "assets/img/item.png"
                );
                $temp_suggestions[$row->item_id] = $data;
            }
            
            foreach ($temp_suggestions as $key => $value) {
                $suggestions[] = array(
                    'value' => $key,
                    'label' => $value['name'],
                    'avatar' => $value['avatar'],
                    'subtitle' => $value['subtitle'] ? $value['subtitle'] : lang('common_none')
                );
            }
            
            $this->db->select('items.*, item_images.image_id, categories.name as category');
            $this->db->from('items');
            $this->db->join('item_images', 'items.item_id = item_images.item_id', 'left');
            $this->db->join('categories', 'categories.id = items.category_id', 'left');
            $this->db->where('items.item_id', $search);
            $this->db->where('items.deleted', 0);
            $this->db->group_by('items.item_id');
            $this->db->limit($limit);
            $by_item_id = $this->db->get();
            $temp_suggestions = array();
            foreach ($by_item_id->result() as $row) {
                $data = array(
                    'name' => $row->item_id,
                    'subtitle' => $row->category,
                    'avatar' => $row->image_id ? app_file_url($row->image_id) : base_url() . "assets/img/item.png"
                );
                $temp_suggestions[$row->item_id] = $data;
            }
            
            foreach ($temp_suggestions as $key => $value) {
                $suggestions[] = array(
                    'value' => $key,
                    'label' => $value['name'],
                    'avatar' => $value['avatar'],
                    'subtitle' => $value['subtitle'] ? $value['subtitle'] : lang('common_none')
                );
            }
            
            $this->db->select('items.item_id,item_images.image_id, name, additional_item_numbers.item_number');
            $this->db->from('additional_item_numbers');
            $this->db->join('items', 'items.item_id = additional_item_numbers.item_id', 'left');
            $this->db->join('item_images', 'items.item_id = item_images.item_id', 'left');
            $this->db->like('additional_item_numbers.item_number', $search, 'both');
            $this->db->group_by('items.item_id');
            $this->db->limit($limit);
            $by_additional_item_numbers = $this->db->get();
            $temp_suggestions = array();
            
            foreach ($by_additional_item_numbers->result() as $row) {
                $data = array(
                    'name' => $row->item_number,
                    'subtitle' => $row->name,
                    'avatar' => $row->image_id ? app_file_url($row->image_id) : base_url() . "assets/img/item.png"
                );
                $temp_suggestions[$row->item_id] = $data;
            }
            
            foreach ($temp_suggestions as $key => $value) {
                $suggestions[] = array(
                    'value' => $key,
                    'label' => $value['name'],
                    'avatar' => $value['avatar'],
                    'subtitle' => $value['subtitle'] ? $value['subtitle'] : lang('common_none')
                );
            }
            
            $this->db->select('items.item_id, items.name as item_name, item_images.image_id, tags.name as tag_name, ');
            $this->db->from('items_tags');
            $this->db->join('tags', 'items_tags.tag_id=tags.id');
            $this->db->join('items', 'items_tags.item_id=items.item_id');
            $this->db->join('item_images', 'items.item_id = item_images.item_id', 'left');
            $this->db->like('tags.name', $search, 'both');
            $this->db->group_by('items.item_id');
            $this->db->limit($limit);
            
            $by_tags = $this->db->get();
            $temp_suggestions = array();
            
            foreach ($by_tags->result() as $row) {
                $data = array(
                    'name' => $row->item_name,
                    'subtitle' => $row->tag_name,
                    'avatar' => $row->image_id ? app_file_url($row->image_id) : base_url() . "assets/img/item.png"
                );
                $temp_suggestions[$row->item_id] = $data;
            }
            
            foreach ($temp_suggestions as $key => $value) {
                $suggestions[] = array(
                    'value' => $key,
                    'label' => $value['name'],
                    'avatar' => $value['avatar'],
                    'subtitle' => $value['subtitle'] ? $value['subtitle'] : lang('common_none')
                );
            }
        }
        
        // only return $limit suggestions
        if (count($suggestions > $limit)) {
            $suggestions = array_slice($suggestions, 0, $limit);
        }
        return $suggestions;
    }

    function check_duplicate($term)
    {
        $this->db->from('items');
        $this->db->where('deleted', 0);
        $query = $this->db->where("name = " . $this->db->escape($term));
        $query = $this->db->get();
        
        if ($query->num_rows() > 0) {
            return true;
        }
    }

    function get_item_search_suggestions($search, $price_field = 'unit_price', $limit = 25, $category_ids = [])
    {
        if (! trim($search)) {
            return array();
        }
        
        $suggestions = array();
        
        if ($this->config->item('supports_full_text') && ! $this->config->item('legacy_search_method')) {
            $this->db->select("item_images.image_id, items.*,categories.name as category, MATCH (" . $this->db->dbprefix('items') . ".name) AGAINST (" . $this->db->escape(escape_full_text_boolean_search($search) . '*') . " IN BOOLEAN MODE) as rel, CASE WHEN " . $this->db->dbprefix('items') . ".name = " . $this->db->escape($search) . " THEN 1 ELSE 0 END AS exact_score", false);
            $this->db->from('items');
            $this->db->join('item_images', 'items.item_id = item_images.item_id', 'left');
            $this->db->join('categories', 'categories.id = items.category_id', 'left');
            $this->db->where('items.deleted', 0);
            $this->db->where("MATCH (" . $this->db->dbprefix('items') . ".name) AGAINST (" . $this->db->escape(escape_full_text_boolean_search($search) . '*') . " IN BOOLEAN MODE)", NULL, FALSE);
            if (!empty($category_ids)) {
                $this->db->where_in('items.category_id', $category_ids);
            }
            $this->db->limit($limit);
            $this->db->order_by('rel DESC, exact_score DESC');
            $this->db->group_by('items.item_id');
            $by_name = $this->db->get();
            
            $temp_suggestions = array();
            
            foreach ($by_name->result() as $row) {
                $data = array(
                    'image' => $row->image_id ? app_file_url($row->image_id) : base_url() . "assets/img/item.png",
                    'category' => $row->category,
                    'item_number' => $row->item_number
                );
                
                if ($row->category && $row->size) {
                    $data['label'] = $row->name . ' (' . $row->category . ', ' . $row->size . ') - ' . to_currency($row->$price_field);
                    
                    $temp_suggestions[$row->item_id] = $data;
                } elseif ($row->category) {
                    $data['label'] = $row->name . ' (' . $row->category . ') - ' . to_currency($row->$price_field);
                    
                    $temp_suggestions[$row->item_id] = $data;
                } elseif ($row->size) {
                    $data['label'] = $row->name . ' (' . $row->size . ') - ' . to_currency($row->$price_field);
                    
                    $temp_suggestions[$row->item_id] = $data;
                } else {
                    $data['label'] = $row->name . ' - ' . to_currency($row->$price_field);
                    
                    $temp_suggestions[$row->item_id] = $data;
                }
            }
            
            foreach ($temp_suggestions as $key => $value) {
                $suggestions[] = array(
                    'value' => $key,
                    'label' => $value['label'],
                    'image' => $value['image'],
                    'category' => $value['category'],
                    'item_number' => $value['item_number']
                );
            }
            
            $this->db->select("item_images.image_id,items.*,categories.name as category, MATCH (item_number) AGAINST (" . $this->db->escape(escape_full_text_boolean_search($search) . '*') . " IN BOOLEAN MODE) as rel, CASE WHEN " . $this->db->dbprefix('items') . ".item_number = " . $this->db->escape($search) . " THEN 1 ELSE 0 END AS exact_score", false);
            $this->db->from('items');
            $this->db->join('item_images', 'items.item_id = item_images.item_id', 'left');
            $this->db->join('categories', 'categories.id = items.category_id', 'left');
            $this->db->where('items.deleted', 0);
            $this->db->where("MATCH (item_number) AGAINST (" . $this->db->escape(escape_full_text_boolean_search($search) . '*') . " IN BOOLEAN MODE)", NULL, FALSE);
            if (!empty($category_ids)) {
                $this->db->where_in('items.category_id', $category_ids);
            }
            $this->db->limit($limit);
            $this->db->order_by('rel DESC, exact_score DESC');
            $this->db->group_by('items.item_id');
            $by_item_number = $this->db->get();
            
            $temp_suggestions = array();
            
            foreach ($by_item_number->result() as $row) {
                $data = array(
                    'label' => $row->item_number . ' (' . $row->name . ') - ' . to_currency($row->$price_field),
                    'image' => $row->image_id ? app_file_url($row->image_id) : base_url() . "assets/img/item.png",
                    'category' => $row->category,
                    'item_number' => $row->item_number
                );
                
                $temp_suggestions[$row->item_id] = $data;
            }
            
            foreach ($temp_suggestions as $key => $value) {
                $suggestions[] = array(
                    'value' => $key,
                    'label' => $value['label'],
                    'image' => $value['image'],
                    'category' => $value['category'],
                    'item_number' => $value['item_number']
                );
            }
            
            $this->db->select("item_images.image_id,items.*,categories.name as category,MATCH (product_id) AGAINST (" . $this->db->escape(escape_full_text_boolean_search($search) . '*') . " IN BOOLEAN MODE) as rel, CASE WHEN " . $this->db->dbprefix('items') . ".product_id = " . $this->db->escape($search) . " THEN 1 ELSE 0 END AS exact_score", false);
            $this->db->from('items');
            $this->db->join('item_images', 'items.item_id = item_images.item_id', 'left');
            $this->db->join('categories', 'categories.id = items.category_id', 'left');
            $this->db->where("MATCH (product_id) AGAINST (" . $this->db->escape(escape_full_text_boolean_search($search) . '*') . " IN BOOLEAN MODE)", NULL, FALSE);
            $this->db->where('items.deleted', 0);
            if (!empty($category_ids)) {
                $this->db->where_in('items.category_id', $category_ids);
            }
            $this->db->group_by('items.item_id');
            $this->db->limit($limit);
            $this->db->order_by('rel DESC, exact_score DESC');
            
            $by_product_id = $this->db->get();
            
            $temp_suggestions = array();
            
            foreach ($by_product_id->result() as $row) {
                $data = array(
                    'label' => $row->product_id . ' (' . $row->name . ') - ' . to_currency($row->$price_field),
                    'image' => $row->image_id ? app_file_url($row->image_id) : base_url() . "assets/img/item.png",
                    'category' => $row->category,
                    'item_number' => $row->item_number
                );
                
                $temp_suggestions[$row->item_id] = $data;
            }
            
            foreach ($temp_suggestions as $key => $value) {
                $suggestions[] = array(
                    'value' => $key,
                    'label' => $value['label'],
                    'image' => $value['image'],
                    'category' => $value['category'],
                    'item_number' => $value['item_number']
                );
            }
            
            $this->db->select("item_images.image_id, additional_item_numbers.*, items.unit_price, items.cost_price, categories.name as category", false);
            $this->db->from('additional_item_numbers');
            $this->db->join('items', 'additional_item_numbers.item_id = items.item_id');
            $this->db->join('item_images', 'items.item_id = item_images.item_id', 'left');
            $this->db->join('categories', 'categories.id = items.category_id', 'left');
            $this->db->like($this->db->dbprefix('additional_item_numbers') . '.item_number', $search, 'both');
            if (!empty($category_ids)) {
                $this->db->where_in('items.category_id', $category_ids);
            }
            $this->db->group_by('items.item_id');
            $this->db->limit($limit);
            $by_additional_item_numbers = $this->db->get();
            
            $temp_suggestions = array();
            foreach ($by_additional_item_numbers->result() as $row) {
                $data = array(
                    'label' => $row->item_number . ' - ' . to_currency($row->$price_field),
                    'image' => $row->image_id ? app_file_url($row->image_id) : base_url() . "assets/img/item.png",
                    'category' => $row->category,
                    'item_number' => $row->item_number
                );
                
                $temp_suggestions[$row->item_id] = $data;
            }
            
            foreach ($temp_suggestions as $key => $value) {
                $suggestions[] = array(
                    'value' => $key,
                    'label' => $value['label'],
                    'image' => $value['image'],
                    'category' => $value['category'],
                    'item_number' => $value['item_number']
                );
            }
        } else {
            $this->db->select("items.*,item_images.image_id, categories.name as category", false);
            $this->db->from('items');
            $this->db->join('item_images', 'items.item_id = item_images.item_id', 'left');
            $this->db->join('categories', 'categories.id = items.category_id', 'left');
            $this->db->where('items.deleted', 0);
            if (!empty($category_ids)) {
                $this->db->where_in('items.category_id', $category_ids);
            }
            $this->db->group_by('items.item_id');
            $this->db->like($this->db->dbprefix('items') . '.name', $search, 'both');
            $this->db->limit($limit);
            $by_name = $this->db->get();
            
            $temp_suggestions = array();
            
            foreach ($by_name->result() as $row) {
                $data = array(
                    'image' => $row->image_id ? app_file_url($row->image_id) : base_url() . "assets/img/item.png",
                    'category' => $row->category,
                    'item_number' => $row->item_number
                );
                
                if ($row->category && $row->size) {
                    $data['label'] = $row->name . ' (' . $row->category . ', ' . $row->size . ') - ' . to_currency($row->$price_field);
                    
                    $temp_suggestions[$row->item_id] = $data;
                } elseif ($row->category) {
                    $data['label'] = $row->name . ' (' . $row->category . ') - ' . to_currency($row->$price_field);
                    
                    $temp_suggestions[$row->item_id] = $data;
                } elseif ($row->size) {
                    $data['label'] = $row->name . ' (' . $row->size . ') - ' . to_currency($row->$price_field);
                    
                    $temp_suggestions[$row->item_id] = $data;
                } else {
                    $data['label'] = $row->name . ' - ' . to_currency($row->$price_field);
                    
                    $temp_suggestions[$row->item_id] = $data;
                }
            }
            $this->load->helper('array');
            uasort($temp_suggestions, 'sort_assoc_array_by_label');
            
            foreach ($temp_suggestions as $key => $value) {
                $suggestions[] = array(
                    'value' => $key,
                    'label' => $value['label'],
                    'image' => $value['image'],
                    'category' => $value['category'],
                    'item_number' => $value['item_number']
                );
            }
            
            $this->db->select("items.*,item_images.image_id,categories.name as category", false);
            $this->db->from('items');
            $this->db->join('item_images', 'items.item_id = item_images.item_id', 'left');
            $this->db->join('categories', 'categories.id = items.category_id', 'left');
            $this->db->where('items.deleted', 0);
            if (!empty($category_ids)) {
                $this->db->where_in('items.category_id', $category_ids);
            }
            $this->db->group_by('items.item_id');
            $this->db->like($this->db->dbprefix('items') . '.item_number', $search, 'both');
            $this->db->limit($limit);
            
            $by_item_number = $this->db->get();
            
            $temp_suggestions = array();
            
            foreach ($by_item_number->result() as $row) {
                $data = array(
                    'label' => $row->item_number . ' (' . $row->name . ') - ' . to_currency($row->$price_field),
                    'image' => $row->image_id ? app_file_url($row->image_id) : base_url() . "assets/img/item.png",
                    'category' => $row->category,
                    'item_number' => $row->item_number
                );
                
                $temp_suggestions[$row->item_id] = $data;
            }
            
            uasort($temp_suggestions, 'sort_assoc_array_by_label');
            
            foreach ($temp_suggestions as $key => $value) {
                $suggestions[] = array(
                    'value' => $key,
                    'label' => $value['label'],
                    'image' => $value['image'],
                    'category' => $value['category'],
                    'item_number' => $value['item_number']
                );
            }
            
            $this->db->select("items.*,item_images.image_id, categories.name as category", false);
            $this->db->from('items');
            $this->db->join('item_images', 'items.item_id = item_images.item_id', 'left');
            $this->db->join('categories', 'categories.id = items.category_id', 'left');
            $this->db->where('items.deleted', 0);
            if (!empty($category_ids)) {
                $this->db->where_in('items.category_id', $category_ids);
            }
            $this->db->group_by('items.item_id');
            $this->db->like($this->db->dbprefix('items') . '.product_id', $search, 'both');
            $this->db->limit($limit);
            
            $by_product_id = $this->db->get();
            
            $temp_suggestions = array();
            
            foreach ($by_product_id->result() as $row) {
                $data = array(
                    'label' => $row->product_id . ' (' . $row->name . ') - ' . to_currency($row->$price_field),
                    'image' => $row->image_id ? app_file_url($row->image_id) : base_url() . "assets/img/item.png",
                    'category' => $row->category,
                    'item_number' => $row->item_number
                );
                
                $temp_suggestions[$row->item_id] = $data;
            }
            
            uasort($temp_suggestions, 'sort_assoc_array_by_label');
            
            foreach ($temp_suggestions as $key => $value) {
                $suggestions[] = array(
                    'value' => $key,
                    'label' => $value['label'],
                    'image' => $value['image'],
                    'category' => $value['category'],
                    'item_number' => $value['item_number']
                );
            }
            
            $this->db->select("additional_item_numbers.*, item_images.image_id, items.unit_price, items.cost_price, categories.name as category", false);
            $this->db->from('additional_item_numbers');
            $this->db->join('items', 'additional_item_numbers.item_id = items.item_id');
            $this->db->join('item_images', 'items.item_id = item_images.item_id', 'left');
            $this->db->join('categories', 'categories.id = items.category_id', 'left');
            if (!empty($category_ids)) {
                $this->db->where_in('items.category_id', $category_ids);
            }
            $this->db->group_by('items.item_id');
            $this->db->like($this->db->dbprefix('additional_item_numbers') . '.item_number', $search, 'both');
            
            $this->db->limit($limit);
            
            $by_additional_item_numbers = $this->db->get();
            $temp_suggestions = array();
            foreach ($by_additional_item_numbers->result() as $row) {
                $data = array(
                    'label' => $row->item_number . ' - ' . to_currency($row->$price_field),
                    'image' => $row->image_id ? app_file_url($row->image_id) : base_url() . "assets/img/item.png",
                    'category' => $row->category,
                    'item_number' => $row->item_number
                );
                
                $temp_suggestions[$row->item_id] = $data;
            }
            
            uasort($temp_suggestions, 'sort_assoc_array_by_label');
            
            foreach ($temp_suggestions as $key => $value) {
                $suggestions[] = array(
                    'value' => $key,
                    'label' => $value['label'],
                    'image' => $value['image'],
                    'category' => $value['category'],
                    'item_number' => $value['item_number']
                );
            }
        }
        
        for ($k = count($suggestions) - 1; $k >= 0; $k --) {
            if (! $suggestions[$k]['label']) {
                unset($suggestions[$k]);
            }
        }
        
        $suggestions = array_values($suggestions);
        
        // only return $limit suggestions
        if (count($suggestions > $limit)) {
            $suggestions = array_slice($suggestions, 0, $limit);
        }
        return $suggestions;
    }

    /**
     * get quantily 
     */
    function get_one_location_item($item_id){

        $current_location = $this->Employee->get_logged_in_employee_current_location_id();
        
        if (! $this->config->item('speed_up_search_queries')) {
            $this->db->distinct();
        }
        
        $location_id = 1;

        $this->db->select('items.*, location_items.quantity as quantity, 
                locations.location_id as location_id');
        $this->db->from('items');
        $this->db->join('location_items', 'location_items.item_id = items.item_id and location_id = ' . $current_location, 'left');
        $this->db->join('locations', 'locations.location_id = location_items.location_id', 'left');
        $this->db->where($this->db->dbprefix('locations') .".location_id", $location_id);
        $this->db->where($this->db->dbprefix('items') .'.item_id', $item_id);
        //return $this->db->last_query();
        $query = $this->db->get();
        if ($query->num_rows() == 1) {
            $row = $query->result_array();
            return $row[0]['quantity'];
            // print_r($row);
        }   
        return FALSE;
    }

    /*
     * Preform a search on items
     */
    function search($search, $category_id = false, $limit = 20, $offset = 0, $column = 'name', $orderby = 'asc', $fields = 'all')
    {
        $current_location = $this->Employee->get_logged_in_employee_current_location_id();
        
        if (! $this->config->item('speed_up_search_queries')) {
            $this->db->distinct();
        }
        
        if ($category_id) {
            if ($this->config->item('include_child_categories_when_searching_or_reporting')) {
                $category_ids = $this->Category->get_category_id_and_children_category_ids_for_category_id($category_id);
            } else {
                $category_ids = array(
                    $category_id
                );
            }
        }
        
        $this->db->select('location_items.location as location, item_images.image_id as image_id, GROUP_CONCAT(' . $this->db->dbprefix('tags') . '.name) as tags,items.*,categories.id as category_id,categories.name as category,
        location_items.quantity as quantity, 
        location_items.reorder_level as location_reorder_level,
        location_items.cost_price as location_cost_price,
        location_items.unit_price as location_unit_price');
        $this->db->from('items');
        
        $this->db->join('items_tags', 'items_tags.item_id = items.item_id', 'left');
        $this->db->join('tags', 'tags.id = items_tags.tag_id', 'left');
        $this->db->join('item_images', 'items.item_id = item_images.item_id', 'left');
        //insert join
        // $this->db->join('items_suppliers', 'items_suppliers.item_id = items.item_id', 'left');
        // $this->db->join('suppliers', 'suppliers.person_id = items_suppliers.person_id','left');
        
        $this->db->group_by('items.item_id');
      
        /**
         * insert add join code 
         * retrun sql query
         */
        if ($fields == $this->db->dbprefix('suppliers') . '.company_name') {
            $this->db->join('items_suppliers', 'items_suppliers.item_id = items.item_id', 'left');
            $this->db->join('suppliers', 'suppliers.person_id = items_suppliers.person_id','left');       
        }


        if ($fields == $this->db->dbprefix('manufacturers') . '.name') {
            $this->db->join('manufacturers', 'items.manufacturer_id = manufacturers.id', 'left');
        }
        
        $this->db->join('categories', 'categories.id = items.category_id', 'left');
        $this->db->join('location_items', 'location_items.item_id = items.item_id and location_id = ' . $current_location, 'left');
         
        //echo $this->db->last_query(); die();

        if ($fields == 'all') {
            if ($search) {
                if ($this->config->item('supports_full_text') && ! $this->config->item('legacy_search_method')) {
                    if ($this->config->item('speed_up_search_queries')) {
                        $this->db->where("(MATCH (" . $this->db->dbprefix('items') . ".name, " . $this->db->dbprefix('items') . ".item_number, product_id, description) AGAINST ('\"" . $this->db->escape_str(escape_full_text_boolean_search($search) . '*') . "\"' IN BOOLEAN MODE" . ") or " . $this->db->dbprefix('items') . ".item_id = " . $this->db->escape($search) . ") and " . $this->db->dbprefix('items') . ".deleted=0", NULL, FALSE);
                    } else {
                        $this->db->join('additional_item_numbers', 'additional_item_numbers.item_id = items.item_id', 'left');
                        $this->db->where("(MATCH (" . $this->db->dbprefix('items') . ".name, " . $this->db->dbprefix('items') . ".item_number, product_id, description) AGAINST ('\"" . $this->db->escape_str(escape_full_text_boolean_search($search) . '*') . "\"' IN BOOLEAN MODE" . ") or " . $this->db->dbprefix('tags') . ".name LIKE '%" . $this->db->escape_like_str($search) . "%'" . " or " . $this->db->dbprefix('categories') . ".name LIKE '%" . $this->db->escape_like_str($search) . "%' or " . $this->db->dbprefix('additional_item_numbers') . ".item_number LIKE '%" . $this->db->escape_like_str($search) . "%' or " . $this->db->dbprefix('items') . ".item_id = " . $this->db->escape($search) . ") and " . $this->db->dbprefix('items') . ".deleted=0", NULL, FALSE);
                    }
                } else {

                    $search_terms_array = explode(" ", $this->db->escape_like_str($search));
                    
                    // to keep track of which search term of the array we're looking at now
                    // or " . $this->db->dbprefix('items') . ".company_name LIKE '%" . $this->db->escape_like_str($search) . "%'
                    $search_name_criteria_counter = 0;
                    $sql_search_name_criteria = '';

                    // loop through array of search terms
                    foreach ($search_terms_array as $x) {
                        $sql_search_name_criteria .= ($search_name_criteria_counter > 0 ? " AND " : "") . $this->db->dbprefix('items') . ".name LIKE '%" . $this->db->escape_like_str($x) . "%'";
                        $search_name_criteria_counter ++;

                    }
                    
                      
                    if ($this->config->item('speed_up_search_queries')) {
                        $this->db->where("((" . $sql_search_name_criteria . ") or 
                        item_number LIKE '%" . $this->db->escape_like_str($search) . "%' or " . "product_id LIKE '%" . $this->db->escape_like_str($search) . "%' or " . $this->db->dbprefix('items') . ".item_id LIKE '%" . $this->db->escape_like_str($search) . "%' or " . $this->db->dbprefix('categories') . ".name LIKE '%" . $this->db->escape_like_str($search) . "%') and " . $this->db->dbprefix('items') . ".deleted=0");
                    } else {
                        //echo "no chay vao day"; die();
                         //var_dump($this->db->dbprefix('suppliers') . ".company_name LIKE '%" . $this->db->escape_like_str($search) . "%'");die();
                        $this->db->join('additional_item_numbers', 'additional_item_numbers.item_id = items.item_id', 'left');
                        //insert join 2 table
                        $this->db->join('items_suppliers', 'items_suppliers.item_id = items.item_id', 'left');
                        $this->db->join('suppliers', 'suppliers.person_id = items_suppliers.person_id','left');

                        $this->db->where("((" . $sql_search_name_criteria . ") or " . $this->db->dbprefix('items') . ".item_number LIKE '%" . $this->db->escape_like_str($search) . "%' 
                            or " . "product_id LIKE '%" . $this->db->escape_like_str($search) . "%' 
                            or " . $this->db->dbprefix('items') . ".item_id LIKE '%" . $this->db->escape_like_str($search) . "%'
                            or " . $this->db->dbprefix('suppliers') . ".company_name LIKE '%" . $this->db->escape_like_str($search) . "%'
                            or " . $this->db->dbprefix('tags') . ".name LIKE '%" . $this->db->escape_like_str($search) . "%' 
                            or " . $this->db->dbprefix('additional_item_numbers') . ".item_number LIKE '%" . $this->db->escape_like_str($search) . "%' 
                            or " . $this->db->dbprefix('categories') . ".name LIKE '%" . $this->db->escape_like_str($search) . "%'
                            
                        ) and " . $this->db->dbprefix('items') . ".deleted=0");

                    }
                }
            }
        } else {
            if ($search) {
                // Exact Match fields  || $fields == $this->db->dbprefix('suppliers') . '.company_name'
                //echo "chay khi chọn nhà cung cấp ";die();
                if ($fields == $this->db->dbprefix('items') . '.item_id' || $fields == $this->db->dbprefix('items') . '.reorder_level' || $fields == $this->db->dbprefix('location_items') . '.quantity' || $fields == $this->db->dbprefix('items') . '.cost_price' || $fields == $this->db->dbprefix('items') . '.unit_price' || $fields == $this->db->dbprefix('items') . '.promo_price' || $fields == $this->db->dbprefix('tags') . '.name'  || $fields == $this->db->dbprefix('manufacturers') . '.name') {
                    // echo ;exit();
                    $this->db->where("$fields = " . $this->db->escape($search) . " and " . $this->db->dbprefix('items') . ".deleted=0");

                } else {
                    if ($this->config->item('supports_full_text') && ! $this->config->item('legacy_search_method')) {
                        // Fulltext
                        $this->db->where("MATCH($fields) AGAINST ('\"" . $this->db->escape_str(escape_full_text_boolean_search($search) . '*') . "\"' IN BOOLEAN MODE" . ") and " . $this->db->dbprefix('items') . ".deleted=0");
                    } else {
                        $this->db->like($fields, $search, 'both');
                        $this->db->where($this->db->dbprefix('items') . ".deleted=0");
                    }
                }
            }

            /**
             * search suppliers
             */
            if ($fields == $this->db->dbprefix('suppliers') . '.company_name') {
                $this->db->where("$fields LIKE '%". $this->db->escape_like_str($search) . "%' and " . $this->db->dbprefix('items') . ".deleted=0");
            }
        }
        
        if (isset($category_ids)) {
            $this->db->where_in('categories.id', $category_ids);
        }
        
        if (! $this->config->item('speed_up_search_queries')) {
            $this->db->order_by($column, $orderby);
        }
        
        if (! $search) // If we don't have a search make sure we filter out deleted items
        {
            $this->db->where('items.deleted', 0);
        }
        
        $this->db->limit($limit);
        $this->db->offset($offset);
        // $str = $this->db->last_query();
        // echo $str; die();
        return $this->db->get();      
    }


    // This is more of an estimation (for performance reasons) as we aren't doing a search for all fields. We might change in future
    function search_count_all($search, $category_id = FALSE, $limit = 10000, $fields = 'all')
    {
        $current_location = $this->Employee->get_logged_in_employee_current_location_id();
        
        if (! $this->config->item('speed_up_search_queries')) {
            $this->db->distinct();
        } else {
            return $limit;
        }
        
        if ($category_id) {
            if ($this->config->item('include_child_categories_when_searching_or_reporting')) {
                $category_ids = $this->Category->get_category_id_and_children_category_ids_for_category_id($category_id);
            } else {
                $category_ids = array(
                    $category_id
                );
            }
        }
        
        $this->db->select('items.*,categories.id as category_id,categories.name as category,
        location_items.quantity as quantity, 
        location_items.reorder_level as location_reorder_level,
        location_items.cost_price as location_cost_price,
        location_items.unit_price as location_unit_price');
        $this->db->from('items');
        
        $this->db->join('items_tags', 'items_tags.item_id = items.item_id', 'left');
        $this->db->join('tags', 'tags.id = items_tags.tag_id', 'left');
        $this->db->group_by('items.item_id');
        
        // count 
        if ($fields == $this->db->dbprefix('suppliers') . '.company_name') {
            //$this->db->join('suppliers', 'items.supplier_id = suppliers.person_id', 'left');
            $this->db->join('items_suppliers', 'items_suppliers.item_id = items.item_id', 'left');
            $this->db->join('suppliers', 'suppliers.person_id = items_suppliers.person_id','left');
        }
        
        if ($fields == $this->db->dbprefix('manufacturers') . '.name') {
            $this->db->join('manufacturers', 'items.manufacturer_id = manufacturers.id', 'left');
        }
        
        $this->db->join('categories', 'categories.id = items.category_id', 'left');
        $this->db->join('location_items', 'location_items.item_id = items.item_id and location_id = ' . $current_location, 'left');
        
        if ($fields == 'all') {
            if ($search) {
                if ($this->config->item('supports_full_text') && ! $this->config->item('legacy_search_method')) {
                    if ($this->config->item('speed_up_search_queries')) {
                        $this->db->where("MATCH (" . $this->db->dbprefix('items') . ".name, " . $this->db->dbprefix('items') . ".item_number, product_id, description) AGAINST ('\"" . $this->db->escape_str(escape_full_text_boolean_search($search) . '*') . "\"' IN BOOLEAN MODE" . ") and " . $this->db->dbprefix('items') . ".deleted=0", NULL, FALSE);
                    } else {
                        $this->db->join('additional_item_numbers', 'additional_item_numbers.item_id = items.item_id', 'left');
                        $this->db->where("(MATCH (" . $this->db->dbprefix('items') . ".name, " . $this->db->dbprefix('items') . ".item_number, product_id, description) AGAINST ('\"" . $this->db->escape_str(escape_full_text_boolean_search($search) . '*') . "\"' IN BOOLEAN MODE" . ") or " . $this->db->dbprefix('tags') . ".name LIKE '%" . $this->db->escape_like_str($search) . "%'" . " or " . $this->db->dbprefix('categories') . ".name LIKE '%" . $this->db->escape_like_str($search) . "%' or " . $this->db->dbprefix('additional_item_numbers') . ".item_number LIKE '%" . $this->db->escape_like_str($search) . "%' or " . $this->db->dbprefix('items') . ".item_id = " . $this->db->escape($search) . ") and " . $this->db->dbprefix('items') . ".deleted=0", NULL, FALSE);
                    }
                } else {
                    $search_terms_array = explode(" ", $this->db->escape_like_str($search));
                    
                    // to keep track of which search term of the array we're looking at now
                    $search_name_criteria_counter = 0;
                    $sql_search_name_criteria = '';
                    // loop through array of search terms
                    foreach ($search_terms_array as $x) {
                        $sql_search_name_criteria .= ($search_name_criteria_counter > 0 ? " AND " : "") . $this->db->dbprefix('items') . ".name LIKE '%" . $this->db->escape_like_str($x) . "%'";
                        $search_name_criteria_counter ++;
                    }
                    
                    if ($this->config->item('speed_up_search_queries')) {
                        $this->db->where("((" . $sql_search_name_criteria . ") or 
                        item_number LIKE '%" . $this->db->escape_like_str($search) . "%' or " . "product_id LIKE '%" . $this->db->escape_like_str($search) . "%' or " . $this->db->dbprefix('items') . ".item_id LIKE '%" . $this->db->escape_like_str($search) . "%' or " . $this->db->dbprefix('categories') . ".name LIKE '%" . $this->db->escape_like_str($search) . "%') and " . $this->db->dbprefix('items') . ".deleted=0");
                    } else {
                        $this->db->join('additional_item_numbers', 'additional_item_numbers.item_id = items.item_id', 'left');

                        $this->db->join('items_suppliers', 'items_suppliers.item_id = items.item_id', 'left');
                        $this->db->join('suppliers', 'suppliers.person_id = items_suppliers.person_id','left');
                        
                        $this->db->where("((" . $sql_search_name_criteria . ") or " . $this->db->dbprefix('items') . ".item_number LIKE '%" . $this->db->escape_like_str($search) . "%' 
                            or " . "product_id LIKE '%" . $this->db->escape_like_str($search) . "%' 
                            or " . $this->db->dbprefix('items') . ".item_id LIKE '%" . $this->db->escape_like_str($search) . "%'
                            or " . $this->db->dbprefix('suppliers') . ".company_name LIKE '%" . $this->db->escape_like_str($search) . "%' 
                            or " . $this->db->dbprefix('tags') . ".name LIKE '%" . $this->db->escape_like_str($search) . "%'
                            or " . $this->db->dbprefix('additional_item_numbers') . ".item_number LIKE '%" . $this->db->escape_like_str($search) . "%' or " . $this->db->dbprefix('categories') . ".name LIKE '%" . $this->db->escape_like_str($search) . "%'
                            
                        ) and " . $this->db->dbprefix('items') . ".deleted=0");
                    }
                }
            }
        } else {
            if ($search) {
                // Exact Match fields 
                // || $fields == $this->db->dbprefix('suppliers') . '.company_name'
                if ($fields == $this->db->dbprefix('items') . '.item_id' || $fields == $this->db->dbprefix('items') . '.reorder_level' || $fields == $this->db->dbprefix('location_items') . '.quantity' || $fields == $this->db->dbprefix('items') . '.cost_price' || $fields == $this->db->dbprefix('items') . '.unit_price' || $fields == $this->db->dbprefix('items') . '.promo_price' || $fields == $this->db->dbprefix('tags') . '.name'  || $fields == $this->db->dbprefix('manufacturers') . '.name') {
                    $this->db->where("$fields = " . $this->db->escape($search) . " and " . $this->db->dbprefix('items') . ".deleted=0");
                } else {
                    if ($this->config->item('supports_full_text') && ! $this->config->item('legacy_search_method')) {
                        // Fulltext
                        $this->db->where("MATCH($fields) AGAINST ('\"" . $this->db->escape_str(escape_full_text_boolean_search($search) . '*') . "\"' IN BOOLEAN MODE" . ") and " . $this->db->dbprefix('items') . ".deleted=0");
                    } else {
                        $this->db->like($fields, $search, 'both');
                        $this->db->where($this->db->dbprefix('items') . ".deleted=0");
                    }
                }
            }

            /**
             * search suppliers
             */
            if ($fields == $this->db->dbprefix('suppliers') . '.company_name') {
                $this->db->where("$fields LIKE '%". $this->db->escape_like_str($search) . "%' and " . $this->db->dbprefix('items') . ".deleted=0");
            }
        }
        
        if (isset($category_ids)) {
            $this->db->where_in('categories.id', $category_ids);
        }
        
        if (! $search) // If we don't have a search make sure we filter out deleted items
        {
            $this->db->where('items.deleted', 0);
        }
        
        $result = $this->db->get();
        //$result->result_array();
        // echo "<pre>";
        // var_dump($result->result_array());
        // echo "</pre>";
        // die();
        return $result->num_rows();
    }

    function cleanup()
    {
        $item_data = array(
            'item_number' => null,
            'product_id' => null
        );
        $this->db->where('deleted', 1);
        $return = $this->db->update('items', $item_data);
        
        if ($return) {
            
            $this->load->model('Additional_item_numbers');
            $this->Additional_item_numbers->cleanup();
            $item_tier_prices_table = $this->db->dbprefix('items_tier_prices');
            $items_table = $this->db->dbprefix('items');
            return $this->db->query("DELETE FROM $item_tier_prices_table WHERE item_id IN (SELECT item_id FROM $items_table WHERE deleted = 1)");
        }
        
        return false;
    }

    function add_image($item_id, $image_id)
    {
        $this->db->insert('item_images', array(
            'item_id' => $item_id,
            'image_id' => $image_id
        ));
    }

    function delete_image($image_id)
    {
        $this->db->where('image_id', $image_id);
        $this->db->delete('item_images');
        $this->load->model('Appfile');
        return $this->Appfile->delete($image_id);
    }

    function save_image_metadata($image_id, $title, $alt_text)
    {
        $this->db->where('image_id', $image_id);
        $this->db->update('item_images', array(
            'title' => $title,
            'alt_text' => $alt_text
        ));
    }

    function link_image_to_ecommerce($image_id, $ecommerce_image_id)
    {
        $this->db->where('image_id', $image_id);
        $this->db->update('item_images', array(
            'ecommerce_image_id' => $ecommerce_image_id
        ));
    }

    function create_or_update_store_account_item()
    {
        $this->lang->load('sales');
        $item_id = FALSE;
        
        $this->db->from('items');
        $this->db->where('product_id', lang('common_store_account_payment'));
        
        $result = $this->db->get();
        if ($result->num_rows() > 0) {
            $query_result = $result->result();
            $item_id = $query_result[0]->item_id;
        }
        
        $item_data = array(
            'name' => lang('common_store_account_payment'),
            'product_id' => lang('common_store_account_payment'),
            'description' => '',
            'item_number' => NULL,
            'category_id' => $this->Category->save(lang('common_store_account_payment'), TRUE, NULL, $this->Category->get_category_id(lang('common_store_account_payment'))),
            'size' => '',
            'cost_price' => 0,
            'unit_price' => 0,
            'tax_included' => 0,
            'reorder_level' => NULL,
            'allow_alt_description' => 0,
            'is_serialized' => 0,
            'is_service' => 1,
            'override_default_tax' => 1,
            'deleted' => 0
        );
        
        $this->save($item_data, $item_id);
        
        if ($item_id) {
            return $item_id;
        } else {
            return $item_data['item_id'];
        }
    }

    function create_or_update_flat_discount_item()
    {
        $item_id = FALSE;
        
        $this->db->from('items');
        $this->db->where('product_id', lang('common_discount'));
        
        $result = $this->db->get();
        if ($result->num_rows() > 0) {
            $query_result = $result->result();
            $item_id = $query_result[0]->item_id;
        }
        
        $item_data = array(
            'name' => lang('common_discount'),
            'product_id' => lang('common_discount'),
            'description' => '',
            'item_number' => NULL,
            'category_id' => $this->Category->save(lang('common_discount'), TRUE, NULL, $this->Category->get_category_id(lang('common_discount'))),
            'size' => '',
            'cost_price' => 0,
            'unit_price' => 0,
            'tax_included' => 0,
            'reorder_level' => NULL,
            'allow_alt_description' => 0,
            'is_serialized' => 0,
            'is_service' => 1,
            'override_default_tax' => 1,
            'deleted' => 0
        );
        
        $this->save($item_data, $item_id);
        
        if ($item_id) {
            return $item_id;
        } else {
            return $item_data['item_id'];
        }
    }

    function get_item_id_for_flat_discount_item()
    {
        $this->db->from('items');
        $this->db->where('product_id', lang('common_discount'));
        $this->db->where('deleted', 0);
        
        $result = $this->db->get();
        if ($result->num_rows() > 0) {
            $query_result = $result->result();
            return $query_result[0]->item_id;
        }
        
        return FALSE;
    }

    function get_store_account_item_id()
    {
        $this->lang->load('sales');
        $this->db->from('items');
        $this->db->where('product_id', lang('common_store_account_payment'));
        $this->db->where('deleted', 0);
        
        $result = $this->db->get();
        if ($result->num_rows() > 0) {
            $query_result = $result->result();
            return $query_result[0]->item_id;
        }
        
        return FALSE;
    }

    function get_non_taxable_item_ids()
    {
        $this->db->select('items.item_id');
        $this->db->from('items');
        $this->db->join('items_taxes', 'items_taxes.item_id = items.item_id', 'left');
        $this->db->where('override_default_tax', 1);
        $this->db->where('items_taxes.item_id IS NULL');
        
        $result = $this->db->get()->result_array();
        
        $return = array();
        
        foreach ($result as $row) {
            $return[$row['item_id']] = TRUE;
        }
        
        return $return;
    }
    
    public function get_all_4_listview($options = [])
    {
        $this->db->select('items.*, categories.name as category');
        $this->db->from('items');
        $this->db->join('categories', 'categories.id = items.category_id', 'left');
        if (!empty($options['limit'])) {
            $this->db->limit($options['limit']);
        }
        if (!empty($options['offset'])) {
            $this->db->offset($options['offset']);
        }
        
        $this->db->where('items.deleted != 1');
        
        if(!empty($options['categories'])) {
            $this->db->where_in('items.category_id', $options['categories']);
        }
        if(!empty($options['query'])) {
            $this->db->where("(items.name LIKE '%". $options['query'] ."%' OR items.product_id LIKE '%". $options['query'] ."%' OR items.item_number LIKE '%". $options['query'] ."%')");
        }
        
        if(!empty($options['order_by']) && !empty($options['order_field'])) {
            $this->db->order_by('items.' . $options['order_field'], $options['order_by']);
        }
        $query = $this->db->get();
        return !empty($query) ? $query->result_array() : [];
    }
    
    public function count_all_4_listview($options = [])
    {
        $this->db->from('items');
        if(!empty($options['query'])) {
            $this->db->where("(items.name LIKE '%". $options['query'] ."%' OR items.product_id LIKE '%". $options['query'] ."%' OR items.item_number LIKE '%". $options['query'] ."%')");
        }
        if(!empty($options['categories'])) {
            $this->db->where_in('items.category_id', $options['categories']);
        }
        $this->db->where('deleted != 1');
        $query = $this->db->get();
        return !empty($query) ? $query->num_rows() : 0;
    }
    
    public function search_suggestions($search = '', $category_id = 0, $limit = 25)
    {
        $this->db->select('items.*, manufacturers.name as manufacturer, units.name as unit');
        $this->db->from('items');
        $this->db->join('manufacturers', 'manufacturers.id = items.manufacturer_id', 'left');
        $this->db->join('units', 'units.id = items.unit_id', 'left');
        $this->db->where('items.category_id', $category_id);
        $this->db->limit($limit);
        $this->db->where('items.deleted !=', 1);
        if(!empty($search)) {
            $this->db->where("(items.name LIKE '%". $search ."%' OR items.product_id LIKE '%". $search ."%' OR items.item_number LIKE '%". $search ."%')");
        }
        $query = $this->db->get();
        $suggestions = !empty($query) ? $query->result_array() : [];
        $suggestions = array_map(function($suggestion){
            $suggestion['label'] = $suggestion['name'];
            $suggestion['value'] = $suggestion['item_id'];
            return $suggestion;
        }, $suggestions);
        return $suggestions;
    }
    public function get_item_id_for_order($input) {
        $item_id_by_name = -1;
        $this->db->select('item_id');
        $this->db->from('items');
        $this->db->where('product_id', $input);
        $this->db->where('deleted !=', 1);
        $query = $this->db->get();
        $item_id_by_product = $query->row_array()['item_id'];
        $this->db->select('item_id');
        $this->db->from('items');
        $this->db->like('REPLACE(name," ","")', preg_replace('/\s/', '', $input), 'both');
        $this->db->where('deleted !=', 1);
        $query = $this->db->get();
        if ($query->num_rows() == 1) {
            $item_id_by_name = $query->row_array()['item_id'];
        }
        
        if (!empty($item_id_by_product)) {
            return $item_id_by_product;
        }
        return $item_id_by_name;    
    }

    function get_all_name_item()
    {
        $this->db->select('name, product_id, item_id');
        $this->db->from('items');
        $this->db->where('deleted', 0);
        $query = $this->db->get();        
        if ($query) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        }
        return false;
    }

}