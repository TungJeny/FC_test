<?php

function import($type = '', $class_path = '')
{
    if ($type == 'model') {
        require_once (APPPATH . "models/" . $class_path);
    }
}

class MY_Loader extends CI_Loader
{

    function __construct()
    {
        parent::__construct();
    }
}

?>
