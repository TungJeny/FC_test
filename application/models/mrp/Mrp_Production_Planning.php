<?php
namespace Models\Mrp;

use Models\Constant;

class Mrp_Production_Planning extends \CI_Model
{

    public function save($data = [])
    {
        $this->db->where('month', $data['month']);
        $this->db->where('type', $data['type']);
        $this->db->update('mrp_production_planning_detail', [
            'deleted' => 1,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        $this->db->insert('mrp_production_planning_detail', $data);
    }

    public function insert_data($data, $month)
    {
        $this->db->where('month', $month);
        $this->db->delete('product_planning_import',[
            'month' => $month
        ]);
        $this->db->insert_batch('product_planning_import', $data);
    }
    
    public function save_planning_detail_sub($data = [])
    {
        $this->db->where('month', $data['month']);
        $this->db->where('status = 0');
        $this->db->from('mrp_production_planning_detail_sub');
        $query = $this->db->get();
        $check = count($query->result());
        if ($check) {
            
            $this->db->where('month', $data['month']);
            $this->db->update('mrp_production_planning_detail_sub', [
                'detail' => ($data['detail']),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        } else
            $this->db->insert('mrp_production_planning_detail_sub', $data);
        // echo $this->db->last_query();
    }

    public function get_planning_detail_sub($month)
    {
        $this->db->where('month', $month);
        $this->db->where('status = 0');
        $this->db->from('mrp_production_planning_detail_sub');
        $query = $this->db->get();
        $record = ! empty($query) ? $query->row_array() : [];
        $record['detail'] = empty(json_decode($record['detail'], true)) ? [] : json_decode($record['detail'], true);
        return $record;
    }

    public function get_detail($month, $type = 'detail')
    {
        $this->db->where('month', $month);
        $this->db->where('type', $type);
        $this->db->where('deleted = 0');
        $this->db->from('mrp_production_planning_detail');
        $query = $this->db->get();
        $record = ! empty($query) ? $query->row_array() : [];
        $record['detail'] = empty(json_decode($record['detail'], true)) ? [] : json_decode($record['detail'], true);
        return $record;
    }
    
    public function get_planning_materials($month = '')
    {
        $current_planning = $this->get_detail($month, 'detail')['detail'];
        $order_items = (new Mrp_Sales_Items())->get_by_month([
            $month
        ]);
        // var_dump($order_items);exit();
        $factories = Constant::factories();
        $matrix = [];
        $orders_materials = [];
        $orders_materials_item = [];
        $list_item_id = array_column($order_items, 'item_id');
        $mrp_item_semi = new Mrp_Item_Semi();
        $list_semi = $mrp_item_semi->list_item_semi_by_item_id($list_item_id);
        foreach ($order_items as $order_item) {
            $orders_materials[] = [
                'item_id' => $order_item['item_id'],
                'name' => $order_item['item_name'],
                'unit' => $order_item['unit'],
                'type' => 'item',
                'type_item' => 'item',
                'price' => $order_item['cost_price'],
                'customer_id' => $order_item['customer_id']
            ];
            $orders_materials_item[$order_item['item_id']] = [
                'item_id' => $order_item['item_id'],
                'name' => $order_item['item_name'],
                'unit' => $order_item['unit'],
                'type' => 'item',
                'type_item' => 'item',
                'price' => $order_item['cost_price'],
                'customer_id' => $order_item['customer_id']
            ];
            if (! empty($list_semi[$order_item['item_id']])) {
                foreach ($list_semi[$order_item['item_id']] as $semi_item) {
                    $orders_materials_item_type[$semi_item['item_id']] = 'semi';
                    $orders_materials[] = [
                        'item_id' => $semi_item['item_id'],
                        'name' => $semi_item['name'],
                        'unit' => $semi_item['unit'],
                        'type' => 'item',
                        'type_item' => 'semi',
                        'price' => $semi_item['cost_price'],
                        'customer_id' => $order_item['customer_id']
                    ];
                    $_matrix = [];
                    $_matrix['xn_' . $month . '_qty'] = 0;
                    // $_matrix['xn_' . $month . '_qty_actual'] = 0;
                    $_matrix['xn_' . $month . '_price'] = 0;
                    foreach ($factories as $id => $factory) {
                        $_matrix['xn_' . $id . '_qty'] = 0;
                        $_matrix['xn_' . $id . '_price'] = number_format(0, 2);
                    }
                    foreach ($_matrix as $key => &$value) {
                        if (! empty($current_planning[$semi_item['item_id']][$key])) {
                            $value = $current_planning[$semi_item['item_id']][$key];
                        }
                    }
                    unset($value);
                    $matrix[$semi_item['item_id']] = $_matrix;
                }
            }
            $_matrix = [];
            $_matrix['xn_' . $month . '_qty'] = 0;
            // $_matrix['xn_' . $month . '_qty_actual'] = 0;
            $_matrix['xn_' . $month . '_price'] = 0;
            foreach ($factories as $id => $factory) {
                $_matrix['xn_' . $id . '_qty'] = 0;
                $_matrix['xn_' . $id . '_price'] = number_format(0, 2);
            }
            foreach ($_matrix as $key => &$value) {
                if (! empty($current_planning[$order_item['item_id']][$key])) {
                    $value = $current_planning[$order_item['item_id']][$key];
                }
            }
            unset($value);
            $matrix[$order_item['item_id']] = $_matrix;
        }
        /* star tính tổng số vật tư kế hoạch sản xuất */
        $total = [];
        foreach ($matrix as $k_matrix => $v_matrix) {
            foreach ($v_matrix as $k => $val) {
                if (! empty($total['tol_' . $k]))
                    $total['tol_' . $k] = $total['tol_' . $k] + $val;
                else
                    $total['tol_' . $k] = $val;
                // if(!is_integer($val))
                $matrix[$k_matrix][$k] = ($val);
            }
        }
        return [
            'factories' => $factories,
            'materials' => $orders_materials,
            'materials_item' => $orders_materials_item,
            'matrix' => (object) $matrix,
            'month' => $month,
            'totals' => $total
        ];
    }
}
