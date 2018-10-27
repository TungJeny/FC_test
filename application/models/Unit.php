<?php

class Unit extends CI_Model
{

    function count_all()
    {
        $this->db->from('units');
        return $this->db->count_all_results();
    }

    function get_all($limit = 10000, $offset = 0, $col = 'name', $order = 'asc')
    {
        $this->db->from('units');
        $this->db->where('deleted', 0);
        if (! $this->config->item('speed_up_search_queries')) {
            $this->db->order_by($col, $order);
        }
        
        $this->db->limit($limit);
        $this->db->offset($offset);
        
        $return = array();
        
        foreach ($this->db->get()->result_array() as $result) {
            $return[$result['id']] = array(
                'name' => $result['name']
            );
        }
        
        return $return;
    }

    function get_multiple_info($unit_ids)
    {
        $this->db->from('units');
        $this->db->where_in('id', $unit_ids);
        $this->db->order_by("name", "asc");
        return $this->db->get();
    }

    function save($unit_name, $unit_id = FALSE)
    {
        if ($unit_id == FALSE) {
            if ($unit_name) {
                if ($this->db->insert('units', array(
                    'name' => $unit_name
                ))) {
                    return $this->db->insert_id();
                }
            }
            return FALSE;
        } else {
            $this->db->where('id', $unit_id);
            if ($this->db->update('units', array(
                'name' => $unit_name
            ))) {
                return $unit_id;
            }
        }
        return FALSE;
    }

    /*
     * Deletes one tag
     */
    function delete($unit_id)
    {
        $this->db->where('id', $unit_id);
        return $this->db->update('units', array(
            'deleted' => 1,
            'name' => NULL
        ));
    }

    function get_units_for_item($item_id)
    {
        $this->db->select('units.name, units.id');
        $this->db->from('items_units');
        $this->db->join('units', 'items_units.unit_id=units.id');
        $this->db->where('items_units.item_id', $item_id);
        
        $return = array();
        
        foreach ($this->db->get()->result_array() as $result) {
            $return[] = $result['name'];
        }
        
        return $return;
    }

    function unit_id_exists($unit_id)
    {
        $this->db->from('units');
        $this->db->where('id', $unit_id);
        $query = $this->db->get();
        
        return ($query->num_rows() == 1);
    }

    function unit_name_exists($unit_name)
    {
        $this->db->from('units');
        $this->db->where('name', $unit_name);
        $query = $this->db->get();
        
        return ($query->num_rows() == 1);
    }

    function get_unit_id_by_name($unit_name)
    {
        $this->db->from('units');
        $this->db->where('name', $unit_name);
        
        $query = $this->db->get();
        
        if ($query->num_rows() == 1) {
            $row = $query->row();
            return $row->id;
        }
        
        return FALSE;
    }

    /**
     * Get Unit Name
     * @param $unit_id
     * @return bool
     */
    function get_unit_name($unit_id)
    {
        $this->db->from('units');
        $this->db->where('id', $unit_id);
        $query = $this->db->get();
        if (!$query) {
            return false;
        }
        $row = $query->row_array();
        $query->free_result();
        return $row['name'];
    }
}