<?php
/**
 * Created by PhpStorm.
 * User: lmy
 * Date: 2017/7/7
 * Time: 15:44
 */

namespace app\android\service;


use app\android\dao\PostDao;
use app\android\dao\ServicemanDao;
use app\android\dao\TestScoreDao;
use app\common\MD5Util;
use think\Db;

class ServicemanService
{
    private $servicemanDao;
    private $postDao;

    public function __construct()
    {
        $this->servicemanDao = new ServicemanDao();
        $this->postDao = new PostDao();
    }

    #判断用户名密码是否存在、正确
    public function judge($account, $password)
    {
        $res = $this->servicemanDao->accountInDb($account);
        if ($res == null) {
            return '用户不存在';
        } else {
            if ($this->servicemanDao->isPasswordCorrected($account, $password)) {
                return '登陆成功';
            } else {
                return '密码错误';
            }
        }
    }

    #将用户的登录状态改为登出
    public function logout($account)
    {
        return $this->servicemanDao->isOnline($account);
    }

    #注册
    public function register($data)
    {
        if (empty($data['account'])) {
            $res = '用户名不能为空';
            return $res;
        } elseif (empty($data['password'])) {
            $res = '密码不能为空';
            return $res;
        } elseif (empty($data['idCard'])) {
            $res = '身份证号码不能为空';
            return $res;
        } elseif (empty($data['tel'])) {
            $res = '电话号码不能为空';
            return $res;
        } elseif (empty($data['name'])) {
            $res = '请输入姓名';
            return $res;
        } else {
            $flag = $this->accountIsExisted($data['account']);
            $idCardInDb = $this->servicemanDao->idCardInDb($data['idCard']);
            if ($idCardInDb != null) {
                return '身份证号码已经被使用';
            }
            if ($flag) {
                return '用户名已存在';
            } else {
                $data['password'] = MD5Util::encode($data['password']);
                $res = $this->servicemanDao->add($data);
                if ($res == 0) {
                    return 'fail';
                } else {
                    return 'success';
                }
            }
        }
    }

    #判断用户名是否存在
    public function accountIsExisted($account)
    {
        $res = $this->servicemanDao->accountInDb($account);
        if ($res == null) {
            return false;
        } else {
            return true;
        }
    }

    #获取维修人员信息
    public function getInfo($account)
    {
        $res = $this->servicemanDao->findByAccount($account);
        return $res;
    }

    public function modifyInfo($account, $data)
    {
        $this->servicemanDao->modifyByAccount($account, $data);
        return '更新成功';
    }

    #维修数量加一
    public function addNum($servicemanAccount)
    {
        $this->servicemanDao->addNum($servicemanAccount);
    }

    //维修评分
    public function setScore($score, $postId)
    {

        $status = $this->postDao->getStatus($postId);
        if ($status == 'wait' || $status == 'notSolved') {
            return '不能评价未完成的报修任务';
        }

        if (!$this->isScored($postId)) {
            Db::startTrans();

            $post = $this->postDao->getPostById($postId);
            $servicemanAccount = $post['repairer'];
            $this->postDao->setScore($postId, $score);
            $scoreBefore = $this->servicemanDao->getScore($servicemanAccount);
            $num = $this->servicemanDao->getNum($servicemanAccount);
            $score = $scoreBefore * $num + $score;
            if ($num != 0)
                $score = $score / $num;
            $res = $this->servicemanDao->setScore($servicemanAccount, $score);
            if (!$res) {
                Db::rollback();
                return '评价失败';
            } else {
                $this->postDao->setScored($postId);
                Db::commit();
                return '评价成功';
            }
        } else {
            return '您已经评价';
        }
    }


    public function isScored($postId)
    {
        $res = $this->postDao->getScored($postId);
        if ($res == 'yes') {
            return true;
        } else {
            return false;
        }
    }

//设置头像
    public function setHeadPath($headLocalPath, $headPath, $account, $isUpdate)
    {
        if ($isUpdate) {
            $oldLocalPath = $this->servicemanDao->getHeadLocalPath($account);
            @unlink($oldLocalPath);
        }
        $res = $this->servicemanDao->setHeadPath($headLocalPath, $headPath, $account);
        if ($res != 0) {
            return true;
        } else {
            return false;
        }
    }

    //处理上传的头像
    public function processHead($res, $data, $isUpdate = false)
    {
        if ($res == 'success' || $res == '更新成功') {
            $head = \request()->file("head");
            if ($head) {
                if (!$head->checkImg()) {
                    return json(['status' => '图片格式不正确']);
                }
                if ($head->checkSize(3145728)) {
                    //检查大小时输入字节
                    return json(['status' => '图片太大']);
                }
                $info = $head->move(ROOT_PATH . 'public' . DS . 'heads');
                if ($info) {
                    $headLocalPath = ROOT_PATH . 'public' . DS . 'heads' . DS . $info->getSaveName();
                    $headPath = "http://" . $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'] . '/' . 'heads' . '/' . $info->getSaveName();
                    if ($this->setHeadPath($headLocalPath, $headPath, $data['account'], $isUpdate)) {
                        return json(['status' => '更新成功']);
                    } else {
                        return json(['status' => '更新失败']);
                    }
                } else {
                    return json(['status' => $info->getError()]);
                }
            } else {
                return json(['status' => $res]);
            }
        } else {
            return json(['status' => $res]);
        }
    }

    //重置密码
    public function resetPassword($account)
    {
        $this->servicemanDao->setPassword($account, MD5Util::encode($account));
    }

    //修改密码
    public function setPassword($account, $newPassword)
    {
        return $this->servicemanDao->setPassword($account, MD5Util::encode($newPassword));
    }

    //获取维修人员数量
    public function getCount()
    {
        return $this->servicemanDao->getCount();
    }

    //删除维修人员
    public function delete($account)
    {
        return $this->servicemanDao->deleteByAccount($account);
    }

    //设置状态
    public function setStatus($account, $status)
    {
        $this->servicemanDao->setStatus($account, $status);
    }
}