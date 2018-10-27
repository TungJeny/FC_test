

<?php
use Helpers\Date_Helper;
use Models\Constant;
use Models\Mrp\Mrp_Production_Planning;
use Models\Mrp\Mrp_Materials_Plans;
use Models\Mrp\Mrp_Item_Bom;
use Models\Mrp\Mrp_Sales_Items;


require_once ("Secure_area.php");

class Production extends Secure_area
{

    function __construct()
    {
        parent::__construct('mrp');
    }
    
    public function materials() {
        $month = date('Y-m');
        $planning_model = new Mrp_Production_Planning();
        $plans_model = new Mrp_Materials_Plans();
        $bom_model = new Mrp_Item_Bom();
        $planning_sub = $plans_model->get_summary_material_plan_sub($month,3);
        $current_planning = $planning_model->get_detail($month, 'detail');
        $order_items = (new Mrp_Sales_Items())->get_by_month($month); 

        $materials_raw = [];
        $item_id = array();
        foreach ($order_items as $item) {
            $materials_raw = array_merge($materials_raw, $bom_model->calculate_materials($item['item_id'], $item['qty'], 3));
            $item_id[$item['item_id']] = ($item['item_id']);
        }

        $bomid=[];
        foreach ($materials_raw as $k => $val) {
            $bomid[$val['bom_id']] = $val['bom_id'];
        }
        $data_bom_id = [];
        $data_bom_id = $bom_model->get_itemid_bom($bomid);
        echo json_encode([
            'type' => 'matrix',
            'data' => $planning_model->get_planning_materials(date('Y-m')),
            'planning_sub' => $planning_sub,
            'current_planning' => $current_planning,
            'data_bom_id' => $data_bom_id,
            'data_planning_detail' =>$planning_model->get_planning_detail_sub($month),
            'planning_sub_temp'=>($materials_raw),
            'item_id' => $bom_model->get_customer_item($item_id),
        ]);
    }
    public function materials_save()
    {
        $materials = json_decode($this->input->post('materials'), true);
        $materials_matrix = json_decode($this->input->post('materials_matrix'), true);  
        $factories = json_decode($this->input->post('factories'), true);
        $note = json_decode($this->input->post('note'), true);
        $type = json_decode($this->input->post('type'), true);
        $month = json_decode($this->input->post('month'), true);
        
        $planning_detail = [
            'month' => $month,
            'type' => $type,
            'detail' => json_encode($materials_matrix),
            'note' => $note,
            'employee_id' => $this->logged_employee->id,
            'deleted' => 0,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        $planning_model = new Mrp_Production_Planning();
        $planning_model->save($planning_detail);
        echo json_encode([
            'type' => 'matrix',
            'data' => []
        ]);
    }
    public function planning_detail_sub_save(){
        $month = json_decode($this->input->post('month'), true);
        $detail = json_decode($this->input->post('data_sub_comn'), true);
        $planning_detail=[
            'detail'=>json_encode($detail),
            'status'=>0,
            'month'=>$month,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        $planning_model = new Mrp_Production_Planning();        
        $planning_model->save_planning_detail_sub($planning_detail);
        echo json_encode([
            'data' => []
        ]);

    }
    public function planning()
    {
        $month = date('Y-m');
        $last_month = date('Y-m', strtotime('-1 month'));
        $est_months = Date_Helper::get_next_months($month);
        $factories = Constant::factories();
        $this->load->view('production/planning', [
            'month' => $month,
            'last_month' => $last_month,
            'est_months' => $est_months,
            'factories' => $factories
        ]);

    }

    public function import_planning($month = '')
    {
        if (! empty($month) ) {
            $month_excel = $month;
        }else{
            $month = date('Y-m');
        }

        //$last_month = date('Y-m', strtotime('-1 month'));
        //$est_months = Date_Helper::get_next_months($month);
        //$factories = Constant::factories();
        // $this->load->view('production/import_planning', [
        //     'month' => $month,
        //     'last_month' => $last_month,
        //     'est_months' => $est_months,
        //     'factories' => $factories
        // ]);
        $this->load->view('production/import_planning');   
    }

    public function upload_file_import($data = [])
    {
        $this->load->model('Product');
        $uploadDirectory = FCPATH . '/storage/';
        $type = 'false';
        $errors = [];
        $planning_model = new Mrp_Production_Planning();          
        ini_set('memory_limit', '1024M');
        if (! empty($_FILES['file_upload_khsx']) ) {
            $fileExtensions = [
                'xlsx'
            ];
                    
            $fileName = $_FILES['file_upload_khsx']['name'];
            $fileSize = $_FILES['file_upload_khsx']['size'];
            $fileTmpName = $_FILES['file_upload_khsx']['tmp_name'];
            $fileType = $_FILES['file_upload_khsx']['type'];
            $uploadPath = $uploadDirectory . basename($fileName);
            $fileParts = explode('.', $fileName);
            $fileExtension = end($fileParts);
            $fileExtension = strtolower($fileExtension);

            if (! in_array($fileExtension, $fileExtensions)) {
                $errors[] = "This file extension is not allowed. Please upload a JPEG or PNG file";
            }
            if ($fileSize > 20000000) {
                $errors[] = "This file is more than 20MB. Sorry, it has to be less than or equal to 20MB";
            }
            
            if (empty($errors)) {
                $didUpload = move_uploaded_file($fileTmpName, $uploadPath);
                if ($didUpload) {
                    require_once (APPPATH . 'libraries/ExcelProcessor.php');
                    $excel_processor = new \ExcelProcessor($fileName);
                    $this->set_excel_calculated_data($excel_processor->convert_2_array());
                    $this->update_import_planning();
                    //$this->generate($fields,$excel_processor,$fileName);  
                    $type = 'success';
                } else {
                    $errors[] = "An error occurred somewhere. Try again or contact the admin";
                }
            }
        }
    }
    
    public function set_excel_calculated_data($data = [])
    {
        $this->excel_data = $data;
        return $this;
    }

    public function update_import_planning()
    {
       //excel_data
        $start_num_row = NULL;
        $end_num_row = NULL;

        $i_col = 'A';
        do {
            $this->col_alphas[] = $i_col;
        } while ($i_col ++ != 'AAA');
        //$end_date = '';
        $num_row = count($this->excel_data[0]);
        $this->handle_table = array_fill(0, $num_row, []);

        for ($row_num = 6; $row_num < count($this->excel_data); $row_num ++) {
            
            for ($col_key = 0; $col_key < count($this->excel_data[1]); $col_key ++) {
                if (trim($this->excel_data[$row_num][$col_key]) == 'HÀNG CHI TIẾT XM') {
                   $start_num_row = $row_num +2;
                   //$this->get_excel_data_import( $start_num_row +1 );
                   break;
                }   
            }
            for ($col_key = 0; $col_key < count($this->excel_data[1]); $col_key ++){
                if (trim($this->excel_data[$row_num][$col_key]) == 'Hàng JOTO - Xuất khẩu') {
                   $end_num_row = $row_num +1;
                   //$this->get_excel_data_import( $start_num_row +1 );
                   break;
                }
            }
        }
        if (! isset($start_num_row) && ! isset($end_num_row) ) {
            $data['msg'] = 'Định dạng file excel không đúng quy chuẩn';
            $this->load->view('production/import_planning', $data);
        }
        $this->get_excel_data_import($start_num_row, $end_num_row);
    }

    
    /**
     * Get data to excel. Filter item data
     * @param  string $start_num_row [description]
     * @param  string $end_num_row   [description]
     * @return [type]                [description]
     */
    public function get_excel_data_import($start_num_row='', $end_num_row='')
    {

        // lấy số lượng
        $month_date_current = date('Y-m');
        $quantity_phoi = 5;

        $this->load->model('Item');
        //$data_item = $this->Item->get_all_name_item();
        $i_col = 'A';
        $msg = '';
        $data_excel_convert = array();

        do {
            $this->col_alphas[] = $i_col;
        } while ($i_col ++ != 'AAA');
        // $end_date = '';
        $num_row = count($this->excel_data[0]);
        $this->handle_table = array_fill(0, $num_row, []);
        for ($start_num_row; $start_num_row < $end_num_row; $start_num_row ++) {      
            //echo count($this->excel_data[$start_num_row]);    
            for ($col_key = 0; $col_key < count($this->excel_data[$start_num_row]); $col_key ++) {

                $data_excel_convert[$this->excel_data[$start_num_row][1]]['item_name'] = trim($this->excel_data[$start_num_row][1]);
                $data_excel_convert[$this->excel_data[$start_num_row][1]]['unit'] = trim($this->excel_data[$start_num_row][2]);
                //$data_excel_convert[$this->excel_data[$start_num_row][1]]['price_kh'] = ceil($this->excel_data[$start_num_row][4]);
                $data_excel_convert[$this->excel_data[$start_num_row][1]]['quantity_phoi'] = trim($this->excel_data[$start_num_row][$quantity_phoi]); 
                // $data_excel_convert[$this->excel_data[$start_num_row][1]]['quantity'] = trim($this->excel_data[$start_num_row][466]); 
            }   
        }
       
        $array_meger_excel = array();
        $not_exist_item = array();
        foreach ($data_excel_convert as $data_excel_array) {
            $item = $this->Item->get_item_by_product_id_or_name($data_excel_array['item_name']);

            if (empty($item) || empty($item['product_id'])) {
                    $status = 'error';          
                    $msg .= 'Không có thông tin sản phẩm ' . $data_excel_array['item_name'] . "<br>.\n";
            }
            if (! empty($item['product_id'])) {
                $array_meger_excel[$data_excel_array['item_name']]['item_id'] = $item['item_id'];
                $array_meger_excel[$data_excel_array['item_name']]['item_name'] = $data_excel_array['item_name'];
                $array_meger_excel[$data_excel_array['item_name']]['unit'] = $data_excel_array['unit'];
                /*$array_meger_excel[$data_excel_array['item_name']]['quantity'] = $data_excel_array['quantity'];*/
                //$array_meger_excel[$data_excel_array['item_name']]['price_kh'] = $data_excel_array['price_kh'];
                $array_meger_excel[$data_excel_array['item_name']]['quantity_phoi'] = $data_excel_array['quantity_phoi'];
            }    
        }

        $data['data'] = $array_meger_excel;
        $data['month'] = $month_date_current;
        $data['msg_error'] = $msg;
        // echo "<pre>";
        // print_r($data['data']);
        // echo "</pre>";

        $this->load->view('production/import_planning', $data); 
    }

    public function update_excel_khsx($sale_monthly_id)
    {
        $this->CI->load->view('orders/honda', [
            'sale_monthly_id' => $sale_monthly_id
        ]);
    }

    public function save_excel_khsx()
    {
        $data = $this->input->post();
        $array_insert = array();
        $month_date_current = date('Y-m');

        foreach ($data as $item) {
            $array_insert[] = array(
                'item_id' => $item["item_id"],
                'quantity_phoi' => $item["quantity_phoi"],
                'quantity_number' => $item["quantity"],
                'month' => $month_date_current
            ); 
        }
        $planning_model = new Mrp_Production_Planning();
        $planning_model->insert_data($array_insert, $month_date_current);

    }

}
?>
