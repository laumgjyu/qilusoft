<?php
/**
 * Created by PhpStorm.
 * User: lmy
 * Date: 2017/7/20
 * Time: 19:36
 */

namespace app\android\dao;


use app\android\model\ImagePath;

class ImagePathDao
{
    public function addImage($imageLocalPath,$imagePath)
    {
        $image = new ImagePath();
        return $image->save([
            'imageLocalPath' => $imageLocalPath,
            'imagePath' => $imagePath,
//            'pr_id'=>
        ]);
    }

    public function getImages($pid)
    {
        $images = ImagePath::all(['pr_id' => $pid]);
        return $images;
    }

    public function deleteImages($pid)
    {
        $res = ImagePath::destroy(['pr_id' => $pid]);
    }
}