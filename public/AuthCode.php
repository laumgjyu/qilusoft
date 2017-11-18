<?php
/**
 * Created by PhpStorm.
 * User: lmy
 * Date: 2017/7/12
 * Time: 19:25
 */

session_start();

$image = imagecreatetruecolor(100, 30);
$bgcolor = imagecolorallocate($image, 255, 255, 255);
imagefill($image, 0, 0, $bgcolor);
/*
for ($i = 0; $i < 4; $i++){
    $fontSize = 6;
    $fontColor = imagecolorallocate($image, rand(0, 120), rand(0, 120), rand(0, 120));
    $fontContent = rand(0, 9);
    $x = ($i * 100 / 4) + rand(5, 10);
    $y = rand(0, 15);

    imagestring($image, $fontSize, $x, $y, $fontContent, $fontColor);
}
*/
$code = '';
for ($i=0;$i<4;$i++) {
    $data = 'abcdefghijkmnpqrstuvwxy3456789';

    $fontSize = 6;
    $fontColor = imagecolorallocate($image, rand(0, 120), rand(0, 120), rand(0, 120));
    $fontContent = substr($data, rand(0, strlen($data)), 1);

    $code .= $fontContent;

    $x = ($i * 100 / 4) + rand(5, 10);
    $y = rand(0, 15);

    imagestring($image, $fontSize, $x, $y, $fontContent, $fontColor);
}
$_SESSION['authCode'] = $code;

for ($i = 0; $i < 200; $i++) {
    $pointColor = imagecolorallocate($image, rand(50, 200), rand(50, 200), rand(50, 200));
    imagesetpixel($image, rand(1, 99), rand(1, 29), $pointColor);
}

for ($i = 0; $i < 4; $i++) {
    $lineColor = imagecolorallocate($image, rand(80, 220), rand(80, 220), rand(80, 220));
    imageline($image, rand(1, 99), rand(1, 29), rand(1, 99), rand(1,29), $lineColor);
}

//输出验证码之前一定要使用header
header('content-type: image/png');
imagepng($image);
imagedestroy($image);