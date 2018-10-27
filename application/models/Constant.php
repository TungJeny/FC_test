<?php
namespace Models;

class Constant
{

    const MRP_CATEGORY_ID_TP = 1;

    const MRP_CATEGORY_ID_BTP = 24;

    const MRP_CATEGORY_ID_VTC = 2;

    const MRP_CATEGORY_ID_VTP = 3;

    const MRP_CATEGORY_ID_PHOI = 5;

    const MRP_CATEGORY_ID_HONDA = 6;

    const MRP_CATEGORY_ID_YAMAHA = 7;

    const ORDER_TYPE_HONDA_NM12 = 'nm1';

    const ORDER_TYPE_HONDA_NM3 = 'nm3';

    const ORDER_TYPE_HONDA_SP = 'sp';

    static function categories()
    {
        return [
            'MRP_CATEGORY_ID_TP' => self::MRP_CATEGORY_ID_TP,
            'MRP_CATEGORY_ID_BTP' => self::MRP_CATEGORY_ID_BTP,
            'MRP_CATEGORY_ID_VTC' => self::MRP_CATEGORY_ID_VTC,
            'MRP_CATEGORY_ID_VTP' => self::MRP_CATEGORY_ID_VTP,
            'MRP_CATEGORY_ID_PHOI' => self::MRP_CATEGORY_ID_PHOI,
            'MRP_CATEGORY_ID_HONDA' => self::MRP_CATEGORY_ID_HONDA,
            'MRP_CATEGORY_ID_YAMAHA' => self::MRP_CATEGORY_ID_YAMAHA
        ];
    }
    
    static function factories ()
    {
        return [
            '1' => 'XN Vòng bi KH',
            '2' => 'XN Rèn KH',
            '3' => 'XN CK2 KH',
            '4' => 'XN CK1 KH',
            '5' => 'XN Nhiệt luyện KH',
            '6' => 'Bao gói XK',
        ];
    }
}