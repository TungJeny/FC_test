<?php

class Item_taxes_finder extends CI_Model
{

    /*
     * Gets tax info for a particular item
     */
    function get_info($item_id, $transaction_type = 'sale') // Can be sale or receiving
    {
        if ($transaction_type == 'receiving') {
            $item_info = $this->Item->get_info($item_id);
            $supplier_id = $item_info->supplier_id;
            
            if ($supplier_id) {
                $supplier_info = $this->Supplier->get_info($supplier_id, TRUE);
                
                if ($supplier_info->override_default_tax) {
                    $this->load->model('Supplier_taxes');
                    return $this->Supplier_taxes->get_info($supplier_id);
                }
            }
        }
        
        if ($transaction_type == 'sale') {
            $this->load->library('sale_lib');
            $this->load->model('Customer');
            
            if ($this->sale_lib->get_customer() != - 1 && $this->sale_lib->get_mode() != 'store_account_payment') {
                $customer_id = $this->sale_lib->get_customer();
                $customer_info = $this->Customer->get_info($customer_id, TRUE);
                if ($customer_info->override_default_tax) {
                    $this->load->model('Customer_taxes');
                    
                    return $this->Customer_taxes->get_info($customer_id);
                }
                
                if (! $customer_info->taxable) {
                    return array();
                }
            }
        }
        
        $item_location_info = $this->Item_location->get_info($item_id, false, true);
        if ($item_location_info->override_default_tax) {
            return $this->Item_location_taxes->get_info($item_id);
        }
        
        $item_info = $this->Item->get_info($item_id);
        
        if ($transaction_type == 'sale') {
            if ($this->sale_lib->get_is_ebt_sale() && $this->config->item('enable_ebt_payments') && $item_info->is_ebt_item) {
                return array();
            }
        }
        
        if ($item_info->override_default_tax) {
            return $this->Item_taxes->get_info($item_id);
        }
        
        $return = array();
        
        // Location Config
        $default_tax_1_rate = $this->Location->get_info_for_key('default_tax_1_rate');
        $default_tax_1_name = $this->Location->get_info_for_key('default_tax_1_name');
        
        $default_tax_2_rate = $this->Location->get_info_for_key('default_tax_2_rate');
        $default_tax_2_name = $this->Location->get_info_for_key('default_tax_2_name');
        $default_tax_2_cumulative = $this->Location->get_info_for_key('default_tax_2_cumulative') ? $this->Location->get_info_for_key('default_tax_2_cumulative') : 0;
        
        $default_tax_3_rate = $this->Location->get_info_for_key('default_tax_3_rate');
        $default_tax_3_name = $this->Location->get_info_for_key('default_tax_3_name');
        
        $default_tax_4_rate = $this->Location->get_info_for_key('default_tax_4_rate');
        $default_tax_4_name = $this->Location->get_info_for_key('default_tax_4_name');
        
        $default_tax_5_rate = $this->Location->get_info_for_key('default_tax_5_rate');
        $default_tax_5_name = $this->Location->get_info_for_key('default_tax_5_name');
        
        if ($default_tax_1_rate && is_numeric($default_tax_1_rate)) {
            $return[] = array(
                'id' => - 1,
                'item_id' => $item_id,
                'name' => $default_tax_1_name,
                'percent' => $default_tax_1_rate,
                'cumulative' => 0
            );
        }
        
        if ($default_tax_2_rate && is_numeric($default_tax_2_rate)) {
            $return[] = array(
                'id' => - 1,
                'item_id' => $item_id,
                'name' => $default_tax_2_name,
                'percent' => $default_tax_2_rate,
                'cumulative' => $default_tax_2_cumulative
            );
        }
        
        if ($default_tax_3_rate && is_numeric($default_tax_3_rate)) {
            $return[] = array(
                'id' => - 1,
                'item_id' => $item_id,
                'name' => $default_tax_3_name,
                'percent' => $default_tax_3_rate,
                'cumulative' => 0
            );
        }
        
        if ($default_tax_4_rate && is_numeric($default_tax_4_rate)) {
            $return[] = array(
                'id' => - 1,
                'item_id' => $item_id,
                'name' => $default_tax_4_name,
                'percent' => $default_tax_4_rate,
                'cumulative' => 0
            );
        }
        
        if ($default_tax_5_rate && is_numeric($default_tax_5_rate)) {
            $return[] = array(
                'id' => - 1,
                'item_id' => $item_id,
                'name' => $default_tax_5_name,
                'percent' => $default_tax_5_rate,
                'cumulative' => 0
            );
        }
        
        if (! empty($return)) {
            return $return;
        }
        
        // Global Store Config
        $default_tax_1_rate = $this->config->item('default_tax_1_rate');
        $default_tax_1_name = $this->config->item('default_tax_1_name');
        
        $default_tax_2_rate = $this->config->item('default_tax_2_rate');
        $default_tax_2_name = $this->config->item('default_tax_2_name');
        $default_tax_2_cumulative = $this->config->item('default_tax_2_cumulative') ? $this->config->item('default_tax_2_cumulative') : 0;
        
        $default_tax_3_rate = $this->config->item('default_tax_3_rate');
        $default_tax_3_name = $this->config->item('default_tax_3_name');
        
        $default_tax_4_rate = $this->config->item('default_tax_4_rate');
        $default_tax_4_name = $this->config->item('default_tax_4_name');
        
        $default_tax_5_rate = $this->config->item('default_tax_5_rate');
        $default_tax_5_name = $this->config->item('default_tax_5_name');
        
        $return = array();
        
        if ($default_tax_1_rate && is_numeric($default_tax_1_rate)) {
            $return[] = array(
                'id' => - 1,
                'item_id' => $item_id,
                'name' => $default_tax_1_name,
                'percent' => $default_tax_1_rate,
                'cumulative' => 0
            );
        }
        
        if ($default_tax_2_rate && is_numeric($default_tax_2_rate)) {
            $return[] = array(
                'id' => - 1,
                'item_id' => $item_id,
                'name' => $default_tax_2_name,
                'percent' => $default_tax_2_rate,
                'cumulative' => $default_tax_2_cumulative
            );
        }
        
        if ($default_tax_3_rate && is_numeric($default_tax_3_rate)) {
            $return[] = array(
                'id' => - 1,
                'item_id' => $item_id,
                'name' => $default_tax_3_name,
                'percent' => $default_tax_3_rate,
                'cumulative' => 0
            );
        }
        
        if ($default_tax_4_rate && is_numeric($default_tax_4_rate)) {
            $return[] = array(
                'id' => - 1,
                'item_id' => $item_id,
                'name' => $default_tax_4_name,
                'percent' => $default_tax_4_rate,
                'cumulative' => 0
            );
        }
        
        if ($default_tax_5_rate && is_numeric($default_tax_5_rate)) {
            $return[] = array(
                'id' => - 1,
                'item_id' => $item_id,
                'name' => $default_tax_5_name,
                'percent' => $default_tax_5_rate,
                'cumulative' => 0
            );
        }
        
        return $return;
    }
}
?>
