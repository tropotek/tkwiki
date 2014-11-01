<?php
header('Content-type: image/jpeg');

if (isset($_REQUEST['sid'])) {
    $sid = $_REQUEST['sid'];
}
session_start();
session_id($sid);

//$width = 80;
//$height = 30;
$width = 50;
$height = 24;

$my_image = imagecreatetruecolor($width, $height);
imagefill($my_image, 0, 0, 0xFFFFFF);
// add noise
for($c = 0; $c < 40; $c++) {
    $x = rand(0, $width - 1);
    $y = rand(0, $height - 1);
    imagesetpixel($my_image, $x, $y, 0x000000);
}

$x = rand(1, 10);
$y = rand(1, 10);

$rand_string = rand(1000, 9999);

imagestring($my_image, 5, $x, $y, $rand_string, 0x000000);
$idx = 0;
if (isset($_REQUEST['idx'])) {
    $idx = $_REQUEST['idx'];
}



$hash = md5($rand_string) . 'f33f';
if (isset($_SESSION['Dk_formImage'])) {
    $_SESSION[$idx] = $hash;
} else {
    $_SESSION['Dk_formImage'] = array($idx => $hash);
}

imagejpeg($my_image);
imagedestroy($my_image);
?>