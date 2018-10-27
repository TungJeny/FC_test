<?php
class MY_Input extends CI_Input 
{
   function __construct()
	{
		parent::__construct();
   }
	
	function request($index)
	{
		if (isset($_REQUEST[$index]))
		{
			return $_REQUEST[$index];
		}
		
		return NULL;
	}
	
	public function ip_address()
	{
    if (getenv('HTTP_CF_CONNECTING_IP'))
        return getenv('HTTP_CF_CONNECTING_IP');
		
		return parent::ip_address();
	}
	
	public function clean_string($str)
	{
		// Clean UTF-8 if supported
		if (UTF8_ENABLED === TRUE)
		{
			$str = $this->uni->clean_string($str);
		}
		
		return $str;
	}

    public function get_param($paramName, $defaultValue='') {
        $value = null;
        if (!function_exists('uri_string')) {
            return $value;
        }
        $uri = strtolower(uri_string());
        $segments = explode('/', $uri);
        for ($index = 0; $index < count($segments); $index++) {
            if ($segments[$index] === $paramName) {
                if (isset($segments[$index + 1])) {
                    $value = $segments[$index + 1];
                    break;
                }
            }
        }
        if (!$value) {
            $value = $this->get_post($paramName);
            if (!$value) {
                $value = $defaultValue;
            }
        }
        return $value;
    }
}