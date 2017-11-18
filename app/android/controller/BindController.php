<?php
/**
 * Created by PhpStorm.
 * User: lmy
 * Date: 2017/7/21
 * Time: 13:10
 */

namespace app\android\controller;


use GatewayClient\Gateway;
use PDO;
use think\Controller;

class BindController extends Controller
{
    /**
     * 将登陆的用户的唯一的client_id与登陆用户的account绑定
     * @internal param 登陆用户的账号 $account
     * @internal param 用户链接websocket之后的唯一id标识 $client_id
     * @internal param 用户身份标识 $identity
     */
    public function index()
    {
        $account = input('post.account');
        $client_id = input('post.client_id');
        $identity = input('post.identity');
        //注册gateway地址
        Gateway::$registerAddress = '127.0.0.1:1236';

        //将用户socket的唯一标识和用户账号绑定
        Gateway::bindUid($client_id, $account);

        if ($identity == 'serviceman') {
            //将用户加入群组
            Gateway::joinGroup($client_id, 'serviceman');
        }
        if ($identity == 'user') {
            Gateway::joinGroup($client_id, 'user');
        }

        $db = new PDO('mysql:host=localhost;dbname=db_qilusoft','root','123456');
        $rs = $db->query('SELECT * FROM t_chat_log WHERE wait_send=1 AND `to`='.$account);
        $result = $rs->fetchAll();
        if (!empty($result)) {
            foreach ($result as $res) {
                $jsonMessage = json_encode(['fromAccount'=>$res['fromAccount'],'message'=>$res['message'],'time'=>$res['send_time']]);
                Gateway::sendToUid($res['to'], $jsonMessage);
                $db->exec("UPDATE t_chat_log SET wait_send='0' WHERE log_id=".$res['log_id']);
            }

        }
        $db = null;

        file_put_contents('/var/www/think/test2.txt', $account . '---' . $client_id . '---' . $identity.'\n',FILE_APPEND);
    }
}