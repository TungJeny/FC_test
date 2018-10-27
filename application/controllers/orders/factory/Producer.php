<?php
namespace orders\factory;

use orders\Honda;
use orders\Yamaha;
use orders\Honda_sample;
use orders\Honda_sp;

class Producer
{
    public static function get_factory($type)
    {
        if ($type == null) {
            return null;
        } elseif (strtolower($type) == 'yamaha') {
            return new Yamaha();
        } elseif (strtolower($type) == 'honda') {
            return new Honda();
        } elseif (strtolower($type) == 'honda_sp') {
            return new Honda_sp();
        } elseif (strtolower($type) == 'honda_sample') {
            return new Honda_sample();
        }
    }
}