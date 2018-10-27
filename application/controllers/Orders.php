<?php
use orders\factory\Producer;
use Models\Mrp\Mrp_Sales_Monthly;
use Models\Mrp\Mrp_Customer;

require_once ("Secure_area.php");
require_once (APPPATH . "controllers/orders/factory/Producer.php");

class Orders extends Secure_area
{
    const ITEM_PER_PAGE = 20;

    const ITEM_PAGE_RANGE = 3;

    /**
     *
     * @var array
     */
    public $rpp = array(
        5,
        15,
        20,
        25,
        30,
        35,
        40,
        45,
        50,
        55,
        60,
        65,
        70,
        75,
        80,
        90,
        100
    );

    function __construct()
    {
        parent::__construct('sales');

    }

    public function index($customer, $sale_for = '')
    {
        Producer::get_factory($customer)->view($sale_for);
    }

    public function save($customer)
    {
        $data_post = $this->input->post();
        Producer::get_factory($customer)->save($data_post);
    }

    public function update($customer, $sale_monthly_id)
    {
        Producer::get_factory($customer)->update($sale_monthly_id);
    }

    public function delete($customer, $sale_monthly_id)
    {
        Producer::get_factory($customer)->delete($sale_monthly_id);
    }

    public function upload($customer)
    {
        Producer::get_factory($customer)->upload($customer);
    }

    public function order_clone($customer, $sale_monthly_id)
    {
        Producer::get_factory($customer)->order_clone($sale_monthly_id);
    }
    public function manage()
    {
        $this->load->view('orders/manage');
    }

    public function load_view($customer, $sale_monthly_id)
    {
        Producer::get_factory($customer)->load_view($sale_monthly_id);
    }

    public function delete_ignore_type()
    {
        $sale_monthly_ids = json_decode($this->input->post('ids'), true);
        return (new Mrp_Sales_Monthly())->delete($sale_monthly_ids);
    }

    public function get_list()
    {

        $params = $this->input->get();
        $offset = 0;
        $limit = ! empty($params['per_page']) ? (int) $params['per_page'] : ($this->config->item('number_of_items_per_page') ? (int) $this->config->item('number_of_items_per_page') : 20);
        $page = ! empty($params['page']) ? (int) $params['page'] : '';
        if (! empty($page)) {
            $offset = $limit * ((int) $page - 1);
        }
        $orderBy = ! empty($params['order_by']) ? $params['order_by'] : '';
        $orderField = ! empty($params['order_field']) ? $params['order_field'] : '';
        
        $sales = (new Mrp_Sales_Monthly())->getAll([
            'limit' => $limit,
            'offset' => $offset,
            'order_by' => $orderBy,
            'order_field' => $orderField
        ]);

        $totalRow = (new Mrp_Sales_Monthly())->countAll();
        $sales = array_map(function ($record) {
            $record['employee'] = $record['first_name'] . ' ' . $record['last_name'];
            $customer = (new Mrp_Customer())->get_by_id($record['customer_id']);
            $record['customer'] = $customer['first_name'] . ' ' . $customer['last_name'];
            return $record;
        }, $sales);
        // echo "<pre>";
        // print_r($sales);
        // echo "</pre>";
        // die();
        echo json_encode([
            'type' => 'mrp_sales',
            'data' => [
                'list' => $sales,
                'pagination' => [
                    'total_row' => $totalRow,
                    'total_page' => ceil($totalRow / $limit),
                    'per_page' => $limit,
                    'current_page' => ! empty($params['page']) ? (int) $params['page'] : 1
                ]
            ]
        ]);
    }
}
?>
