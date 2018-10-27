<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_create_delivery_order_ttm extends MY_Migration
{

    public function up()
    {
        $this->execute_sql(realpath(dirname(__FILE__) . '/' . '20181021221638_create_delivery_order_ttm.sql'));
    }

    public function down()
    {}
}