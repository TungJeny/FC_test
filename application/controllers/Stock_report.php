<?php

require_once("Secure_area.php");

class Stock_report extends Secure_area
{
    function __construct()
    {
        parent::__construct('stock_in');
        $this->load->library('stock_lib');
        $this->load->model('Receiver_location');
        $this->lang->load('stock_in');
        $this->lang->load('module');
        $this->load->helper('items');
        $this->load->helper('format');
        $this->Stock_report = new \Models\Stock_report_model();
    }

    /**
     * Index
     */
    function update()
    {
        $material_ids = $this->input->post('material_ids');
        $location_id = $this->Employee->get_logged_in_employee_current_location_id();
        $this->Stock_report->generate_report($material_ids, $location_id);
    }

    /**
     * @param null $selected_month
     * @param null $selected_year
     */
    public function view_by_month($selected_month = null, $selected_year = null) {
        $is_ajax = $this->input->is_ajax_request();
        // Get Logged Employee Account
        $person_info = $this->Employee->get_logged_in_employee_info();
        if (empty($selected_month)) {
            $selected_month = date('m') - 1;
            if ($selected_month <= 0) {
                $selected_month = 1;
            }
        }
        if (empty($selected_year)) {
            $selected_year = date('Y');
        }
        $data['title'] = 'Báo cáo nhập xuất tồn';
        $data['location_id'] = $this->Employee->get_logged_in_employee_current_location_id();
        $data['location'] = $this->Location->get_info($data['location_id']);
        $data['selected_month'] = sprintf('%02d', $selected_month);
        $data['selected_year'] = $selected_year;
        $data['months'] = range(1,12);
        $data['receiver_locations'] = $this->Receiver_location->get_collection();
        $data['items'] = $this->Stock_report->get_report_by_month($data['selected_year'] . '-' . $data['selected_month'], $data['location_id']);
        // Check Permission
        $data['items_module_allowed'] = $this->Employee->has_module_permission('items', $person_info->person_id);
        if ($is_ajax) {
            $this->load->view("stock_report/index/form", $data);
        } else {
            $this->load->view("stock_report/index", $data);
        }
    }

    /**
     * @param null $selected_month
     * @param null $selected_year
     */
    public function view_by_month_detail($selected_month = null, $selected_year = null) {
        // Get Logged Employee Account
        $person_info = $this->Employee->get_logged_in_employee_info();
        if (empty($selected_month)) {
            $selected_month = date('m') - 1;
            if ($selected_month <= 0) {
                $selected_month = 1;
            }
        }
        if (empty($selected_year)) {
            $selected_year = date('Y');
        }
        $this->load->model('Item');
        $data['item_id'] = $this->input->get('item_id');
        $data['title'] = 'Báo cáo nhập xuất tồn';
        $data['location_id'] = $this->Employee->get_logged_in_employee_current_location_id();
        $data['location'] = $this->Location->get_info($data['location_id']);
        $data['selected_month'] = sprintf('%02d', $selected_month);
        $data['selected_year'] = $selected_year;
        $data['months'] = range(1,12);
        $data['receiver_locations'] = $this->Receiver_location->get_collection();
        $etc_location = new stdClass();
        $etc_location->id = -1;
        $etc_location->name = 'Khác';
        $data['receiver_locations'][] = $etc_location;
        $data['item'] = $this->Stock_report->get_report_item_detail($data['item_id'], $data['selected_year'] . '-' . $data['selected_month'], $data['location_id']);
        $data['count_total_quantity'] = get_data($data['item'], 'quantity', 0);
        $data['dates'] = [];
        for ($date = 1; $date <= days_in_month($selected_month, $selected_year); $date++) {
            // Format Two Digits
            $date = sprintf("%02d", $date);
            $data['dates'][$selected_year . $selected_month . $date] = [];
            $data['dates'][$selected_year . $selected_month . $date]['date'] = $selected_year . '-' . $selected_month . '-' . $date;
            if (!empty($data['item']->stock_in)) {
                foreach ($data['item']->stock_in as $stock_in) {
                    if (get_data($stock_in, 'trans_date') == $selected_year . '-' . $selected_month . '-' . $date) {
                        $data['dates'][$selected_year . $selected_month . $date]['stock_in'] = $stock_in;
                    }
                }
            }
            if (!empty($data['item']->stock_out)) {
                foreach ($data['item']->stock_out as $stock_out) {
                    if (get_data($stock_out, 'trans_date') == $selected_year . '-' . $selected_month . '-' . $date) {
                        $data['dates'][$selected_year . $selected_month . $date]['stock_out'] = $stock_out;
                    }
                }
            }
            if (!empty($data['item']->stock_out_receiver_location)) {
                foreach ($data['item']->stock_out_receiver_location as $stock_out_receiver_location) {
                    $stock_out_receiver_location->received_day = date('Y-m-d', get_data($stock_out_receiver_location, 'received_at'));
                    if (get_data($stock_out_receiver_location, 'received_day') == $selected_year . '-' . $selected_month . '-' . $date) {
                        $data['dates'][$selected_year . $selected_month . $date]['stock_out_receiver_location'][get_data($stock_out_receiver_location, 'receiver_location_id')] = $stock_out_receiver_location;
                    }
                }
            }
            $date = intval($date);
        }
        // Check Permission
        $data['items_module_allowed'] = $this->Employee->has_module_permission('items', $person_info->person_id);
        $this->load->view("stock_report/index/form/detail", $data);
    }
}