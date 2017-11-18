<?php
/**
 * Created by PhpStorm.
 * User: lmy
 * Date: 2017/7/23
 * Time: 10:46
 */

namespace app\admin\dao;


use app\admin\model\Admin;

class AdminDao
{
    public function saveAdmin($data)
    {
        $admin = new Admin();
        $admin->save($data);
    }

    public function getAdmin($username)
    {
        $admin = new Admin();
        return $admin->where('username', $username)->find();
    }

    public function getPassword($username)
    {
        $admin = Admin::get(['username' => $username]);
        return $admin->password;
    }

    public function changePassword($username, $newPassword)
    {
        $admin = $this->getAdmin($username);
        return $admin->save(['password'=> $newPassword]);
    }
}