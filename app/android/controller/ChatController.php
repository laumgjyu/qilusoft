<?php
//require_once './../../../vendor/workerman/gatewayclient/Gateway.php';
/**
 * Created by PhpStorm.
 * User: lmy
 * Date: 2017/7/21
 * Time: 12:48
 */

namespace app\android\controller;

use app\android\service\ChatLogService;
use GatewayClient\Gateway;
use think\Controller;
use think\Request;

class ChatController extends Controller
{
    private $chatLogService;

    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->chatLogService = new ChatLogService();
    }

    /**
     * 处理用户发来的信息
     * @internal param 发送者 $fromAccount
     * @internal param 接收者 $toAccount
     * @internal param 信息具体内容 $message
     */
    public function index()
    {
        $fromAccount = input('post.fromAccount');
        $toAccount = input('post.toAccount');
        $message = input('post.message');
        // 设置GatewayWorker服务的Register服务ip和端口，请根据实际情况改成实际值

        file_put_contents('./test.txt', $fromAccount . '---' . $toAccount . '---' . $message.'\n',FILE_APPEND);

        Gateway::$registerAddress = '127.0.0.1:1236';
        $jsonMessage = json_encode(['message'=>$message,'time'=>date('Y-m-d H:i:s')]);

        if (Gateway::isUidOnline($toAccount)) {
            //如果要发送信息的用户在线，则发送数据并且将数据插入到数据库
            $this->chatLogService->saveChatLog($fromAccount,$toAccount,$message);
            //向固定的用户发送消息
            Gateway::sendToUid($toAccount, $jsonMessage);
        } else {
            //如果用户不在线则将消息设置为待发送，存入数据库
            $this->chatLogService->saveChatLog($fromAccount, $toAccount, $message, 1);
        }

    }

    public function getChatLog()
    {
        $fromAccount = input('post.fromAccount');
        $toAccount = input('post.toAccount');
        $page = input('post.page');
        $res=$this->chatLogService->getChatLog($fromAccount, $toAccount,$page);
        return json($res);
    }
}