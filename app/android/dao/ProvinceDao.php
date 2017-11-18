<?php
/**
 * Created by PhpStorm.
 * User: lmy
 * Date: 2017/7/19
 * Time: 16:12
 */

namespace app\android\dao;


use app\android\model\Province;

class ProvinceDao
{
    /**获取所有省份
     * @return false|\PDOStatement|string|\think\Collection 省份
     */
    public function getProvinces()
    {
        $province = new Province();
        return $province->select();
    }

    /**
     * 获取对应省份的城市
     * @param $pId 省份id
     * @return mixed 对应省份的城市
     */
    public function getCities($pId)
    {
        $province = Province::get($pId);
        return $province->cities;
    }

    public function getUniversities($pId,$cId)
    {
        $province = Province::get($pId);
        return $province->universities()->where('university.c_id', $cId)->select();
    }
}