<?php
namespace Models\Mrp;

class Mrp_Sales_Daily extends \CI_Model
{

    public function save($record = [])
    {
        if ($this->db->insert('mrp_sales_daily', $record)) {
            return $this->db->insert_id();
        }
        return FALSE;
    }

    public function save_batch($sales_daily = [])
    {
        $list_insert_id = [];
        $insert_row_num = count($sales_daily);
        $list_key = array_keys($sales_daily);
        if ($this->db->insert_batch('mrp_sales_daily', $sales_daily)) {
            $first_id = $this->db->insert_id();
            $last_id = $first_id + ($insert_row_num - 1);
            for ($i = $first_id; $i <= $last_id; $i ++) {
                $list_insert_id[] = $i;
            }
            $list_insert_id = array_combine($list_key, $list_insert_id);
        }
        return $list_insert_id;
    }

    public function get_by_date($sale_month_id = '', $date = '')
    {
        $this->db->from('mrp_sales_daily');
        $this->db->where('sale_monthly_id', $sale_month_id);
        $this->db->where('date', $date);
        $query = $this->db->get();
        if ($query->num_rows() >= 1) {
            return $query->row_array();
        }
        return null;
    }
    public function delete_by_po_sale_monthly($sale_monthly_id)
    {
        $this->db->where('sale_monthly_id', $sale_monthly_id);
        return $this->db->delete('mrp_sales_daily');
    }
    
    public function get_sales_daily_by_monthly_id($sale_monthly_id = '')
    {
        $result = $this->db->from('mrp_sales_daily')
            ->where('sale_monthly_id', $sale_monthly_id)
            ->get()->result_array();
        return $result;
    }
}
