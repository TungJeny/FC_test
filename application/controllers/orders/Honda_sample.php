<?php
namespace orders;

use Models\Mrp\Orders\Mrp_Order_Honda_Sample;
use orders\interfaces\IAction;
use Models\Constant;
use Models\Mrp\Mrp_Sales_Monthly;

class Honda_sample extends Order implements IAction
{

    private $honda_sample;

    public function __construct()
    {
        parent::__construct();
        $this->honda_sample = new Mrp_Order_Honda_Sample();
    }

    public function view($sale_for = '')
    {
        if (empty($order_for)) {
            $order_for = Constant::ORDER_TYPE_HONDA_NM12;
        }
        $_SESSION['order_for'] = $order_for;
        $this->CI->load->view('orders/honda_sample');
    }

    public function save($data_post)
    {
        $data['data_save'] = json_decode($data_post['data']);
        $data['month'] = $data_post['month'];
        
        $data['merge_cell'] = json_decode($data_post['merge_cell']);
        $data['sale_monthly_id'] = json_decode($data_post['sale_monthly_id']);
        if ($this->honda_sample->save($data)) {
            echo json_encode([
                'status' => 'success'
            ]);
            
            return;
        }
        echo json_encode([
            'status' => 'error',
            'msg' => 'Đã có lỗi xảy ra'
        ]);
    }

    public function update($sale_monthly_id)
    {
        $sales_monthly = new Mrp_Sales_Monthly();
        $monthly = $sales_monthly->get_by_id($sale_monthly_id);
        $data_view = $this->honda_sample->get_saved_view($sale_monthly_id);
        $data_view['order_for'] = $monthly['order_for'];
        $data_view['month'] = $monthly['month'];
        $data_view['is_clone'] = false;
        $this->CI->load->view('orders/honda_sample', $data_view);
    }

    public function delete($sale_monthly_id)
    {
        $this->honda_sample->delete($sale_monthly_id);
    }

    public function order_clone($sale_monthly_id)
    {
        $data_view = $this->honda_sample->get_saved_view($sale_monthly_id);
        $data_view['is_clone'] = true;
        $this->CI->load->view('orders/honda_sample', $data_view);
    }

    public function upload($data = [])
    {}
}
