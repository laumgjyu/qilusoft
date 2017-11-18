<?php
/**
 * Created by PhpStorm.
 * User: lmy
 * Date: 2017/7/19
 * Time: 16:12
 */

namespace app\android\service;


use app\android\dao\ProvinceDao;

class ProvinceService
{
    private $provinceDao;

    /**
     * ProvinceService constructor.
     * @internal param $provinceDao
     */
    public function __construct()
    {
        $this->provinceDao = new ProvinceDao();
    }

    /**
     * 获取省份
     * @return false|\PDOStatement|string|\think\Collection 省份的集合
     */
    public function getProvicnes()
    {
        return $this->provinceDao->getProvinces();
    }

    /**
     * 获取对应省份的城市
     * @param $pId 省id
     * @return mixed 对应省份城市的集合
     */
    public function getCities($pId)
    {
        return $this->provinceDao->getCities($pId);
    }

    /**
     * 获取相应城市的大学
     */
    public function getUniversities($pId,$cId)
    {
        return $this->provinceDao->getUniversities($pId, $cId);
    }
}