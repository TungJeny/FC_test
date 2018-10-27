<?php
namespace Models\Mrp;

use Models\Mrp\Orders\Mrp_Order;
use Helpers\Date_Helper;
use Models\Constant;
use Models\Inventory;

class Mrp_Materials_Plans extends \CI_Model
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Purchase_order');
        $this->load->model('Item');
    }

    public function save($data = [],$load=0)
    {
        $this->db->where('month', $data['month']);
        $this->db->where('category_id', $data['category_id']);
        $this->db->where('type', $data['type']);
        $this->db->delete('mrp_materials_plans',[
            'category_id' => $data['category_id']
        ]);
        // $this->db->update('mrp_materials_plans', [
        //     'deleted' => 1,
        //     'updated_at' => date('Y-m-d H:i:s')
        // ]);
        if($load==0)
        $this->db->insert('mrp_materials_plans', $data);
    }

    public function save_batch($materials_plan_detail = [])
    {
        return $this->db->insert_batch('mrp_materials_plans', $materials_plan_detail);
    }

    public function remove($material_ids = [], $months = [], $category_id = '')
    {
        $this->db->where_in('material_id', $material_ids);
        $this->db->where_in('month', $months);
        $this->db->where('category_id', $category_id);
        $this->db->delete('mrp_materials_plans');
    }

    public function get_detail($month, $type = 'detail')
    {
        $this->db->where('month', $month);
        $this->db->where('type', $type);
        $this->db->where('deleted = 0');
        $this->db->from('mrp_materials_plans');
        $query = $this->db->get();
        $record = ! empty($query) ? $query->row_array() : [];
        $record['detail'] = json_decode($record['detail'], true);

        return $record;
    }

    public function get_detail_category($month, $category_id, $type = 'detail')
    {
        $this->db->where('month', $month);
        $this->db->where('type', $type);
        $this->db->where('category_id', $category_id);
        $this->db->from('mrp_materials_plans');
        $query = $this->db->get();
        $record = ! empty($query) ? $query->row_array() : [];
        $record['detail'] = json_decode($record['detail'], true);

        return $record;
    }

    protected function calc_booked_quater_qty($item_id, $months, $plan_detail)
    {
        if (empty($plan_detail['detail'][$item_id])) {
            return 0;
        }
        $booked_quater_qty = 0;
        foreach ($plan_detail['detail'][$item_id] as $month => $detail) {
            if (in_array($month, $months) && ! empty($detail['qty_booked'])) {
                $booked_quater_qty += $detail['qty_booked'];
            }
        }
        return $booked_quater_qty;
    }

    public function get_summary_material_plan_sub($month,$category_id)
    {
        $order_model = new Mrp_Order();
        $order_items = (new Mrp_Sales_Items())->get_by_month([$month]);
        $factories = Constant::factories();
        $order_materials = $order_model->get_all_materials_by_month([$month], [$category_id]);
        return $order_materials;
    }
    public function get_summary_material_plan($month, $category_id)
    {
        $this->load->helper('format');
        $order_model = new Mrp_Order();
        if ($category_id == Constant::MRP_CATEGORY_ID_VTP) {
            $order_materials = $order_model->get_all_materials_by_month([$month], [
                $category_id
            ]);
            
            $material_ids = array_unique(array_map(function ($material) {
                return $material['material_id'];
            }, $order_materials));
            
            $history_trans = $this->Inventory->get_history_trans($material_ids, [
                'end_date' => date("Y-m-t 23:59:59", strtotime($month))
            ]);
            $inventories = [];
            foreach ($history_trans as $trans) {
                if (! array_key_exists($trans['item_id'], $inventories)) {
                    $inventories[$trans['item_id']]['qty'] = $trans['trans_inventory'];
                } else {
                    $inventories[$trans['item_id']]['qty'] += $trans['trans_inventory'];
                }
            }

            /*
               Begin
               Get Total Quantity For Material
            */
            $month = date('Y-m');
            $product_plan_model = new Mrp_Production_Planning();
            $current_planning = $product_plan_model->get_detail($month, 'detail');
            $details = get_data($current_planning, 'detail');
            $item_ids = (array_keys($details));

            $this->db->from('mrp_items_boms_raw');
            $this->db->join('mrp_items_boms', 'mrp_items_boms_raw.bom_id = mrp_items_boms.id');
            $this->db->where_in('item_id', $item_ids);
            $result = $this->db->get();
            $rows = $result->result_array();
            $result->free_result();
            $positions = array();
            foreach ($rows as $row) {
                foreach ($details as $key => $detail) {
                    if ($row['item_id'] == $key) {
                        $positions[$key][] = get_data($row, 'material_id');
                    }
                }
            }
            $materials = array();
            foreach ($details as $key => $detail) {
                if (isset($positions[$key])) {
                    foreach ($positions[$key] as $index => $material_id) {
                        if (!isset($materials[$material_id])) {
                            $materials[$material_id] = 0;
                        }
                        for ($index_key = 1; $index_key <= 6; $index_key++) {
                            if (isset($detail['xn_' . $index_key . '_qty'])) {
                                $materials[$material_id] += intval($detail['xn_' . $index_key . '_qty']);
                            }
                        }
                    }
                }
            }
            /* End */

            foreach ($order_materials as $id => &$order_material) {
                $month2 = substr($month,-2);
                $month1 = substr($month,-2)-1;
                $month1 = $month1<10?'0'.$month1:$month1;
                $month1 = str_replace('-'.$month2,'-'.$month1,$month);
                $inve = $this->get_sum_inventory($month1,$id);
                $inventory_month_qty = ($inve->TN)-($inve->TX);
                $order_material['inventory_month_qty'] =$inventory_month_qty;
                if (isset($materials[$id])) {
                    $order_material['calculated_by_unit'] = $materials[$id];
                } else {
                    $order_material['calculated_by_unit'] = 0;
                }
                $order_material['inventory_month_price'] = $order_material['inventory_month_qty'] * $order_material['cost_price'];
                $order_material['price_calculated'] = $order_material['calculated_by_unit'] * $order_material['cost_price'];
                $order_material['target_month_qty'] = $order_material['calculated_by_unit'] - $order_material['inventory_month_qty'];
                $order_material['target_month_price'] = $order_material['target_month_qty'] * $order_material['cost_price'];
            }

            return [
                'materials' => $order_materials,
                'summary_matrix' => []
            ];
        } else {
            
            $order_materials = $order_model->get_all_materials_by_month([$month], [
                $category_id
            ]);
            $est_months = Date_Helper::get_next_months($month);
            $forecast_materials = $order_model->get_all_forecast_materials_by_months($est_months, [
                $category_id
            ]);
            
            $material_ids = array_unique(array_map(function ($material) {
                return $material['material_id'];
            }, $order_materials));

            /**
             *   ______    __                                 __                       
             /_  __/  __\_\_   ____    ____ _             / /  ___    ____    __  __
              / /    / / / /  / __ \  / __ `/        __  / /  / _ \  / __ \  / / / /
             / /    / /_/ /  / / / / / /_/ /        / /_/ /  /  __/ / / / / / /_/ / 
            /_/     \__,_/  /_/ /_/  \__, /         \____/   \___/ /_/ /_/  \__, /  
                                    /____/                                 /____/   
             */
            if ($category_id == Constant::MRP_CATEGORY_ID_VTC) {
                $materials_plan_summary = $this->get_detail_category($month, $category_id, 'summary');
                $plan_summary = $materials_plan_summary['detail'];
                $materials_plan_detail = $this->get_detail_category($month, $category_id, 'summary');
            }

            if ($category_id == Constant::MRP_CATEGORY_ID_PHOI) {
                $materials_plan_summary = $this->get_detail_category($month, $category_id, 'summary');
                $plan_summary = $materials_plan_summary['detail'];
                $materials_plan_detail = $this->get_detail_category($month, $category_id, 'summary');
            }

            /**
             * END CHECK
             */
            // $materials_plan_summary = $this->get_detail($month, 'summary');
            // $plan_summary = $materials_plan_summary['detail'];
            // $materials_plan_detail = $this->get_detail($month);
            $matrix = [];
            foreach ($order_materials as $id => $material) {
                $inventory_previous_month_qty = (float) $this->get_material_plan_value($id, date('Y-m', strtotime('-1 month')), 'qty_inventory', [
                    'order_materials' => $order_materials,
                    'materials_plan_detail' => $materials_plan_detail
                ]);
                $khsx_month_qty = (float) $this->get_material_plan_value($id, date('Y-m'), 'qty_khsx', [
                    'order_materials' => $order_materials,
                    'materials_plan_detail' => $materials_plan_detail,
                    'category_id' => $category_id
                ]);
                $booked_month_qty = $this->get_value_of_month($id, date('Y-m') ) - $this->get_number_reality_of_item($id, date('Y-m'));
                
                $inventory_month_qty = (float) $inventory_previous_month_qty + $booked_month_qty - $khsx_month_qty;

                $booked_quater_qty3 =  $this->get_material_plan_value($id, date('Y-m', strtotime('+3 month')), 'qty_esitmate', [
                    'order_materials' => $order_materials,
                    'materials_plan_detail' => $materials_plan_detail
                ]);

                $booked_quater_qty1 =  $this->get_material_plan_value($id, date('Y-m', strtotime('+1 month')), 'qty_esitmate', [
                    'order_materials' => $order_materials,
                    'materials_plan_detail' => $materials_plan_detail
                ]);

                $booked_quater_qty2 =  $this->get_material_plan_value($id, date('Y-m', strtotime('+2 month')), 'qty_esitmate', [
                    'order_materials' => $order_materials,
                    'materials_plan_detail' => $materials_plan_detail
                ]);

                $booked_quater_qty = $booked_quater_qty1 + $booked_quater_qty2 + $booked_quater_qty3;


                $forecast_calculated_by_qty = ! empty($forecast_materials[$id]) ? $forecast_materials[$id]['calculated_by_qty'] : 0;
                $target_month_qty = (! empty($plan_summary[$id]) && ! empty($plan_summary[$id]['target_month_qty'])) ? $plan_summary[$id]['target_month_qty'] : ($inventory_month_qty + $booked_quater_qty - $forecast_calculated_by_qty - $material['limit']);
                // add files ghi chú. note
                $target_time_needed = (! empty($plan_summary[$id]) && ! empty($plan_summary[$id]['target_time_needed'])) ? $plan_summary[$id]['target_time_needed'] : '';

                $target_uses = (! empty($plan_summary[$id]) && ! empty($plan_summary[$id]['target_uses'])) ? $plan_summary[$id]['target_uses'] : '';

                $target_supplier = (! empty($plan_summary[$id]) && ! empty($plan_summary[$id]['target_supplier'])) ? $plan_summary[$id]['target_supplier'] : '';

                $target_categories = (! empty($plan_summary[$id]) && ! empty($plan_summary[$id]['target_categories'])) ? $plan_summary[$id]['target_categories'] : '';

                // end files note
                $matrix[$id] = [
                    'inventory_previous_month_qty' => $inventory_previous_month_qty,
                    'inventory_previous_month_price' => $inventory_previous_month_qty * $material['cost_price'],
                    'khsx_month_qty' => $khsx_month_qty,
                    'khsx_month_price' => $khsx_month_qty * $material['cost_price'],
                    'booked_month_qty' => $booked_month_qty,
                    'booked_month_price' => $booked_month_qty * $material['cost_price'],
                    'inventory_month_qty' => $inventory_month_qty,
                    'inventory_month_price' => $inventory_month_qty * $material['cost_price'],
                    'limit_number' => (float) $material['limit'],
                    'khsx_quater_qty' => $forecast_calculated_by_qty,
                    'khsx_quater_price' => $forecast_calculated_by_qty * $material['cost_price'],
                    'booked_quater_qty' => $booked_quater_qty,
                    'booked_quater_price' => $booked_quater_qty * $material['cost_price'],
                    'target_month_qty' => $target_month_qty,
                    'target_month_price' => $target_month_qty * $material['cost_price'],

                    'target_time_needed' => $target_time_needed,
                    'target_uses' => $target_uses,
                    'target_supplier' => $target_supplier,
                    'target_categories' => $target_categories
                ];
            }

            return [
                'materials' => $order_materials,
                'summary_matrix' => $matrix
            ];
        }
    }
    
    /**
     * get number reality of item
     * @param  [type] $item_id [description]
     * @param  [type] $month   [description]
     * @return [type]          [description]
     */
    function get_number_reality_of_item($item_id, $month){
        $this->load->model('Purchase_order');
        $start_date = strtotime($month);
        $end_date = strtotime('+1 month', strtotime($month));
        $data = $this->Purchase_order->get_collection();
        $id_purchase = [];
        foreach ($data as $row) {
            $id_purchase[] = $row->id;
        }
        $total_reality = $this->Purchase_order->sumQuantityStockById_all_of_item($id_purchase, 'purchase_order' , $item_id, $start_date, $end_date);

        if ($total_reality) {
            return $total_reality;
        }else{
            return 0;
        }
    }

    /**
     * get total of 12 month
     * @param  [type] $item_id 
     * @param  [type] $month   
     * @return [type]   
     */
    public function get_value_of_month($item_id, $month){
        $this->load->model('Purchase_order');
        $start_date = strtotime($month);
        $end_date = strtotime('+1 month', strtotime($month));
        $time_collection_of_month = $this->Purchase_order->get_collection_for_item($item_id,  $start_date, $end_date);
        if ($time_collection_of_month) {
            return $time_collection_of_month['total_month'];
        }else{
            return 0;
        }
    }

    public function get_material_plan($month, $category_id)
    {
        $order_model = new Mrp_Order();
        $product_planning = new Mrp_Production_Planning();

        $est_months = Date_Helper::get_next_months(date('Y-m'));
        $months = Date_Helper::get_months();
        $order_materials = $order_model->get_all_materials_by_month([$month], [$category_id]);
        
        //$item_plan = $product_planning->get_detail(date('Y-m'),'detail');
        $forecast_items = $order_model->get_forecast_materials_by_months($months, [
            $category_id
        ]);
        $matrix = [];
        $records = [];
        $i = 1;
        
        foreach ($order_materials as $id => $order_material) {
            $records[] = [
                'id' => $order_material['id'],
                'item_id' => $order_material['item_id'],
                'type' => 'material',
                'material_id' => $order_material['material_id'],
                'unit' => $order_material['unit'],
                'name' => $order_material['item_id'] . ' - ' . $order_material['name']
            ];
            $records[] = [
                'id' => $id . '_qty_khsx',
                'item_id' => $id . '_qty_khsx',
                'type' => 'qty_khsx',
                'material_id' => $order_material['material_id'],
                'unit' => $order_material['unit'],
                'name' => 'Số lượng cấp theo KHSX'
            ];
            $records[] = [
                'id' => $id . '_qty_used_actual',
                'item_id' => $id . '_qty_used_actual',
                'type' => 'qty_used_actual',
                'material_id' => $order_material['material_id'],
                'unit' => $order_material['unit'],
                'name' => 'Sử dụng thực tế'
            ];
            
            $records[] = [
                'id' => $id . '_qty_inventory',
                'item_id' => $id . '_qty_inventory',
                'unit' => $order_material['unit'],
                'material_id' => $order_material['material_id'],
                'type' => 'qty_inventory',
                'name' => 'Tồn kho đầu tháng'
            ];
            
            $records[] = [
                'id' => $id . '_expire_time',
                'item_id' => $id . '_expire_time',
                'unit' => $order_material['unit'],
                'material_id' => $order_material['material_id'],
                'type' => 'expire_time',
                'name' => 'Thời gian VT cần về cho SX'
            ];
            
            $records[] = [
                'id' => $id . '_qty_booked',
                'item_id' => $id . '_qty_booked',
                'type' => 'qty_booked',
                'material_id' => $order_material['material_id'],
                'unit' => $order_material['unit'],
                'name' => 'Số lượng đặt'
            ];
            
            $records[] = [
                'id' => $id . '_qty_esitmate',
                'item_id' => $id . '_qty_esitmate',
                'type' => 'qty_esitmate',
                'material_id' => $order_material['material_id'],
                'unit' => $order_material['unit'],
                'name' => 'Số lượng dự kiến về trong tháng'
            ];
            $records[] = [
                'id' => $id . '_qty_actual_income',
                'item_id' => $id . '_qty_actual_income',
                'type' => 'qty_actual_income',
                'material_id' => $order_material['material_id'],
                'unit' => $order_material['unit'],
                'name' => 'Thực tế hàng về'
            ];
            $i ++;
        }

        // add $category_id == Constant::MRP_CATEGORY_ID_PHOI
        if ($category_id == Constant::MRP_CATEGORY_ID_PHOI) {

            $material_ids = array_unique(array_map(function ($material) {
            return $material['material_id'];
            }, $records));
            $materials_plan_detail = $this->get_detail_category($month,$category_id);
            foreach ($records as $record) {
                foreach ($months as $month) {
                    if (is_numeric($record['item_id'])) {
                        $matrix[$record['item_id']][$month] = [
                            'material' => '-',
                            'qty_khsx' => $this->get_material_plan_value($record['material_id'], $month, 'qty_khsx', [
                                'order_materials' => $order_materials,
                                'materials_plan_detail' => $materials_plan_detail,
                                'forecast_items' => $forecast_items,
                                'category_id' => $category_id
                            ]),
                            'qty_used_actual' => $this->get_material_plan_value($record['material_id'], $month, 'qty_used_actual', [
                                'order_materials' => $order_materials,
                                'materials_plan_detail' => $materials_plan_detail
                            ]),
                            'qty_inventory' => $this->get_material_plan_value($record['material_id'], $month, 'qty_inventory', [
                                'order_materials' => $order_materials,
                                'materials_plan_detail' => $materials_plan_detail
                            ]),
                            'expire_time' => $this->get_material_plan_value($record['material_id'], $month, 'expire_time', [
                                'order_materials' => $order_materials,
                                'materials_plan_detail' => $materials_plan_detail
                            ]),
                            'qty_booked' => $this->get_value_of_month($record['material_id'], $month ),
                          
                            'qty_esitmate' => $this->get_material_plan_value($record['material_id'], $month, 'qty_esitmate', [
                                'order_materials' => $order_materials,
                                'materials_plan_detail' => $materials_plan_detail
                            ]),

                            'qty_actual_income' => $this->get_number_reality_of_item($record['material_id'], $month)
                        ];
                    }
                }
            }

            return [
                'materials' => $records,
                'months' => $months,
                'materials_matrix' => $matrix
            ];
        }

        // END Constant::MRP_CATEGORY_ID_PHOI
        
        if ($category_id == Constant::MRP_CATEGORY_ID_VTC) {
            $material_ids = array_unique(array_map(function ($material) {
                return $material['material_id'];
            }, $records));
            $materials_plan_detail = $this->get_detail_category($month,$category_id);
            foreach ($records as $record) {
                foreach ($months as $month) {
                    if (is_numeric($record['item_id'])) {
                        $matrix[$record['item_id']][$month] = [
                            'material' => '-',
                            'qty_khsx' => $this->get_material_plan_value($record['material_id'], $month, 'qty_khsx', [
                                'order_materials' => $order_materials,
                                'materials_plan_detail' => $materials_plan_detail,
                                'forecast_items' => $forecast_items,
                                'category_id' => $category_id
                            ]),
                            'qty_used_actual' => $this->get_material_plan_value($record['material_id'], $month, 'qty_used_actual', [
                                'order_materials' => $order_materials,
                                'materials_plan_detail' => $materials_plan_detail
                            ]),
                            'qty_inventory' => $this->get_material_plan_value($record['material_id'], $month, 'qty_inventory', [
                                'order_materials' => $order_materials,
                                'materials_plan_detail' => $materials_plan_detail
                            ]),
                            'expire_time' => $this->get_material_plan_value($record['material_id'], $month, 'expire_time', [
                                'order_materials' => $order_materials,
                                'materials_plan_detail' => $materials_plan_detail
                            ]),
                            'qty_booked' => $this->get_value_of_month($record['material_id'], $month),
                          
                            'qty_esitmate' =>  $this->get_total_staged_item($record['material_id'], $month),

                            'qty_actual_income' => $this->get_number_reality_of_item($record['material_id'], $month)
                        ];
                    }
                }
            }

            return [
                'materials' => $records,
                'months' => $months,
                'materials_matrix' => $matrix
            ];
        }

    }
    /**
     * lấy thời thian dự kiến về trong tháng thông qua giai đoạn
     * @param  [type] $item_id [description]
     * @param  [type] $month   [description]
     * @return [type]          [description]
     */
    public function get_total_staged_item($item_id, $month)
    {
        $this->load->model('Purchase_order');

        $start_date = strtotime($month);
        $end_date = strtotime('+1 month', strtotime($month));
        $time_collection_staged_of_month = $this->Purchase_order->get_collection_staged_table($item_id, $month);
        if ($time_collection_staged_of_month) {
            return $time_collection_staged_of_month['total_month_staged'];
        }else{
            return 0;
        }

    }

    
    /**
     * * get quantity number phoi.
     * lấy số lượng phôi : mà số lượng phôi trong import excel là số lượng trong thành phẩm tạo ra sản phẩm trong vật tư chính
     * $bom_id chính là item_id là 1.. chúng đều là id định danh sản phẩm trong kho
     * get item or month , items_id
     *  item_id chính là bom_id
     * @param  [type] $month    [description]
     * @param  [type] $items_id [description]
     * @return [type]           [description]
     */
    public function get_number_quantity_phoi($bom_id, $month )
    {
        $this->db->from('mrp_items_boms');
        $this->db->join('product_planning_import', 'mrp_items_boms.item_id = product_planning_import.item_id', 'left');
        $this->db->where('mrp_items_boms.id', $bom_id);
        $this->db->where('month', $month);
        $query = $this->db->get();
       // echo $this->db->last_query();
        if ($query->num_rows() == 1) {
            $result = $query->row_array();
            $query->free_result();
            return $result['quantity_phoi'];
        }
        return false;
    }

    function get_quantity_material_norms($material_id){
        $this->db->select('
        items.name as name,
        items.cost_price as cost_price, 
        items.limit as limit, 
        items.category_id as category_id, 
        mrp_items_boms_raw.material_id as item_id,
        items.product_id as product_id, 
        mrp_items_boms_raw.*
        ');
        $this->db->from('mrp_items_boms');
        $this->db->join('mrp_items_boms_raw', 'mrp_items_boms_raw.bom_id = mrp_items_boms.id');
        $this->db->join('items', 'items.item_id = mrp_items_boms_raw.material_id');
        $this->db->where('mrp_items_boms_raw.material_id', $material_id);
        $query = $this->db->get();
        //echo $this->db->last_query().'<br>';
        if ($query) {
            $result = $query->result_array();
            $query->free_result(); 
            return $result;
        }
        return false;
    }

    public function get_coefficient_item($item_id)
    {
        $this->db->from('items');
        $this->db->where('item_id', $item_id);
        $query = $this->db->get();
        if ($query->num_rows() == 1) {
            $result = $query->row_array();
            $query->free_result();
            return $result['coefficient'];
        }
        return false;
    }
           
    public function get_number_khsx_current($material_item_id, $bom_id, $month)
    {
        // $material_item_id is là item_id trong vtc đăng có
        // $month is là tháng hiện tại đăng thao tác
        // $category_id is là danh mục vtc hay phoi hay phụ bỏ trống ko dùng tới
        $quantity_material_norms[$material_item_id] = $this->get_quantity_material_norms($material_item_id);
        $array_material_item[] = $material_item_id;
        $quantity = 0;  
        $data_material = array();

        foreach ($quantity_material_norms[$material_item_id] as $item => $item_val) {  
            $data_material[$item] = $item_val;
            //$data_material[$item][$item_val['item_id']] = 1111;
            $data_material[$item]['quantity_phoi'] = $this->get_number_quantity_phoi($item_val['bom_id'], $month);
            $data_material[$item]['coefficient'] = $this->get_coefficient_item($item_val['item_id']);
        }
        foreach ($array_material_item as $id_material) {
                    
            foreach ($data_material as $item_material) {
                if( $item_material['item_id'] == $id_material ){
                    $quantity += ($item_material['quantity_phoi'] ? $item_material['quantity_phoi'] : 0) * ( $item_material['coefficient'] ? $item_material['coefficient'] : 0 ) * ($item_material['rate_of_qty'] ? $item_material['rate_of_qty'] : 0 );
                }
            }
        }
        return $quantity;          
    }

   /**
    * ham ke thua tu bang khsx , xu ly mang du lieu de * 1.05. cua thang hien tai
    * hàm lấy tổng số lượng bên KHSX về của tháng hiện tại. để nhân và tính.. cùng vs số đv trong định mức vật tư
    * @param  [type] $material_item_id [description]
    * @return [type]                   [description]
    */
    public function get_number_khsx($material_item_id, $month, $category_id ='')
    {  
       // $month = date('Y-m');
        $data_number = (new Mrp_Production_Planning())->get_planning_materials($month);
        
        $item_id_bom = [];
       
        foreach ($data_number['materials_item'] as $row_item_id => $item_id) {
           $item_id_bom[] = $item_id['item_id'];
        }

        // $result_item_number là mảng key là item_id_pom value = la tổng số
        $result_item_number = [];
        foreach ($data_number['matrix'] as $row_matrix => $value) {    
            foreach ($item_id_bom as $row) {
                if ($row_matrix == $row ) {
                    $result_item_number[$row_matrix] = $value['xn_'.$month.'_qty'];
                }
            }
        }

        $item_id_bom_id = [];
        foreach ($result_item_number as $item_id_bom => $total_number_khsx) {
                    
                $item_id_bom_id[$item_id_bom] = $this->get_itemid_bom_ts($item_id_bom);
        }

        $array_map_view_bom = [];
        foreach ($item_id_bom_id as $key_item_id => $value_bom_id) {
           // $array_map_view_bom[$key_item_id] = $key_item_id; $array_map_view_bom[$key_item_id]
            $array_map_view_bom[$key_item_id] = $this->get_map_data_bom_item($key_item_id, $value_bom_id, $category_id = 2);
        }

        $result_total_all = 0;
        foreach ($array_map_view_bom as $key_item_id_array => $value_item_id_array) {

            foreach ($value_item_id_array as $key_index_item_id => $value_index_item_id) {

                if ($value_index_item_id['material_id'] == $material_item_id) {
                    
                    foreach ($result_item_number as $result_item_pom =>  $result_value_item_pom) {
                          
                        if ($result_item_pom == $key_item_id_array) {
                            $result_total_all += $result_value_item_pom * $value_index_item_id['rate_of_qty'] * 1.05;
                        }
                    }
                }
            }
        }

        return $result_total_all;

    }
    // hàm lấy bom_id 
    function get_itemid_bom_ts($item_id){
       $this->db->select('*');    
       $this->db->from('mrp_items_boms');
       $this->db->where('item_id',$item_id);
       $query = $this->db->get();

       if ($query->num_rows() == 1) {
        $result = $query->row_array();
        $query->free_result();
        return $result['id'];
       }
       return false;
    }
    // hàm check để lấy mảng gồm khối mảng mã item_bom, bom_id, materials_id = item_id trong bảng items
    function get_map_data_bom_item($item_id_bom, $bom_id, $category_id){
        $this->db->select('
        items.name as name,
        items.cost_price as cost_price, 
        items.limit as limit,
        items.category_id as category_id, 
        mrp_items_boms_raw.material_id as item_id,
        items.product_id as product_id, 
        mrp_items_boms_raw.*');

        $this->db->from('mrp_items_boms');
        $this->db->join('mrp_items_boms_raw', 'mrp_items_boms_raw.bom_id = mrp_items_boms.id');
        $this->db->join('items', 'items.item_id = mrp_items_boms_raw.material_id');
        $this->db->where('mrp_items_boms.item_id', $item_id_bom);
        $this->db->where('mrp_items_boms_raw.bom_id', $bom_id);
        $this->db->where('items.category_id', $category_id);

        $query = $this->db->get();
        if ($query) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        }
        return false;
    }

    // hàm xử lý 3 tháng tiếp theo khsx vtc 
    //get all danh sach yamaha honda theo tháng hiện tại date('Y-m')
    // $total_qty Là tổng số forecase của 2 cổng cộng lại
    public function get_list_oder_of_month($material_item_id, $month, $category_id = ''){

        //$category_id = 2 : là tính toán khsx theo VTC
        //$category_id = 5 : là tính toán khsx theo Phôi
        if ($category_id == Constant::MRP_CATEGORY_ID_VTC) {
            $Mrp_Sales_Monthly = new Mrp_Sales_Monthly();
            $data_sales = $Mrp_Sales_Monthly->get_all_of_month_current();
            $list_oder = '';
            foreach ($data_sales as $oder ) {
               
                $list_oder .= $oder['id'].',';
            }
            $list_oder_id = trim($list_oder, ",");
            $oder_list = explode(",",$list_oder_id);
            $data_oder = $Mrp_Sales_Monthly->get_forecast_of_next_three_month($oder_list);

            $total_qty_of_oder = [];
            $item_id = [];
            $item_id_bom = [];
        
            foreach ($data_oder as $row) {
                $item_id[$row['item_id']][$row['month']] = $row['total_qty'];
                //$item_id[] = $row['item_id'];
            }      
            //vòng này để lấy bom_id đi cùng vs item_id_bom
            $item_id_bom_id = [];
            foreach ($item_id as $item_id_bom => $total_number_khsx) {
               
                $item_id_bom_id[$item_id_bom] = $this->get_itemid_bom_ts($item_id_bom);
            }

            $array_map_view_bom = [];
            foreach ($item_id_bom_id as $key_item_id => $value_bom_id) {
               // $array_map_view_bom[$key_item_id] = $key_item_id; $array_map_view_bom[$key_item_id]
                $array_map_view_bom[$key_item_id] = $this->get_map_data_bom_item($key_item_id, $value_bom_id, $category_id = 2);
            }

            $result_total_all = 0;
            foreach ($array_map_view_bom as $key_item_id_array => $value_item_id_array) {

                foreach ($value_item_id_array as $key_index_item_id => $value_index_item_id) {
                
                    // $material_item_id = đây là id của sản phẩm vật tư trong vtc hiển thị. đc truyền vào
                   if ($value_index_item_id['material_id'] == $material_item_id) {
                        
                        foreach ($item_id as $result_item_pom =>  $result_value_item_pom) {
                            
                            if ($result_item_pom == $key_item_id_array) {
                               
                                foreach ($result_value_item_pom as $month_key => $value_month) {
                                    
                                    if ($month_key == $month) {     
                                       //$result_total_all += $value_month * $value_index_item_id['rate_of_qty'] * 1.05; 
                                        $result_total_all += $value_month * $value_index_item_id['rate_of_qty'] * ( $this->get_coefficient_item($value_index_item_id['item_id']) ? $this->get_coefficient_item($value_index_item_id['item_id']) : 0 ); 
                                    }
                                }
                            }
                        }
                   }
                }
            }
            return $result_total_all;
        } /*END IF CATAGORY = 2 :VTC*/

        if ($category_id == constant::MRP_CATEGORY_ID_PHOI) {
            $Mrp_Sales_Monthly = new Mrp_Sales_Monthly();
            $data_sales = $Mrp_Sales_Monthly->get_all_of_month_current();
            $list_oder = '';
            foreach ($data_sales as $oder ) {
               
                $list_oder .= $oder['id'].',';
            }
            $list_oder_id = trim($list_oder, ",");
            $oder_list = explode(",",$list_oder_id);
            $data_oder = $Mrp_Sales_Monthly->get_forecast_of_next_three_month($oder_list);

            $total_qty_of_oder = [];
            $item_id = [];
            $item_id_bom = [];
        
            foreach ($data_oder as $row) {
                $item_id[$row['item_id']][$row['month']] = $row['total_qty'];
                //$item_id[] = $row['item_id'];
            }      
            //vòng này để lấy bom_id đi cùng vs item_id_bom
            $item_id_bom_id = [];
            foreach ($item_id as $item_id_bom => $total_number_khsx) {
               
                $item_id_bom_id[$item_id_bom] = $this->get_itemid_bom_ts($item_id_bom);
            }

            $array_map_view_bom = [];
            foreach ($item_id_bom_id as $key_item_id => $value_bom_id) {
               // $array_map_view_bom[$key_item_id] = $key_item_id; $array_map_view_bom[$key_item_id]
                $array_map_view_bom[$key_item_id] = $this->get_map_data_bom_item($key_item_id, $value_bom_id, $category_id = 5);
            }

            $result_total_all = 0;
            foreach ($array_map_view_bom as $key_item_id_array => $value_item_id_array) {

                foreach ($value_item_id_array as $key_index_item_id => $value_index_item_id) {
                
                    // $material_item_id = đây là id của sản phẩm vật tư trong vtc hiển thị. đc truyền vào
                   if ($value_index_item_id['material_id'] == $material_item_id) {
                        
                        foreach ($item_id as $result_item_pom =>  $result_value_item_pom) {
                            
                            if ($result_item_pom == $key_item_id_array) {
                               
                                foreach ($result_value_item_pom as $month_key => $value_month) {
                                    
                                    if ($month_key == $month) {
                                        
                                        $result_total_all += $value_month * $value_index_item_id['rate_of_unit'] * 1.2;
                                        
                                    }
                                }
                            }
                        }
                   }
                }
            }
            return $result_total_all;
        } /*END CATEGORY = 5 PHOI*/
        
    }


     public function get_sum_inventory($month, $id)
    {
        $this->db->SELECT(" SUM(CASE WHEN trans_inventory > 0 THEN trans_inventory ELSE 0 END) AS 'TN'");
        $this->db->SELECT(" SUM(CASE WHEN trans_inventory < 0 THEN trans_inventory ELSE 0 END) AS 'TX'");
        $this->db->like('trans_date', $month);
        $this->db->where('trans_items', $id);
        $this->db->from('inventory');
        $query = $this->db->get();
        return $query->row();
    }
    protected function get_material_plan_value($material_id = '', $month = '', $type = '', $options = [])
    {

        $materials_plan_detail = ! empty($options['materials_plan_detail']) ? $options['materials_plan_detail'] : [];
        $order_materials = ! empty($options['order_materials']) ? $options['order_materials'] : [];
        $category_id = ! empty($options['category_id']) ? $options['category_id'] : [];
        $forecast_items = ! empty($options['forecast_items']) ? $options['forecast_items'] : [];
        $detail = [];
        if (! empty($materials_plan_detail['detail'])) {
            foreach ($materials_plan_detail['detail'] as $m_id => $m_plan_detail) {
                foreach ($m_plan_detail as $m_month => $m_detail) {
                    if ($m_id == $material_id && $month == $m_month) {
                        $detail = $m_detail;
                        break;
                    }
                }
            }
        }
        /*echo "<pre>";
        print_r($order_materials);
        echo "</pre>";*/

        switch ($type) {
            case 'qty_khsx':
                $current_month = date('Y-m');
                $order_materials = $options['order_materials'];
                
                $buffer_rate = ! empty($order_materials[$material_id]['buffer_rate']) ? (float) $order_materials[$material_id]['buffer_rate'] : 1;
                
                if ($month == $current_month && !empty($order_materials[$material_id])) {
                    if ($category_id == Constant::MRP_CATEGORY_ID_VTC) {
                        //return $this->get_number_khsx($order_materials[$material_id]['material_id'], $current_month);
                        return $this->get_number_khsx_current($order_materials[$material_id]['material_id'], $order_materials[$material_id]['bom_id'], $current_month);
                    }
                    if ($category_id == Constant::MRP_CATEGORY_ID_PHOI) {
                        return ! empty($detail[$type]) ? $detail[$type] : '0';
                    }
                    //return ($category_id == 3) ? $order_materials[$material_id]['calculated_by_unit'] : $order_materials[$material_id]['calculated_by_qty'] * $buffer_rate;
                }
                
                if (! empty($forecast_items[$month][$material_id])) {
                   // return ! empty($forecast_items[$month][$material_id]['calculated_by_qty']) ? $forecast_items[$month][$material_id]['calculated_by_qty'] * $buffer_rate *1.05 : $forecast_items[$month][$material_id]['calculated_by_unit']; 
                    //code này là code cũ. không giám xóa :( 
                    if ($category_id == Constant::MRP_CATEGORY_ID_VTC) {
                        return $this->get_list_oder_of_month($order_materials[$material_id]['material_id'], $month ,$category_id);
                    }
                    if ($category_id == Constant::MRP_CATEGORY_ID_PHOI) {
                             
                        if (! empty($detail[$type])) {
                            $result = $detail[$type];
                        }else{
                            $result = $this->get_list_oder_of_month($order_materials[$material_id]['material_id'], $month, $category_id);
                        }
                        return $result;
                    } 
                    
                }
                
                return ! empty($detail[$type]) ? $detail[$type] : '0';
                break;
            case 'qty_used_actual':
//                $records = $this->Inventory->get_history_trans([
//                    $material_id
//                ], [
//                    'start_date' => date("Y-m-01 00:00:00", strtotime($month)),
//                    'end_date' => date("Y-m-t 23:59:59", strtotime($month))
//                ]);
//                $inventory = 0;
//                if (! empty($records)) {
//                    foreach ($records as $record) {
//                        if ($record['trans_inventory'] < 0) {
//                            $inventory += $record['trans_inventory'];
//                        }
//                    }
//                    return abs($inventory);
//                }
//                return ! empty($detail[$type]) ? $detail[$type] : '0';
                $this->load->helper('format');
                $this->Stock_report = new \Models\Stock_report_model();
                $report_item = $this->Stock_report->get_used_actual_quantity($material_id, $month);
                return get_data($report_item, 'quantity', 0);
                break;
            case 'qty_inventory':
                // $month2 = substr($month,-2);
                // $month1 = substr($month,-2)-1;
                // $month1 = $month1<10?'0'.$month1:$month1;
                // $month1 = str_replace('-'.$month2,'-'.$month1,$month);
                // $inve = $this->get_sum_inventory($month1,$material_id);
                // $detail[$type] = ($inve->TN)+($inve->TX);
//                $records = $this->Inventory->get_history_trans([
//                    $material_id
//                ], [
//                    'start_date' => date("Y-m-01 00:00:00", strtotime($month)),
//                    'end_date' => date("Y-m-t 23:59:59", strtotime($month))
//                ]);
                // return ! empty($detail[$type]) ? $detail[$type] : 0;
                $this->load->helper('format');
                $this->Stock_report = new \Models\Stock_report_model();
                $report_item = $this->Stock_report->get_report_item($material_id, $month);
               
                return get_data($report_item, 'quantity', 0);
                break;
            case 'expire_time':
                $expire_time = '';
                $month2 = substr($month,-2);
                $month1 = substr($month,-2)-1;
                $month1 = $month1 < 10 ? '0'.$month1:$month1;
                $month1 = str_replace('-'.$month2,'-'.$month1,$month);

                $qty_inventory = ! empty($detail['qty_inventory']) ? $detail['qty_inventory'] :0;

                if(!isset($detail['qty_khsx'])){
                    $detail['qty_khsx'] = 0;
                }
                
                $qty_khsx = $detail['qty_khsx'];
               
                if ($qty_inventory <= $qty_khsx) {
                    if ($qty_inventory > 0) {
                        $expire_time = round(($qty_inventory * 30 / $qty_khsx) - 15 ,0);
                        
                        if ($expire_time > 0) {
                            $expire_time = $expire_time."/".$month2;
                        }else{
                            $expire_time = (30 + $expire_time)."/".($month2-1);
                        }
                    }

                    if ($qty_inventory <= 0) {
                        //$expire_time = $month2;
                    }
                }

                return $expire_time;
                break;
            case 'qty_booked':
                return ! empty($detail[$type]) ? $detail[$type] : '0';
                break;
            case 'qty_esitmate':
                
                return ! empty($detail[$type]) ? $detail[$type] : '0';
                break;
            case 'qty_actual_income':
                return ! empty($detail[$type]) ? ($detail[$type]) : '0';
                break;
            default:
                return ! empty($detail[$type]) ? $detail[$type] : '0';
                break;
        }
    }
    
}

