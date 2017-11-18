<?php
/**
 * Created by PhpStorm.
 * User: lmy
 * Date: 2017/7/7
 * Time: 16:10
 */

namespace app\android\service;


use app\android\dao\ImagePathDao;
use app\android\dao\PostDao;
use app\android\dao\UserDao;
use app\common\MD5Util;
use GatewayClient\Gateway;
use think\Db;
use think\Exception;

class UserService
{
    private $userDao;
    private $imagePathDao;
    private $postDao;

    public function __construct()
    {
        $this->userDao = new UserDao();
        $this->imagePathDao = new ImagePathDao();
        $this->postDao = new PostDao();
    }

    #判断用户名和密码是否正确
    public function judge($account, $password)
    {
        if (empty($account)) {
            $res = '用户名不能为空';
            return $res;
        } elseif (empty($password)) {
            $res = '密码不能为空';
            return $res;
        } else {
            $res = $this->userDao->accountInDb($account);
            if ($res == null) {
                return '用户名不存在';
            } else {
                $flag = $this->isPasswordCorrected($account, $password);
                if ($flag) {
                    $this->userDao->setOnline($account);
                    $res = '登陆成功';
                    return $res;
                } else {
                    $res = '登陆失败';
                    return $res;
                }
            }
        }
    }

    #判断密码是否正确
    public function isPasswordCorrected($account, $password)
    {
        $passwordInDb = $this->userDao->passwordInDb($account);
        if (MD5Util::encode($password) == $passwordInDb) {
            return true;
        } else {
            return false;
        }
    }

    #登出
    public function logout($account)
    {
        $this->userDao->setOnline($account);
        return '登出成功';
    }

    #注册
    public function register($data)
    {
        if (empty($data['account'])) {
            return '用户名不能为空';
        } elseif (empty($data['password'])) {
            return '密码不能为空';
        } elseif (empty($data['studentId'])) {
            return '学号不能为空';
        } elseif (empty($data['idCard'])) {
            return '身份证号不能为空';
        } elseif (empty($data['name'])) {
            return '请输入姓名';
        } elseif (empty($data['tel'])) {
            return '电话号码不能为空';
        } else {
            $idCardInDb = $this->userDao->idCardInDb($data['idCard']);
            $accountInDb = $this->userDao->accountInDb($data['account']);
            $studentIdInDb = $this->userDao->studentIdInDb($data['studentId']);
            if ($accountInDb != null) {
                return '用户名已被注册';
            }
            if ($idCardInDb != null) {
                return '身份证已经被使用';
            }
            if ($studentIdInDb != null) {
                return '学号已使用';
            }
            $data['password'] = MD5Util::encode($data['password']);
            $res = $this->userDao->addUser($data);
            if ($res != 0) {
                return '注册成功';
            } else {
                return '注册失败';
            }
        }
    }

    //上传头像
    public function setHeadPath($headLocalPath, $headPath, $account, $isUpdate)
    {
        if ($isUpdate) {
            $oldLocalPath = $this->userDao->getHeadLocalPath($account);
            @unlink($oldLocalPath);
        }
        $res = $this->userDao->setHeadPath($headLocalPath, $headPath, $account);
        if ($res != 0) {
            return true;
        } else {
            return false;
        }
    }

    //处理用户头像上传
    public function processHead($res, $data, $isUpdate = false)
    {
        if ($res == '注册成功' || $res == '更新成功') {
            $head = \request()->file('head');
            if ($head) {
                if (!$head->checkImg()) {
                    return json(['status' => '图片格式不正确']);
                }
                if (!$head->checkSize(3145728)) {
                    //检查大小时需要输入字节
                    return json(['status' => '图片太大']);
                }
                $info = $head->move(ROOT_PATH . 'public' . DS . 'heads');
                if ($info) {
                    $headLocalPath = ROOT_PATH . 'public' . DS . 'heads' . DS . $info->getSaveName();
                    $headPath = 'http://' . $_SERVER['SERVER_NAME'] . ':' . $_SERVER["SERVER_PORT"] . '/' . 'heads' . '/' . $info->getSaveName();
                    if ($this->setHeadPath($headLocalPath, $headPath, $data['account'], $isUpdate)) {
                        return json(['status' => '更新成功']);
                    } else {
                        return json(['status' => '更新失败']);
                    }
                } else {
                    return json(['status' => $head->getError()]);
                }
            } else {
                return json(['status' => $res]);
            }
        } else {
            return json(['status' => $res]);
        }
    }


    #获取用户信息
    public function getInfo($account)
    {
        return $this->userDao->getInfo($account);
    }

    //修改用户信息
    public function modifyInfo($data)
    {
        $this->userDao->modify($data);
        return '更新成功';
    }

    //发布报修
    public function addPost($account, $theme, $content, $urgent, $images)
    {
        Db::startTrans();
        try {
            $postId = $this->userDao->addPost($account, $theme, $content, $urgent);
            if ($images) {
                foreach ($images as $image) {
                    $info = $image->move(ROOT_PATH . 'public' . DS . 'images');
                    if ($info) {
                        $imageLocalPath = ROOT_PATH . 'public' . DS . 'images' . DS . $info->getSaveName();
                        $imagePath = 'http://' . $_SERVER['SERVER_NAME'] . ':' . $_SERVER["SERVER_PORT"] . '/' . 'images' . '/' . $info->getSaveName();
                        $this->postDao->addImagePath($postId, $imageLocalPath, $imagePath);
                    }
                }
            }
            Db::commit();
            Gateway::$registerAddress = '127.0.0.1:1236';
            Gateway::sendToGroup('serviceman', '收到新的报修');
            return '发布成功';
        } catch (Exception $exception) {
            Db::rollback();
            return $exception->getMessage();
        }

    }

    #获取报修帖子
    public function getPosts($account)
    {
        $res = $this->userDao->getPostsByAccount($account);
        return $res;
    }

    #获取具体报修帖子的内容
    public function getPostInfo($postId)
    {
        $postRes = $this->postDao->getPostInfoById($postId);
        $imagePathRes = $this->postDao->getImagePath($postId);
        if (empty($imagePathRes)) {
            $postRes[0]['images'] = null;
        }else{
            $postRes[0]['images'] = $imagePathRes;
        }
        return $postRes;
    }

    //重置密码
    public function resetPassword($account)
    {
        $this->userDao->setPassword($account, MD5Util::encode($account));
    }

    //修改密码
    public function setPassword($account, $password)
    {
        return $this->userDao->setPassword($account, MD5Util::encode($password));
    }

    //获取用户数量
    public function getCount()
    {
        return $this->userDao->getCount();
    }

    //删除用户
    public function delete($account)
    {
        return $this->userDao->deleteByAccount($account);
    }


    //设置用户状态
    public function setStatus($account, $status)
    {
        $this->userDao->setStatus($account, $status);
    }

}