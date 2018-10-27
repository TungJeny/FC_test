<?php
require_once ("Secure_area.php");

class Departments extends Secure_area
{

    function __construct()
    {
        parent::__construct();
        $this->load->model('Department');
    }
    
    public function index()
    {
        $data= [];
        $this->load->view('departments/manage', $data);
    }
    
    public function getList()
    {
        $params = $this->input->get();
        $offset = 0;
        $limit = !empty($params['per_page']) ? (int) $params['per_page'] : ($this->config->item('number_of_items_per_page') ? (int) $this->config->item('number_of_items_per_page') : 20);
        $page = !empty($params['page']) ? (int) $params['page'] : '';
        if (!empty($page)) {
            $offset = $limit * ((int) $page - 1);
        }
        $query = !empty($params['q']) ? $params['q'] : '';
        $orderBy = !empty($params['order_by']) ? $params['order_by'] : '';
        $orderField = !empty($params['order_field']) ? $params['order_field'] : '';
        $departments = $this->Department->getAll([
            'limit' => $limit,
            'offset' => $offset,
            'query' => $query,
            'order_by' => $orderBy,
            'order_field' => $orderField,
        ]);
        $totalRow = $this->Department->countAll(['query' => $query]);
        echo json_encode([
            'type' => 'departments',
            'data' => [
                'list' => $departments,
                'pagination' => [
                    'total_row' => $totalRow,
                    'total_page' => ceil($totalRow/$limit),
                    'per_page' => $limit,
                    'current_page' => !empty($params['page']) ? (int) $params['page'] : 1
                ]
            ]
        ]);
    }
    
    public function save()
    {
        $department = json_decode($this->input->post('department'), true);
        if ($this->Department->save($department)) {
            echo json_encode([
                'type' => 'department',
                'data' => $this->Department->getByCode($department['code'])
            ]);
        } else {
            echo json_encode([
                'type' => 'error',
                'data' =>[] 
            ]);
        }
    }
    
    public function delete()
    {
        $ids = json_decode($this->input->post('ids'), true);
        $this->Department->delete($ids);
    }
    
    public function view($id = -1) {
        $data= [];
        $data['vueObjects'] = [
            'department' => $this->Department->get($id)
        ];
        $this->load->view('departments/view', $data);
    }
    
    public function abc($id = -1) {
        $data= [];
        
        $this->load->view('departments/abc', $data);
    }
    
    public function upload() {
        $uploadDirectory = FCPATH . '/storage/';
        $type =  'false';
        $errors = [];
        if (!empty($_FILES['vfile'])) {
            $fileExtensions = ['xlsx'];
            $fileName = $_FILES['vfile']['name'];
            $fileSize = $_FILES['vfile']['size'];
            $fileTmpName  = $_FILES['vfile']['tmp_name'];
            $fileType = $_FILES['vfile']['type'];
            
            $uploadPath = $uploadDirectory . basename($fileName); 
            
            $fileParts = explode('.',$fileName);
            $fileExtension = end($fileParts);
            
            $fileExtension = strtolower($fileExtension);
            if (! in_array($fileExtension,$fileExtensions)) {
                $errors[] = "This file extension is not allowed. Please upload a JPEG or PNG file";
            }
            
            if ($fileSize > 2000000) {
                $errors[] = "This file is more than 2MB. Sorry, it has to be less than or equal to 2MB";
            }
            if (empty($errors)) {
                $didUpload = move_uploaded_file($fileTmpName, $uploadPath);
                if ($didUpload) {
                    $this->load->model('mrp/orders/Mrp_Order_Honda');
                    require_once (APPPATH . 'libraries/ExcelProcessor.php');
                    $excel_processor = new ExcelProcessor('order_honda.xlsx');
                    $this->Mrp_Order_Honda->set_excel_data($excel_processor->convert_2_array());
                    $orders = $this->Mrp_Order_Honda->extract_orders_data();
                    $this->Mrp_Order_Honda->save_order($orders);
                    $type = 'success';
                } else {
                    $errors[] = "An error occurred somewhere. Try again or contact the admin";
                }
            }
        }
        
        echo json_encode([
            'type' => $type,
            'data' => ['messages' => $errors]
        ]);
    }
}
?>