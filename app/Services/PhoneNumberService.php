<?php

namespace App\Services;

class PhoneNumberService
{

    public static function filter_mobile_number($mob_num)
    {
        $mob_num = self::convertArabicNumber($mob_num);
        $first_3_val = substr($mob_num, 0, 3);
        $first_4_val = substr($mob_num, 0, 4);
        $sixth_val = substr($mob_num, 0, 6);
        $first_val = substr($mob_num, 0, 1);
        $mob_number = 0;
        $val = 0;
        if ($sixth_val == "009665") {
            $val = null;
            $mob_number = substr($mob_num, 2, 12);
        } elseif ($sixth_val == "009660") {
            $val = 966;
            $mob_number = substr($mob_num, 6, 14);
        } elseif ($first_3_val == "+96") {
            $val = "966";
            $mob_number = substr($mob_num, 4);
        } elseif ($first_4_val == "9660") {
            $val = "966";
            $mob_number = substr($mob_num, 4);
        } elseif ($first_3_val == "966") {
            $val = null;
            $mob_number = $mob_num;
        } elseif ($first_val == "5") {
            $val = "966";
            $mob_number = $mob_num;
        } elseif ($first_3_val == "009") {
            $val = "9";
            $mob_number = substr($mob_num, 4);
        } elseif ($first_val == "0") {
            $val = "966";
            $mob_number = substr($mob_num, 1, 9);
        } else {
            $val = "966";
            $mob_number = $mob_num;
        }

        $real_mob_number = $val . $mob_number;
        return $real_mob_number;
    }

    public static function convertArabicNumber($number)
    {
        $arabic_array = ['۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4', '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9', '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4', '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9'];
        return strtr($number, $arabic_array);
    }

    public static function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}
