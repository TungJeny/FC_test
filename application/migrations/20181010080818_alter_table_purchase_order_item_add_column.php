<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_alter_table_purchase_order_item_add_column extends MY_Migration
{

    public function up()
    {
        $this->execute_sql(realpath(dirname(__FILE__) . '/' . '20181010080818_alter_table_purchase_order_item_add_column.sql'));
    }

    public function down()
    {
    	
    }
}