<?php

include "FaceDetector.php";

$face_detect = new Face_Detector('detection.dat');
$face_detect->face_detect('img3.jpg');
echo $face_detect->toJson();

?>
