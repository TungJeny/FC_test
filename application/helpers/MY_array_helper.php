<?php

function sort_assoc_array_by_label($a, $b)
{
    if ($a['label'] == $b['label']) {
        return 0;
    }
    return ($a['label'] < $b['label']) ? - 1 : 1;
}

function sort_assoc_array_by_name($a, $b)
{
    if ($a['name'] == $b['name']) {
        return 0;
    }
    return ($a['name'] < $b['name']) ? - 1 : 1;
}

function sort_cart_items($a, $b)
{
    return $a['line'] - $b['line'];
}

function object_to_array($d) {
    if (is_object($d)) {
        // Gets the properties of the given object
        // with get_object_vars function
        $d = get_object_vars($d);
    }

    if (is_array($d)) {
        /*
        * Return array converted to object
        * Using __FUNCTION__ (Magic constant)
        * for recursive call
        */
        return array_map(__FUNCTION__, $d);
    }
    else {
        // Return array
        return $d;
    }
}

?>
