<?php
namespace App\Controllers;

use Twig\Loader\FilesystemLoader;
use Twig\Environment;
use Laminas\Diactoros\Response\HtmlResponse;

class BaseController {

    protected $twig;

    public function __construct() {
        // Configura Twig para que busque las vistas en el directorio 'templates'
        $loader = new FilesystemLoader(__DIR__ . '/../templates');
        $this->twig = new Environment($loader);
    }

    // Método para renderizar plantillas Twig
    protected function render($template, $params = []) {
        $content = $this->twig->render($template, $params);
        return new HtmlResponse($content);
    }
}


