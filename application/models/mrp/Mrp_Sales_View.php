<?php
namespace Models\Mrp;

class Mrp_Sales_View extends \CI_Model
{

    public function save($record = [])
    {
        if ($this->db->insert('mrp_sales_view', $record)) {
            return $this->db->insert_id();
        }
        return FALSE;
    }

    public function delete_by_sale_monthly($sale_monthly_id = '')
    {
        $this->db->where('sale_monthly_id', $sale_monthly_id);
        $this->db->delete('mrp_sales_view');
    }

    public function add_sale_monthly_id_to_sale_view($sale_monthly_id, $sale_view)
    {
        $sale_view['sale_monthly_id'] = $sale_monthly_id;
        return $sale_view;
    }
}
