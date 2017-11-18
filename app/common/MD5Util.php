<?php
/**
 * Created by PhpStorm.
 * User: lmy
 * Date: 2017/7/22
 * Time: 22:04
 */

namespace app\common;


class MD5Util
{
    public static function encode($value='')
    {
        $value .= 'qilusoft';
        $value = md5($value);
        return md5($value);
    }
}