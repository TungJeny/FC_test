<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$config['vue_modules'] = [
    'departments' =>[
        'index' => ['list_view', 'autocomplete', 'upload_file']
    ],
    'attributes' =>[
        'index' => ['list_view']
    ],
    'attribute_groups' =>[
        'index' => ['list_view']
    ],
    'orders' =>[
        'index' => ['hot_table', 'autocomplete', 'upload_file'],
        'manage' => ['list_view']
    ], 
    'yamaha' => [
        'index' => ['hot_table', 'autocomplete']
    ],
    'materials' => [
        'bom' => ['list_view'],
        'view' => ['autocomplete']
    ],
    'items' => [
        'view' => ['list_view', 'autocomplete']
    ],
    'materials_plan' => [],
    'production' => [],
];
