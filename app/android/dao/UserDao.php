<?php
/**
 * Created by PhpStorm.
 * User: lmy
 * Date: 2017/7/7
 * Time: 16:04
 */

namespace app\android\dao;


use app\android\model\User;
use think\Db;

class UserDao
{
    #检查用户名是否存在
    public function accountInDb($account)
    {
        $user = new User();
        $res = $user->where('account', $account)->find();
        return $res;
    }

    #检查身份证是否已存在
    public function idCardInDb($idCard)
    {
        $user = new User();
        $res = $user->where('idCard', $idCard)->find();
        return $res;
    }

    #检查学号是否已经存在
    public function studentIdInDb($studentId)
    {
        $user = new User();
        $res = $user->where('studentId', $studentId)->find();
        return $res;
    }

    #检查密码是否正确
    public function passwordInDb($account)
    {
        $user = new User();
        $res = $user->where('account', $account)->find();
        return $res->password;
    }

    #设置在线状态
    public function setOnline($account)
    {
        $user = new User();
        Db::startTrans();
        $res = $user->where('account', $account)->find();
        if ($res->isOnline == 'false' || empty($res->isOnline)) {
            $res->isOnline = 'true';
        } else {
            $res->isOnline = 'false';
        }
        $res->save();
        Db::commit();
    }

    #添加用户
    public function addUser($data)
    {
        $user = new User();
        $user->data($data);
        $res = $user->save();
        $this->setOnline($data['account']);
        return $res;
    }

    #获取用户信息
    public function getInfo($account)
    {
        $user = new User();
        $res = $user->where('account', $account)->field('id,account,name,sex,studentId,idCard,school,tel,headPath')->find();
        return $res;
    }

    //修改用户信息
    public function modify($data)
    {
        $user = new User();
        $res = $user->save($data, ['account' => $data['account']]);
        return $res;
    }

    //添加报修帖子
    public function addPost($account, $theme, $content, $urgent)
    {
        $user = User::get(['account' => $account]);
        $school = $user->school;
        $post_time = date('Y-m-d H:i:s');
        $res = $user->posts()->save([
            'theme' => $theme,
            'content' => $content,
            'urgent' => $urgent,
            'post_time' => $post_time,
            'last_modify_time' => $post_time,
            'status' => 'notSolved',
            'school' => $school,
        ]);
        return $user->posts()->where(['user_id' => $user->id, 'post_time' => $post_time])->field('id')->find()->id;
    }

    //获取帖子
    public function getPostsByAccount($account)
    {
        $user = User::get(['account' => $account]);
        $res = $user->posts()
            ->field('id,theme,post_time,repair_time,repairer,score,status')
            ->order('post_time','desc')
            ->select();
        return $res;
    }

/*
    //获取帖子内容
    public function getPostInfoById($postId)
    {
        $user = new User();
        $res = $user->posts()->where('id', $postId)->select();
//        $posts = Db::table('t_post')
//            ->alias('p')
//            ->join(['t_user' => 'u'], 'p.user_id = u.id')
//            ->field('p.id,theme,post_time,repair_time,last_modify_time,content,repairer,status,urgent,p.school,account,studentId,tel,name,sex')
            ->order('post_time','desc')
            ->page($pageNum,10)
//            ->where('id',$postId)
//            ->select();
        return $res;
    }
*/

    //设置头像
    public function setHeadPath($headLocalPath, $headPath, $account)
    {
        $user = User::get(['account' => $account]);
        $user->headPath = $headPath;
        $user->headLocalPath = $headLocalPath;
        return $user->save();
    }


    //获取头像本地储存地址
    public function getHeadLocalPath($account)
    {
        $user = User::get(['account' => $account]);
        return $user->headLocalPath;
    }

    //通过分页的方式获取用户
    public function getUsersByPage()
    {
        $user = new User();
        return $user->paginate(10);
    }

    //改密码
    public function setPassword($account, $password)
    {
        $user = User::get(['account' => $account]);
        return $user->save(['password' => $password]);
    }

    //获取用户数量
    public function getCount()
    {
        return db('user')->count();
    }

    //删除用户
    public function deleteByAccount($account)
    {
        $user = User::get(['account' => $account]);
        if (empty($user)) {
            return 0;
        } else {
            return $user->delete();
        }
    }

    //设置用户状态
    public function setStatus($account, $status)
    {
        $user = User::get(['account' => $account]);
        $user->isOnline = $status;
        $user->save();
    }
}