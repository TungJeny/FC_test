<?php
namespace orders;

use orders\interfaces\IAction;
use Models\Constant;
use mrp_order_model\Mrp_Order_Honda;

class Honda implements IAction
{

    private $CI = NULL;

    private $mrp_order_honda;

    public function __construct()
    {
        $this->CI = &get_instance();
        $this->mrp_order_honda = new Mrp_Order_Honda();
    }

    public function upload($data = [])
    {
        $uploadDirectory = FCPATH . '/storage/';
        $type = 'false';
        $errors = [];
        if (! empty($_FILES['vfile'])) {
            $fileExtensions = [
                'xlsx'
            ];
            $fileName = $_FILES['vfile']['name'];
            $fileSize = $_FILES['vfile']['size'];
            $fileTmpName = $_FILES['vfile']['tmp_name'];
            $fileType = $_FILES['vfile']['type'];
            $uploadPath = $uploadDirectory . basename($fileName);
            $fileParts = explode('.', $fileName);
            $fileExtension = end($fileParts);
            $fileExtension = strtolower($fileExtension);
            if (! in_array($fileExtension, $fileExtensions)) {
                $errors[] = "This file extension is not allowed. Please upload a JPEG or PNG file";
            }
            if ($fileSize > 2000000) {
                $errors[] = "This file is more than 2MB. Sorry, it has to be less than or equal to 2MB";
            }
            if (empty($errors)) {
                $didUpload = move_uploaded_file($fileTmpName, $uploadPath);
                if ($didUpload) {
                    require_once (APPPATH . 'libraries/ExcelProcessor.php');
                    $excel_processor = new \ExcelProcessor($fileName);
                    $this->mrp_order_honda->set_excel_calculated_data($excel_processor->convert_2_array());
                    $this->mrp_order_honda->upload();
                    $type = 'success';
                } else {
                    $errors[] = "An error occurred somewhere. Try again or contact the admin";
                }
            }
        }
        
        echo json_encode([
            'type' => $type,
            'data' => [
                'messages' => $errors,
                'file_uploaded' => [
                    'name' => $fileName,
                    'file_size' => $fileSize
                ]
            ]
        ]);
    }

    public function save($data_post)
    {
        $data['data_save'] = json_decode($data_post['data']);
        $data['month'] = $data_post['month'];
        $data['merge_cell'] = $data_post['merge_cell'];
        $data['end_row_body'] = $data_post['end_row_body'];
        $data['sale_monthly_id'] = json_decode($data_post['sale_monthly_id']);
        if ($this->mrp_order_honda->save($data)) {
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

    public function view($order_for ='')
    {
        if (empty($order_for)) {
            $order_for = Constant::ORDER_TYPE_HONDA_NM12;
        }
        $_SESSION['order_for'] = $order_for;
        $this->CI->load->view('orders/honda');
    }

    public function delete($sale_monthly_id)
    {
        $this->mrp_order_honda->delete($sale_monthly_id);
    }

    public function update($sale_monthly_id)
    {
        $this->CI->load->view('orders/honda', [
            'sale_monthly_id' => $sale_monthly_id
        ]);
    }

    public function load_view($sale_monthly_id)
    {
        echo json_encode([
            'type' => 'order',
            'data' => $this->mrp_order_honda->get_saved_view($sale_monthly_id)
        ]);
    }

    public function order_clone($sale_monthly_id)
    {
        $this->CI->load->view('orders/honda', [
            'sale_monthly_id' => $sale_monthly_id,
            'is_clone' => true
        ]);
    }
}
