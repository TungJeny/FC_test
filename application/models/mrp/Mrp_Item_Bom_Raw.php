<?php
namespace Models\Mrp;

class Mrp_Item_Bom_Raw extends \CI_Model
{

    public function prepare_bom_raw($bom_id, $materials = [])
    {
        $bom_raw = [];
        foreach ($materials as $semi_key => $semi_materials) {
            $semi_id = str_replace('semi_', '', $semi_key);
            foreach ($semi_materials as $semi_material) {
                $bom_raw[] = [
                    'bom_id' => $bom_id,
                    'semi_id' => $semi_id != 'main' ? $semi_id : NULL,
                    'material_id' => $semi_material['item_id'],
                    'rate_of_qty' => ! empty($semi_material['rate_of_qty']) ? $semi_material['rate_of_qty'] : NULL,
                    'rate_of_unit' => ! empty($semi_material['rate_of_unit']) ? $semi_material['rate_of_unit'] : NULL
                ];
            }
        }
        return $bom_raw;
    }

    public function delete_by_bom($bom_id = 0)
    {
        $this->db->where_in('bom_id', $bom_id);
        $this->db->delete('mrp_items_boms_raw');
    }

    public function save_bom_raws($bom_id, $records = [])
    {
        $this->delete_by_bom($bom_id);
        foreach ($records as $record) {
            $this->db->insert('mrp_items_boms_raw', $record);
        }
        return true;
    }
}
