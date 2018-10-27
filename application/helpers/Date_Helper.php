<?php
namespace Helpers;

class Date_Helper
{

    public static function get_current_quarter()
    {
        $quarter = ceil(date('n', time()) / 3);
        
        $quarter_mapping = [
            1 => [
                'T1',
                'T2',
                'T3',
            ],
            2 => [
                'T4',
                'T5',
                'T6',
            ],
            3 => [
                'T7',
                'T8',
                'T9',
            ],
            4 => [
                'T10',
                'T11',
                'T12',
            ]
        ];
        return [
            'quarter' => $quarter,
            'months' => $quarter_mapping[$quarter],
            'start_date' => date('Y-m-d', strtotime('first day of this month', strtotime(date(reset($quarter_mapping[$quarter]))))),
            'end_date' => date('Y-m-d', strtotime('last day of this month', strtotime(date(end($quarter_mapping[$quarter])))))
        ];
    }
    
    public static function get_months()
    {
        return [
            date('Y') . '-01',
            date('Y') . '-02',
            date('Y') . '-03',
            date('Y') . '-04',
            date('Y') . '-05',
            date('Y') . '-06',
            date('Y') . '-07',
            date('Y') . '-08',
            date('Y') . '-09',
            date('Y') . '-10',
            date('Y') . '-11',
            date('Y') . '-12',
        ];
    }
    
    public static function get_next_months($month = '', $number = 3)
    {
        return [
            date('Y-m', strtotime('+1 month', strtotime(date($month)))),
            date('Y-m', strtotime('+2 months', strtotime(date($month)))),
            date('Y-m', strtotime('+3 months', strtotime(date($month))))
        ];
    }
}