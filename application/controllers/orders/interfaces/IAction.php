<?php
namespace orders\interfaces;

interface IAction
{

    public function view($type = '');

    public function save($data);

    public function update($sale_monthly_id);

    public function delete($sale_monthly_id);

    public function upload();
    
    public function order_clone($sale_monthly_id);
}