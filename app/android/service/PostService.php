<?php
/**
 * Created by PhpStorm.
 * User: lmy
 * Date: 2017/7/8
 * Time: 22:53
 */

namespace app\android\service;


use app\android\dao\ImagePathDao;
use app\android\dao\PostDao;
use think\Db;

class PostService
{
    private $postDao;
    private $imageDao;

    public function __construct()
    {
        $this->postDao = new PostDao();
        $this->imageDao = new ImagePathDao();
    }

    #修改帖子内容
    public function editPostInfo($postId, $data,$images)
    {
        $status = $this->postDao->getStatus($postId);
        if ($status == 'solved') {
            return '已经关闭的帖子不能修改';
        }
        try {
            Db::startTrans();
            $this->imageDao->deleteImages($postId);
            $res = $this->postDao->editPostInfo($postId, $data);
            if ($images) {
                foreach ($images as $image) {
                    $info = $image->move(ROOT_PATH . 'public' . DS . 'images');
                    if ($info) {
                        $imageLocalPath = ROOT_PATH . 'public' . DS . 'images' . DS . $info->getSaveName();
                        $imagePath = 'http://' . $_SERVER['SERVER_NAME'] . ':' . $_SERVER["SERVER_PORT"] . '/' . 'images' . '/' . $info->getSaveName();
                        $this->postDao->addImagePath($postId, $imageLocalPath, $imagePath);
                    }
                }
            }
            if ($res != 0) {
                Db::commit();
                return '修改成功';
            } else {
                Db::rollback();
                return '修改失败';
            }
        } catch (Exception $exception) {
            Db::rollback();
            return $exception->getMessage();
        }
    }

    #删除帖子
    public function deletePost($postId)
    {
        try {
            Db::startTrans();
            $images = $this->imageDao->getImages($postId);
            $imagesArray = [];
            if (!empty($images))
                foreach ($images as $image)
                    if (is_file($image['imageLocalPath']))
                        $imagesArray[] = $image['imageLocalPath'];

            $res = $this->postDao->deletePostById($postId);
            if ($res != 0) {
                Db::commit();
                foreach ($imagesArray as $image) {
                    unlink($image);
                }
                return '删除成功';
            } else {
                Db::rollback();
                return '删除失败';
            }
        } catch (Exception $exception) {
            Db::rollback();
        }
    }

    #获取所有帖子
    public function getAllPosts($account)
    {
//        $totalPage=$this->postDao->getCount();
//        if ($pageNum<1)
//            $pageNum=1;
//        if ($pageNum>$totalPage)
//            $pageNum = $totalPage;
        $posts = $this->postDao->getAllPosts($account);
        if ($posts != null) {
            return $posts;
        } else {
            return '';
        }
    }

    #获取未修理的帖子
    public function getNotRepairedPosts($account)
    {
//        $totalPageNum = $this->postDao->getCount();
//        if ($pageNum<1)
//            $pageNum = 1;
//        if ($pageNum>$totalPageNum)
//            $pageNum = $totalPageNum;
        $res = $this->postDao->getNotRepairedPosts($account);
        if ($res != null) {
            return $res;
        } else {
            return '';
        }
    }

    #获取需要马上修理的帖子
    public function getUrgentPosts($account)
    {
//        $totalPageNum = $this->postDao->getCount();
//        if ($pageNum<1)
//            $pageNum = 1;
//        if ($pageNum>$totalPageNum)
//            $pageNum = $totalPageNum;
        $res = $this->postDao->getUrgentPosts($account);
        if ($res != null) {
            return $res;
        } else {
            return '';
        }
    }

    #查看不需要马上解决的tiezi
    public function getNotUrgentPosts($account)
    {
//        $totalPageNum = $this->postDao->getCount();
//        if ($pageNum<1)
//            $pageNum = 1;
//        if ($pageNum>$totalPageNum)
//            $pageNum = $totalPageNum;
        $res = $this->postDao->getNotUrgentPosts($account);
        if ($res != null) {
            return $res;
        } else {
            return '';
        }
    }

    #获取自己修复的报修
    public function getRepaired($account)
    {
//        $totalPageNum = $this->postDao->getCount();
//        if ($pageNum<1)
//            $pageNum = 1;
//        if ($pageNum>$totalPageNum)
//            $pageNum = $totalPageNum;
        $res = $this->postDao->getRepairedPosts($account);
        if ($res != null) {
            return $res;
        } else {
            return '';
        }
    }

    #修理
    public function repair($postId)
    {
        $status = $this->postDao->getStatus($postId);
        if ($status == 'wait') {
            $this->postDao->setRepair($postId);
            return '关闭成功';
        } elseif ($status == 'solved') {
            return '帖子已关闭';
        } else {
            return '不能关闭未申请维修的帖子';
        }

    }

    #申请维修
    public function applyToRepaire($postId, $account)
    {
        $this->postDao->setStatus($postId, $account);
        return '申请成功';
    }

    #获取维修人员已经申请的维修帖子
    public function getAppliedPosts($account)
    {
//        $totalPageNum = $this->postDao->getCount();
//        if ($pageNum < 1)
//            $pageNum = 1;
//        if ($pageNum > $totalPageNum)
//            $pageNum = $totalPageNum;
        $res = $this->postDao->getPostByRepairerAndStatus($account);
        if ($res != null) {
            return $res;
        } else {
            return '';
        }
    }

    //获取当前维修人员的所有帖子
    public function getPostsByOwn($account)
    {
//        $totalPageNum = $this->postDao->getCount();
//        if ($pageNum < 1)
//            $pageNum = 1;
//        if ($pageNum > $totalPageNum)
//            $pageNum = $totalPageNum;
        return $this->postDao->getPostsByRepairer($account);
    }

    public function getPosts()
    {
        return $this->postDao->getPosts();
    }

    public function getCount()
    {
        return $this->postDao->getCount();
    }

    //根据id获取具体帖子
    public function getPost($id)
    {
        return $this->postDao->getPostById($id);
    }

    #获取具体帖子的图片
    public function getPostImages($postId)
    {
        return $this->postDao->getImagePath($postId);
    }

}