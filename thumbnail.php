<?php
function return_bytes($val) {
   $val = trim($val);
   $last = strtolower($val{strlen($val)-1});
   switch($last) {
       case 'g':
           $val *= 1024;
       case 'm':
           $val *= 1024;
       case 'k':
           $val *= 1024;
   }
   return $val;
}
if ( return_bytes(ini_get('memory_limit')) < 20971520 ) ini_set('memory_limit', '20M');
$imginfo = getimagesize($_SERVER['DOCUMENT_ROOT'] . $_GET['src']);
$img = imagecreatefromjpeg($_SERVER['DOCUMENT_ROOT'] . $_GET['src']);
$thumbnail = imagecreatetruecolor($_GET['width'], $_GET['height']);
imagecopyresized($thumbnail, $img, 0, 0, 0, 0, $_GET['width'], $_GET['height'], $imginfo[0], $imginfo[1]);
header('Content-type: image/jpeg');
imagejpeg($thumbnail);