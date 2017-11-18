<?php
/**
 * Created by PhpStorm.
 * User: lmy
 * Date: 2017/7/12
 * Time: 15:16
 */

namespace app\android\service;


use app\android\dao\ReplyDao;

class ReplyService
{
    private $replyDao;
    public function __construct()
    {
        $this->replyDao = new ReplyDao();
    }

    #回复
    public function reply($id, $account, $content,$role)
    {
        $res=$this->replyDao->addReply($id, $account, $content,$role);
        if ($res != 0) {
            return '回复成功';
        } else {
            return '回复失败';
        }
    }

    public function getReplies($pid,$pageNum)
    {
        $totalPageNum = $this->replyDao->getCount();
        if ($pageNum<1)
            $pageNum = 1;
        if ($pageNum>$totalPageNum)
            $pageNum = $totalPageNum;
        $res = $this->replyDao->getReplies($pid,$pageNum);
        return $res;
    }
}