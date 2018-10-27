<?php

class Receiver_location extends CI_Model
{
    /*
     * Determines if a given location_id is an location
     */
    function exists($id)
    {
        $this->db->from('receiver_location');
        $this->db->where('id', $id);
        $query = $this->db->get();
        if (!$query) {
            return false;
        }
        return true;
    }

    /*
     * Gets information about a particular location
     */
    function get_info($location_id)
    {
        $this->db->from('receiver_location');
        $this->db->where('id', $location_id);

        $query = $this->db->get();
        if (!$query) {
            return false;
        }
        if ($query->num_rows() == 1) {
            return $query->row();
        } else {
            // Get empty base parent object, as $location_id is NOT a location
            $location_obj = new stdClass();

            // Get all the fields from locations table
            $fields = $this->db->list_fields('receiver_location');

            foreach ($fields as $field) {
                $location_obj->$field = '';
            }

            return $location_obj;
        }
    }

    function save($recever_location_name)
    {
        $this->db->insert('receiver_location', ['name' => $recever_location_name]);
        $insert_id = $this->db->insert_id();
        return $insert_id;
    }


    function get_search_suggestions($search, $limit = 25)
    {
        if (!trim($search)) {
            return array();
        }
        $suggestions = array();
        $this->db->from('receiver_location');
        $this->db->like('name', $search, 'both');
        $this->db->limit($limit);
        $by_name = $this->db->get();
        if (!$by_name) {
            return array();
        }
        $temp_suggestions = array();
        foreach ($by_name->result() as $row) {
            $data = array(
                'name' => $row->name,
                'email' => '',
                'avatar' => base_url() . "assets/img/user.png"
            );
            $temp_suggestions[$row->id] = $data;
        }
        asort($temp_suggestions);
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

    /**
     * @param int $start
     * @param int $limit
     * @param null $conditions
     * @param null $orders
     * @return mixed
     */
    function get_collection($start = 0, $limit = 100, $conditions = null, $orders = null) {
        $this->db->from('receiver_location');
        if (!empty($conditions)) {
            $this->db->where($conditions);
        }
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
}