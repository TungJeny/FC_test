<?php
namespace orders;

abstract class Order
{

    public $CI;

    public function __construct()
    {
        $this->CI = & get_instance();
    }

}