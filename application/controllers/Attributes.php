<?php
require_once ("Secure_area.php");

class Attributes extends Secure_area
{

    function __construct()
    {
        parent::__construct();
        $this->load->model('Attribute');
    }

    public function index()
    {
        $this->load->view('attributes/manage');
    }

    public function getList()
    {
        $params = $this->input->get();
        $config_perpage = $this->config->item('number_of_items_per_page');
        $offset = 0;
        $limit = ! empty($params['per_page']) ? (int) $params['per_page'] : ($config_perpage ? (int) $config_perpage : 20);
        $page = ! empty($params['page']) ? (int) $params['page'] : '';
        if (! empty($page)) {
            $offset = $limit * ((int) $page - 1);
        }
        $query = ! empty($params['q']) ? $params['q'] : '';
        
        $orderBy = ! empty($params['order_by']) ? $params['order_by'] : '';
        $orderField = ! empty($params['order_field']) ? $params['order_field'] : '';
        
        $attributes = $this->Attribute->get_all([
            'limit' => $limit,
            'offset' => $offset,
            'query' => $query,
            'order_by' => $orderBy,
            'order_field' => $orderField
        ]);
        
        $totalRow = $this->Attribute->countAll([
            'query' => $query
        ]);
        
        echo json_encode([
            'type' => 'attributes',
            'data' => [
                'list' => $attributes,
                'pagination' => [
                    'total_row' => $totalRow,
                    'total_page' => ceil($totalRow / $limit),
                    'per_page' => $limit,
                    'current_page' => ! empty($params['page']) ? (int) $params['page'] : 1
                ]
            ]
        ]);
    }

    public function view($id = -1)
    {
        $data = [];
        $data['vueObjects'] = [
            'attribute' => $this->Attribute->get($id)
        ];
        $this->load->view('attributes/view', $data);
    }

    public function save()
    {
        $attribute = json_decode($this->input->post('attribute'), true);
        $attribute['code'] = create_slug($attribute['code']);
        if ($this->Attribute->save($attribute)) {
            echo json_encode([
                'type' => 'attribute',
                'data' => $this->Attribute->get_by_code($attribute['code'])
            ]);
        } else {
            echo json_encode([
                'type' => 'error',
                'data' => []
            ]);
        }
    }

    public function check_duplicate_code($attribute_id = -1)
    {
        $a= 1;
        $new_code = create_slug($this->input->post('new_code'));
        $is_exists_attr_code = $this->Attribute->exists_by_code($new_code);
        
        $old_code = $this->Attribute->get($attribute_id)['code'];
        if ($is_exists_attr_code && $new_code != $old_code) {
            echo json_encode([
                'valid' => false
            ]);
            return;
        }
        echo json_encode([
            'valid' => true
        ]);
    }

    public function delete()
    {
        $ids = json_decode($this->input->post('ids'), true);
        $this->Attribute->delete($ids);
    }
}

