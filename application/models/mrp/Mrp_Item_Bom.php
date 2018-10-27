<?php
namespace Models\Mrp;

class Mrp_Item_Bom extends \CI_Model
{
    public function save($data = [])
    {
        $old_data = !empty($data['id']) ? $this->get($data['id']) : null;
        if (empty($old_data)) {
            if (!$this->exists_by_code($data['code']) && $this->db->insert('mrp_items_boms', $data)) {
                return $this->db->insert_id();
            }
        } else {
            if ($old_data['code'] == $data['code']) {
                $this->db->where('id', $old_data['id']);
                if ($this->db->update('mrp_items_boms', $data))
                {
                    return $old_data['id'];
                }
            } elseif (!$this->exists_by_code($data['code'])) {
                $this->db->where('id', $old_data['id']);
                if ($this->db->update('mrp_items_boms', $data))
                {
                    return $old_data['id'];
                }
            }
        }
        return false;
    }
    
    public function delete($id = '')
    {
        $this->db->where_in('id', $id);
        $this->db->delete('mrp_items_boms');
        $mrp_Item_Bom_Raw = new Mrp_Item_Bom_Raw();
        $mrp_Item_Bom_Raw->delete_by_bom($id);
    }
    
    public function get($id = '')
    {
        $this->db->from('mrp_items_boms');
        $this->db->where('id', $id);
        $query = $this->db->get();
        if ($query->num_rows() == 1) {
            return $query->row_array();
        }
        return null;
    }
    
    function exists_by_code($code)
    {
        $this->db->from('mrp_items_boms');
        $this->db->where('code', $code);
        $query = $this->db->get();
        return ($query->num_rows() >= 1);
    }
    
    public function get_all_boms($item_id = 0)
    {
        $this->db->from('mrp_items_boms');
        $this->db->where('mrp_items_boms.item_id', $item_id);
        $query = $this->db->get();
        return !empty($query) ? $query->result_array() : [];
    }
    
    public function count_all_boms($item_id = 0)
    {
        $this->db->from('mrp_items_boms');
        $this->db->where('item_id', $item_id);
        return !empty($query) ? $query->num_rows() : 0;
    }
    
    public function get_bom_raw($item_id = 0, $bom_id = -1, $categories = [])
    {
        $this->db->select('items.name as name,items.cost_price as cost_price, items.limit as limit, items.limit as limit, items.category_id as category_id, mrp_items_boms_raw.material_id as item_id, items.product_id as product_id, units.name as unit, manufacturers.name as manufacturer, mrp_items_boms_raw.*');
        $this->db->from('mrp_items_boms');
        $this->db->join('mrp_items_boms_raw', 'mrp_items_boms_raw.bom_id = mrp_items_boms.id');
        $this->db->join('items', 'items.item_id = mrp_items_boms_raw.material_id');
        $this->db->join('units', 'units.id = items.unit_id', 'left');
        $this->db->join('manufacturers', 'items.manufacturer_id = manufacturers.id', 'left');
        $this->db->where('mrp_items_boms.item_id', $item_id);
        $this->db->where('mrp_items_boms_raw.bom_id', $bom_id);
        $this->db->where('items.deleted', 0);
        if (!empty($categories)) {
            $this->db->where_in('items.category_id', $categories  );
        }
        $query = $this->db->get();
        return !empty($query) ? $query->result_array() : [];
    }
    
    public function calculate_materials($item_id = '', $qty = 0, $categories = [])
    {
        $boms = $this->get_all_boms($item_id);
        $bom = reset($boms);
        $materials = $this->get_bom_raw($item_id, $bom['id'], $categories);
        $materials = array_map(function($material) use ($qty) {
            $material['calculated_by_qty'] = ($qty * $material['rate_of_qty']);
            $material['calculated_by_unit'] = (!empty($material['rate_of_unit']) && $material['rate_of_unit']!=0) ? ($qty / $material['rate_of_unit']) : NULL;
            return $material;
        }, $materials);
        return $materials;
    }
     function get_itemid_bom($val){
        if (! empty($val)) {
            $this->db->select('item_id,id');
            $this->db->where_in('id', $val);
            $this->db->from('mrp_items_boms');
            $query = $this->db->get();
            return ! empty($query) ? $query->result_array() : [];
        }
        return [];
    }
    function get_customer_item($item_id){
        if (! empty($item_id)) {
            $this->db->select('customers.code,items_customers.item_id,items_customers.person_id');
            $this->db->from('items_customers');
            $this->db->join('customers','customers.person_id = items_customers.person_id');
            $this->db->join('items','items.item_id = items_customers.item_id');
            $this->db->where_in('items_customers.item_id',$item_id);
            $this->db->where_in('items.deleted',0);
            $query = $this->db->get();
            return $query->result_array();
        }
        return [];
    }
}
