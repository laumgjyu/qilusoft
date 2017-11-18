<?php
/**
 * Created by PhpStorm.
 * User: lmy
 * Date: 2017/7/23
 * Time: 10:46
 */

namespace app\admin\service;


use app\admin\dao\AdminDao;
use app\android\dao\ServicemanDao;
use app\android\dao\UserDao;
use app\common\MD5Util;
use think\Loader;
use think\Validate;

class AdminService
{
    private $adminDao;
    private $servicemanDao;
    private $userDao;

    public function __construct()
    {
        $this->adminDao = new AdminDao();
        $this->servicemanDao = new ServicemanDao();
        $this->userDao = new UserDao();
    }

    public function login($username, $password)
    {
        $usernameInDb = $this->adminDao->getAdmin($username);
        if (empty($usernameInDb) || $password != $this->adminDao->getPassword($username)) {
            return '用户名或密码错误';
        }
        return 'success';
    }

    public function listServicemen()
    {
        return $this->servicemanDao->getServicemanByPage();
    }

    public function listUsers()
    {
        return $this->userDao->getUsersByPage();
    }

    public function changePassword($username,$oldPassword, $newPassword)
    {
        $oldPasswordInDb = $this->adminDao->getPassword($username);
        if (MD5Util::encode($oldPassword) != $oldPasswordInDb) {
            return '旧密码输入错误！';
        } else {
            $res=$this->adminDao->changePassword($username, MD5Util::encode($newPassword));
            if ($res == 0){
                return '修改失败！';
            } else {
                return "修改成功!";
            }
        }
    }
}