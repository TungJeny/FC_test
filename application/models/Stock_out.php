<?php
namespace Models;
class Stock_out extends Stock
{
    const STOCK_TYPE_DIRECT = 'direct';
    const STOCK_TYPE_ORDER = 'order';
    const STOCK_TYPE_PACKAGE = 'package';
    const VALUE_YES = 1;
    private $_license_plates = [
        '20C-00337',
        '20L-3262',
        '20M-0385',
        '20C-13406'
    ];

    public function get_modes() {
        $modes = array();
        $modes[self::STOCK_TYPE_DIRECT] = 'Trực tiếp';
        $modes[self::STOCK_TYPE_ORDER] = 'Xuất kho TP';
        $modes[self::STOCK_TYPE_PACKAGE] = 'Vật tư theo lô';
        return $modes;
    }

    public function get_license_plates() {
        return $this->_license_plates;
    }

    /**
     * @param $data
     * @return bool
     */
    public function save_stock_out_request($data)
    {
        $is_success = false;
        if (empty($data['items'])) {
            return $is_success;
        }
        $items = $data['items'];
        unset($data['items']);
        $this->load->library('stock_lib');
        $this->db->insert('stock', $data);
        $stock_id = $this->db->insert_id();
        if (!empty($stock_id)) {
            $this->db->trans_start();
            foreach ($items as $item) {
                $row = array(
                    'stock_id' => $stock_id,
                    'item_id' => $item['item_id'],
                    'line' => $item['line'],
                    'name' => trim($item['name']),
                    'product_id' => '',
                    'quantity' => $item['quantity'],
                    'quantity_received' => $item['quantity_received'],
                    'price' => $item['price'],
                    'default_cost_price' =>0,
                    'selling_price' => 0,
                    'note' => ''
                );
                $this->db->insert('stock_items', $row);
            }
            $this->db->trans_complete();
        }
        return $stock_id;
    }

    public function save_stock_out_package($data)
    {
        $is_success = false;
        if (empty($data['items'])) {
            return $is_success;
        }
        $items = $data['items'];
        $stock_out_by_type = !empty($data['stock_out_by_type']) ? $data['stock_out_by_type'] : null;
        $item_product = !empty($data['item_product']) ? $data['item_product'] : null;
        unset($data['items']);
        unset($data['stock_out_by_type']);
        unset($data['item_product']);
        $this->load->library('stock_lib');
        $this->db->insert('stock', $data);
        $stock_id = $this->db->insert_id();

        if (!empty($stock_id)) {
            $this->db->trans_start();
            foreach ($items as $item) {
                // Create Item
                $row = array(
                    'stock_id' => $stock_id,
                    'item_id' => $item['item_id'],
                    'line' => $item['line'],
                    'name' => trim($item['name']),
                    'product_id' => '',
                    'quantity' => $item['quantity'],
                    'quantity_received' => '',
                    'price' => $item['price'],
                    'default_cost_price' =>0,
                    'selling_price' => 0,
                    'note' => ''
                );
                $this->db->insert('stock_items', $row);
                // Update Inventory

                $this->update_qty_location_items(array(
                    'location_id' => $data['location_id'],
                    'item_id' => $item['item_id'],
                    'quantity' => $item['quantity']
                ));
                if (!empty($item['package_id'])) {
                    $row = array(
                        'item_id' => $item['item_id'],
                        'stock_id' => $stock_id,
                        'stock_out_by_id' => $item['package_id'],
                        'item_product' => $item_product,
                        'quantity' => $item['quantity'],
                        'note' => $item['note'],
                        'stock_out_by_type' => $stock_out_by_type
                    );
                    $this->db->insert('stock_out', $row);
                }
                if ($data['type'] == self::TYPE_OUT) {
                    $item['quantity'] *= -1;
                }
                $row = array(
                    'trans_items' => $item['item_id'],
                    'trans_user' => $data['employee_id'],
                    'trans_comment' => 'STOCK IN ' . $stock_id . ' ' . $data['comment'] . ($item['package_id'] ? $stock_out_by_type . 'id' . $item['package_id'] : ''),
                    'trans_inventory' => $item['quantity'],
                    'location_id' => $data['location_id']
                );
                $this->db->insert('inventory', $row);
            }
            $this->db->trans_complete();
        }
        return $stock_id;
    }

    /**
     * @param array $conditions
     * @return mixed
     */
    function get_stock_requests($conditions = array(), $limit = 100) {
        $this->db->select('stock.*,people.first_name,people.last_name,locations.name AS location,customers.code AS customer');
        $this->db->from('stock');
        if (!empty($conditions)) {
            $this->db->where($conditions);
        }
        $this->db->where('status', self::STATUS_PENDING);
        $this->db->where('type', self::TYPE_OUT);
        $this->db->join('people', 'stock.employee_id = people.person_id');
        $this->db->join('locations', 'stock.location_id = locations.location_id');
        $this->db->join('customers', 'stock.customer_id = customers.id');
        $this->db->limit($limit);
        $query = $this->db->get();
        if ($query) {
            $result = $query->result();
            $query->free_result();
            return $result;
        }
        return $query;
    }
}