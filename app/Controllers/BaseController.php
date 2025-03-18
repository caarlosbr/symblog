<?php
namespace App\Controllers;

use Twig\Loader\FilesystemLoader;
use Twig\Environment;
use Laminas\Diactoros\Response\HtmlResponse;

class BaseController {

    protected $twig; // Propiedad para almacenar el objeto de Twig

    public function __construct() {
        // Configura Twig para que busque las vistas en el directorio 'templates'
        $loader = new FilesystemLoader(__DIR__ . '/../templates'); // este loader sirve para ºcargar las plantillas y de donde se cargan
        $this->twig = new Environment($loader); 
    }

    // Método para renderizar plantillas Twig
    protected function render($template, $params = []) { // este metodo sirve para renderizar las plantillas
        $content = $this->twig->render($template, $params); // renderiza la plantilla
        return new HtmlResponse($content);  // retorna el contenido
    }
}


