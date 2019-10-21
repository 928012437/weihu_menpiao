<?php
use Grafika\Grafika;

require_once 'src/autoloader.php';

$file=$_POST['file'];

$editor = Grafika::createEditor();

$editor->open($image1 , $file); // 打开yanying.jpg并且存放到$image1
$editor->resizeExactWidth($image1 , 650);
$editor->save($image1 , $file);