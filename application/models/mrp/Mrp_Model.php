<?php
namespace Models\Mrp;

class Mrp_Model extends \CI_Model
{
    protected $CI = NULL;
    
    public function __construct()
    {
        parent::__construct();
        $this->CI =& get_instance();
    }
}
