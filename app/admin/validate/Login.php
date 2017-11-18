<?php
/**
 * Created by PhpStorm.
 * User: lmy
 * Date: 2017/7/23
 * Time: 10:50
 */

namespace app\admin\validate;


use think\Validate;

class Login extends Validate
{
    protected $rule = [
        'admin' => "require|max:24|unique",
        'password' => "require|max:16|min:8",
        'email' => 'email'
    ];
    protected $message = [
        'admin.require' => '用户名不能为空',
        'admin.max' => '用户名不能超过24个字符',
        'admin.unique' => '用户名已经存在',
        'password.require' => '密码不能为空',
        'password.max' => '密码不能超过16个字符',
        'password.min' => '密码不能少于8个字符',
        'email' => '邮箱格式不正确'
    ];
}