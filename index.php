<?php 


    include "/home/luismec90/webapps/round/public/assets/libs/FaceDetector/FaceDetector.php";
        $input = "/home/luismec90/webapps/round/public/images/test.jpg";
        $face = new Face_Detector("/home/luismec90/webapps/round/public/assets/libs/FaceDetector/detection.dat");
        $face->face_detect($input);
		echo $face->toJpeg();
        return;


include "FaceDetector.php";
$input='img3.jpg';
$face = new Face_Detector('detection.dat');
$face->face_detect($input);

$out=convert($input,$face->getX(),$face->getY(),$face->getW());
header('Content-type: image/jpeg');
echo $out;
function convert($input,$X,$Y,$W){
	$size = getimagesize($input);
	$X=$X+$W/2;
	$X=$X/$size[0];
	$Y=$Y/$size[1];

	$image = new Imagick($input);
	$size = getimagesize($input);
	$image->resizeImage($size[0]/10,$size[1]/10,Imagick::FILTER_LANCZOS,1);
	$image->cropImage(200,200,$X*$size[0]/10-100,$Y*$size[1]/10-30);

	$image->setImageFormat("png");
	$image->roundCorners($size[0]/10,$size[1]/10);
	return $image;
}










