<?php
/**
 * Created by PhpStorm.
 * User: lmy
 * Date: 2017/7/22
 * Time: 17:02
 */

namespace app\admin\controller;


use app\admin\service\AdminService;
use app\android\service\ImagePathService;
use app\android\service\PostService;
use app\android\service\ReplyService;
use app\android\service\ServicemanService;
use app\android\service\UserService;
use app\common\MD5Util;
use think\Controller;
use think\Hook;
use think\Request;
use think\Session;

class Admin extends Controller
{
    private $adminService;
    private $userService;
    private $servicemanService;
    private $postService;
    private $replyService;
    private $imagePathService;

    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->adminService = new AdminService();
        $this->userService = new UserService();
        $this->servicemanService = new ServicemanService();
        $this->postService = new PostService();
        $this->replyService = new ReplyService();
        $this->imagePathService = new ImagePathService();
    }

    /**
     * 起始页
     * @return mixed
     */
    public function index()
    {
        if (empty(Session::get('admin'))) {
            $this->assign('message', '');
            $this->assign('username', '用户名');
            return $this->fetch('index');
        } else {
            $this->assign('message', '');
            return $this->fetch('success');
        }
    }

    /**
     * 用户登录
     * @param string $username
     * @param string $password
     * @return mixed
     */
    public function login($username = '', $password = '')
    {
        Hook::listen('testLogin');
        $message = $this->adminService->login($username, MD5Util::encode($password));
        if ($message == 'success') {
            Session::set('admin', $username);
            return $this->loginSuccess();
        }else{
            $this->assign('message', '用户名或密码错误！');
            $this->assign('username', $username);
            return $this->fetch('index');
        }
    }

    /**
     * 登陆成功
     * @return mixed
     */
    public function loginSuccess()
    {
        Hook::listen('testLogin');
        return $this->fetch('success');
    }

    /**
     * 用户等登出
     * @return mixed
     */
    public function logout()
    {
        Session::delete('admin');
        return $this->index();
    }

    /**
     * 列出所有维修人员
     * @return mixed
     */
    public function listServicemen()
    {
        Hook::listen('test:Login');
        $servicemen = $this->adminService->listServicemen();
        $servicemanNum = $this->servicemanService->getCount();
        $this->assign('list', $servicemen);
        $this->assign('count', $servicemanNum);
        return $this->fetch('listServicemen');
    }

    /**
     * 列出所有用户
     * @return mixed
     */
    public function listUsers()
    {
        Hook::listen('testLogin');
        $users = $this->adminService->listUsers();
        $count = $this->userService->getCount();
        $this->assign('list', $users);
        $this->assign('count', $count);
        return $this->fetch('listUsers');
    }

    public function changePassword()
    {
        Hook::listen('testLogin');
        $status = input('get.status');
        if ($status == '123') {
            $username = input('post.username');
            $oldPassword = input('post.old');
            $newPassword = input('post.new');
            $newPasswordAgain = input('post.newAgain');
            if ($newPassword != $newPasswordAgain) {
                $this->assign('message', '两次输入的密码不一致！');
                return $this->fetch('changePassword');
            }
            $res = $this->adminService->changePassword($username, $oldPassword, $newPassword);
            $this->assign('message', $res);
            return $this->fetch('changePassword');
        } else {
            $this->assign('message', '');
            return $this->fetch('changePassword');
        }
    }

    public function resetPassword()
    {
        Hook::listen('testLogin');
        $account = input('get.account');
        $identity = input('get.identity');
        if ($identity == 'user') {
            $this->userService->resetPassword($account);
            $this->success('操作成功', '/admin/listUsers');
        }
        if ($identity == 'serviceman') {
            $this->servicemanService->resetPassword($account);
            $this->success('操作成功', '/admin/listServicemen');
        }
    }

    public function manageServicemen()
    {
        Hook::listen('testLogin');
        $status = input('get.status');
        $account = input('get.account');
        if ($status == '1' && !empty($account)) {
            $serviceman = $this->servicemanService->getInfo($account);
            if (empty($serviceman))
                $this->error('参数错误！');
            $this->assign('serviceman', $serviceman);
            $this->assign('message', '');
            return $this->fetch('modifyServiceman');
        } else {
            $servicemen = $this->adminService->listServicemen();
            $servicemanNum = $this->servicemanService->getCount();
            $this->assign('list', $servicemen);
            $this->assign('count', $servicemanNum);
            return $this->fetch('manageServicemen');
        }
    }

    public function manageUsers()
    {
        Hook::listen('testLogin');
        $status = input('get.status');
        $account = input('get.account');
        if ($status == '1' && !empty($account)) {
            $user = $this->userService->getInfo($account);
            if (empty($user))
                $this->error('参数错误！');
            $this->assign('user', $user);
            $this->assign('message', '');
            return $this->fetch('modifyUser');
        } else {
            $users = $this->adminService->listUsers();
            $count = $this->userService->getCount();
            $this->assign('list', $users);
            $this->assign('count', $count);
            return $this->fetch('manageUsers');
        }
    }

    public function submitModify($identity, $account, $name, $sex, $idCard, $tel, $school)
    {
        Hook::listen('testLogin');
        $data = [
            'account' => $account,
            'name' => $name,
            'sex' => $sex,
            'idCard' => $idCard,
            'tel' => $tel,
            'school' => $school
        ];
        if ($identity == 'user') {
            $studentId = input('post.studentId');
            $data['studentId'] = $studentId;
            $res = $this->userService->modifyInfo($data);
            if ($res != 1)
                $this->assign('message', '修改失败');
            else
                $this->assign('message', '修改成功');
            $list = $this->adminService->listUsers();
            $count = $this->userService->getCount();
            $this->assign('count', $count);
            $this->assign('list', $list);
            return $this->fetch('manageUsers');
        }
        if ($identity == 'serviceman') {
            $res = $this->servicemanService->modifyInfo($data['account'], $data);
            if ($res != 1)
                $this->assign('message', '修改失败');
            else
                $this->assign('message', '修改成功');
            $list = $this->adminService->listServicemen();
            $count = $this->servicemanService->getCount();
            $this->assign('count', $count);
            $this->assign('list', $list);
            return $this->fetch('manageServicemen');
        }
        $this->error('参数错误！');
    }

    public function delete($account, $code)
    {
        Hook::listen('testLogin');
        if ($code == '1') {
            //维修人员
            $this->servicemanService->delete($account);
            $this->success('操作成功', '/admin/manageServicemen');
        } elseif ($code == '2') {
            //用户
            $this->userService->delete($account);
            $this->success('操作成功', '/admin/manageUsers');
        } else {
            $this->error("参数错误");
        }
    }

    public function ban($account,$identity)
    {
        Hook::listen('testLogin');
        if ($identity == 'user') {
            $this->userService->setStatus($account,'banned');
            $this->success('操作成功','/admin/manageUsers');
        } elseif ($identity == 'serviceman') {
            $this->servicemanService->setStatus($account, 'banned');
            $this->success('操作成功','/admin/manageServicemen');
        } else {
            $this->error('参数错误');
        }
    }

    public function rmBan($account,$identity)
    {
        Hook::listen('testLogin');
        if ($identity == 'user') {
            $this->userService->setStatus($account,'false');
            $this->success('操作成功','/admin/manageUsers');
        } elseif ($identity == 'serviceman') {
            $this->servicemanService->setStatus($account, 'false');
            $this->success('操作成功','/admin/manageServicemen');
        } else {
            $this->error('参数错误');
        }
    }

    public function listPosts()
    {
        Hook::listen('testLogin');
        $posts = $this->postService->getPosts();
        $count = $this->postService->getCount();
        $this->assign('count', $count);
        $this->assign('list', $posts);
        return $this->fetch('listPosts');
    }

    public function postInfo($id,$pageNum)
    {
        $post = $this->postService->getPost($id);
        $replies = $this->replyService->getReplies($id, $pageNum);
        $images = $this->imagePathService->getImages($id);
        $this->assign('post', $post);
        $this->assign('replies', $replies);
        $this->assign('images', $images);
        return $this->fetch('postInfo');
    }

    public function deletePost($id)
    {
        $this->postService->deletePost($id);
        $this->success('操作成功', '/admin/listPosts');
    }

}