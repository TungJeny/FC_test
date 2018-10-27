<?php
require_once ("Secure_area.php");
use Models\Stock_in;
class Stock_in_temp extends Secure_area
{
    protected $stock_in;
    public function __construct() 
    {
        parent::__construct();
        $this->stock_in = new Stock_in();
        $this->load->library('stock_lib');
    }
    function index()
    {
        $data['list_po'] = $this->stock_in->get_list_po();
        $data['list_supplier'] = $this->stock_in->get_suppliers();
        $data['list_package_code'] = $this->stock_in->get_list_package_code();
        $data['stock_temp_items'] = $this->stock_lib->get_stock_in_temp();
        $this->load->view('stock_in_temp/index', $data);
    }

    function search_item() 
    {
        $post = $this->input->post();
        $data['stock_temp_items'] = $this->stock_in->search_stock_temp_item($post);
        $this->stock_lib->set_stock_in_temp($data['stock_temp_items']);
        echo json_encode($this->load->view('stock_in_temp/search_item', $data, true));
    }
    
    function transfer_item()
    {
        $stock_temp_items = $this->stock_lib->get_stock_in_temp();
        if (empty($stock_temp_items)) {
            echo json_encode([
                'status' => 'error',
                'msg' => 'Không có thông tin sản phẩm'
                
            ]);
            return;
        }
        if ($this->stock_in->transfer_stock_in_temp($stock_temp_items)){
            $this->stock_lib->clear_stock_in_temp();
            echo json_encode([
                'status' => 'success',
                'msg' => 'Lưu thành công'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'msg' => 'Không thành công'
            ]);
        }
        
       
    }
}