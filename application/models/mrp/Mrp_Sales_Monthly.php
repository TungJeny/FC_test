<?php
namespace Models\Mrp;

class Mrp_Sales_Monthly extends \CI_Model
{

    public function save($record = [])
    {
        if ($this->db->insert('mrp_sales_monthly', $record)) {
            return $this->db->insert_id();
        }
        return FALSE;
    }

    public function save_batch($sales_monthly = [])
    {
        $list_insert_id = [];
        $insert_row_num = count($sales_monthly);
        $list_key = array_keys($sales_monthly);
        if ($this->db->insert_batch('mrp_sales_monthly', $sales_monthly)) {
            $first_id = $this->db->insert_id();
            $last_id = $first_id + ($insert_row_num - 1);
            for ($i = $first_id; $i <= $last_id; $i ++) {
                $list_insert_id[] = $i;
            }
            $list_insert_id = array_combine($list_key, $list_insert_id);
        }
        return $list_insert_id;
    }

    public function get_by_month($month = '')
    {
        $this->db->from('mrp_sales_monthly');
        $this->db->where('month', $month);
        $this->db->where('type', 'po');
        $query = $this->db->get();
        if ($query->num_rows() >= 1) {
            return $query->row_array();
        }
        return null;
    }

    public function get_forecast_by_months($months = [])
    {
        $this->db->from('mrp_sales_monthly');
        $this->db->where_in('month', $months);
        $this->db->where('type', 'forecast');
        $query = $this->db->get();
        if ($query->num_rows() >= 1) {
            return $query->row_array();
        }
        return null;
    }

    public function delete_by_po_sale_monthly($po_monthly_id)
    {
        $this->db->where('po_monthly_id', $po_monthly_id);
        return $this->db->delete('mrp_sales_monthly');
    }

    public function delete($id)
    {
        if (is_array($id)) {
            $this->db->where_in('id', $id);
            return $this->db->delete('mrp_sales_monthly');
        }
        $this->db->where('id', $id);
        return $this->db->delete('mrp_sales_monthly');
    }

    public function getAll($options = [])
    {
        $this->db->select('mrp_sales_monthly.*, people.first_name, people.last_name');
        $this->db->from('mrp_sales_monthly');
        $this->db->join('employees', 'employees.id = mrp_sales_monthly.employee_id');
        $this->db->join('people', 'employees.person_id = people.person_id');
        $this->db->where('type', 'po');
        
        if (! empty($options['limit'])) {
            $this->db->limit($options['limit']);
        }
        if (! empty($options['offset'])) {
            $this->db->offset($options['offset']);
        }
        
        if (! empty($options['order_by']) && ! empty($options['order_field'])) {
            $this->db->order_by($options['order_field'], $options['order_by']);
        }
        
        $query = $this->db->get();
        return ! empty($query) ? $query->result_array() : [];
    }

    public function countAll($options = [])
    {
        $this->db->select('mrp_sales_monthly.*, people.first_name, people.last_name');
        $this->db->from('mrp_sales_monthly');
        $this->db->join('employees', 'employees.id = mrp_sales_monthly.employee_id');
        $this->db->join('people', 'employees.person_id = people.person_id');
        $this->db->where('type', 'po');
        $query = $this->db->get();
        return ! empty($query) ? $query->num_rows() : 0;
    }

    public function delete_sales_monthly_by_month($month, $ignore_id, $customer_id, $order_for = '')
    {
        $list_must_be_delete_id = [];
        $this->db->select('id');
        $this->db->from('mrp_sales_monthly');
        $this->db->where('id !=', $ignore_id);
        $this->db->where('month', $month);
        $this->db->where('customer_id', $customer_id);
        if (! empty($order_for)) {
            $this->db->where('order_for', $order_for);
        }
        $query = $this->db->get()->result_array();
        if (! empty($query)) {
            $list_must_be_delete_id_po = array_column($query, 'id');
            $this->db->select('id');
            $this->db->from('mrp_sales_monthly');
            $this->db->where_in('po_monthly_id', $list_must_be_delete_id_po);
            $this->db->where('customer_id', $customer_id);
            $query = $this->db->get()->result_array();
            $list_must_be_delete_id_forecast = array_column($query, 'id');
            $list_must_be_delete_id = array_merge($list_must_be_delete_id_po, $list_must_be_delete_id_forecast);
        } else {
            return;
        }
        $this->db->where_in('id', $list_must_be_delete_id);
        $this->db->or_where_in('po_monthly_id', $list_must_be_delete_id);
        $this->db->delete('mrp_sales_monthly');
    }

    // ham lây danh sách đơn hàng theo tháng
    public function get_all_of_month_current()
    {
        // $this->db->distinct('mrp_sales_daily.sale_monthly_id');
        $this->db->select('mrp_sales_monthly.*');
        $this->db->from('mrp_sales_monthly');
        $this->db->where('month(created_at)', date('m'));
        $this->db->where('type', 'po');
        $query = $this->db->get();
        return ! empty($query) ? $query->result_array() : [];
    }

    // hàm lấy forecast 3 tháng tiếp theo ở đơn hàng
    public function get_forecast_of_next_three_month($oder_list)
    {
        $this->db->select('*,SUM(qty) AS total_qty');
        $this->db->from('mrp_sales_forecast_items');
        $this->db->join('mrp_sales_monthly', 'mrp_sales_monthly.id = mrp_sales_forecast_items.sale_monthly_id');
        $this->db->where_in('mrp_sales_monthly.po_monthly_id', $oder_list);
        $this->db->where('mrp_sales_monthly.type', 'forecast');
        $this->db->group_by('sale_monthly_id');
        $query = $this->db->get();
        return ! empty($query) ? $query->result_array() : [];
    }

    public function get_by_id($id)
    {
        $this->db->from('mrp_sales_monthly');
        $this->db->where('mrp_sales_monthly.id', $id);
        $query = $this->db->get();
        return ! empty($query) ? $query->row_array() : [];
    }
}
