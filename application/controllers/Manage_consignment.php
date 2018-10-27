<?php

use Models\M_manage_consignment;
require_once ("Secure_area.php");

class Manage_consignment extends Secure_area
{
    function __construct()
    {
        parent::__construct(); 
        $this->load->helper('report');
        $this->lang->load('module');
        $this->lang->load('home');
        $this->load->model('Item');
        $this->load->model('Item_kit');
        $this->load->model('Supplier');
        $this->load->model('Customer');
        $this->load->model('Employee');
        $this->load->model('Giftcard');
        $this->load->model('Sale');  
        $this->load->library('pagination');
        $this->load->helper('url');
    }

    public function lvt()
    {
        $lvt = new M_manage_consignment();
        $data =array();
        $data['categories'] = $lvt->get_categories();
        $page = $this->uri->segment(3);
        intval($page);
        $page = ($page<1) ? 1: $page;
        $limit = 20;
        $offset = ($page -1)*$limit;
        $config = array();
        $config['base_url']= base_url('manage_consignment/lvt');
        $config['total_rows'] = count($lvt->get_list_lvt());
        $config['per_page'] =$limit;
        $config['prev_link'] = "<<";
        $config['next_link'] = ">>";
        $config['full_tag_open'] = '<ul class="pagination">';
        $config['full_tag_close'] = '</ul>';
        $config['cur_tag_open'] = '<li class="active"><a>';
        $config['cur_tag_close'] = '</a></li>';
        $config['num_tag_open'] = '<li class="pagi">';
        $config['num_tag_close'] = '</li>';
        $config['prev_tag_open'] = '<li class="pagi">';
        $config['prev_tag_close'] = '</li>';
        $config['next_tag_open'] = '<li class="pagi">';
        $config['next_tag_close'] = '</li>';
        $config['last_tag_open'] = '<li class="pagi">';
        $config['last_tag_close'] = '</li>';
        $config['first_tag_open'] = '<li class="pagi">';
        $config['first_tag_close'] = '</li>';
        $config['last_link'] = 'Trang cuối';
        $config['first_link'] = 'Trang đầu';
        $config['use_page_numbers'] = TRUE;
        $data['temp'] = $lvt->get_list_lvt($limit,$offset);
        $this->pagination->initialize($config);
        $data['pagination'] = $this->pagination->create_links();
        $data['offset'] = $offset;
        
        $this->load->view('consignment/lvt', $data);
    }


    public function ltp()
    {
        $ltp = new M_manage_consignment();
        
        $data =array();
        $data['categories'] = $ltp->get_categories_ltp();
        $page = $this->uri->segment(3);
        intval($page);
        $page = ($page<1) ? 1: $page;
        $limit = 5;
        $offset = ($page -1)*$limit;
        $config = array();
        $config['base_url']= base_url('manage_consignment/ltp');
        $config['total_rows'] = count($ltp->get_package());
        $config['per_page'] =$limit;
        $config['prev_link'] = "<<";
        $config['next_link'] = ">>";
        $config['full_tag_open'] = '<ul class="pagination">';
        $config['full_tag_close'] = '</ul>';
        $config['cur_tag_open'] = '<li class="active"><a>';
        $config['cur_tag_close'] = '</a></li>';
        $config['num_tag_open'] = '<li class="pagi">';
        $config['num_tag_close'] = '</li>';
        $config['prev_tag_open'] = '<li class="pagi">';
        $config['prev_tag_close'] = '</li>';
        $config['next_tag_open'] = '<li class="pagi">';
        $config['next_tag_close'] = '</li>';
        $config['last_tag_open'] = '<li class="pagi">';
        $config['last_tag_close'] = '</li>';
        $config['first_tag_open'] = '<li class="pagi">';
        $config['first_tag_close'] = '</li>';
        $config['last_link'] = 'Trang cuối';
        $config['first_link'] = 'Trang đầu';
        $config['use_page_numbers'] = TRUE;
        $data['pi'] = $ltp->get_package($limit,$offset);
        $data['temp'] = $ltp->get_list_ltp();
        $this->pagination->initialize($config);
        $data['pagination'] = $this->pagination->create_links();
        $data['offset'] = $offset;
        $data['tung'] = [];
        $data['items'] = [];
        $data['collection'] = [];
        foreach ($data['pi'] as $row) {
            $data['collection'][$row['package_slug']] = [
                'id' => $row['id'],
                'package_code' => $row['package_code'],
                'package_slug' => $row['package_slug'],
                'package_id' => $ltp->get_consignment_package_name( $row['package_id'] )
                 
            ];
        }

        foreach ($data['collection'] as $key => $row) {   
           foreach ($data['temp'] as $temp) 
            {
                if ($row['package_slug'] == $temp['package_slug']) 
                {
                    $data['collection'][$key]['items'][] = $temp;
                }
            }
        }
      
        $this->load->view('consignment/ltp', $data);
    }
    

    public function sorting()
    {
        $lvt = new M_manage_consignment();

        $start_date = $this->input->post('start_date') ? $this->input->post('start_date') : "" ;
        $end_date = $this->input->post('end_date') ? $this->input->post('end_date') : "";
        $categories = $this->input->post('categories') ? $this->input->post('categories') : "";
        $search =trim( $this->input->post('search')) ? trim($this->input->post('search')) : "";
        $order =  $this->input->post('order') ? $this->input->post('order') : "";
        $order_by =  $this->input->post('order_by') ? $this->input->post('order_by') : "";
        $end_date =$end_date." 23:59:59";
        $page = $this->uri->segment(3,1); $data['page'] = $page;
        intval($page);
        $page = ($page<1) ? 1: $page;
        $limit = 20;
        $offset = ($page -1)*$limit;
        $data['temp'] = $lvt->get_list_lvt($limit,$offset,$search,$categories,$start_date,$end_date,$order,$order_by);
        $config = array();
        $config['base_url']= base_url('manage_consignment/sorting');
        $config['total_rows'] = count($lvt->get_list_lvt("","",$search,$categories,$start_date,$end_date));
        $config['per_page'] =$limit;
        $config['prev_link'] = "<<";
        $config['next_link'] = ">>";
        $config['full_tag_open'] = '<ul class="pagination">';
        $config['full_tag_close'] = '</ul>';
        $config['cur_tag_open'] = '<li class="active"><a>';
        $config['cur_tag_close'] = '</a></li>';
        $config['num_tag_open'] = '<li class="pagi">';
        $config['num_tag_close'] = '</li>';
        $config['prev_tag_open'] = '<li class="pagi">';
        $config['prev_tag_close'] = '</li>';
        $config['next_tag_open'] = '<li class="pagi">';
        $config['next_tag_close'] = '</li>';
        $config['last_tag_open'] = '<li class="pagi">';
        $config['last_tag_close'] = '</li>';
        $config['first_tag_open'] = '<li class="pagi">';
        $config['first_tag_close'] = '</li>';
        $config['last_link'] = 'Trang cuối';
        $config['first_link'] = 'Trang đầu tiên';
        $config['use_page_numbers'] = TRUE;  
        $this->pagination->initialize($config);
        $data['pagination'] = $this->pagination->create_links();
        $data['offset'] = $offset;   
        if ($config['total_rows'] == NULL) {
            echo "<script>  alert('Không có dữ liệu tìm kiếm phù hợp !'); </script>";
        }
        $this->load->view('consignment/search_result',$data);
    }

    public function sorting_ltp()
    {
        $order =  $this->input->post('order') ? $this->input->post('order') : "";
        $order_by =  $this->input->post('order_by') ? $this->input->post('order_by') : "";
        $ltp = new M_manage_consignment();
        $page = $this->uri->segment(3,1); $data['page'] = $page;
        intval($page);
        $page = ($page<1) ? 1: $page;
        $limit = 5;
        $offset = ($page -1)*$limit;
        $data['pi'] = $ltp->get_package($limit,$offset,$order,$order_by);
        $data['temp'] = $ltp->get_list_ltp();
        $config = array();
        $config['base_url']= base_url('manage_consignment/sorting_ltp');
        $config['total_rows'] = count($ltp->get_package("","",$order,$order_by));
        $config['per_page'] =$limit;
        $config['prev_link'] = "<<";
        $config['next_link'] = ">>";
        $config['full_tag_open'] = '<ul class="pagination">';
        $config['full_tag_close'] = '</ul>';
        $config['cur_tag_open'] = '<li class="active"><a>';
        $config['cur_tag_close'] = '</a></li>';
        $config['num_tag_open'] = '<li class="pagi">';
        $config['num_tag_close'] = '</li>';
        $config['prev_tag_open'] = '<li class="pagi">';
        $config['prev_tag_close'] = '</li>';
        $config['next_tag_open'] = '<li class="pagi">';
        $config['next_tag_close'] = '</li>';
        $config['last_tag_open'] = '<li class="pagi">';
        $config['last_tag_close'] = '</li>';
        $config['first_tag_open'] = '<li class="pagi">';
        $config['first_tag_close'] = '</li>';
        $config['last_link'] = 'Trang cuối';
        $config['first_link'] = 'Trang đầu tiên';
        $config['use_page_numbers'] = TRUE;  
        $this->pagination->initialize($config);
        $data['pagination'] = $this->pagination->create_links();
        $data['offset'] = $offset; 
        $data['collection'] = [];
        foreach ($data['pi'] as $row) {
            $data['collection'][$row['package_slug']] = [
                'id' => $row['id'],
                'package_code' => $row['package_code'],
                'package_slug' => $row['package_slug'],
                'package_id' => $ltp->get_consignment_package_name( $row['package_id'] ),
            
            ];
        }
        foreach ($data['collection'] as $key => $row) {
           foreach ($data['temp'] as $temp) 
            {
                if ($row['package_slug'] == $temp['package_slug']) 
                {
                    $data['collection'][$key]['items'][] = $temp;
                }
            }
        }  
        $this->load->view('consignment/search_result_ltp',$data);
    }

    public function search_categories()
    {
        $ltp = new M_manage_consignment();
        $start_date = $this->input->post('start_date') ? $this->input->post('start_date') : "" ;
        $end_date = $this->input->post('end_date') ? $this->input->post('end_date') : "";
        $categories = $this->input->post('categories') ? $this->input->post('categories') : "";
        $search = trim($this->input->post('search')) ? trim($this->input->post('search')) : "";
        $order =  $this->input->post('order') ? $this->input->post('order') : "";
        $order_by =  $this->input->post('order_by') ? $this->input->post('order_by') : "";
        $end_date =$end_date." 23:59:59";
        $data['temp'] = $ltp->get_list_ltp();
        $data['cate'] = $ltp->get_list_category($categories,$search,$start_date,$end_date,$order,$order_by);
        $data['collection'] = [];
        foreach ($data['cate'] as $row) {
            $data['collection'][$row['package_slug']] = [
                'id' => $row['id'],
                'package_code' => $row['package_code'],
                'package_slug' => $row['package_slug'],
                'package_id' => $ltp->get_consignment_package_name( $row['package_id'] ),
            
            ];
        }
        foreach ($data['collection'] as $key => $row) {
           foreach ($data['temp'] as $temp) 
            {
                if ($row['package_slug'] == $temp['package_slug']) 
                {
                    $data['collection'][$key]['items'][] = $temp;
                }
            }
        }  
        $this->load->view('consignment/search_categories',$data);
    }


}
?>


