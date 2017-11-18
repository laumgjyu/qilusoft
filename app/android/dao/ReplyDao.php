<?php
/**
 * Created by PhpStorm.
 * User: lmy
 * Date: 2017/7/12
 * Time: 15:16
 */

namespace app\android\dao;


use app\android\model\Reply;

class ReplyDao
{
    public function addReply($id, $account, $content,$role)
    {
        $reply = new Reply();
        $reply->pid = $id;
        $reply->account = $account;
        $reply->content = $content;
        $reply->time = date('Y-m-d H:i:s');
        $reply->role = $role;
        return $reply->save();
    }

    #获取回复
    public function getReplies($pid,$pageNum)
    {
        $reply = new Reply();
        $res= $reply->where('pid', $pid)->order('time','asc')->page($pageNum,10)->select();
        return $res;
    }


    //获取回复数量
    public function getCount()
    {
        return db('reply')->count();
    }
}