<?php
require_once ("Secure_area.php");

class Yamaha extends Secure_area
{
    
    function __construct()
    {
        parent::__construct('sales');
    }
    
    public function index() {
        $this->load->view('orders/yamaha');
    }
}
?>
