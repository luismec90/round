<?php

class HomeController extends BaseController {
    /*
      |--------------------------------------------------------------------------
      | Default Home Controller
      |--------------------------------------------------------------------------
      |
      | You may wish to use controllers instead of, or in addition to, Closure
      | based routes. That's great! Here is an example controller method to
      | get you started. To route to this controller, just add the route:
      |
      |	Route::get('/', 'HomeController@showWelcome');
      |
     */

    public function showWelcome() {

        $imagen = new Imagick();
        $imagen->newPseudoImage(100, 100, "magick:rose");
        $imagen->setImageFormat("png");

        $imagen->roundCorners(5, 3);
        $imagen->writeImage("redondeada.png");
        //return View::make('hello');
    }

}
