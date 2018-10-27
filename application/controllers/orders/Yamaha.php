<?php
namespace orders;

use orders\interfaces\IAction;
use Models\Mrp\Orders\Mrp_Order_Yamaha;

class Yamaha extends Order implements IAction
{

    private $mrp_order_yamaha;

    public function __construct()
    {
        parent::__construct();
        $this->mrp_order_yamaha = new Mrp_Order_Yamaha();
    }

    public function view($sale_for = '')
    {
        $this->CI->load->view('orders/yamaha');
    }

    public function save($data_post)
    {
        $data['data_save'] = json_decode($data_post['data']);
        $data['month'] = $data_post['month'];
        $data['merge_cell'] = $data_post['merge_cell'];
        $data['end_row_body'] = $data_post['end_row_body'];
        $data['cell'] = $data_post['cell'];
        $data['sale_monthly_id'] = json_decode($data_post['sale_monthly_id']);
        if ($this->mrp_order_yamaha->save($data)) {
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
        $data_view = $this->mrp_order_yamaha->get_saved_view($sale_monthly_id);
        $data_view['is_clone'] = false;
        $this->CI->load->view('orders/yamaha', $data_view);
    }

    public function delete($sale_monthly_id)
    {
        $this->mrp_order_yamaha->delete($sale_monthly_id);
    }

    public function order_clone($sale_monthly_id)
    {
        $data_view = $this->mrp_order_yamaha->get_saved_view($sale_monthly_id);
        $data_view['is_clone'] = true;
        $this->CI->load->view('orders/yamaha', $data_view);
    }

    public function upload($data = [])
    {}
}