<?php

require_once 'Sale.php';

class Sale_daily extends Sale
{
    /**
     * Sale_daily constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('format');
    }

    /**
     * @param $id
     * @return bool
     */
    public function get_info($id) {
        $this->db->select('*');
        $this->db->from('mrp_sales_daily');
        $this->db->where('id', $id);
        $query = $this->db->get();
        $result = false;
        if ($query) {
            $result = $query->row();
            $query->free_result();
        }
        return $result;
    }

    /**
     * @param $sale_daily_id
     * @return bool
     */
    function exists($sale_daily_id)
    {
        $this->db->from('mrp_sales_daily');
        $this->db->where('id', $sale_daily_id);
        $query = $this->db->get();
        if ($query) {
            $result = ($query->num_rows() > 0);
            $query->free_result();
            return ($result > 0);
        }
        return false;
    }

    /**
     * @param string $search
     * @param $customer_id
     * @param int $limit
     * @return array|bool
     */
    public function get_search_suggestions($search = '', $customer_id, $limit = 25)
    {
        $this->db->select('mrp_sales_daily.id AS `id`, mrp_sales_daily.date AS `date`, mrp_sales_daily.sale_monthly_id');
        $this->db->from('mrp_sales_monthly');
        $this->db->join('mrp_sales_daily', 'mrp_sales_monthly.id = mrp_sales_daily.sale_monthly_id');
        $this->db->join('customers', 'mrp_sales_monthly.customer_id = customers.id');
        $this->db->where('mrp_sales_monthly.customer_id', $customer_id);
        $this->db->where('mrp_sales_monthly.month LIKE "' . $search . '"');
        $this->db->limit($limit);
        $query = $this->db->get();
        if ($query) {
            $suggestions = !empty($query) ? $query->result_array() : [];
            $query->free_result();
            $suggestions = array_map(function ($suggestion) {
                $suggestion['label'] = 'Đơn hàng ngày ' . render_date(get_data($suggestion, 'date'));
                $suggestion['value'] = $suggestion['id'];
                return $suggestion;
            }, $suggestions);
            return $suggestions;
        }
        return false;
    }

    /**
     * @param $sale_daily_id
     * @return array
     */
    public function get_items($sale_daily_id) {
        // Get Items
        $this->db->select('*');
        $this->db->select('GROUP_CONCAT(port) AS `ports`');
        $this->db->select_sum('mrp_sales_items.qty', 'quantity');
        $this->db->from('mrp_sales_items');
        $this->db->join('items', 'items.item_id = mrp_sales_items.item_id');
        $this->db->where('sale_daily_id', $sale_daily_id);
        $this->db->group_by('mrp_sales_items.item_id');
        $result = $this->db->get();
        $collection = array();
        if ($result) {
            $collection = $result->result_array();
            $line = 1;
            foreach ($collection as $key => $row) {
                $collection[$key]['line'] = $line;
                $line++;
            }
            $result->free_result();
        }
        return $collection;
    }

    /**
     * @param $sale_daily_id
     * @param $port
     * @return array
     */
    public function get_port_items($sale_daily_id, $port) {
        // Get Items
        $this->db->select('*');
        $this->db->select('GROUP_CONCAT(port) AS `ports`');
        $this->db->select_sum('mrp_sales_items.qty', 'quantity');
        $this->db->from('mrp_sales_items');
        $this->db->join('items', 'items.item_id = mrp_sales_items.item_id');
        $this->db->where('sale_daily_id', $sale_daily_id);
        $this->db->group_by('mrp_sales_items.item_id');
        if (!empty($port)) {
            $this->db->where_in('port', $port);
        } else {
            $this->db->where('port', $port);
        }
        $result = $this->db->get();
        $collection = array();
        if ($result) {
            $collection = $result->result_array();
            $line = 1;
            foreach ($collection as $key => $row) {
                $collection[$key]['line'] = $line;
                $line++;
            }
            $result->free_result();
        }
        return $collection;
    }
}