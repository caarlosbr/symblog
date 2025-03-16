<?php
namespace App\Controllers;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use App\Models\Blog;
use App\Models\Comment;
use Respect\Validation\Validator as v;
class IndexController extends BaseController
{
    public function indexAction()
    {
        $blogs = Blog::getAll();
        $this->renderHTML('index_view.twig', ['blogs' => $blogs]);
    }
}