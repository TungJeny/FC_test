<?php

function get_date_format()
{
    $CI = & get_instance();
    switch ($CI->config->item('date_format')) {
        case "middle_endian":
            return "m/d/Y";
        case "little_endian":
            return "d-m-Y";
        case "big_endian":
            return "Y-m-d";
        default:
            return "m/d/Y";
    }
}

function get_js_date_format()
{
    $CI = & get_instance();
    switch ($CI->config->item('date_format')) {
        case "middle_endian":
            return "MM/DD/YYYY";
        case "little_endian":
            return "DD-MM-YYYY";
        case "big_endian":
            return "YYYY-MM-DD";
        default:
            return "MM/DD/YYYY";
    }
}

function get_time_format()
{
    $CI = & get_instance();
    switch ($CI->config->item('time_format')) {
        case "12_hour":
            return "h:i a";
        case "24_hour":
            return "H:i";
        default:
            return "h:i a";
    }
}

function get_js_time_format()
{
    $CI = & get_instance();
    $locale = get_js_locale();
    
    switch ($CI->config->item('time_format')) {
        case "12_hour":
            if ($locale == 'id') {
                return 'LT';
            }
            return "hh:mm a";
        case "24_hour":
            return "HH:mm";
        default:
            if ($locale == 'id') {
                return 'LT';
            }
            return "hh:mm a";
    }
}

function get_js_locale()
{
    $CI = & get_instance();
    $languages = array(
        'english' => 'en',
        'indonesia' => 'id',
        'spanish' => 'es',
        'french' => 'fr',
        'italian' => 'it',
        'german' => 'de',
        'dutch' => 'nl',
        'portugues' => 'pt',
        'arabic' => 'ar',
        'khmer' => 'km'
    );
    
    return isset($languages[$CI->config->item("language")]) ? $languages[$CI->config->item("language")] : 'en';
}
?>
