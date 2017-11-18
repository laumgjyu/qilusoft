<?php
/**
 * Created by PhpStorm.
 * User: lmy
 * Date: 2017/7/19
 * Time: 16:06
 */

namespace app\android\controller;

use app\android\service\ProvinceService;
use think\Controller;
use think\Request;

class UniversityController extends Controller
{
    private $provinceService;
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->provinceService = new ProvinceService();
    }

    #获取所有省份
    public function getProvinces()
    {
        $res=$this->provinceService->getProvicnes();
        return json($res);
    }

    /**
     * 获取相应省份的所有市区
     * @return \think\response\Json
     * @internal param 省份id $pId
     */
    public function getCities()
    {
        $pId = input('get.pId');
        $res=$this->provinceService->getCities($pId);
        return json($res);
    }

    /**
     * 获取相应城市的大学
     * @return \think\Collection|\think\response\Json 大学的集合
     * @internal param 对应省份id $pId
     * @internal param 对应城市id $cId
     */
    public function getUniversities()
    {
        $pId = input('get.pId');
        $cId = input('get.cId');
        $res=$this->provinceService->getUniversities($pId, $cId);
        return json($res);
    }
}