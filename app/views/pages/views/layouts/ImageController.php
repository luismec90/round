<?php

class ImageController extends \BaseController {

    public function index() {
        $json["files"] = array();
        $images = File::files('images');
        foreach ($images as $row) {
            $nombre = explode("/", $row);
            $nombre = $nombre[1];
            array_push($json["files"], array(
                "deleteType" => "DELETE",
                "deleteUrl" => URL::to("deleteImage/$nombre"),
                "downloadUrl" => URL::to("downloadImage/$nombre"),
                "name" => "$nombre",
                "thumbnailUrl" => URL::asset("images/$nombre"),
                "originalImage" => URL::asset("originalImages/$nombre"),
                "url" => URL::asset("images/$nombre")));
        }
        return Response::json($json);
    }

    public function process() {
        include_once "FaceDetector.php";

        $json["files"] = array();

        if (Input::hasFile('files')) {
            foreach (Input::file('files') as $file) {
                $nombreCompleto = $file->getClientOriginalName();
                $file->move("images", $nombreCompleto);
                $nombre = explode(".", $nombreCompleto);
                $nombre = $nombre[0];
                $input = "images/$nombreCompleto";

                $input = "images/$nombreCompleto";

                $img = Image::make($input)->widen(400)->encode('png');
                $img->save("originalImages/$nombre.png");

                $size = getimagesize($input);

                $detector = new svay\FaceDetector('detection.dat');
                $detector->faceDetect($input);

                $X = $detector->getX();
                $Y = $detector->getY();
                $W = $detector->getW();


                $X = $X + $W / 2;
                $X = $X / $size[0];
                $Y = $Y / $size[1];

                $image = new Imagick($input);
                unlink($input);
                $image->resizeImage($size[0] / 10, $size[1] / 10, Imagick::FILTER_LANCZOS, 1);
                $image->cropImage(200, 200, $X * $size[0] / 10 - 100, $Y * $size[1] / 10 - 30);
                $image->roundCorners($size[0] / 10, $size[1] / 10);
                $image->setImageFormat("png");
                $nombre = "$nombre.png";
                $image->writeImage("images/$nombre");



                array_push($json["files"], array(
                    "deleteType" => "DELETE",
                    "deleteUrl" => URL::to("deleteImage/$nombre"),
                    "downloadUrl" => URL::to("downloadImage/$nombre"),
                    "name" => "asd.jpg",
                    "thumbnailUrl" => URL::asset("images/$nombre"),
                    "originalImage" => URL::asset("originalImages/$nombre"),
                    "url" => URL::asset("images/$nombre")));
            }
        }
        return Response::json($json);
    }

    public function fix() {
        $nombreImagen = Input::get('nombreImagen');
        $x = Input::get('x');
        $y = Input::get('y');
        $w = Input::get('w');


        $input = "originalImage/$nombreImagen";
        $image = new Imagick($input);
        $image->cropImage($w, $w, $x, $y);
        $image->roundCorners($w, $w);
        $image->writeImage("images/$nombreImagen");
    }

    public function download($image) {
        return Response::download("images/$image");
    }

    public function delete($image) {
        $filename1 = "images/$image";
        if (File::exists($filename1)) {
            File::delete($filename1);
        }

        $filename2 = "originalImages/$image";
        if (File::exists($filename2)) {
            File::delete($filename2);
        }
        return Response::json(array($image => true));
    }

}
