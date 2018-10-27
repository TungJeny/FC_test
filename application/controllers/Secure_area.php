<?php
use Models\Constant;

class Secure_area extends MY_Controller
{

    var $module_id;

    protected $logged_employee = null;

    /*
     * Controllers that are considered secure extend Secure_area, optionally a $module_id can
     * be set to also check if a user can access a particular module in the system.
     */
    function __construct($module_id = null)
    {
        parent::__construct();
        $this->module_id = $module_id;
        $this->load->model('Employee');
        $this->load->model('Location');
        $this->lang->load('module');
        $this->lang->load('common');
        $this->lang->load('attribute');
        
        if (! $this->Employee->is_logged_in()) {
            redirect('login?continue=' . rawurlencode(uri_string() . '?' . $_SERVER['QUERY_STRING']));
        }
        
        if ($this->module_id != 'mrp' && ! $this->Employee->has_module_permission($this->module_id, $this->Employee->get_logged_in_employee_info()->person_id)) {
            redirect('no_access/' . $this->module_id);
        }
        
        // load up global data
        $this->logged_employee = $logged_in_employee_info = $this->Employee->get_logged_in_employee_info();
        $data['allowed_modules'] = $this->Module->get_allowed_modules($logged_in_employee_info->person_id);
        /*
         * echo "<pre>";
         * print_r( $data['allowed_modules']->result() );
         * echo "</pre>";
         * die();
         */
        
        $data['user_info'] = $logged_in_employee_info;
        $data['new_message_count'] = $this->Employee->get_unread_messages_count();
        ;
        
        $all_locations_in_system = array();
        $locations_list = $this->Location->get_all();
        
        $authenticated_locations = $this->Employee->get_authenticated_location_ids($logged_in_employee_info->person_id);
        $locations = array();
        $total_locations_in_system = 0;
        foreach ($locations_list->result() as $row) {
            if (in_array($row->location_id, $authenticated_locations)) {
                $locations[$row->location_id] = $row->name;
            }
            $all_locations_in_system[$row->location_id] = $row->name;
            $total_locations_in_system ++;
        }
        
        $data['total_locations_in_system'] = $total_locations_in_system;
        $data['authenticated_locations'] = $locations;
        $data['all_locations_in_system'] = $all_locations_in_system;
        
        $location_id = $this->Employee->get_logged_in_employee_current_location_id();
        $loc_info = $this->Location->get_info($location_id);
        
        $data['current_logged_in_location_id'] = $location_id;
        $data['current_employee_location_info'] = $loc_info;
        $data['location_color'] = $loc_info->color;
        $data['controller_name'] = strtolower(get_called_class());
        
        $this->config->load('vue');
        $data['vue_modules'] = $this->config->item('vue_modules');
        
        $data['mrp_menus'] = $this->get_mrp_menus();
        
        // load items menu
        $data['item_menus'] = $this->get_items_menus();
        
        $data['const'] = Constant::categories();
        
        $data['order_menus'] = $this->get_orders_menus();
        $this->load->helper('update');
        $this->load->vars($data);
    }

    function check_action_permission($action_id)
    {
        if (! $this->Employee->has_module_action_permission($this->module_id, $action_id, $this->Employee->get_logged_in_employee_info()->person_id)) {
            redirect('no_access/' . $this->module_id);
        }
    }

    protected function get_items_menus()
    {
        return [
            [
                'label' => 'Hàng Hóa',
                'href' => '',
                'is_openning' => $this->check_menu_openning('items') || $this->check_menu_openning('stock_out') || $this->check_menu_openning('stock_in'),
                'childs' => [
                    [
                        'label' => 'Quản Lý Kho',
                        'href' => site_url() . 'items/index',
                        'childs' => [],
                        'is_openning' => $this->check_menu_openning('items') && $this->uri->segment(2) == 'index'
                    ],
                    [
                        'label' => 'Nhập Kho',
                        'href' => site_url() . 'stock_in/index',
                        'childs' => [],
                        'is_openning' => $this->check_menu_openning('stock_in', 'index')
                    ],
                    [
                        'label' => 'Xuất Kho',
                        'href' => site_url() . 'stock_out',
                        'is_openning' => $this->check_menu_openning('stock_out') && $this->uri->segment(1) == 'stock_out',
                        'childs' => []
                    ],
                    [
                        'label' => 'In Tem, Lô',
                        'href' => site_url() . 'stock_in/print_stamp',
                        'childs' => [],
                        'is_openning' => $this->check_menu_openning('stock_in', 'print_stamp')
                    
                    ]
                ]
            ]
        ];
    }
    protected function get_($value='')
    {
        # code...
    }
    protected function get_orders_menus()
    {
        return [
                    [
                        'label' => 'Đặt hàng',
                        'href' => '',
                        'is_openning' => $this->check_menu_openning('orders'),
                        'childs' => [
                            [
                                'label' => 'D/S đơn hàng',
                                'href' => site_url() . 'orders/manage',
                                'childs' => [],
                                'is_openning' => $this->check_menu_openning('orders') && $this->uri->segment(2) == 'manage'
                            ],
                            [
                                'label' => 'Yamaha',
                                'href' => site_url() . 'orders/index/yamaha',
                                'childs' => [],
                                'is_openning' => $this->check_menu_openning('orders') && $this->uri->segment(3) == 'yamaha'
                            ],
                            [
                                'label' => 'Honda',
                                'href' => '',
                                'childs' => [
                                    [
                                        'label' => 'Nhà máy 1, 2',
                                        'href' => site_url() . 'orders/index/honda',
                                        'is_openning' => $this->check_menu_openning('orders') && $this->uri->segment(4) == '',
                                        'childs' => [],
                                    ],
                                    [
                                        'label' => 'Nhà máy 3',
                                        'href' => site_url() . 'orders/index/honda/nm3',
                                        'is_openning' => $this->check_menu_openning('orders') && $this->uri->segment(4) == 'NM3',
                                        'childs' => []
                                    ],
                                    [
                                        'label' => 'Hàng SP',
                                        'href' => site_url() . 'orders/index/honda_sp/sp',
                                        'is_openning' => $this->check_menu_openning('orders') && $this->uri->segment(3) == 'honda_sp',
                                        'childs' => []
                                    ],
                                    [
                                        'label' => 'Hàng Mẫu',
                                        'href' => site_url() . 'orders/index/honda_sample/sample',
                                        'is_openning' => $this->check_menu_openning('orders') && $this->uri->segment(3) == 'honda_sample',
                                        'childs' => []
                                    ]
                                ],
                                'is_openning' => $this->check_menu_openning('orders') && $this->uri->segment(3) == 'honda',
                            ]
                        
                        ]
                    ]
        ];
    }

    protected function get_mrp_menus()
    {
        return [
            [
                'label' => 'Quản lý sản xuất',
                'href' => '',
                'is_openning' => $this->check_menu_openning('materials') || $this->check_menu_openning('purchase_orders') || $this->check_menu_openning('materials_plan') || $this->check_menu_openning('production') || $this->check_menu_openning('manage_consignment'),
                'childs' => [
                    // [
                    //     'label' => 'Kế hoạch sản xuất',
                    //     'href' => site_url() . 'production/planning',
                    //     'is_openning' => $this->check_menu_openning('production', 'planning'),
                    //     'childs' => []
                    // ],

                    [
                        'label' => 'Kế hoạch sản xuất',
                        'href' => '',
                        'childs' => [
                            [
                                'label' => 'KHSX Tự Động',
                                'href' => site_url() . 'production/planning',
                                'is_openning' => $this->check_menu_openning('production', 'planning'),
                                'childs' => [],
                                'controller' => ''
                            ], 
                            [
                                'label' => 'KHSX Import',
                                'href' => site_url() . 'production/import_planning',
                                'is_openning' => $this->check_menu_openning('production', 'import_planning'),
                                'childs' => [],
                                'controller' => ''
                            ]
                        ], 
                        'is_openning' => $this->check_menu_openning('production')
                    ],

                    [
                        'label' => 'Kế hoạch mua vật tư',
                        'href' => '',
                        'childs' => [
                            [
                                'label' => 'Vật liệu chính',
                                'href' => site_url() . 'materials_plan/vtc',
                                'is_openning' => $this->check_menu_openning('materials_plan', 'vtc'),
                                'childs' => [],
                                'controller' => ''
                            ],
                            [
                                'label' => 'Vật liệu phụ',
                                'href' => site_url() . 'materials_plan/vtp',
                                'is_openning' => $this->check_menu_openning('materials_plan', 'vtp'),
                                'childs' => []
                            ],
                            [
                                'label' => 'Phôi',
                                'href' => site_url() . 'materials_plan/phoi',
                                'is_openning' => $this->check_menu_openning('materials_plan', 'phoi'),
                                'childs' => []
                            ]
                        ],
                        'is_openning' => $this->check_menu_openning('materials_plan')
                    ],
                    [
                        'label' => 'PO Cho nhà cung cấp',
                        'href' => site_url() . 'purchase_orders',
                        'is_openning' => $this->check_menu_openning('purchase_orders'),
                        'childs' => []
                    ],
                    [
                        'label' => 'Định mức vật tư',
                        'href' => site_url() . 'materials/boms',
                        'is_openning' => $this->check_menu_openning('materials', 'boms'),
                        'childs' => []
                    ],
                    [
                        'label' => 'Quản lý lô',
                        'href' => '',
                        'childs' => [
                            [
                                'label' => 'Lô vật tư',
                                'href' => site_url() . 'manage_consignment/lvt',
                                'is_openning' => $this->check_menu_openning('manage_consignment', 'lvt'),
                                'childs' => []
                            ],
                            [
                                'label' => 'Lô TP',
                                'href' => site_url() . 'manage_consignment/ltp',
                                'is_openning' => $this->check_menu_openning('manage_consignment', 'ltp'),
                                'childs' => []
                            ]
                        ],
                        'is_openning' => $this->check_menu_openning('manage_consignment')
                    ]
                ]
            ]
        ];
    }

    protected function check_menu_openning($menu_code = '', $menu_action = NULL)
    {
        $menu_mapping = [
            'materials' => [
                'materials'
            ],
            'orders' => [
                'orders'
            ],
            'materials_plan' => [
                'materials_plan'
            ],
            'production' => [
                'production'
            ],
            'purchase_orders' => [
                'purchase_orders'
            ],
            'manage_consignment' => [
                'manage_consignment'
            ],
            'items' => [
                'items'
            ],
            'stock_in' => [
                'stock_in'
            ],
            'stock_out' => [
                'stock_out'
            ]
        ];
        
        if ($menu_action === NULL) {
            return in_array($this->uri->segment(1), $menu_mapping[$menu_code]);
        }
        return in_array($this->uri->segment(1), $menu_mapping[$menu_code]) && $this->uri->segment(2) == $menu_action;
    }
}
?>