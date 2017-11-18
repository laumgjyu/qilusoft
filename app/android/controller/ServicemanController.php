<?php
/**
 * Created by PhpStorm.
 * User: lmy
 * Date: 2017/7/7
 * Time: 14:25
 */

namespace app\android\controller;

//session_start();

use app\android\service\PostService;
use app\android\service\ReplyService;
use app\android\service\ServicemanService;
use app\android\service\UserService;
use app\common\MD5Util;
use think\Controller;
use think\Request;

class ServicemanController extends Controller
{
    private $servicemanService;
    private $postService;
    private $userService;
    private $replyService;

    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->servicemanService = new ServicemanService();
        $this->postService = new PostService();
        $this->userService = new UserService();
        $this->replyService = new ReplyService();
    }

    #登陆
    public function login()
    {
//        $authCode = input('post.authCode');
//        if ($_SESSION['authCode'] == $authCode) {

        $account = input('post.account');
        $password = input('post.password');
        $res = $this->servicemanService->judge($account, MD5Util::encode($password));
        return json(['status' => $res]);
//        } else {
//            return json(['status' => '验证码错误']);
//        }
    }

    #注册
    public function register()
    {
        $data = [
            'account' => input('post.account'),
            'password' => input('post.password'),
            'name' => input('post.name'),
            'sex' => input('post.sex'),
            'idCard' => input('post.idCard'),
            'tel' => input('post.tel'),
            'address' => input('post.address'),
            'school' => input('post.school'),
            'headPath' => null
        ];
        $res = $this->servicemanService->register($data);
        return $this->servicemanService->processHead($res, $data);
    }

    #登出
    public function logout()
    {
        $account = input('get.account');

        $res = $this->servicemanService->logout($account);
        return json(['status' => $res]);
    }

    #获取用户信息
    public function getInfo()
    {
        $account = input('get.account');
        $res = $this->servicemanService->getInfo($account);
        return json($res);
    }

    #修改用户信息
    public function modifyInfo()
    {
        $account = input('post.account');
        $data = [
            'name' => input('post.name'),
            'sex' => input('post.sex'),
            'tel' => input('post.tel'),
            'school' => input('post.school'),
            'address' => input('post.address'),
        ];
        if (empty($account)) {
            $res = '用户名不能为空';
            return json(['status' => $res]);
        } else {
            $res = $this->servicemanService->modifyInfo($account, $data);
            return $this->servicemanService->processHead($res, $data, true);
        }

    }

    #忘记密码
    public function forgetPassword()
    {
        $account = input('post.account');
        $newPassword = input('post.newPassword');
        $res = $this->servicemanService->setPassword($account, $newPassword);
        if ($res == 0) {
            return json(['status' => '修改失败']);
        } else {
            return json(['status' => '修改成功']);
        }

    }

    #获取所有报修信息
    public function getPosts()
    {
//        $school = input('post.school');
//        $pageNum = input('post.pageNum');
        $account = input('get.account');
        $res = $this->postService->getAllPosts($account);
        return json($res);
    }

    //获取具体帖子的详细信息
    public function getPostInfo($id)
    {
        return json($this->userService->getPostInfo($id));
    }

    #查看未解决的保修信息
    public function getNotRepairedPosts()
    {
//        $school = input('post.school');
//        $pageNum = input('post.pageNum');
        $account = input('get.account');
        $res = $this->postService->getNotRepairedPosts($account);
        return json($res);

    }

    #查看需要马上解决的保修信息
    public function getUrgentPosts()
    {
//        $school = input('post.school');
//        $pageNum = input('post.pageNum');
        $account = input('get.account');
        $res = $this->postService->getUrgentPosts($account);

        return json($res);
    }

    #查看不需要马上解决的保修信息
    public function getNotUrgentPosts()
    {
//        $school = input('post.school');
//        $pageNum = input('post.pageNum');
        $account = input('get.account');
        $res = $this->postService->getNotUrgentPosts($account);
        return json($res);
    }

    #查看自己解决的问题
    public function getRepaired()
    {
        $account = input('get.account');
//        $school = input('post.school');
//        $pageNum = input('post.pageNum');
        $res = $this->postService->getRepaired($account);
        return json($res);
    }

    #申请维修
    public function applyToRepair()
    {
        $account = input('get.account');
        $postId = input('get.id');
        $this->postService->applyToRepaire($postId, $account);
        return json(['status' => '申请成功']);
    }

    #查看自己已经申请的维修信息
    public function getAppliedPosts()
    {
        $account = input('get.account');
//        $school = input('post.school');
//        $pageNum = input('post.pageNum');
        $res = $this->postService->getAppliedPosts($account);
        return json($res);
    }

    //查看自己已经接的所有任务
    public function getPostsByOwn($account)
    {
        $res = $this->postService->getPostsByOwn($account);
        return json($res);
    }

    //完成任务
    public function complete()
    {
        $postId = input('get.id');
        $account = input('get.account');
        $res = $this->postService->repair($postId);
        if ($res == '关闭成功') {
            $this->servicemanService->addNum($account);
        }
        return json(['status' => $res]);
    }


    #回复
    public function reply()
    {
        $postId = input('post.id');
        $content = input('post.content');
        $account = input('post.account');
        $res = $this->replyService->reply($postId, $account, $content, 'serviceman');
        return json(['status' => $res]);
    }

    #查看所有回复
    public function getReplies()
    {
        $pid = input('get.id');
        $pageNum = input('get.pageNum');
        $res = $this->replyService->getReplies($pid, $pageNum);
        return json($res);
    }

}