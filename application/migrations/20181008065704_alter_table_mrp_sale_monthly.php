<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_alter_table_mrp_sale_monthly extends MY_Migration
{

    public function up()
    {
        $this->execute_sql(realpath(dirname(__FILE__) . '/' . '20181008065704_alter_table_mrp_sale_monthly.sql'));
    }

    public function down()
    {}
}