<?php
namespace Models\Mrp;

class Mrp_Sales_Items extends \CI_Model
{

    public function save($record = [])
    {
        if ($this->db->insert('mrp_sales_items', $record)) {
            return $this->db->insert_id();
        }
        return FALSE;
    }

    public function get_by_item($sale_date_id = '', $item_id = '')
    {
        $this->db->from('mrp_sales_items');
        $this->db->where('sale_date_id', $sale_date_id);
        $this->db->where('item_id', $item_id);
        $query = $this->db->get();
        if ($query->num_rows() >= 1) {
            return $query->row_array();
        }
        return null;
    }

    public function save_batch($sales_items = [])
    {
        return $this->db->insert_batch('mrp_sales_items', $sales_items);
    }
    
    public function get_by_month($months = []) {
        $this->db->select('mrp_sales_items.*, mrp_sales_monthly.customer_id, items.cost_price as cost_price, items.name as item_name, SUM(`phppos_mrp_sales_items`.`qty`) as total_qty, units.name as unit');
        $this->db->from('mrp_sales_items');
        $this->db->join('mrp_sales_daily', 'mrp_sales_daily.id = mrp_sales_items.sale_daily_id');
        $this->db->join('mrp_sales_monthly', 'mrp_sales_monthly.id = mrp_sales_daily.sale_monthly_id');
        $this->db->join('items', 'mrp_sales_items.item_id = items.item_id');
        $this->db->join('units', 'units.id = items.unit_id', 'left');
        $this->db->where('mrp_sales_monthly.type', 'po');
        $this->db->where('items.deleted !=', 1);
        $this->db->group_by('mrp_sales_items.item_id'); 
        if (!empty($months)) {
            $this->db->where_in('mrp_sales_monthly.month', $months);
        }
        $query = $this->db->get();
        return !empty($query) ? $query->result_array() : [];
    }
    
    public function get_all_by_items($item_ids = [], $month) {
        $this->db->select('mrp_sales_items.*, items.name as item_name, mrp_sales_monthly.month, mrp_sales_monthly.type, mrp_sales_daily.date');
        $this->db->from('mrp_sales_items');
        $this->db->join('mrp_sales_daily', 'mrp_sales_daily.id = mrp_sales_items.sale_daily_id');
        $this->db->join('mrp_sales_monthly', 'mrp_sales_monthly.id =    mrp_sales_daily.sale_monthly_id');
        $this->db->join('items', 'mrp_sales_items.item_id = items.item_id');
        $this->db->where('mrp_sales_monthly.month', $month);
        $this->db->where_in('mrp_sales_items.item_id', $item_ids);
        $this->db->where('mrp_sales_monthly.type', 'po');
        $this->db->where('items.deleted', 0);
        
        $query = $this->db->get();
        return !empty($query) ? $query->result_array() : [];
    }
    
    public function get_forecast_by_items($item_ids = [], $months = []) {
        $this->db->select('mrp_sales_forecast_items.*, items.name as item_name, mrp_sales_monthly.month, mrp_sales_monthly.type');
        $this->db->from('mrp_sales_forecast_items');
        $this->db->join('mrp_sales_monthly', 'mrp_sales_monthly.id = mrp_sales_forecast_items.sale_monthly_id');
        $this->db->join('items', 'mrp_sales_forecast_items.item_id = items.item_id');
        $this->db->where_in('mrp_sales_monthly.month', $months);
        $this->db->where_in('items.item_id', $item_ids);
        $this->db->where('mrp_sales_monthly.type', 'forecast');
        $this->db->where('items.deleted', 0);
        $query = $this->db->get();
        return !empty($query) ? $query->result_array() : [];
    }
    
    public function get_forecast_item($months = [])
    {
        $this->db->from('mrp_sales_forecast_items');
        $this->db->join('mrp_sales_monthly', 'mrp_sales_monthly.id = mrp_sales_forecast_items.sale_monthly_id');
        $this->db->where_in('mrp_sales_monthly.month', $months);
        $this->db->where('mrp_sales_monthly.type', 'forecast');
        $query = $this->db->get();
        return !empty($query) ? $query->result_array() : [];
    }
}
