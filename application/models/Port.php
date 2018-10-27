<?php

class Port extends CI_Model
{
    /**
     * @var array
     */
    private $_ports = [
        'honda' => [
            'V2AF02 - V2AE02',
            'V2AE03 - V2AF03',
            'V2AE02 - V2AF02',
            'V2AF03 - V2MC03 - V2MC05',
            'V2AF04 - V2AE04',
            'V2AF40 - V2AE40',
            'V2AF51 - V2AE51',
            'V2AF52 - V2AE52',
            'V2AF53 - V2AE53',
            'V2AF54 - V2AE54',
            'V2AF58 - V2AE58',
            'V2AF57 - V2AE57',
            'V2AF05 - V2AE05'
        ],
        'yamaha' => [
            'MP',
            'SP'
        ]
    ];

    /**
     * @return array
     */
    function get_ports()
    {
        return $this->_ports;
    }

    /**
     * @param $customer_code
     * @return mixed
     */
    public function get_customer_ports($customer_code) {
        if (!isset($this->_ports[$customer_code])) {
            return null;
        }
        return $this->_ports[$customer_code];
    }
}