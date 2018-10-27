<?php
require_once ("Secure_area.php");

class Attribute_groups extends Secure_area
{

    function __construct()
    {
        parent::__construct();
        $this->load->model('Attribute_group');
        $this->load->model('Attribute');
    }

    public function index()
    {
        $this->load->view('attribute_groups/manage');
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
        
        $attribute_groups = $this->Attribute_group->getAll([
            'limit' => $limit,
            'offset' => $offset,
            'query' => $query,
            'order_by' => $orderBy,
            'order_field' => $orderField
        ]);
        
        $totalRow = $this->Attribute_group->countAll();
        
        echo json_encode([
            'type' => 'attribute_groups',
            'data' => [
                'list' => $attribute_groups,
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
        $attribute_group = $this->Attribute_group->get($id);
        $related_object = empty($attribute_group['related_object']) ? [] : explode(',', $attribute_group['related_object']);
        $selected_attrs = $this->Attribute_group->get_related_attr($id);
        $attrs_without_selected = $this->Attribute->get_all_for_attr_gr_combine($selected_attrs);
        $data['vueObjects'] = [
            'attribute_group' => $attribute_group,
            'related_object' => $related_object,
            'all_related_obj' => $this->Attribute_group->get_all_related_obj(),
            'attrs_without_selected' => $attrs_without_selected,
            'selected_attrs' => $selected_attrs
        ];
        $this->load->view('attribute_groups/view', $data);
    }

    public function save()
    {
        $attribute_group = json_decode($this->input->post('attribute_group'), true);
        $selected_attr = json_decode($this->input->post('selected_attrs'), true);
        $attribute_group['related_object'] = implode(',', json_decode($this->input->post('related_object'), true));
        $attribute_group['code'] = create_slug($attribute_group['code']);
        if ($attr_group_id = $this->Attribute_group->save($attribute_group)) {
            $this->Attribute_group->save_attr_combine($attr_group_id, $selected_attr);
            echo json_encode([
                'type' => 'attribute_group',
                'data' => $this->Attribute_group->get_by_code($attribute_group['code'])
            ]);
        } else {
            echo json_encode([
                'type' => 'error',
                'data' => []
            ]);
        }
    }

    public function check_duplicate_code($attr_group_id = -1)
    {
        $new_code = create_slug($this->input->post('new_code'));
        $is_exists_attr_grp_code = $this->Attribute_group->exists_by_code($new_code);
        $old_code = $this->Attribute_group->get($attr_group_id)['code'];
        if ($is_exists_attr_grp_code && $new_code != $old_code) {
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
        $this->Attribute_group->delete($ids);
    }

    public function get_detail($group_id = 0)
    {
        echo json_encode([
            'type' => 'attributes',
            'data' => $this->Attribute_group->get_attributes_by_group($group_id)
        ]);
    }
}

