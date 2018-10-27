<?php

require_once 'Sale.php';

class Sale_daily_item extends Sale
{

    /**
     * Sale_daily_item constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param null $term
     * @param $sale_daily_id
     * @param int $limit
     * @return array|bool
     */
    public function get_search_port_suggestions($term = null, $sale_daily_id, $limit = 100)
    {
        $this->db->select('`port`');
        $this->db->from('mrp_sales_items');
        $this->db->where('sale_daily_id', $sale_daily_id);
        if (!empty($term)) {
            $this->db->where('`port` LIKE "%' . $term . '%"');
        }
        $this->db->limit($limit);
        $query = $this->db->get();
        if ($query) {
            $suggestions = !empty($query) ? $query->result_array() : [];
            $query->free_result();
            $suggestions = array_map(function ($suggestion) {
                $suggestion['label'] = 'Cổng giao hàng ' . get_data($suggestion, 'port');
                $suggestion['value'] = get_data($suggestion, 'port');
                return $suggestion;
            }, $suggestions);
            return $suggestions;
        }
        return false;
    }

    /**
     * @param $sale_daily_id
     * @return array|bool
     */
    public function get_ports($sale_daily_id) {
        $this->db->distinct('`port`');
        $this->db->from('mrp_sales_items');
        $this->db->where('sale_daily_id', $sale_daily_id);
        $query = $this->db->get();
        // echo $this->db->last_query();
        if ($query) {
            $result = !empty($query) ? $query->result() : [];
            $query->free_result();
            $ports = array();
            foreach ($result as $row) {
                $ports[] = get_data($row, 'port');
            }
            $ports = array_filter(array_unique($ports));
            return $ports;
        }
        return false;
    }
}