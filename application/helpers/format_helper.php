<?php
/**
 * Created by PhpStorm.
 * User: AnhDT
 * Date: 14/07/2018
 * Time: 4:21 PM
 */

function format_begin_month($month) {
    $month = explode('/', $month);
    return $month[1] . '-' . $month[0] . '-01';
}

function format_end_month($month) {
    $date = new DateTime(format_begin_month($month));
    $date->modify('last day of this month');
    return $date->format('Y-m-d');
}

function date_to_timestamp($date, $delimiter = '/') {
    $segments = explode($delimiter, $date);
    return strtotime($segments[2] . '-' . $segments[1] . '-' . $segments[0] . ' 00:00:00');
}

function render_date($date, $delimiter = '-') {
    $segments = explode($delimiter, $date);
    return $segments[2] . '/' . $segments[1] . '/' . $segments[0];
}

function get_data($item, $field, $default_value = '') {
    if (is_object($item) && isset($item->$field)) {
        return $item->$field;
    }
    if (is_array($item) && isset($item[$field])) {
        return $item[$field];
    }
    return $default_value;
}

function get_filter($item, $path, $default = null)
{
    if (!empty($item->$path)) {
        return $item->$path;
    }
    if (!empty($item[$path])) {
        return $item[$path];
    }
    $segments = explode('/', $path);
    $key = array_shift($segments);
    $path = implode('/', $segments);
    if (!empty($item[$key])) {
        return get_filter($item[$key], $path, $default);
    }
    if (!empty($item->$key)) {
        return get_filter($item->$key, $path, $default);
    }
    if (isset($default)) {
        return $default;
    }
    return null;
}

function render_paginate($current_page, $record_per_page, $count_results, $paginatee_range)
{
    $paginate = array();
    if ($current_page <= 1) {
        $current_page = 1;
    }
    $paginate['start'] = ($current_page - 1) * $record_per_page;
    if ($paginate['start'] < 0) {
        $paginate['start'] = 0;
    }
    $count_pages = ceil($count_results / $record_per_page);
    $paginate['total'] = $count_pages;
    $delta = ceil($paginatee_range / 2);
    if ($current_page - $delta > $count_pages - $paginatee_range) {
        $paginate['lower'] = $count_pages - $paginatee_range;
        $paginate['upper'] = $count_pages;
    } else {
        if ($current_page - $delta < 0) {
            $delta = $current_page;
        }
        $offset = $current_page - $delta;
        $paginate['lower'] = $offset + 1;
        $paginate['upper'] = $offset + $paginatee_range;
    }
    if ($paginate['lower'] <= 1) {
        $paginate['lower'] = 1;
    }
    if ($paginate['upper'] >= $count_pages) {
        $paginate['upper'] = $count_pages;
    }
    if ($paginate['upper'] <= 1) {
        $paginate['upper'] = 1;
    }
    return $paginate;
}

function render_sorter($sorter, $field)
{
    if (get_data($sorter, 'name') == $field) {
        return '<span class="glyphicon ' . ((get_data($sorter, 'value') == 'ASC') ? 'glyphicon-arrow-up' : 'glyphicon-arrow-down') . '"></span>';
    }
    return null;
}

function analyze_filters($filters)
{
    $conds = array();

    foreach ($filters as $key => $field) {
        if (count($field) < 2 || !isset($field['value']) || empty($field['value'])) {
            continue;
        }
        if (!is_array($field['value'])) {
            $value = trim($field['value']);
            if (isset($field['exclude']) && $value == $field['exclude']) {
                continue;
            }
        } else {
            $value = array_unique($field['value']);
        }
        switch ($field['type']) {
            case 'equal':
                $conds[$key] = $key . ' = ' . $value;
                break;
            case 'text':
                $value = addslashes($value);
                if ($value[0] == '#') {
                    $value = substr($value, 1, strlen($value) - 1);
                    $conds[$key] = $key . ' LIKE "' . $value . '%"';
                } else {
                    $conds[$key] = $key . ' LIKE "%' . $value . '%"';
                }
                break;
            case 'in':
                $conds[$key] = 'CONCAT(",", ' . $key . ', ",") ' . ' LIKE "%,' . $value . ',%"';
                break;
            case 'in_array':
                $conds[$key] = $key . ' IN (' . implode(',', $value) . ')';
                break;
            case 'range':
                if (strstr($value, '-')) {
                    $segments = explode('-', $value);
                    if (count($segments) == 2) {
                        $conds[$key] = $key . ' >= ' . $segments[0] . ' AND ' . $key . ' <= ' . $segments[1];
                    }
                } else {
                    $conds[$key] = $key . ' = ' . $value;
                }
                break;
            default:
                break;
        }
    }
    if (count($conds) == 0) {
        $conds = null;
    }
    return $conds;
}