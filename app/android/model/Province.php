<?php
/**
 * Created by PhpStorm.
 * User: lmy
 * Date: 2017/7/19
 * Time: 15:58
 */

namespace app\android\model;


use think\Model;

class Province extends Model
{
    public function cities()
    {
        return $this->hasMany('City','p_id','id');
    }

    public function universities()
    {
        //参数依次为，远程关联的模型名，中间模型名，中间模型的外键名，关联模型的外键名，当前模型的主键名
        return $this->hasManyThrough('University', 'City', 'p_id','c_id','id');
    }
}