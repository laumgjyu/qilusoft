<?php
/**
 * Created by PhpStorm.
 * User: lmy
 * Date: 2017/7/21
 * Time: 14:34
 */

namespace app\android\service;


use app\android\dao\ChatLogDao;

class ChatLogService
{
    private $chatLogDao;
    public function __construct()
    {
        $this->chatLogDao = new ChatLogDao();
    }

    //添加聊天记录
    public function saveChatLog($fromAccount, $toAccount, $message,$status=0)
    {
        $this->chatLogDao->saveChatLog($fromAccount, $toAccount, $message,$status);
    }

    /**
     * 获取聊天记录
     * @param $fromAccount 当前账号
     * @param $toAccount 目标账号
     * @param $page 当前页码
     * @return false|\PDOStatement|string|\think\Collection 返回查询到的数据
     */
    public function getChatLog($fromAccount, $toAccount,$page)
    {
        $totalRecords = $this->getCount($fromAccount, $toAccount);
        $totalPage = ($totalRecords + 10 - 1) / 10;
        if ($page<1)
            $page = 1;
        if ($page>$totalPage)
            $page = $totalPage;
        return $this->chatLogDao->getChatLog($fromAccount, $toAccount,$page);
    }

    /**
     * 获取符合条件的信息的数量
     * @param $fromAccount
     * @param $toAccount
     * @return int|string
     */
    public function getCount($fromAccount, $toAccount)
    {
        return $this->chatLogDao->getCount($fromAccount, $toAccount);
    }
}