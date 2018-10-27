<?php

function character_limiter($str, $n = 500, $end_char = '&#8230;')
{
    if (strlen($str) < $n) {
        return $str;
    }
    
    if (function_exists('mb_substr')) {
        return mb_substr($str, 0, $n) . $end_char;
    }
    
    return substr($str, 0, $n) . $end_char;
}

function replace_newline($string)
{
    return (string) str_replace(array(
        "\r",
        "\r\n",
        "\n"
    ), '', $string);
}

function number_pad($number, $n)
{
    return str_pad((int) $number, $n, "0", STR_PAD_LEFT);
}

function H($input)
{
    return htmlentities($input, ENT_QUOTES, 'UTF-8', false);
}

// From http://stackoverflow.com/a/26537463/627473
function escape_full_text_boolean_search($search)
{
    $return = preg_replace('/[+\-><\(\)~*\"@]+/', ' ', $search);
    if (trim($return) == "") {
        // If we have no search return a bar character is this prevents fatal error
        $return = '|';
    }
    return $return;
}

function does_contain_only_digits($string)
{
    return (preg_match('/^[0-9]+$/', $string));
}

/**
 *
 * @access    public
 * @param    string
 * @return    string
 */
if ( ! function_exists('create_slug'))
{
    function create_slug($string , $delimiter = '_') {
        $search = array (
            '#(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)#',
            '#(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)#',
            '#(ì|í|ị|ỉ|ĩ)#',
            '#(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)#',
            '#(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)#',
            '#(ỳ|ý|ỵ|ỷ|ỹ)#',
            '#(đ)#',
            '#(À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ)#',
            '#(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)#',
            '#(Ì|Í|Ị|Ỉ|Ĩ)#',
            '#(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ)#',
            '#(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)#',
            '#(Ỳ|Ý|Ỵ|Ỷ|Ỹ)#',
            '#(Đ)#',
            "/[^a-zA-Z0-9\-\_]/",
        ) ;
        $replace = array (
            'a',
            'e',
            'i',
            'o',
            'u',
            'y',
            'd',
            'A',
            'E',
            'I',
            'O',
            'U',
            'Y',
            'D',
            '-',
        ) ;
        $string = preg_replace($search, $replace, $string);
        $string = preg_replace('/(-)+/', $delimiter, $string);
        $string = strtolower($string);
        return $string;
    }
}

?>