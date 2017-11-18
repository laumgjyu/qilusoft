<?php
/**
 * Created by PhpStorm.
 * User: lmy
 * Date: 2017/7/19
 * Time: 15:58
 */

namespace app\android\model;


use think\Model;

class City extends Model
{
    public function universities()
    {
        return $this->hasMany('University', 'c_id', 'id');
    }
}