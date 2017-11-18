<?php
/**
 * Created by PhpStorm.
 * User: lmy
 * Date: 2017/7/7
 * Time: 15:53
 */

namespace app\android\dao;


use app\android\model\Serviceman;

class ServicemanDao
{
    #判断账户是否存在
    public function accountInDb($account)
    {
        $serviceman = new Serviceman();
        $res = $serviceman->where('account', $account)->find();
        return $res;
    }

    #判断身份证号码是否已经使用
    public function idCardInDb($idCard)
    {
        $serviceman = new Serviceman();
        $res = $serviceman->where('idCard', $idCard)->find();
        return $res;
    }

    #判断密码是否正确
    public function isPasswordCorrected($account, $password)
    {
        $serviceman = new Serviceman();
        $res = $serviceman->where('account', $account)->find();
        if ($password == $res->password) {
            $res->isOnline = 'true';
            $res->save();
            return true;
        } else {
            return false;
        }
    }

    #判断用户是否在线
    public function isOnline($account)
    {
        $serviceman = new Serviceman();
        $res = $serviceman->where('account', $account)->find();
        if ($res->isOnline == 'true') {
            $res->isOnline = 'false';
            $res->save();
            return '登出成功';
        }
    }

    #添加维修人员
    public function add($data)
    {
        $serviceman = new Serviceman();
        $serviceman->data($data);
        $res = $serviceman->save();
        return $res;
    }

    #根据账号查找
    public function findByAccount($account)
    {
        $serviceman = new Serviceman();
        $res = $serviceman->where('account', $account)->field('account,name,sex,tel,idCard,address,number,credit,school,headPath')->find();
        return $res;
    }

    #根据用户名修改
    public function modifyByAccount($account, $data)
    {
        $serviceman = new Serviceman();
        $res = $serviceman->save($data, ['account' => $account]);
        return $res;
    }

    #维修数量加一
    public function addNum($servicemanAccount)
    {
        $serviceman = Serviceman::get(['account' => $servicemanAccount]);
        $num = $serviceman->number;
        $serviceman->number = $num + 1;
        return $serviceman->save();
    }

    #获取维修人评分
    public function getScore($servicemanAccount)
    {
        $serivceman = Serviceman::get(['account' => $servicemanAccount]);
        return $serivceman->credit;
    }

    #设置维修人员评分
    public function setScore($servicemanAccount, $score)
    {
        $serviceman = Serviceman::get(['account' => $servicemanAccount]);
        $serviceman->credit = $score;
        return $serviceman->save();
    }

    #获取维修数量
    public function getNum($servicemanAccount)
    {
        $serviceman = Serviceman::get(['account' => $servicemanAccount]);
        return $serviceman->number;
    }

    //设置头像
    public function setHeadPath($headLocalPath, $headPath, $account)
    {
        $serviceman = Serviceman::get(['account' => $account]);
        $serviceman->headPath = $headPath;
        $serviceman->headLocalPath = $headLocalPath;
        return $serviceman->save();
    }

    //分页获取维修人员
    public function getServicemanByPage()
    {
        $serviceman = new Serviceman();
        return $serviceman->paginate(10);
    }

    //设置密码
    public function setPassword($account, $password)
    {
        $serviceman = Serviceman::get(['account' => $account]);
        return $serviceman->save(['password' => $password]);
    }

    //获取维修人员总数
    public function getCount()
    {
        return db('serviceman')->count();
    }

    public function deleteByAccount($account)
    {
        $serviceman = Serviceman::get(['account' => $account]);
        if (empty($serviceman)) {
            return 0;
        } else {
            return $serviceman->delete();
        }
    }

    public function setStatus($account, $status)
    {
        $serviceman = Serviceman::get(['account' => $account]);
        $serviceman->isOnline = $status;
        $serviceman->save();
    }

    //根据account获取维修人员所在学校
    public function getSchool($account){
        $serviceman = Serviceman::get(['account' => $account]);
        return $serviceman->school;
    }

    public function getHeadLocalPath($account)
    {
        $serviceman = Serviceman::get(['account' => $account]);
        return $serviceman->headLocalPath;
    }
}