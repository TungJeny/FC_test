<?php
use Models\Mrp\Mrp_Item_Bom;
use Models\Mrp\Mrp_Item_Semi;
use Models\Mrp\Mrp_Item_Bom_Raw;

require_once ("Secure_area.php");

class Materials extends Secure_area
{
    protected $Mrp_Item_Bom = null;
    
    protected $Mrp_Item_Semi = null;
    
    protected $Mrp_Item_Bom_Raw = null;
    
    function __construct()
    {
        parent::__construct('mrp');
        $this->Mrp_Item_Bom = new Mrp_Item_Bom();
        $this->load->model('Item');
        $this->Mrp_Item_Semi = new Mrp_Item_Semi();
        $this->Mrp_Item_Bom_Raw = new Mrp_Item_Bom_Raw();
    }
    
    public function index() {
        $this->load->view('materials/manage');
    }
    
    public function boms() {
        $this->load->view('materials/boms');
    }
    
    public function save() {
        $bom = json_decode($this->input->post('bom'), true);
        $materials = json_decode($this->input->post('materials'), true);
        $bom_id = 0;
        if ($bom_id = $this->Mrp_Item_Bom->save($bom)) {
            $item_semis = $this->Mrp_Item_Bom_Raw->prepare_bom_raw($bom_id, $materials);
            $this->Mrp_Item_Bom_Raw->save_bom_raws($bom_id, $item_semis);
            echo json_encode([
                'type' => 'mrp_item_bom',
                'data' => []
            ]);
        } else {
            echo json_encode([
                'type' => 'error',
                'data' =>[]
            ]);
        }
    }
    
    public function get_list_boms($item_id = 0)
    {
        $boms = $this->Mrp_Item_Bom->get_all_boms($item_id);
        echo json_encode([
            'type' => 'boms',
            'data' => [
                'list' => $boms,
                'pagination' => [
                    'total_row' => $this->Mrp_Item_Bom->count_all_boms($item_id),
                    'total_page' => 1,
                    'per_page' => 100000,
                    'current_page' => 1
                ]
            ]
        ]);
    }
    
    public function delete_bom($bom_id)
    {
        $oldBom = $this->Mrp_Item_Bom->get($bom_id);
        $this->Mrp_Item_Bom->delete($bom_id);
        $item_id = $oldBom['item_id'];
        $boms = $this->Mrp_Item_Bom->get_all_boms($item_id);
        echo json_encode([
            'type' => 'boms',
            'data' => [
                'list' => $boms,
                'pagination' => [
                    'total_row' => $this->Mrp_Item_Bom->count_all_boms($item_id),
                    'total_page' => 1,
                    'per_page' => 100000,
                    'current_page' => 1
                ]
            ]
        ]);
    }
    
    public function view($item_id = -1, $bom_id = -1) {
        $data= [];
        $bom_raw = $this->Mrp_Item_Bom->get_bom_raw($item_id, $bom_id);
        // var_dump($bom_raw);exit();
        $bom_raw_formatted = [];
        foreach ($bom_raw as $record) {
            $key = 'semi_' . (!empty($record['semi_id']) ? $record['semi_id'] : 'main');
            $bom_raw_formatted[$key][] = $record;
        }
        $data['vueObjects'] = [
            'item' => $this->Item->get_info($item_id),
            'bom' => $this->Mrp_Item_Bom->get($bom_id),
            'bom_raw' => $bom_raw_formatted,
            'semi_items' => $this->Mrp_Item_Semi->get_all_semis_of_item($item_id)
        ];
        
        $this->load->view('materials/view', $data);
    }
}
?>
