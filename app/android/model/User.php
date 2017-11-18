<?php
/**
 * Created by PhpStorm.
 * User: lmy
 * Date: 2017/7/7
 * Time: 14:42
 */

namespace app\android\model;


use think\Model;

class User extends Model
{
//    // 定义关联模型列表
//    protected $relationModel = ['Post'];
//    // 定义关联外键
//    protected $fk = 'user_id';
//    protected $mapFields = [
//        // 为混淆字段定义映射
//        'id'        =>  'User.id',
//        'post_id' =>  'Post.id',
//    ];
    public function posts()
    {
        return $this->hasMany('Post','user_id','id')->field('id,theme,post_time,repair_time,content,status,repairer,urgent');
    }
}