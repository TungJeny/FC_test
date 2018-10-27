<?php
namespace Models\Mrp;

class Mrp_Sales_Forecast_Items extends \CI_Model
{

    public function save($record = [])
    {
        if ($this->db->insert('mrp_sales_forecast_items', $record)) {
            return $this->db->insert_id();
        }
        return FALSE;
    }
    
    public function save_batch($sales_items = [])
    {
        return $this->db->insert_batch('mrp_sales_forecast_items', $sales_items);
    }
    
    public function delete_by_sale_monthly($sale_monthly_id = [])
    {
        $this->db->where('sale_monthly_id',$sale_monthly_id);
        return $this->db->delete('mrp_sales_forecast_items');
    }
}
