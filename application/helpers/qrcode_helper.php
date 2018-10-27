<?php 

	// generate QR code 
	// params : array  , $data_input : array
	// return : avalible string

	function get_items_qrcode_data($item_ids)
	{

		$CI = & get_instance();
		$result = array();
		$item_ids = explode('~', $item_ids);
		foreach ($item_ids as $item_id){
			$item_info = $CI->Item->get_info($item_id);
			$result[] = array(
				'name' => $item_info->name,
				'id'   => $item_info->item_id
			);
		}

		return $result;
	}

	// save png jpg QR code store

	// function save_qrcode_data(){

	// 	$this->load->library('ciqrcode');
	// 	$params['data'] = 'This is a text to encode become QR Code';
	// 	$params['level'] = 'H';
	// 	$params['size'] = 10;
	// 	$params['savename'] = FCPATH.'tes.png';
	// 	$this->ciqrcode->generate($params);
	// 	echo '<img src="'.base_url().'tes.png" />';

	// }


	// Optional configuration QR code
		
	// function optional_config_qrcode(){

	// 	$this->load->library('ciqrcode');
	// 	$config['cacheable']	= true; //boolean, the default is true
	// 	$config['cachedir']		= ''; //string, the default is application/cache/
	// 	$config['errorlog']		= ''; //string, the default is application/logs/
	// 	$config['quality']		= true; //boolean, the default is true
	// 	$config['size']			= ''; //interger, the default is 1024
	// 	$config['black']		= array(224,255,255); // array, default is array(255,255,255)
	// 	$config['white']		= array(70,130,180); // array, default is array(0,0,0)
	// 	$this->ciqrcode->initialize($config);

	// }


 ?>