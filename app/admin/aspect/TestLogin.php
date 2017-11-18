<?php
/**
 * Created by PhpStorm.
 * User: lmy
 * Date: 2017/7/23
 * Time: 15:59
 */

namespace app\admin\aspect;


use think\Session;

class TestLogin
{
    public function run()
    {
        if (empty(Session::get('admin'))) {
            return view('index');
        }
    }
}