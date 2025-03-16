<?php
namespace App\Controllers;

class PagesController extends BaseController{
    public function aboutAction(){
        $this->renderHTML(__DIR__ . '/../views/about_view.php');
    }


}