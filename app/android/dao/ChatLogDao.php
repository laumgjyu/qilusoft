<?php
/**
 * Created by PhpStorm.
 * User: lmy
 * Date: 2017/7/21
 * Time: 14:34
 */

namespace app\android\dao;


use app\android\model\ChatLog;

class ChatLogDao
{

    //添加聊天记录
    public function saveChatLog($fromAccount, $toAccount, $message,$status)
    {
        $chatLog = new ChatLog();
        $chatLog->save([
            'send_time' => date('Y-m-d H:i:s'),
            'from' => $fromAccount,
            'to' => $toAccount,
            'message' => $message,
            'wait_send'=>$status
        ]);
    }

    //获取聊天记录
    public function getChatLog($fromAccount, $toAccount,$page)
    {
        $charLog = new ChatLog();
        return $charLog->where(['from' => $fromAccount, 'to', $toAccount])
            ->field('log_id,send_time,from,to,message')
            ->order('send_time','desc')
            ->page($page,10)
            ->select();
    }

    //获取符合条件的数目
    public function getCount($fromAccount, $toAccount)
    {
        return db('chat_log')->where(['from' => $fromAccount, 'to' => $toAccount])->count();

    }
}