<?php
namespace Models;
class M_manage_consignment extends \CI_Model
{

    public function __construct()
    {
        parent::__construct();
    }

	function get_list_lvt($limit="",$offset="",$search="",$categories="",$start_date="",$end_date="",$order="package_by_id",$order_by="desc")
	{
        $this->db->select('stock.stock_id,stock_package.item_id,items.category_id,stock_package.package_by_id,stock_package.package_slug,stock_package.package_code,stock_items.name,stock_items.quantity,suppliers.company_name,stock.created_at,people.full_name,stock_items.note'); 
        $this->db->from('stock_package');
        $this->db->join('stock','stock.stock_id = stock_package.package_by_id');
        $this->db->join('stock_items','stock_items.item_id = stock_package.item_id');
        $this->db->join('suppliers','suppliers.person_id = stock.supplier_id');
        $this->db->join('people','people.person_id = stock.employee_id');
        $this->db->join('items','items.item_id = stock_items.item_id');
        $this->db->where('stock_items.stock_id = stock.stock_id');
        $this->db->where('package_type', 1);

        if($categories !=="")
        {
            $this->db->where('items.category_id', $categories);
        }
        if($start_date !=="")
        {
            $start_date = strtotime($start_date);
            $this->db->where('stock.created_at >',$start_date);
        }
        if ($end_date !=="")
        {
            $end_date = strtotime($end_date);
            $this->db->where('stock.created_at <' ,$end_date);
        }
        if($search !=="")
        {
            $this->db->like('stock_package.package_code', $search);
        }
        if($limit!=="" && $offset !=="") 
        { 
            $this->db->limit($limit,$offset);
        }
        if ($start_date !== "" || $end_date !== "" ) {
            $this->db->where('stock.created_at >', $start_date);
        }
       
        if($order!=="" && $order_by!=="")
        {
        $this->db->order_by($order, $order_by);
        }
        $query = $this->db->get();
         
        return $query->result_array();
    }

	function get_list_ltp()
    {
        $this->db->select('stock_package.*,stock.package_id,stock.stock_id,items.item_id,stock_items.name,stock_items.quantity,items.category_id,stock.created_at,people.full_name,stock_items.note,categories.name AS name2', false);
        $this->db->from('stock_package');
        $this->db->join('stock','stock.stock_id = stock_package.package_by_id');
        $this->db->join('stock_items','stock_items.stock_id = stock.stock_id');
        $this->db->join('items','items.item_id = stock_items.item_id');
        $this->db->join('categories','items.category_id = categories.id');
        $this->db->join('people','people.person_id = stock.employee_id');
        $this->db->where('stock_items.stock_id = stock.stock_id');
        $this->db->where('package_type', 2);
        $query = $this->db->get();
        return $query->result_array();
    }

    function get_package($limit="",$offset="",$order="package_by_id",$order_by="desc")
    {
        $this->db->select('stock_package.*, stock.package_id, stock.created_at', false); 
        $this->db->from('stock_package');
        $this->db->join('stock','stock.stock_id = stock_package.package_by_id');
        $this->db->where('package_type', 2);
        $this->db->where('stock.deleted', 0);
        $this->db->where('stock.deleted', 0);
        if($order!=="" && $order_by!=="")
        {
        $this->db->order_by($order, $order_by);
        }
        if($limit!=="" && $offset !=="") 
        { 
            $this->db->limit($limit,$offset);
        }
        $query = $this->db->get();
        return $query->result_array();
    }

     function get_list_category($categories="",$search="",$start_date="",$end_date="",$order="package_by_id",$order_by="desc")
    {
        $this->db->select('stock_package.*,stock.package_id,stock.stock_id,items.item_id,stock_items.name,stock_items.quantity,items.category_id,stock.created_at,people.full_name,stock_items.note,categories.name AS name2', false);
        $this->db->from('stock_package');
        $this->db->join('stock','stock.stock_id = stock_package.package_by_id');
        $this->db->join('stock_items','stock_items.stock_id = stock.stock_id');
        $this->db->join('items','items.item_id = stock_items.item_id');
        $this->db->join('categories','items.category_id = categories.id');
        $this->db->join('people','people.person_id = stock.employee_id');
        $this->db->where('stock_items.stock_id = stock.stock_id');
        $this->db->where('package_type', 2);

        if ($categories !== "" ) {
            $this->db->where('items.category_id', $categories);
        }
        if($start_date !=="")
        {
            $start_date = strtotime($start_date);
            $this->db->where('stock.created_at >',$start_date);
        }
        if ($end_date !=="")
        {
            $end_date = strtotime($end_date);
            $this->db->where('stock.created_at <' ,$end_date);
        }
        if($search !=="")
        {
            $this->db->like('stock_package.package_code', $search);
        }
       
        if ($start_date !== "" || $end_date !== "" ) {
            $this->db->where('stock.created_at >', $start_date);
        }
       
        if($order!=="" && $order_by!=="")
        {
        $this->db->order_by($order, $order_by);
        }
        $query = $this->db->get();
        return $query->result_array();
    }

    function get_consignment_package_name($stock_package_id){
        $this->db->select('package_code');
        $this->db->from('stock_package');
        $this->db->where('id', $stock_package_id);
        $query = $this->db->get();
        if (!$query) {
            return false;
        }
        $row = $query->row_array();
        $query->free_result();

        return $row['package_code'];
    }
 	
    function get_categories()
    {
        $this->db->select();
        $this->db->from('categories');
        $ids = array(2,3,5);
        $this->db->where_in('id', $ids);
        return $this->db->get()->result_array();     
    }
       function get_categories_ltp()
    {
        $this->db->select();
        $this->db->from('categories');
        $ids = array(1,6,7);
        $this->db->where_in('id', $ids);
        return $this->db->get()->result_array();     
    }



}