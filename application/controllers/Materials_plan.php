<?php
use Models\Mrp\Mrp_Materials_Plans;
use Helpers\Date_Helper;
use Models\Mrp\Mrp_Item;
require_once ("Secure_area.php");

class Materials_plan extends Secure_area
{

    function __construct()
    {
        parent::__construct('mrp');
        $this->load->model('Item_location');
        $this->load->model('Inventory');
    }

    public function vtc()
    {
        $month = date('Y-m');
        $last_month = date('Y-m', strtotime('-1 month'));
        $est_months = Date_Helper::get_next_months($month);
        $this->load->view('materials_plan/vtc', [
            'month' => $month,
            'last_month' => $last_month,
            'est_months' => $est_months
        ]);
    }

    public function matrix_summary($category_id = '')
    {
        $plan_model = new Mrp_Materials_Plans();
        echo json_encode([
            'type' => 'matrix',
            'data' => $plan_model->get_summary_material_plan(date('Y-m'), $category_id)
        ]);
    }

    public function matrix_detail($category_id = '')
    {
        $plan_model = new Mrp_Materials_Plans();
        echo json_encode([
            'type' => 'matrix',
            'data' => $plan_model->get_material_plan(date('Y-m'), $category_id)
        ]);
    }
    
    
    public function save()
    {
        $materials_matrix = json_decode($this->input->post('materials_matrix'), true);
        $months = json_decode($this->input->post('months'), true);
        $note = json_decode($this->input->post('note'), true);
        $type = json_decode($this->input->post('type'), true);
        $category_id = $this->input->post('category_id');
        $detail = $materials_matrix;
       
        $plan_detail = [
            'month' => date('Y-m'),
            'category_id' => $category_id,
            'type' => $type,
            'detail' => json_encode($detail),
            'note' => $note,
            'deleted'=>0,
            'employee_id' => $this->logged_employee->id,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $plan_model = new Mrp_Materials_Plans();
        $plan_model->save($plan_detail);
        echo json_encode([
            'type' => 'matrix',
            'data' => $plan_detail
        ]);
    }
    public function loadsummary()
    {
        $materials = json_decode($this->input->post('materials'), true);
        $materials_matrix = json_decode($this->input->post('materials_matrix'), true);
        $months = json_decode($this->input->post('months'), true);
        $note = json_decode($this->input->post('note'), true);
        $type = json_decode($this->input->post('type'), true);
        $category_id = $this->input->post('category_id');
        $detail = $type!='summary'?$materials_matrix:$materials;
        $plan_detail = [
            'month' => date('Y-m'),
            'category_id' => $category_id,
            'type' => $type,
            'detail' => json_encode($detail),
            'note' => $note,
            'deleted'=>0,
            'employee_id' => $this->logged_employee->id,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        $plan_model = new Mrp_Materials_Plans();
        $plan_model->save($plan_detail,1);
        echo json_encode([
            'type' => 'matrix',
            'data' => $plan_detail
        ]);
    }
    public function vtp()
    {
        $month = date('Y-m');
        $last_month = date('Y-m', strtotime('-1 month'));
        $est_months = Date_Helper::get_next_months($month);
        $this->load->view('materials_plan/vtp', [
            'month' => $month,
            'last_month' => $last_month,
            'est_months' => $est_months
        ]);
    }

    public function phoi()
    {
        $month = date('Y-m');
        $last_month = date('Y-m', strtotime('-1 month'));
        $est_months = Date_Helper::get_next_months($month);
        $this->load->view('materials_plan/phoi', [
            'month' => $month,
            'last_month' => $last_month,
            'est_months' => $est_months
        ]);
    }
    
    public function detail($material_id = 0)
    {
        $mrp_item_model = new Mrp_Item();
        $material = $mrp_item_model->get_plan_detail($material_id, date('Y-m'));
        echo json_encode([
            'type' => 'material',
            'data' => $material
        ]);
    }
}
?>
