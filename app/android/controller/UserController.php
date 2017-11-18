<?php
/**
 * Created by PhpStorm.
 * User: lmy
 * Date: 2017/7/7
 * Time: 14:25
 */

namespace app\android\controller;
session_start();

use app\android\service\PostService;
use app\android\service\ReplyService;
use app\android\service\ServicemanService;
use app\android\service\UserService;
use think\Controller;
use think\Request;

class UserController extends Controller
{
    private $userService;
    private $postService;
    private $servicemanService;
    private $replyService;

    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->userService = new UserService();
        $this->postService = new PostService();
        $this->servicemanService = new ServicemanService();
        $this->replyService = new ReplyService();
    }

    #登陆
    public function login()
    {
        $authCode = input('post.authCode');
        if ($_SESSION['authCode'] == $authCode) {
            $account = input('post.account');
            $password = input('post.password');
            $res = $this->userService->judge($account, $password);
            return json(['status' => $res]);
        } else {
            return json(['status' => '验证码错误']);
        }
    }

    #登出
    public function logout()
    {
        $account = input('get.account');
        $res = $this->userService->logout($account);
        return json(['status' => $res]);
    }

    #注册
    public function register()
    {
        $data = [
            'account' => input('post.account'),
            'password' => input('post.password'),
            'name' => input('post.name'),
            'sex' => input('post.sex'),
            'studentId' => input('post.studentId'),
            'idCard' => input('post.idCard'),
            'tel' => input('post.tel'),
            'school' => input('post.school'),
            'headPath' => null
        ];
        $res = $this->userService->register($data);
        return $this->userService->processHead($res,$data);
    }

    #找回密码
    public function forgetPassword()
    {
        $account = input('post.account');
        $newPassword = input('post.newPassword');
        $res=$this->userService->setPassword($account,$newPassword);
        if ($res == 0) {
            return json(['status' => '修改失败']);
        } else {
            return json(['status'=>'修改成功']);
        }

    }

    #修改个人信息
    public function modifyInfo()
    {
        $data = [
            'account' => input('post.account'),
            'name' => input('post.name'),
            'sex' => input('post.sex'),
            'tel' => input('post.tel'),
            'idCard' => input('post.idCard'),
            'studentId' => input('post.studentId'),
            'school' => input('post.school'),
        ];
        $res = $this->userService->modifyInfo($data);
//        return json(['status' => $res]);
        return $this->userService->processHead($res, $data,true);
    }

    #获取个人信息
    public function getInfo()
    {
        $account = input('get.account');
        $info = $this->userService->getInfo($account);
        return json($info);
    }

    #报修功能
    public function report()
    {
        $images = request()->file('image');
        $account = input('post.account');
        $theme = input('post.theme');
        $content = input('post.content');
        $urgent = input('post.urgent');
        $res = $this->userService->addPost($account, $theme, $content, $urgent, $images);
        return json(['status' => $res]);

    }

    #获取发布过的报修帖子
    public function getPosts()
    {
        $account = input('get.account');
        $res = $this->userService->getPosts($account);
        return json($res);
    }

    #获取某个报修帖子的内容
    public function getPostInfo()
    {
        $postId = input('get.id');
        $res = $this->userService->getPostInfo($postId);
        return json($res);
    }

    #获取具体帖子下的图片
    public function getPostImages(){
        $postId = input('get.id');
        $res = $this->postService->getPostImages($postId);
        return json($res);
    }


    #修改报修内容
    public function editPost()
    {
        $postId = input('post.id');
        $data = [
            'theme' => $theme = input('post.theme'),
            'content' => $content = input('post.content'),
            'last_modify_time' => date('Y-m-d H-m-s'),
            'urgent' => input('post.urgent')
        ];
        $images = request()->file('image');
        $res = $this->postService->editPostInfo($postId, $data,$images);
        return json(['status' => $res]);
    }

    #删除帖子
    public function deletePost()
    {
        $postId = input('get.id');
        $res = $this->postService->deletePost($postId);
        return json(['status' => $res]);
    }

    #维修完成之后关闭帖子
    public function repair()
    {
        $postId = input('get.id');
        $servicemanAccount = input('get.repairer');
        $res = $this->postService->repair($postId);
        if ($res == '关闭成功') {
            $this->servicemanService->addNum($servicemanAccount);
        }
        return json(['status' => $res]);
    }

    #评分
    public function setScore()
    {
        $score = input('get.score');
        $postId = input('get.id');
        $res = $this->servicemanService->setScore($score, $postId);
        return json(['status' => $res]);
    }

    #获取维修人信息
    public function getRepairerInfo()
    {
        $servicemanAccount = input('get.repairer');
        $res = $this->servicemanService->getInfo($servicemanAccount);
        return json($res);
    }

    #回复
    public function reply()
    {
        $postId = input('post.id');
        $content = input('post.content');
        $account = input('post.account');
        $res = $this->replyService->reply($postId, $account, $content, 'user');
        return json(['status' => $res]);
    }

    #查看所有回复
    public function getReplies()
    {
        $pid = input('get.id');
        $pageNum = input('get.pageNum');
        $res = $this->replyService->getReplies($pid,$pageNum);
        return json($res);
    }

}