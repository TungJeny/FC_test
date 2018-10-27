<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_create_table_purchase_order_item_staged extends MY_Migration
{

    public function up()
    {
        $this->execute_sql(realpath(dirname(__FILE__) . '/' . '20181010080821_create_table_purchase_order_item_staged.sql'));
    }

    public function down()
    {
    	
    }
}