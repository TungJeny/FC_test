<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_create_table_product_planning_import extends MY_Migration
{

    public function up()
    {
        $this->execute_sql(realpath(dirname(__FILE__) . '/' . '20181010080844_create_table_product_planning_import.sql'));
    }

    public function down()
    {
    	
    }
}