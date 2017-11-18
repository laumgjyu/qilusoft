<?php
/**
 * Created by PhpStorm.
 * User: lmy
 * Date: 2017/7/8
 * Time: 22:53
 */

namespace app\android\dao;


use app\android\model\Post;
use think\Db;

class PostDao
{
    private $servicemanDao;

    public function __construct()
    {
        $this->servicemanDao = new ServicemanDao();
    }

    #修改帖子内容
    public function editPostInfo($postId, $data)
    {
        $post = new Post();
        $res = $post->save($data, ['id' => $postId]);
        return $res;
    }

    #删除帖子
    public function deletePostById($postId)
    {
        $res = Post::destroy($postId);
        return $res;
    }


    #获取所有帖子
    public function getAllPosts($account)
    {
        $school = $this->servicemanDao->getSchool($account);
        $sameSchoolPost = Db::table('t_user')->where(['school' => $school])->buildSql();
        $posts = Db::table('t_post')
            ->alias('p')
            ->join([$sameSchoolPost => 'u'], 'p.user_id = u.id')
            ->field('p.id,theme,post_time,repair_time,last_modify_time,content,repairer,status,urgent,p.school,score,account,studentId,tel,name,sex')
            ->order('post_time', 'desc')
//            ->page($pageNum,10)
            ->select();
        return $posts;
    }

    //获取帖子内容
    public function getPostInfoById($postId)
    {
        $posts = Db::table('t_post')
            ->alias('p')
            ->join(['t_user' => 'u'], 'p.user_id = u.id')
            ->field('p.id,theme,post_time,repair_time,last_modify_time,content,repairer,status,urgent,p.school,score,account,studentId,tel,name,sex')
            ->where('p.id', $postId)
            ->select();
        return $posts;
    }

    #获取所有未解决的帖子
    public function getNotRepairedPosts($account)
    {
        $school = $this->servicemanDao->getSchool($account);
        $sameSchoolPost = Db::table('t_user')->where(['school' => $school])->buildSql();
        $posts = Db::table('t_post')
            ->alias('p')
            ->join([$sameSchoolPost => 'u'], 'p.user_id = u.id')
            ->field('p.id,theme,post_time,repair_time,last_modify_time,content,repairer,status,urgent,p.school,score,account,studentId,tel,name,sex')
            ->where('p.status', 'notSolved')
            ->order('post_time', 'desc')
//            ->page($pageNum,10)
            ->select();
        return $posts;
    }

    #获取需要马上修理的帖子
    public function getUrgentPosts($account)
    {
        $school = $this->servicemanDao->getSchool($account);
        $sameSchoolPost = Db::table('t_user')->where(['school' => $school])->buildSql();
        $posts = Db::table('t_post')
            ->alias('p')
            ->join([$sameSchoolPost => 'u'], 'p.user_id = u.id')
            ->field('p.id,theme,post_time,repair_time,last_modify_time,content,repairer,status,urgent,p.school,score,account,studentId,tel,name,sex')
            ->where('p.urgent', 'yes')
            ->where('status', 'notSolved')
            ->order('post_time', 'desc')
//            ->page($pageNum,10)
            ->select();
        return $posts;
    }

    #获取不需要马上解决的帖子
    public function getNotUrgentPosts($account)
    {
        $school = $this->servicemanDao->getSchool($account);
        $sameSchoolPost = Db::table('t_user')->where(['school' => $school])->buildSql();
        $posts = Db::table('t_post')
            ->alias('p')
            ->join([$sameSchoolPost => 'u'], 'p.user_id = u.id')
            ->field('p.id,theme,post_time,repair_time,last_modify_time,content,repairer,status,urgent,p.school,score,account,studentId,tel,name,sex')
            ->where('p.urgent', 'no')
            ->where('status', 'notSolved')
            ->order('post_time', 'desc')
//            ->whereOr('p.urgent',null)
//            ->page($pageNum,10)
            ->select();
        return $posts;
    }

    #获取由此维修人员解决的报修
    public function getRepairedPosts($account)
    {
        $school = $this->servicemanDao->getSchool($account);
        $sameSchoolPost = Db::table('t_user')->where(['school' => $school])->buildSql();
        $posts = Db::table('t_post')
            ->alias('p')
            ->join([$sameSchoolPost => 'u'], 'p.user_id = u.id')
            ->field('p.id,theme,post_time,repair_time,last_modify_time,content,repairer,status,urgent,p.school,score,account,studentId,tel,name,sex')
            ->where('p.repairer', $account)->where('status', 'solved')
            ->order('post_time', 'desc')
//            ->page($pageNum,10)
            ->select();
        return $posts;
    }

    #维修
    public function setRepair($postId)
    {
        $post = Post::get($postId);
        $post->repair_time = date('Y-m-d H:i:s');
        $post->status = 'solved';
        $res = $post->save();
        return $res;
    }

    #获取帖子状态
    public function getStatus($postId)
    {
        $post = Post::get($postId);
        dump($postId);
        dump($post);
        die();
        return $post->status;
    }

    #申请维修
    public function setStatus($postId, $account)
    {
        $post = Post::get($postId);
        $post->repairer = $account;
        $post->status = 'wait';
        $post->save();
    }

    public function getPostByRepairerAndStatus($account)
    {
        $school = $this->servicemanDao->getSchool($account);

        $sameSchoolPost = Db::table('t_user')->where(['school' => $school])->buildSql();
        $posts = Db::table('t_post')
            ->alias('p')
            ->join([$sameSchoolPost => 'u'], 'p.user_id = u.id')
            ->field('p.id,theme,post_time,repair_time,last_modify_time,content,repairer,status,urgent,p.school,score,account,studentId,tel,name,sex')
            ->where('p.repairer', $account)->where('status', 'wait')
            ->order('post_time', 'desc')
//            ->page($pageNum,10)
            ->select();
        return $posts;
    }

    #设置帖子以评分
    public function setScored($postId)
    {
        $post = Post::get($postId);
        $post->scored = 'yes';
        $post->save();
    }

    #获取是否已经评分
    public function getScored($postId)
    {
        $post = Post::get($postId);
        return $post->scored;
    }

    //根据id获取帖子
    public function getPostById($id)
    {
        $post = Post::get($id);
        return $post;
    }

//    给帖子添加图片路径
    public function addImagePath($postId, $imageLocalPath, $imagePath)
    {
        $post = $this->getPostById($postId);
        $post->images()->save([
            'imageLocalPath' => $imageLocalPath,
            'imagePath' => $imagePath
        ]);
    }

//获取指定帖子的图片
    public function getImagePath($postId)
    {
        $post = Post::get($postId);
        return $post->images;
    }

    //获取帖子数量
    public function getCount()
    {
        return db('post')->count();
    }

    //获取当前维修人员的帖子
    public function getPostsByRepairer($account)
    {
        $post = new Post();
        $post->paginate();
        return $post->where('repairer', $account)
            ->order('post_time', 'desc')
//            ->page($pageNum, 10)
            ->select();
    }

//获取所有报修信息
    public function getPosts()
    {
        $posts = Db::table('t_post')
            ->alias('p')
            ->join('t_user u', 'p.user_id = u.id')
            ->field('p.id,theme,post_time,repair_time,last_modify_time,content,repairer,status,urgent,p.school,score,account,studentId,tel,name,sex')
            ->order('post_time', 'desc')
            ->paginate(10);
        return $posts;

    }

    //设置报修评分
    public function setScore($id, $score)
    {
        $post = Post::get($id);
        $post->score = $score;
        $post->save();
    }

}