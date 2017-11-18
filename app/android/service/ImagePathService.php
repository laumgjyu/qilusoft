<?php
/**
 * Created by PhpStorm.
 * User: lmy
 * Date: 2017/7/25
 * Time: 11:00
 */

namespace app\android\service;


use app\android\dao\ImagePathDao;

class ImagePathService
{
    private $imagePathDao;
    public function __construct()
    {
        $this->imagePathDao = new ImagePathDao();
    }

    public function getImages($pid)
    {
        return $this->imagePathDao->getImages($pid);
    }
}