<?php

/**
 * [type_unit description] hàm truyền 3 params
 * @param  [type] $type_unit     [type_unit is array : la mang du lieu ve quy cach]
 * @param  [type] $total_unit    [ tong so luong san pham]
 * @param  [type] $input_unit_id [input_unit_id la don vi dau tien cua san pham vs: kg]
 * @return [type]                [description]
 */
function type_unit($type_unit, $total_unit, $input_unit_id)
{
    $CI = &get_instance();
    if (!class_exists('Unit')) {
        $CI->load->model('Unit');
    }
    $total_quantity = $total_unit;
    $result_unit = '';
    foreach ($type_unit as $unit_id => $unit_number_change) {
        if (!empty($unit_number_change)) {
            if ($total_quantity % $unit_number_change == 0) {
                $result = floor($total_quantity / $unit_number_change);
                if ($result > 0) {
                    $result_unit .= floor($total_quantity / $unit_number_change) . " " . $CI->Unit->get_unit_name($unit_id);
                }
                //break;
                return $result_unit;
            } else {
                $result = floor($total_quantity / $unit_number_change);
                if ($result > 0) {
                    $total_quantity = $total_quantity - ($result * $unit_number_change) . " ";
                    $result_unit .= $result . " " . $CI->Unit->get_unit_name($unit_id) . " ";
                }
            }
        }
    }
    if (!empty($result_unit)) {
        $text = $result_unit . " - " . $total_quantity . " " . $CI->Unit->get_unit_name($input_unit_id);
    } else {
        $text = $total_quantity . " " . $CI->Unit->get_unit_name($input_unit_id);
    }
    return $text;
}

function get_unit_text($item_id, $quantity)
{
    $CI =& get_instance();
    $item = $CI->Item->get_info($item_id);
    
    if ($item->type_unit_formula) {
       $type_unit = json_decode($item->type_unit_formula); 
    }else{
        return 'Chưa có quy cách đơn vị tính';  //Unit is not calculated
    }

    if ($quantity == 0) {
        return 'Số Lượng chưa có';
    }

    $input_unit_id = $item->unit_id;
    return type_unit($type_unit, $quantity, $input_unit_id);
}