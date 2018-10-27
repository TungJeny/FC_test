<?php
namespace Models\Mrp;

use Helpers\Date_Helper;

import('model', 'Item.php');

class Mrp_Item extends \Item
{
    public function get_plan_detail($material_id = '', $month)
    {
        $this->db->select('items.name as name,items.cost_price as cost_price, items.limit as limit, items.limit as limit, items.category_id as category_id, mrp_items_boms.item_id as item_id, items.product_id as product_id, units.name as unit, manufacturers.name as manufacturer, mrp_items_boms_raw.*');
        $this->db->from('mrp_items_boms');
        $this->db->join('mrp_items_boms_raw', 'mrp_items_boms_raw.bom_id = mrp_items_boms.id');
        $this->db->join('items', 'items.item_id = mrp_items_boms.item_id');
        $this->db->join('units', 'units.id = items.unit_id', 'left');
        $this->db->join('manufacturers', 'items.manufacturer_id = manufacturers.id', 'left');
        $this->db->where('mrp_items_boms_raw.material_id', $material_id);
        
        $query = $this->db->get();
        $bom_items = !empty($query) ? $query->result_array() : [];
        
        $bom_item_ids = [];
        foreach ($bom_items as $bom_item) {
            if (!in_array($bom_item['item_id'], $bom_item_ids)) {
                $bom_item_ids[] = $bom_item['item_id'];
            }
        }
        
        $sale_items = (new Mrp_Sales_Items())->get_all_by_items($bom_item_ids, $month);
        $sale_items_summary = [];
        foreach ($sale_items as $sale_item) {
            $_date = $sale_item['date'];
            if (!in_array($sale_item['item_id'], array_keys($sale_items_summary))) {
                $sale_items_summary[$sale_item['item_id']] = [
                    'item_id' => $sale_item['item_id'],
                    'item_name' => $sale_item['item_name'],
                    'dates' => [$_date => [
                        'date' => $_date,
                        'qty' => $sale_item['qty'],
                     ]],
                    'total' => $sale_item['qty']
                ];
            } else {
                if (!array_key_exists($_date, $sale_items_summary[$sale_item['item_id']]['dates'])) {
                    $sale_items_summary[$sale_item['item_id']]['dates'][$_date] = [
                        'date' => $_date,
                        'qty' => $sale_item['qty'],
                    ];
                } else {
                    $sale_items_summary[$sale_item['item_id']]['dates'][$_date]['qty'] += $sale_item['qty'];
                }
                $sale_items_summary[$sale_item['item_id']]['total'] += $sale_item['qty'];
            }
        }
        $material = $this->get_info($material_id);
        
        $sale_item_ids = array_keys($sale_items_summary);
        
        $bom_items = array_filter($bom_items, function($item) use ($sale_item_ids) {
            return in_array($item['item_id'], $sale_item_ids);
        });
        
        
        $forecast_items = (new Mrp_Sales_Items())->get_forecast_by_items($sale_item_ids, Date_Helper::get_next_months($month));
        $forecast_items_summary = [];
        foreach ($forecast_items as $forecast_item) {
            $_month = $forecast_item['month'];
            if (!in_array($forecast_item['item_id'], array_keys($forecast_items_summary))) {
                $forecast_items_summary[$forecast_item['item_id']] = [
                    'item_id' => $forecast_item['item_id'],
                    'item_name' => $forecast_item['item_name'],
                    'months' => [$_month => [
                        'month' => $_month,
                        'qty' => $forecast_item['qty'],
                    ]],
                    'total' => $forecast_item['qty']
                ];
            } else {
                if (!array_key_exists($_month, $forecast_items_summary[$forecast_item['item_id']]['months'])) {
                    $forecast_items_summary[$forecast_item['item_id']]['months'][$_month] = [
                        'month' => $_month,
                        'qty' => $forecast_item['qty'],
                    ];
                } else {
                    $forecast_items_summary[$forecast_item['item_id']]['months'][$_month]['qty'] = $forecast_item['qty'];
                }
                $forecast_items_summary[$forecast_item['item_id']]['total'] += $forecast_item['qty'];
            }
        }
        return [
            'material' => [
                'item_id' => $material->item_id,
                'name' => $material->name,
                'cost_price' => (float) $material->cost_price,
                'buffer_rate' => $material->buffer_rate,
                'limit' => $material->limit,
            ],
            'boms' => $bom_items,
            'sale_items' => $sale_items_summary,
            'forecast_items' => $forecast_items_summary,
        ];
    }
    function get_item_id_by_product_id($product_id)
    {
        $this->db->select('item_id');
        $this->db->from('items');
        $this->db->where('product_id', $product_id);
        $query = $this->db->get();
        return $query->row_array()['item_id'];
    }
}
