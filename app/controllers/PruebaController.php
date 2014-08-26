<?php

class PruebaController extends \BaseController {

    public function index() {
        include_once "FaceDetector.php";
        
        $input = "testImages/img5.jpg";
        
        $detector = new svay\FaceDetector('detection.dat');
        $detector->faceDetect($input);
        $detector->toJpeg();
    }

}
