<?php

class stock_lib
{

    var $CI;

    // This is used when we need to change the recv state and restore it before changing it (The case of showing a receipt in the middle of a recv)
    var $recv_state;

    function __construct()
    {
        $this->CI = &get_instance();
    }

    function get_cart()
    {
        if ($this->CI->session->userdata('cartRecv') === NULL)
            $this->set_cart(array());

        return $this->CI->session->userdata('cartRecv');
    }

    function set_cart($cart_data)
    {
        $this->CI->session->set_userdata('cartRecv', $cart_data);
    }

    // Alain Multiple Payments
    function get_payments()
    {
        if ($this->CI->session->userdata('recv_payments') === NULL)
            $this->set_payments(array());

        return $this->CI->session->userdata('recv_payments');
    }

    // Alain Multiple Payments
    function set_payments($payments_data)
    {
        $this->CI->session->set_userdata('recv_payments', $payments_data);
    }

    function empty_payments()
    {
        $this->CI->session->unset_userdata('recv_payments');
    }

    function get_selected_payment()
    {
        if ($this->CI->session->userdata('recv_selected_payment') === NULL)
            $this->set_selected_payment('');

        return $this->CI->session->userdata('recv_selected_payment');
    }

    function set_selected_payment($payment)
    {
        $this->CI->session->set_userdata('recv_selected_payment', $payment);
    }

    function clear_selected_payment()
    {
        $this->CI->session->unset_userdata('recv_selected_payment');
    }

    function add_payment($payment_type, $payment_amount, $payment_date = false)
    {
        $payments = $this->get_payments();
        $payment = array(
            'payment_type' => $payment_type,
            'payment_amount' => $payment_amount,
            'payment_date' => $payment_date !== FALSE ? $payment_date : date('Y-m-d H:i:s')
        );

        $payments[] = $payment;
        $this->set_payments($payments);
        return true;
    }

    public function get_payment_ids($payment_type)
    {
        $payment_ids = array();

        $payments = $this->get_payments();

        for ($k = 0; $k < count($payments); $k++) {
            if ($payments[$k]['payment_type'] == $payment_type) {
                $payment_ids[] = $k;
            }
        }

        return $payment_ids;
    }

    public function get_payment_amount($payment_type)
    {
        $payment_amount = 0;
        if (($payment_ids = $this->get_payment_ids($payment_type)) !== FALSE) {
            $payments = $this->get_payments();

            foreach ($payment_ids as $payment_id) {
                $payment_amount += $payments[$payment_id]['payment_amount'];
            }
        }

        return $payment_amount;
    }

    function get_supplier()
    {
        if (!$this->CI->session->userdata('supplier'))
            $this->set_supplier(-1);

        return $this->CI->session->userdata('supplier');
    }

    function set_supplier($supplier_id)
    {
        if (is_numeric($supplier_id)) {
            $this->CI->session->set_userdata('supplier', $supplier_id);
        }
    }

    function get_receiver_location()
    {
        if (!$this->CI->session->userdata('receiver_location'))
            $this->set_receiver_location(-1);

        return $this->CI->session->userdata('receiver_location');
    }

    function set_receiver_location($location_id)
    {
        if (is_numeric($location_id)) {
            $this->CI->session->set_userdata('receiver_location', $location_id);
        }
    }

    function get_employee()
    {
        if (!$this->CI->session->userdata('employee'))
            $this->set_employee(-1);

        return $this->CI->session->userdata('employee');
    }

    function set_employee($employee_id)
    {
        if (is_numeric($employee_id)) {
            $this->CI->session->set_userdata('employee', $employee_id);
        }
    }

    function get_package()
    {
        if (!$this->CI->session->userdata('package'))
            $this->set_package(-1);

        return $this->CI->session->userdata('package');
    }

    function set_package($package_id)
    {
        if (is_numeric($package_id)) {
            $this->CI->session->set_userdata('package', $package_id);
        }
    }

    function get_po()
    {
        return $this->CI->session->userdata('is_po') ? $this->CI->session->userdata('is_po') : FALSE;
    }

    function set_po($value)
    {
        $this->CI->session->set_userdata('is_po', $value);
    }

    function get_location()
    {
        if (!$this->CI->session->userdata('location'))
            $this->set_location(-1);

        return $this->CI->session->userdata('location');
    }

    function set_location($location_id)
    {
        if (is_numeric($location_id)) {
            $this->CI->session->set_userdata('location', $location_id);
        }
    }

    function get_email_receipt()
    {
        return $this->CI->session->userdata('supplier_email_receipt');
    }

    function set_email_receipt($email_receipt)
    {
        $this->CI->session->set_userdata('supplier_email_receipt', $email_receipt);
    }

    function clear_email_receipt()
    {
        $this->CI->session->unset_userdata('supplier_email_receipt');
    }

    function get_mode()
    {
        if (!$this->CI->session->userdata('recv_mode'))
            $this->set_mode('receive');

        return $this->CI->session->userdata('recv_mode');
    }

    function get_items_in_cart()
    {
        $items_in_cart = 0;
        foreach ($this->get_cart() as $item) {
            $items_in_cart += $item['quantity'];
        }

        return $items_in_cart;
    }

    function set_mode($mode)
    {
        $this->CI->session->set_userdata('recv_mode', $mode);

        if ($mode == 'purchase_order') {
            $this->set_po(TRUE);
        } else {
            $this->set_po(FALSE);
        }
    }

    function set_comment($comment)
    {
        $this->CI->session->set_userdata('recv_comment', $comment);
    }

    function get_comment()
    {
        return $this->CI->session->userdata('recv_comment') ? $this->CI->session->userdata('recv_comment') : '';
    }

    function set_suspended_stock_in_id($suspended_stock_in_id)
    {
        $this->CI->session->set_userdata('suspended_recv_id', $suspended_stock_in_id);
    }

    function get_suspended_stock_in_id()
    {
        return $this->CI->session->userdata('suspended_recv_id');
    }

    function get_change_recv_id()
    {
        return $this->CI->session->userdata('change_recv_id');
    }

    function set_change_recv_id($change_recv_id)
    {
        $this->CI->session->set_userdata('change_recv_id', $change_recv_id);
    }

    function delete_change_recv_id()
    {
        $this->CI->session->unset_userdata('change_recv_id');
    }

    function get_deleted_taxes()
    {
        $recv_deleted_taxes = $this->CI->session->userdata('recv_deleted_taxes') ? $this->CI->session->userdata('recv_deleted_taxes') : array();
        return $recv_deleted_taxes;
    }

    function add_deleted_tax($name)
    {
        $recv_deleted_taxes = $this->CI->session->userdata('recv_deleted_taxes') ? $this->CI->session->userdata('recv_deleted_taxes') : array();

        if (!in_array($name, $recv_deleted_taxes)) {
            $recv_deleted_taxes[] = $name;
        }
        $this->CI->session->set_userdata('recv_deleted_taxes', $recv_deleted_taxes);
    }

    function set_deleted_taxes($recv_deleted_taxes)
    {
        $this->CI->session->set_userdata('recv_deleted_taxes', $recv_deleted_taxes);
    }

    function get_change_stock_in_date()
    {
        return $this->CI->session->userdata('change_stock_in_date') ? $this->CI->session->userdata('change_stock_in_date') : '';
    }

    function clear_change_stock_in_date()
    {
        $this->CI->session->unset_userdata('change_stock_in_date');
    }

    function clear_change_stock_in_date_enable()
    {
        $this->CI->session->unset_userdata('change_stock_in_date_enable');
    }

    function set_change_stock_in_date_enable($change_stock_in_date_enable)
    {
        $this->CI->session->set_userdata('change_stock_in_date_enable', $change_stock_in_date_enable);
    }

    function get_change_stock_in_date_enable()
    {
        return $this->CI->session->userdata('change_stock_in_date_enable') ? $this->CI->session->userdata('change_stock_in_date_enable') : '';
    }

    function set_change_stock_in_date($change_stock_in_date)
    {
        $this->CI->session->set_userdata('change_stock_in_date', $change_stock_in_date);
    }

    function clear_deleted_taxes()
    {
        $this->CI->session->unset_userdata('recv_deleted_taxes');
    }

    function add_paid_store_account_stock_in($stock_in_id)
    {
        $paid_store_account_stock_in = $this->get_paid_store_account_stock_in();
        $paid_store_account_stock_in[$stock_in_id] = TRUE;
        $this->CI->session->set_userdata('paid_store_account_stock_in', $paid_store_account_stock_in);
    }

    function get_paid_store_account_stock_in()
    {
        if ($this->CI->session->userdata('paid_store_account_stock_in') === NULL) {
            return array();
        }

        return $this->CI->session->userdata('paid_store_account_stock_in');
    }

    function remove_paid_store_account_stock_in($stock_in_id)
    {
        $paid_store_account_stock_in = $this->get_paid_store_account_stock_in();

        if (isset($paid_store_account_stock_in[$stock_in_id])) {
            unset($paid_store_account_stock_in[$stock_in_id]);
            $this->CI->session->set_userdata('paid_store_account_stock_in', $paid_store_account_stock_in);
            return true;
        }

        return false;
    }

    function clear_all_paid_store_account_stock_in()
    {
        $this->CI->session->unset_userdata('paid_store_account_stock_in');
    }

    function add_scale_item($scan)
    {
        $data = parse_scale_data($scan);
        return $this->add_item($data['item_id'], to_quantity($data['cost_quantity']), 0, 0, $data['cost_price']);
    }

    function select_item($stock_id, $item_id)
    {
        $selected_items = $this->get_selected_items($stock_id);
        $selected_items[$item_id] = $item_id;
        $this->CI->session->set_userdata('stock_in/selected_items/id/' . $stock_id, serialize($selected_items));
    }

    function unselect_item($stock_id, $item_id)
    {
        $selected_items = $this->get_selected_items($stock_id);
        unset($selected_items[$item_id]);
        $this->CI->session->set_userdata('stock_in/selected_items/id/' . $stock_id, serialize($selected_items));
    }

    function get_selected_items($stock_id)
    {
        return @unserialize($this->CI->session->userdata('stock_in/selected_items/id/' . $stock_id));
    }

    function add_item($item_id,
                      $quantity = 1,
                      $quantity_received = NULL,
                      $discount = 0,
                      $price = null,
                      $description = null,
                      $serialnumber = null,
                      $expire_date = null,
                      $force_add = FALSE,
                      $line = FALSE,
                      $note = 0,
                      $status = 0,
                      $overflow_quantity = 0,
                      $package_code = null,
                      $unit_id = 0,
                      $unit_name = 'Kg'
    )
    {
        // make sure item exists in database.
        if (!$force_add && !$this->CI->Item->exists(does_contain_only_digits($item_id) ? (int)$item_id : -1)) {
            // try to get item id given an item_number
            $item_id = $this->CI->Item->get_item_id($item_id);

            if (!$item_id)
                return false;
        }
        // Get items in the stock_in so far.
        $items = $this->get_cart();

        // We need to loop through all items in the cart.
        // If the item is already there, get it's key($updatekey).
        // We also need to get the next key that we are going to use in case we need to add the
        // item to the list. Since items can be deleted, we can't use a count. we use the highest key + 1.

        $maxkey = 0; // Highest key so far
        $itemalreadyinsale = FALSE; // We did not find the item yet.
        $insertkey = 0; // Key to use for new entry.
        $updatekey = 0; // Key to use to update(quantity)

        foreach ($items as $item) {
            // We primed the loop so maxkey is 0 the first time.
            // Also, we have stored the key in the element itself so we can compare.
            // There is an array function to get the associated key for an element, but I like it better
            // like that!

            if ($maxkey <= $item['line']) {
                $maxkey = $item['line'];
            }

            if ($item['item_id'] == $item_id) {
                $itemalreadyinsale = TRUE;
                $updatekey = $item['line'];
            }
        }

        $insertkey = $maxkey + 1;

        $cur_item_info = $this->CI->Item->get_info($item_id);
        $cur_item_location_info = $this->CI->Item_location->get_info($item_id);

        $default_cost_price = ($cur_item_location_info && $cur_item_location_info->cost_price) ? $cur_item_location_info->cost_price : $cur_item_info->cost_price;

        if ($expire_date === NULL && $cur_item_info->expire_days !== NULL) {
            $expire_date = date(get_date_format(), strtotime('+ ' . $cur_item_info->expire_days . ' days'));
        } elseif ($expire_date !== NULL) {
            $expire_date = date(get_date_format(), strtotime($expire_date));
        } else {
            $expire_date = NULL;
        }

        // array records are identified by $insertkey and item_id is just another field.
        $item = array(
            ($line === FALSE ? $insertkey : $line) => array(
                'item_id' => $item_id,
                'line' => $line === FALSE ? $insertkey : $line,
                'name' => $this->CI->Item->get_info($item_id)->name,
                'size' => $this->CI->Item->get_info($item_id)->size,
                'item_number' => $cur_item_info->item_number,
                'product_id' => $cur_item_info->product_id,
                'description' => $description != null ? $description : $this->CI->Item->get_info($item_id)->description,
                'serialnumber' => $serialnumber != null ? $serialnumber : '',
                'allow_alt_description' => $this->CI->Item->get_info($item_id)->allow_alt_description,
                'is_serialized' => $this->CI->Item->get_info($item_id)->is_serialized,
                'quantity' => $quantity,
                'quantity_received' => $quantity_received,
                'overflow_quantity' => $overflow_quantity,
                'status' => $status,
                'note' => $note,
                'discount' => $discount,
                'price' => $price != null ? $price : $default_cost_price,
                'default_cost_price' => $default_cost_price,
                'expire_date' => $expire_date,
                'cost_price_preview' => $this->calculate_average_cost_price_preview($item_id, $price != null ? $price : $default_cost_price, $quantity, $discount),
                'selling_price' => $cur_item_info->unit_price,
                'package_code' => $package_code,
                'unit_id' => $unit_id,
                'unit_name' => $unit_name
            )
        );

        // Item already exists
        if ($itemalreadyinsale && !$this->CI->config->item('do_not_group_same_items') && isset($items[$line === FALSE ? $updatekey : $line])) {
            $items[$line === FALSE ? $updatekey : $line]['quantity'] += $quantity;
            $items[$updatekey]['cost_price_preview'] = $this->calculate_average_cost_price_preview($item_id, $price != null ? $price : $default_cost_price, $quantity, $discount);
        } else {
            // add to existing array
            $items += $item;
        }
        
        $this->set_cart($items);
        return true;
    }

    function update_item($line, $description = NULL, $serialnumber = NULL, $expire_date = null, $quantity = NULL, $quantity_received = NULL, $discount = NULL, $price = NULL, $selling_price = NULL, $overflow_quantity = NULL, $note = NULL, $status = NULL)
    {
        $items = $this->get_cart();
        if (isset($items[$line])) {
            if ($description !== NULL) {
                $items[$line]['description'] = $description;
            }
            if ($serialnumber !== NULL) {
                $items[$line]['serialnumber'] = $serialnumber;
            }

            if ($expire_date !== NULL) {

                if ($expire_date == '') {
                    $items[$line]['expire_date'] = NULL;
                } else {
                    $items[$line]['expire_date'] = date(get_date_format(), strtotime($expire_date));
                }
            }

            if ($quantity_received !== NULL) {
                $items[$line]['quantity_received'] = $quantity_received;
            }

            if ($quantity !== NULL) {
                $items[$line]['quantity'] = $quantity;
            }
            if ($discount !== NULL) {
                $items[$line]['discount'] = $discount;
            }
            if ($price !== NULL) {
                $items[$line]['price'] = $price;
            }

            if ($selling_price !== NULL) {
                $items[$line]['selling_price'] = $selling_price;
            }

            if ($overflow_quantity !== NULL) {
                $items[$line]['overflow_quantity'] = $overflow_quantity;
            }

            if ($note !== NULL) {
                $items[$line]['note'] = $note;
            }

            if ($status !== NULL) {
                $items[$line]['status'] = $status;
            }
            $items[$line]['cost_price_preview'] = $this->calculate_average_cost_price_preview($items[$line]['item_id'], $items[$line]['price'], $items[$line]['quantity'], $items[$line]['discount']);

            $this->set_cart($items);

            return true;
        }

        return false;
    }

    function is_valid_receipt($receipt_stock_in_id)
    {
        // RECV #
        $pieces = explode(' ', $receipt_stock_in_id);

        if (count($pieces) == 2 && $pieces[0] == 'RECV') {
            return $this->CI->Receiving->exists($pieces[1]);
        }

        return false;
    }

    function is_valid_item_kit($item_kit_id)
    {
        // KIT #
        $pieces = explode(' ', $item_kit_id);

        if (count($pieces) == 2 && strtolower($pieces[0]) == 'kit') {
            return $this->CI->Item_kit->exists($pieces[1]);
        } else {
            return $this->CI->Item_kit->get_item_kit_id($item_kit_id) !== FALSE;
        }
    }

    function return_entire_stock_in($receipt_stock_in_id)
    {
        // POS #
        $pieces = explode(' ', $receipt_stock_in_id);
        $stock_in_id = $pieces[1];

        $this->empty_cart();
        $this->delete_supplier();

        $stock_in_taxes = $this->get_taxes($stock_in_id);

        foreach ($this->CI->Receiving->get_stock_in_items($stock_in_id)->result() as $row) {
            $item_info = $this->CI->Item->get_info($row->item_id);
            $price_to_use = $row->item_unit_price;

            // For return quantity_received needs to be NULL so the quantity gets updated correctly
            $this->add_item($row->item_id, -$row->quantity_purchased, NULL, $row->discount_percent, $price_to_use, $row->description, $row->serialnumber, $row->expire_date, TRUE, $row->line);
        }
        $recv_info = $this->CI->Receiving->get_info($stock_in_id)->row_array();
        $this->set_supplier($this->CI->Receiving->get_supplier($stock_in_id)->person_id);
        $this->set_location($recv_info['transfer_to_location_id']);

        if ($recv_info['transfer_to_location_id']) {
            $this->set_mode('transfer');
        }

        $this->set_deleted_taxes($this->CI->Receiving->get_deleted_taxes($stock_in_id));
    }

    function add_item_kit($external_item_kit_id_or_item_number)
    {
        if (strpos(strtolower($external_item_kit_id_or_item_number), 'kit') !== FALSE) {
            // KIT #
            $pieces = explode(' ', $external_item_kit_id_or_item_number);
            $item_kit_id = (int)$pieces[1];
        } else {
            $item_kit_id = $this->CI->Item_kit->get_item_kit_id($external_item_kit_id_or_item_number);
        }

        foreach ($this->CI->Item_kit_items->get_info($item_kit_id) as $item_kit_item) {
            $this->add_item($item_kit_item->item_id, $item_kit_item->quantity);
        }

        return TRUE;
    }

    function copy_entire_stock_in($stock_in_id, $is_receipt = false)
    {
        $this->empty_cart();
        $this->delete_supplier();
        $stock_in_taxes = $this->get_taxes($stock_in_id);

        foreach ($this->CI->Receiving->get_stock_in_items($stock_in_id)->result() as $row) {
            $item_info = $this->CI->Item->get_info($row->item_id);
            $price_to_use = $row->item_unit_price;
            $this->add_item($row->item_id, $row->quantity_purchased, $row->quantity_received, $row->discount_percent, $price_to_use, $row->description, $row->serialnumber, $row->expire_date, TRUE, $row->line);
        }

        foreach ($this->CI->Receiving->get_recv_payments($stock_in_id)->result() as $row) {
            $this->add_payment($row->payment_type, $row->payment_amount, $row->payment_date);
        }

        $this->set_supplier($this->CI->Receiving->get_supplier($stock_in_id)->person_id);

        $recv_info = $this->CI->Receiving->get_info($stock_in_id)->row_array();
        $this->set_comment($recv_info['comment']);
        $this->set_location($recv_info['transfer_to_location_id']);

        if ($recv_info['transfer_to_location_id']) {
            $this->set_mode('transfer');
        }
        $this->set_deleted_taxes($this->CI->Receiving->get_deleted_taxes($stock_in_id));
    }

    function delete_item($line)
    {
        $items = $this->get_cart();
        unset($items[$line]);
        $this->set_cart($items);
    }
    
    function delete_stock_out_package_item($line)
    {
        $items = $this->get_cart();
        unset($items[$line]);
        $this->set_cart($items);
    }
    function empty_cart()
    {
        $this->CI->session->unset_userdata('cartRecv');
    }

    function delete_supplier()
    {
        $this->CI->session->unset_userdata('supplier');
    }

    function delete_employee()
    {
        $this->CI->session->unset_userdata('employee');
    }

    function delete_location()
    {
        $this->CI->session->unset_userdata('location');
    }

    function delete_receiver_location()
    {
        $this->CI->session->unset_userdata('receiver_location');
    }

    function delete_package()
    {
        $this->CI->session->unset_userdata('package');
    }

    function clear_mode()
    {
        $this->CI->session->unset_userdata('recv_mode');
    }

    function delete_comment()
    {
        $this->CI->session->unset_userdata('recv_comment');
    }

    function delete_suspended_stock_in_id()
    {
        $this->CI->session->unset_userdata('suspended_recv_id');
    }

    function clear_po()
    {
        $this->CI->session->unset_userdata('is_po');
    }

    function clear_all()
    {
        $this->clear_mode();
        $this->reset_data();
    }

    function reset_data()
    {
        $this->empty_cart();
        $this->delete_supplier();
        $this->delete_employee();
        $this->delete_location();
        $this->delete_comment();
        $this->delete_suspended_stock_in_id();
        $this->clear_deleted_taxes();
        $this->clear_change_stock_in_date_enable();
        $this->clear_change_stock_in_date();
        $this->delete_change_recv_id();
        $this->clear_po();
        $this->clear_email_receipt();
        $this->empty_payments();
        $this->reset_stock_out_customer();
        $this->reset_stock_out_sale_daily();
        $this->reset_stock_out_port();
        $this->reset_stock_out_ports();
        $this->reset_stock_out_received_day();
        $this->set_stock_out_is_reset(true);
        $this->clear_selected_payment();
        $this->clear_all_paid_store_account_stock_in();
        $this->delete_receiver_location();
        $this->delete_package();
        $this->clear_qty_stock();
        $this->clear_qty_total();
        $this->clear_stock_type_id();
        $this->clear_qty_remain();
        $this->clear_stock_out_package_item_tp();
        $this->clear_po_code();
    }

    function save_current_recv_state()
    {
        $this->recv_state = $this->CI->session->all_userdata();
    }

    function restore_current_recv_state()
    {
        if (isset($this->recv_state)) {
            $this->CI->session->set_userdata($this->recv_state);
        }
    }

    function get_taxes($stock_in_id = false)
    {
        $taxes = array();

        if (!$this->CI->config->item('charge_tax_on_recv')) {
            return $taxes;
        }

        if ($stock_in_id) {
            $taxes_from_stock_in = $this->CI->Receiving->get_stock_in_items_taxes($stock_in_id);
            foreach ($taxes_from_stock_in as $key => $tax_item) {
                $name = $tax_item['percent'] . '% ' . $tax_item['name'];

                if ($tax_item['cumulative']) {
                    $prev_tax = ($tax_item['price'] * $tax_item['quantity'] - $tax_item['price'] * $tax_item['quantity'] * $tax_item['discount'] / 100) * (($taxes_from_stock_in[$key - 1]['percent']) / 100);
                    $tax_amount = (($tax_item['price'] * $tax_item['quantity'] - $tax_item['price'] * $tax_item['quantity'] * $tax_item['discount'] / 100) + $prev_tax) * (($tax_item['percent']) / 100);
                } else {
                    $tax_amount = ($tax_item['price'] * $tax_item['quantity'] - $tax_item['price'] * $tax_item['quantity'] * $tax_item['discount'] / 100) * (($tax_item['percent']) / 100);
                }

                if (!isset($taxes[$name])) {
                    $taxes[$name] = 0;
                }
                $taxes[$name] += $tax_amount;
            }
        } else {

            foreach ($this->get_cart() as $line => $item) {
                $price_to_use = $this->_get_price_for_item_in_cart($item);

                $tax_info = $this->CI->Item_taxes_finder->get_info($item['item_id'], 'stock_in');
                foreach ($tax_info as $key => $tax) {
                    $name = $tax['percent'] . '% ' . $tax['name'];

                    if ($tax['cumulative']) {
                        $prev_tax = ($price_to_use * $item['quantity'] - $price_to_use * $item['quantity'] * $item['discount'] / 100) * (($tax_info[$key - 1]['percent']) / 100);
                        $tax_amount = (($price_to_use * $item['quantity'] - $price_to_use * $item['quantity'] * $item['discount'] / 100) + $prev_tax) * (($tax['percent']) / 100);
                    } else {
                        $tax_amount = ($price_to_use * $item['quantity'] - $price_to_use * $item['quantity'] * $item['discount'] / 100) * (($tax['percent']) / 100);
                    }

                    if (!in_array($name, $this->get_deleted_taxes())) {
                        if (!isset($taxes[$name])) {
                            $taxes[$name] = 0;
                        }

                        $taxes[$name] += $tax_amount;
                    }
                }
            }
        }
        return $taxes;
    }

    function get_total_quantity()
    {
        $cart_count = 0;
        foreach ($this->get_cart() as $line => $item) {
            $cart_count = $cart_count + $item['quantity'];
        }

        return $cart_count;
    }

    function get_item_subtotal($line)
    {
        $cart = $this->get_cart();
        $item = $cart[$line];
        $price_to_use = $this->_get_price_for_item_in_cart($item, FALSE);
        $subtotal = to_currency_no_money($price_to_use * $item['quantity'] - $price_to_use * $item['quantity'] * $item['discount'] / 100);

        return to_currency_no_money($subtotal);
    }

    function get_subtotal($stock_in_id = FALSE)
    {
        $subtotal = 0;
        foreach ($this->get_cart() as $item) {
            $price_to_use = $this->_get_price_for_item_in_cart($item, $stock_in_id);
            $subtotal += to_currency_no_money($price_to_use * $item['quantity'] - $price_to_use * $item['quantity'] * $item['discount'] / 100);
        }

        return to_currency_no_money($subtotal);
    }

    function get_item_total($line)
    {
        $cart = $this->get_cart();
        $item = $cart[$line];

        $price_to_use = $this->_get_price_for_item_in_cart($item, FALSE);
        $total = to_currency_no_money($price_to_use * $item['quantity'] - $price_to_use * $item['quantity'] * $item['discount'] / 100);

        $tax_info = $this->CI->Item_taxes_finder->get_info($item['item_id'], 'stock_in');
        foreach ($tax_info as $key => $tax) {
            if ($tax['cumulative']) {
                $prev_tax = ($price_to_use * $item['quantity'] - $price_to_use * $item['quantity'] * $item['discount'] / 100) * (($tax_info[$key - 1]['percent']) / 100);
                $tax_amount = (($price_to_use * $item['quantity'] - $price_to_use * $item['quantity'] * $item['discount'] / 100) + $prev_tax) * (($tax['percent']) / 100);
            } else {
                $tax_amount = ($price_to_use * $item['quantity'] - $price_to_use * $item['quantity'] * $item['discount'] / 100) * (($tax['percent']) / 100);
            }

            $name = $tax['percent'] . '% ' . $tax['name'];

            if (!in_array($name, $this->get_deleted_taxes())) {
                $total += $tax_amount;
            }
        }

        return to_currency_no_money($total);
    }

    function get_item_profit($line, $item_cost_price)
    {
        $cart = $this->get_cart();
        $item = $cart[$line];
        $price_to_use = $this->_get_price_for_item_in_cart($item, FALSE);
        $profit = to_currency_no_money(($price_to_use * $item['quantity'] - $price_to_use * $item['quantity'] * $item['discount'] / 100) - ($item_cost_price * $item['quantity']));

        return to_currency_no_money($profit);
    }

    function get_total($stock_in_id = false)
    {
        $total = 0;
        foreach ($this->get_cart() as $item) {
            $price_to_use = $this->_get_price_for_item_in_cart($item, $stock_in_id);
            $total += to_currency_no_money($price_to_use * $item['quantity'] - $price_to_use * $item['quantity'] * $item['discount'] / 100);
        }

        foreach ($this->get_taxes($stock_in_id) as $tax) {
            $total += $tax;
        }

        return to_currency_no_money($total);
    }

    function _get_price_for_item_in_cart($item, $stock_in_id = FALSE)
    {
        $price_to_use = $item['price'];
        return $price_to_use;
    }

    function calculate_average_cost_price_preview($item_id, $price, $additional_quantity, $discount_percent)
    {
        if ($this->CI->config->item('calculate_average_cost_price_from_stock_in')) {
            $this->CI->load->model('Receiving');
            return $this->CI->Receiving->calculate_cost_price_preview($item_id, $price, $additional_quantity, $discount_percent);
        }
        return false;
    }

    function get_amount_due($recv_id = false)
    {
        $amount_due = 0;
        $payment_total = $this->get_payments_totals();
        $sales_total = $this->get_total($recv_id);
        $amount_due = to_currency_no_money($sales_total - $payment_total);
        return $amount_due;
    }

    function get_payments_totals()
    {
        $subtotal = 0;
        foreach ($this->get_payments() as $payments) {
            $subtotal += $payments['payment_amount'];
        }

        return to_currency_no_money($subtotal);
    }

    function delete_payment($payment_ids)
    {
        $payments = $this->get_payments();
        if (is_array($payment_ids)) {
            foreach ($payment_ids as $payment_id) {
                unset($payments[$payment_id]);
            }
        } else {
            unset($payments[$payment_ids]);
        }
        $this->set_payments(array_values($payments));
    }

    function get_stock_type()
    {
        if ($this->CI->session->userdata('stockType') === NULL)
            $this->set_stock_type(array());

        return $this->CI->session->userdata('stockType');
    }

    function set_stock_type($type)
    {
        $this->CI->session->set_userdata('stockType', $type);
    }

    function reset_stock_type()
    {
        $this->CI->session->set_userdata('stockType', 'free_style');
    }

    function get_stock_out_type()
    {
        if ($this->CI->session->userdata('stock_out/type') === NULL)
            $this->set_stock_out_type(array());

        return $this->CI->session->userdata('stock_out/type');
    }

    function set_stock_out_type($type)
    {
        $this->CI->session->set_userdata('stock_out/type', $type);
    }

    function reset_stock_out_type()
    {
        $this->CI->session->set_userdata('stock_out/type', 'direct');
    }

    function get_stock_out_license_plate()
    {
        if ($this->CI->session->userdata('stock_out/license_plate') === NULL)
            $this->set_stock_out_license_plate(array());

        return $this->CI->session->userdata('stock_out/license_plate');
    }

    function set_stock_out_license_plate($type)
    {
        $this->CI->session->set_userdata('stock_out/license_plate', $type);
    }

    function reset_stock_out_license_plate()
    {
        $this->CI->session->set_userdata('stock_out/license_plate', 'direct');
    }

    function get_stock_out_customer()
    {
        if ($this->CI->session->userdata('stock_out/customer') === NULL)
            $this->set_stock_out_customer(array());

        return $this->CI->session->userdata('stock_out/customer');
    }

    function set_stock_out_customer($customer)
    {
        $this->CI->session->set_userdata('stock_out/customer', $customer);
    }

    function reset_stock_out_customer()
    {
        $this->CI->load->model('Customer');
        $collection = $this->CI->Customer->get_collection();
        $customer = '';
        if (!empty($collection)) {
            $default_customer = array_shift($collection);
            $customer = $default_customer->code;
        }
        $this->CI->session->set_userdata('stock_out/customer', $customer);
    }

    function get_stock_out_sale_daily()
    {
        if ($this->CI->session->userdata('stock_out/sale_daily') === NULL) {
            $this->reset_stock_out_sale_daily();
        }
        return $this->CI->session->userdata('stock_out/sale_daily');
    }

    function set_stock_out_sale_daily($sale_daily)
    {
        $this->CI->session->set_userdata('stock_out/sale_daily', $sale_daily);
    }

    function get_stock_out_is_reset()
    {
        if ($this->CI->session->userdata('stock_out/is_reset') === NULL) {
            $this->reset_stock_out_is_reset();
        }
        return $this->CI->session->userdata('stock_out/is_reset');
    }

    function set_stock_out_is_reset($is_reset)
    {
        $this->CI->session->set_userdata('stock_out/is_reset', $is_reset);
    }

    function reset_stock_out_sale_daily()
    {
        $this->CI->session->set_userdata('stock_out/sale_daily', '');
    }

    function get_stock_out_port()
    {
        if ($this->CI->session->userdata('stock_out/port') === NULL) {
            $this->reset_stock_out_port();
        }
        return $this->CI->session->userdata('stock_out/port');
    }

    function set_stock_out_port($port)
    {
        $this->CI->session->set_userdata('stock_out/port', $port);
    }

    function reset_stock_out_port()
    {
        $this->CI->session->set_userdata('stock_out/port', '');
    }

    function get_stock_out_received_day()
    {
        if ($this->CI->session->userdata('stock_out/received_day') === NULL) {
            $this->reset_stock_out_received_day();
        }
        return $this->CI->session->userdata('stock_out/received_day');
    }

    function set_stock_out_received_day($received_day)
    {
        $this->CI->session->set_userdata('stock_out/received_day', $received_day);
    }

    function reset_stock_out_received_day()
    {
        $this->CI->session->set_userdata('stock_out/received_day', '');
    }

    function get_stock_out_ports()
    {
        if ($this->CI->session->userdata('stock_out/ports') === NULL) {
            $this->reset_stock_out_ports();
        }
        $ports = $this->CI->session->userdata('stock_out/ports');
        if (!empty($ports)) {
            $ports = unserialize($ports);
        } else {
            $ports = [];
        }
        return $ports;
    }

    function select_stock_out_port($port)
    {
        $ports = $this->get_stock_out_ports();
        $ports[$port] = $port;
        $this->CI->session->set_userdata('stock_out/ports', serialize($ports));
    }

    function unselect_stock_out_port($port)
    {
        $ports = $this->get_stock_out_ports();
        unset($ports[$port]);
        $this->CI->session->set_userdata('stock_out/ports', serialize($ports));
    }

    function reset_stock_out_ports()
    {
        $this->CI->session->set_userdata('stock_out/ports', '');
    }

    function get_stock_type_id()
    {
        if ($this->CI->session->userdata('stockTypeId') === NULL)
            $this->set_stock_type_id(array());

        return $this->CI->session->userdata('stockTypeId');
    }

    function set_stock_type_id($typeId)
    {
        $this->CI->session->set_userdata('stockTypeId', $typeId);
    }

    function clear_stock_type_id()
    {
        $this->CI->session->unset_userdata('stockTypeId');
    }

    function get_qty_stock()
    {
        if ($this->CI->session->userdata('qtyStock') === NULL)
            $this->set_qty_stock([]);

        return $this->CI->session->userdata('qtyStock');
    }

    function set_qty_stock($qtyStock)
    {
        $this->CI->session->set_userdata('qtyStock', $qtyStock);
    }

    function clear_qty_stock()
    {
        $this->CI->session->unset_userdata('qtyStock');
    }

    function get_qty_remain()
    {
        if ($this->CI->session->userdata('qtyRemain') === NULL)
            $this->set_qty_remain([]);

        return $this->CI->session->userdata('qtyRemain');
    }

    function set_qty_remain($qtyRemain)
    {
        $this->CI->session->set_userdata('qtyRemain', $qtyRemain);
    }

    function clear_qty_remain()
    {
        $this->CI->session->unset_userdata('qtyRemain');
    }

    function get_qty_total()
    {
        if ($this->CI->session->userdata('qtyTotal') === NULL)
            $this->set_qty_total(0);

        return $this->CI->session->userdata('qtyTotal');
    }

    function set_qty_total($qtyRemain)
    {
        $this->CI->session->set_userdata('qtyTotal', $qtyRemain);
    }

    function clear_qty_total()
    {
        $this->CI->session->unset_userdata('qtyTotal');
    }

    function get_stock_in_temp()
    {
        if ($this->CI->session->userdata('stock_in_temp') === NULL)
            $this->set_stock_in_temp([]);

        return $this->CI->session->userdata('stock_in_temp');
    }

    function set_stock_in_temp($stock_in_temp)
    {
        $this->CI->session->set_userdata('stock_in_temp', $stock_in_temp);
    }

    function clear_stock_in_temp()
    {
        $this->CI->session->unset_userdata('stock_in_temp');
    }
    function get_stock_out_package_item_tp()
    {
        if ($this->CI->session->userdata('stock_out_package_item_tp') === NULL)
            $this->set_stock_out_package_item_tp(-1);
            
            return $this->CI->session->userdata('stock_out_package_item_tp');
    }
    
    function set_stock_out_package_item_tp($stock_out_package_item_tp)
    {
        $this->CI->session->set_userdata('stock_out_package_item_tp', $stock_out_package_item_tp);
    }
    
    function clear_stock_out_package_item_tp()
    {
        $this->CI->session->unset_userdata('stock_out_package_item_tp');
    }
    
    function get_po_code()
    {
        if ($this->CI->session->userdata('stock_po_code') === NULL)
            $this->set_po_code('');

        return $this->CI->session->userdata('stock_po_code');
    }

    function set_po_code($stock_po_code)
    {
        $this->CI->session->set_userdata('stock_po_code', $stock_po_code);
    }

    function clear_po_code()
    {
        $this->CI->session->unset_userdata('stock_po_code');
    }

    function add_item_stock_out_package($item_id,
        $package_id = -1,
        $package_code = '',
        $total_quantity = 0,
        $total_stock_qty = 0,
        $unit_name = null,
        $line = FALSE,
        $note = ''
        )
    {
       
        $items = $this->get_cart();
        
        // We need to loop through all items in the cart.
        // If the item is already there, get it's key($updatekey).
        // We also need to get the next key that we are going to use in case we need to add the
        // item to the list. Since items can be deleted, we can't use a count. we use the highest key + 1.
        
        $maxkey = 0; // Highest key so far
        $itemalreadyinsale = FALSE; // We did not find the item yet.
        $insertkey = 0; // Key to use for new entry.
        $updatekey = 0; // Key to use to update(quantity)
        
        foreach ($items as $item) {
            // We primed the loop so maxkey is 0 the first time.
            // Also, we have stored the key in the element itself so we can compare.
            // There is an array function to get the associated key for an element, but I like it better
            // like that!
            
            if ($maxkey <= $item['line']) {
                $maxkey = $item['line'];
            }
            
            if ($item['item_id'] == $item_id) {
                $itemalreadyinsale = TRUE;
                $updatekey = $item['line'];
            }
        }
        
        $insertkey = $maxkey + 1;
        $cur_item_info = $this->CI->Item->get_info($item_id);
        $cur_item_location_info = $this->CI->Item_location->get_info($item_id);
        
        $default_cost_price = ($cur_item_location_info && $cur_item_location_info->cost_price) ? $cur_item_location_info->cost_price : $cur_item_info->cost_price;
        
        // array records are identified by $insertkey and item_id is just another field.
        $item = array(
            ($line === FALSE ? $insertkey : $line) => array(
                'item_id' => $item_id,
                'line' => $line === FALSE ? $insertkey : $line,
                'name' => $this->CI->Item->get_info($item_id)->name,
                'quantity' => $total_quantity - $total_stock_qty,
                'total_quantity' => $total_quantity,
                'total_stock_qty' => $total_stock_qty,
                'note' => $note,
                'package_id' => $package_id,
                'package_code' =>$package_code,
                'unit_name' => $unit_name,
                'price' =>$default_cost_price,
                'discount' => 0
            
            )
        );
        $items += $item;
        $this->set_cart($items);
        return true;
    }
    
    function update_item_stock_out_package(
        $line = FALSE,
        $quantity = NULL,
        $note = NULL)
    {
        $items = $this->get_cart();
        if (isset($items[$line])) {
            if ($quantity !== NULL) {
                $items[$line]['quantity'] = $quantity;
            }
            if ($note !== NULL) {
                $items[$line]['note'] = $note;
            }
            $this->set_cart($items);
            
            return true;
        }
        
        return false;
    }
    
}