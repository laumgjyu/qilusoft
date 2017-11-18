<?php
/**
 * Created by PhpStorm.
 * User: lmy
 * Date: 2017/7/8
 * Time: 22:53
 */

namespace app\android\model;


use think\Model;

class Post extends Model
{
    public function user()
    {
        return $this->belongsTo('User');
    }

    public function replies()
    {
        return $this->hasMany('Reply', 'pid', 'id');
    }

    public function images()
    {
        return $this->hasMany('ImagePath', 'pr_id', 'id')->field('i_id,imagePath');
    }
}