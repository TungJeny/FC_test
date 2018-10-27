<?php

namespace Models;

class Stock_report_model extends \CI_Model
{
    protected $_location_ids = [1, 2];
    const REPORT_STOCK_TYPE_IN = 1;
    const REPORT_STOCK_TYPE_OUT = 2;
    const REPORT_STOCK_TYPE_IN_OUT = 3;

    /**
     * @param $item_ids
     * @param $location_id
     * @return $this
     */
    public function generate_report($item_ids, $location_id) {
        $this->load->helper('format');
        $this->db->trans_start();
        if (empty($this->_location_ids)) {
            $this->_location_ids[] = $location_id;
        }
        // Update Stock Report For Two Selected Locations
        if (!empty($this->_location_ids) && is_array($this->_location_ids)) {
            foreach ($this->_location_ids as $location_id) {
                // Get Inventory Items
                $this->db->select('trans_items, trans_inventory, DATE_FORMAT(trans_date, "%Y-%m") AS `trans_month`, SUM(trans_inventory) AS `trans_quantity`');
                $this->db->from('inventory');
                $this->db->where('location_id', $location_id);
                $this->db->where_in('trans_items', explode(',', $item_ids));
                $this->db->group_by('trans_items, trans_month');
                $this->db->order_by('trans_month DESC');
                $query = $this->db->get();
                $result = $query->result();
                $query->free_result();

                // Create Report
                $this->db->reset_query();
                $report = $this->get_report();
                if ($report) {
                    $this->db->where('id', get_data($report, 'id'));
                    $this->db->update('stock_report', ['updated_at' => time()]);
                } else {
                    $report = [
                        'name' => 'Report ' . date('d/m/Y'),
                        'created_at' => time(),
                        'updated_at' => time()
                    ];
                    $this->db->insert('stock_report', $report);
                    $report['id'] = $this->db->insert_id();
                }

                // Create Report Items
                $this->db->reset_query();
                $this->db->where_in('item_id', explode(',', $item_ids));
                $this->db->where('location_id', $location_id);
                $this->db->delete('stock_report_item');
                $item_rows = [];
                foreach ($result as $row) {
                    $item_rows[get_data($row, 'trans_items')][get_data($row, 'trans_month')] = intval(get_data($row, 'trans_quantity'));
                }
                foreach (range(1,12) as $month_in_year) {
                    $month_in_year = date('Y') . '-' . sprintf("%02d", $month_in_year);
                    foreach ($item_rows as $key => $item_row) {
                        if (!isset($item_row[$month_in_year])) {
                            $item_rows[$key][$month_in_year] = 0;
                        }
                    }
                }
                foreach ($item_rows as $key => $item_row) {
                    foreach ($item_row as $month => $quantity) {
                        $item_rows[$key][$month] = $this->_get_recursive_quantity($item_row, $month);
                    }
                }
                foreach ($item_rows as $key => $item_row) {
                    foreach ($item_row as $month => $quantity) {
                        $item = [
                            'report_id' => get_data($report, 'id'),
                            'item_id' => $key,
                            'month' => $month,
                            'quantity' => $quantity,
                            'location_id' => $location_id,
                            'sort_order' => strtotime($month . '-01')
                        ];
                        $this->db->insert('stock_report_item', $item);
                    }
                }
            }
        }
        $this->db->trans_complete();
        return $this;
    }

    /**
     * @param $rows
     * @param $month
     * @return int
     */
    protected function _get_recursive_quantity($rows, $month) {
        $total = 0;
        foreach ($rows as $key => $quantity) {
            $key = intval(str_replace('-', '', $key));
            $month = intval(str_replace('-', '', $month));
            if ($key < $month) {
                $total += $quantity;
            }
        }
        return $total;
    }

    /**
     * @param $item_ids
     * @return array
     */
    public function get_report_items($item_ids) {
        $this->load->helper('format');
        if (is_string($item_ids)) {
            $item_ids = explode(',', $item_ids);
        }
        $this->db->from('stock_report_item');
        $this->db->where_in('item_id', $item_ids);
        $this->db->order_by('month ASC');
        $query = $this->db->get();
        $result = $query->result();
        $rendered_collection = [];
        foreach ($result as $row) {
            $rendered_collection[get_data($row, 'item_id')][get_data($row, 'month')] = get_data($row, 'quantity');
        }
        $query->free_result();
        return $rendered_collection;
    }

    /**
     * @param $item_id
     * @param $month
     * @return mixed
     */
    public function get_report_item($item_id, $month) {
        $this->db->select('item_id, month, location_id, SUM(quantity) AS `quantity`');
        $this->db->from('stock_report_item');
        $this->db->where('item_id', $item_id);
        $this->db->where('sort_order <= ' . strtotime($month, '-01'));
        $this->db->where_in('location_id', $this->_location_ids);
        $this->db->order_by('sort_order DESC');
        $this->db->group_by('item_id, month');
        $this->db->limit(1);
        $query = $this->db->get();
        if (!$query) {
            return false;
        }
        $result = $query->row();
        $query->free_result();
        return $result;
    }

    /**
     * @return bool
     */
    public function get_report() {
        $this->db->from('stock_report');
        $this->db->order_by('created_at ASC');
        $this->db->limit(1);
        $query = $this->db->get();
        if (!$query) {
            return false;
        }
        $row = $query->row();
        $query->free_result();
        return $row;
    }

    /**
     * @param $item_id
     * @param $month
     * @return bool
     */
    public function get_used_actual_quantity($item_id, $month, $type = null) {
        $this->db->select('trans_items, SUM(trans_inventory) AS `quantity`');
        $this->db->from('inventory');
        $this->db->where('trans_items', $item_id);
        $this->db->like('trans_date', $month);
        $this->db->where_in('location_id', $this->_location_ids);
        if (!empty($type)) {
            switch ($type) {
                case self::REPORT_STOCK_TYPE_IN:
                    $this->db->where('trans_inventory > 0');
                    break;
                case self::REPORT_STOCK_TYPE_OUT:
                    $this->db->where('trans_inventory < 0');
                    break;
                case self::REPORT_STOCK_TYPE_IN_OUT:
                    break;
                default:
                    $this->db->where('trans_inventory < 0');
                    break;
            }
        } else {
            $this->db->where('trans_inventory < 0');
        }
        $this->db->group_by('trans_items');
        $query = $this->db->get();
        if (!$query) {
            return false;
        }
        $result = $query->row();
        $query->free_result();
        return $result;
    }

    /**
     * @param $month
     * @param $location_id
     * @return mixed
     */
    public function get_report_by_month($month, $location_id) {
        $this->db->trans_start();

        // Select Item IDS
        $this->db->select('items.item_id AS `item_id`');
        $this->db->from('items');
        $this->db->join('location_items', 'items.item_id = location_items.item_id');
        $this->db->where('location_id', $location_id);
        $query = $this->db->get();
        if (!$query) {
            return false;
        }
        $result = $query->result();
        $query->free_result();
        $item_ids = [];
        foreach ($result as $row) {
            $item_ids[] = get_data($row, 'item_id');
        }

        // Reset Report
        $this->_location_ids = [];
        $this->generate_report(implode(',', $item_ids), $location_id);


        // Get Stock Items
        $selected_fields = [
            'items.name AS `item_name`',
            'items.item_id AS `item_id`',
            'items_customers.person_id AS `person_id`',
            'units.name AS `unit_name`',
            'stock_report_item.quantity AS `quantity`',
            'cost_price',
            'limit'
        ];
        $this->db->reset_query();
        $this->db->select(implode(',', $selected_fields));
        $this->db->from('stock_report_item');
        $this->db->join('items', 'items.item_id = stock_report_item.item_id');
        $this->db->join('items_customers', 'items.item_id = items_customers.item_id', 'left');
        $this->db->join('units', 'items.unit_id = units.id');
        $this->db->where_in('stock_report_item.item_id', $item_ids);
        $this->db->where('stock_report_item.location_id', $location_id);
        $this->db->where('stock_report_item.month', $month);
        $this->db->where('stock_report_item.quantity > 0');
        $this->db->group_by('item_id');
        $query = $this->db->get();
        if (!$query) {
            return false;
        }
        $result = $query->result();
        $query->free_result();

        // Get Matching Products
        $this->db->reset_query();
        $this->db->select('GROUP_CONCAT(' . $this->db->dbprefix('items') . '.name) AS `name`, material_id');
        $this->db->from('mrp_items_boms_raw');
        $this->db->join('mrp_items_boms', 'mrp_items_boms.id = mrp_items_boms_raw.bom_id');
        $this->db->join('items', 'mrp_items_boms.item_id = items.item_id');
        $this->db->where_in('material_id', $item_ids);
        $this->db->group_by('material_id');
        $query = $this->db->get();
        $products = null;
        if ($query) {
            $products = $query->result();
            $query->free_result();
        }

        // Get Matching Purchase Items
        $this->db->reset_query();
        $this->db->select('item_id, GROUP_CONCAT(json_staged) AS `json_staged`, GROUP_CONCAT(receive_date) AS `receive_date`');
        $this->db->from('purchase_order_items');
        $this->db->join('purchase_orders', 'purchase_order_items.purchase_order_id = purchase_orders.id');
        $this->db->where_in('purchase_order_items.item_id', $item_ids);
        $this->db->group_by('item_id');
        $query = $this->db->get();
        $purchase_items = null;
        if ($query) {
            $purchase_items = $query->result();
            $query->free_result();
        }

        // Get Stock In Items
        $this->db->reset_query();
        $this->db->select('trans_items AS `item_id`, SUM(trans_inventory) AS `quantity`');
        $this->db->from('inventory');
        $this->db->where_in('trans_items', $item_ids);
        $this->db->like('trans_date', $month);
        $this->db->where('trans_inventory > 0');
        $this->db->group_by('trans_items');
        $query = $this->db->get();
        $stock_in_items = null;
        if ($query) {
            $stock_in_items = $query->result();
            $query->free_result();
        }

        // Get Stock Out Items
        $this->db->reset_query();
        $this->db->select('trans_items AS `item_id`, SUM(trans_inventory) AS `quantity`');
        $this->db->from('inventory');
        $this->db->where_in('trans_items', $item_ids);
        $this->db->like('trans_date', $month);
        $this->db->where('trans_inventory < 0');
        $this->db->group_by('trans_items');
        $query = $this->db->get();
        $stock_out_items = null;
        if ($query) {
            $stock_out_items = $query->result();
            $query->free_result();
        }

        // Get Summary Items
        $this->db->reset_query();
        $this->db->select('item_id, SUM(quantity) AS `quantity`');
        $this->db->from('location_items');
        $this->db->where_in('item_id', $item_ids);
        $this->db->where('location_id', $location_id);
        $this->db->group_by('item_id');
        $query = $this->db->get();
        $stock_summary_items = null;
        if ($query) {
            $stock_summary_items = $query->result();
            $query->free_result();
        }

        // Get Stock out By Receiver Location
        $this->db->reset_query();
        $this->db->select('item_id, receiver_location_id, SUM(' . $this->db->dbprefix('stock_items') . '.quantity) AS `quantity`');
        $this->db->from('stock_items');
        $this->db->join('stock', 'stock.stock_id = stock_items.stock_id');
        $this->db->where_in('item_id', $item_ids);
        $this->db->where('location_id', $location_id);
        $this->db->where('type', \Models\Stock::TYPE_OUT);
        $this->db->group_by('item_id, receiver_location_id');
        $query = $this->db->get();
        $stock_out_receiver_location_items = null;
        if ($query) {
            $stock_out_receiver_location_items = $query->result();
            $query->free_result();
        }

        // Get All Customers For Matching
        $this->load->model('Customer');
        $customers = $this->Customer->get_collection();

        // Render Collection
        foreach ($result as $key => $row) {
            $result[$key]->po_quantity = 0;
            if (!empty($customers)) {
                foreach ($customers as $customer) {
                    if (get_data($row, 'person_id') == get_data($customer, 'person_id')) {
                        $result[$key]->customer_name = get_data($customer, 'code');
                    }
                }
            }
            if (!empty($purchase_items)) {
                foreach ($purchase_items as $purchase_item) {
                    if (get_data($row, 'item_id') == get_data($purchase_item, 'item_id')) {
                        if (!empty($purchase_item->json_staged)) {
                            $json_stages = explode(']', $purchase_item->json_staged);
                            if (!empty($json_stages) && is_array($json_stages)) {
                                foreach ($json_stages as $json_stage) {
                                    $stages = json_decode($json_stage . ']');
                                    if (!empty($stages) && is_array($stages)) {
                                        foreach ($stages as $stage) {
                                            if (get_data($stage, 'month') == $month) {
                                                $result[$key]->po_quantity += get_data($stage, 'quantity');
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        $result[$key]->po_receive_date = get_data($purchase_item, 'receive_date');
                    }
                }
            }
            if (!empty($products)) {
                foreach ($products as $product) {
                    if (get_data($row, 'item_id') == get_data($product, 'material_id')) {
                        $result[$key]->product_name = get_data($product, 'name');
                    }
                }
            }
            if (!empty($stock_in_items)) {
                foreach ($stock_in_items as $stock_in_item) {
                    if (get_data($row, 'item_id') == get_data($stock_in_item, 'item_id')) {
                        $result[$key]->stock_in_quantity = intval(get_data($stock_in_item, 'quantity'));
                    }
                }
            }
            if (!empty($stock_out_items)) {
                foreach ($stock_out_items as $stock_out_item) {
                    if (get_data($row, 'item_id') == get_data($stock_out_item, 'item_id')) {
                        $result[$key]->stock_out_quantity = intval(get_data($stock_out_item, 'quantity'));
                    }
                }
            }
            if (!empty($stock_summary_items)) {
                foreach ($stock_summary_items as $stock_summary_item) {
                    if (get_data($row, 'item_id') == get_data($stock_summary_item, 'item_id')) {
                        $result[$key]->stock_summary_quantity = intval(get_data($stock_summary_item, 'quantity'));
                    }
                }
            }
            if (!empty($stock_out_receiver_location_items)) {
                foreach ($stock_out_receiver_location_items as $stock_out_receiver_location_item) {
                    if (get_data($row, 'item_id') == get_data($stock_out_receiver_location_item, 'item_id')) {
                        if (!isset($result[$key]->receiver_location_items)) {
                            $result[$key]->receiver_location_items = [];
                        }
                        $result[$key]->receiver_location_items[get_data($stock_out_receiver_location_item, 'receiver_location_id')] = $stock_out_receiver_location_item;
                    }
                }
            }
        }
        $this->db->trans_complete();
        return $result;
    }

    /**
     * @param $item_id
     * @param $month
     * @param $location_id
     * @return bool
     */
    public function get_report_item_detail($item_id, $month, $location_id) {
        // Get Stock Items
        $selected_fields = [
            'items.name AS `item_name`',
            'items.item_id AS `item_id`',
            'items.cost_price',
            'stock_report_item.quantity AS `quantity`'
        ];
        $this->db->select(implode(',', $selected_fields));
        $this->db->from('stock_report_item');
        $this->db->join('items', 'items.item_id = stock_report_item.item_id');
        $this->db->where('stock_report_item.location_id', $location_id);
        $this->db->where('items.item_id', $item_id);
        $this->db->where('stock_report_item.month', $month);
        $this->db->where('stock_report_item.quantity > 0');
        $this->db->group_by('item_id');
        $query = $this->db->get();
        if (!$query) {
            return false;
        }
        $result = $query->row();
        $query->free_result();

        // Get Matching Stock In
        $this->db->reset_query();
        $this->db->select('trans_items, trans_inventory, DATE_FORMAT(trans_date, "%Y-%m-%d") AS `trans_date`, SUM(trans_inventory) AS `trans_quantity`');
        $this->db->from('inventory');
        $this->db->where('location_id', $location_id);
        $this->db->where('trans_items', $item_id);
        $this->db->where('trans_inventory > 0');
        $this->db->group_by('trans_items, trans_date');
        $query = $this->db->get();
        if ($query) {
            $result->stock_in = $query->result();
            $query->free_result();
        }

        // Get Matching Stock Out
        $this->db->reset_query();
        $this->db->select('trans_items, trans_inventory, DATE_FORMAT(trans_date, "%Y-%m-%d") AS `trans_date`, SUM(trans_inventory) AS `trans_quantity`');
        $this->db->from('inventory');
        $this->db->where('location_id', $location_id);
        $this->db->where('trans_items', $item_id);
        $this->db->where('trans_inventory < 0');
        $this->db->group_by('trans_items, trans_date');
        $query = $this->db->get();
        if ($query) {
            $result->stock_out = $query->result();
            $query->free_result();
        }

        // Get Stock out By Receiver Location
        $this->db->reset_query();
        $this->db->select('receiver_location_id, received_at, SUM(' . $this->db->dbprefix('stock_items') . '.quantity) AS `quantity`');
        $this->db->from('stock_items');
        $this->db->join('stock', 'stock.stock_id = stock_items.stock_id');
        $this->db->where('item_id', $item_id);
        $this->db->where('location_id', $location_id);
        $this->db->where('type', \Models\Stock::TYPE_OUT);
        $this->db->group_by('item_id, receiver_location_id');
        $query = $this->db->get();
        if ($query) {
            $result->stock_out_receiver_location = $query->result();
            $query->free_result();
        }
        // var_dump($result);
        return $result;
    }
}