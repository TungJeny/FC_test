<?php

function get_price_rules_manage_table($price_rules, $controller)
{
    $CI = & get_instance();
    $table = '<table class="tablesorter table  table-hover" id="sortable_table">';
    $controller_name = strtolower(get_class($CI));
    
    $headers = array(
        '<input type="checkbox" id="select_all" /><label for="select_all"><span></span></label>',
        lang('price_rules_id'),
        lang('price_rules_name'),
        lang('price_rules_start_date'),
        lang('price_rules_end_date'),
        lang('price_rules_type'),
        lang('price_rules_status'),
        // '&nbsp;',
        '&nbsp;'
    );
    
    $table .= '<thead><tr>';
    
    $count = 0;
    foreach ($headers as $header) {
        $count ++;
        
        if ($count == 1) {
            $table .= "<th class='leftmost'>$header</th>";
        } elseif ($count == count($headers)) {
            $table .= "<th class='rightmost'>$header</th>";
        } else {
            $table .= "<th>$header</th>";
        }
    }
    $table .= '</tr></thead><tbody>';
    $table .= get_price_rules_manage_table_data_rows($price_rules, $controller_name);
    $table .= '</tbody></table>';
    return $table;
}

function get_price_rules_manage_table_data_rows($priceRules, $controller_name)
{
    $CI = & get_instance();
    $table_data_rows = '';
    
    foreach ($priceRules->result() as $rule) {
        $table_data_rows .= get_price_rule_data_row($rule, $controller_name);
    }
    
    if ($priceRules->num_rows() == 0) {
        $table_data_rows .= "<tr><td  colspan='8'><span class='col-md-12 text-center text-warning' >" . lang('price_rules_no_rule') . "</span></td></tr>";
    }
    
    return $table_data_rows;
}

function get_price_rule_data_row($rule, $controller_name)
{
    $CI = & get_instance();
    $controller_name = strtolower(get_class($CI));
    
    $table_data_row = '<tr>';
    $table_data_row .= "<td><input type='checkbox' id='pricerule_" . $rule->id . "' value='" . $rule->id . "'/><label for='pricerule_" . $rule->id . "'><span></span></label></td>";
    $table_data_row .= '<td>' . $rule->id . '</td>';
    $table_data_row .= '<td>' . H($rule->name) . '</td>';
    $table_data_row .= '<td>' . date(get_date_format(), strtotime($rule->start_date)) . '</td>';
    $table_data_row .= '<td>' . date(get_date_format(), strtotime($rule->end_date)) . '</td>';
    if ($rule->type) {
        $table_data_row .= '<td>' . lang($rule->type) . '</td>';
    } else {
        $table_data_row .= '<td>' . lang('common_none') . '</td>';
    }
    $table_data_row .= '<td>' . ($rule->active == 0 ? lang('common_inactive') : lang('common_active')) . '</td>';
    // $table_data_row.='<td class="rightmost">'.anchor($controller_name."/rule_details/$rule->id", lang('price_rules_view_rule'),array('class'=>' ','title'=>lang('common_clone'))).'</td>';
    $table_data_row .= '<td class="rightmost">' . anchor($controller_name . "/view/$rule->id	", lang('common_edit'), array(
        'class' => ' ',
        'title' => lang($controller_name . '_update')
    )) . '</td>';
    
    $table_data_row .= '</tr>';
    return $table_data_row;
}

?>