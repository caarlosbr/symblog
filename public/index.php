<?php
session_start();
if (!isset($_SESSION['auth']) || $_SESSION['auth'] === false) {
  $_SESSION['auth'] = false;
  $_SESSION['perfil'] = 'Invitado';
} 

require('../vendor/autoload.php');
require "../bootstrap.php";

use App\Controllers\BlogsController;
use Aura\Router\RouterContainer;
use Laminas\Diactoros\ServerRequestFactory;
use App\Controllers\UsersController;

// Crear la petición a partir de las variables globales
$request = ServerRequestFactory::fromGlobals(
  $_SERVER,
  $_GET,
  $_POST,
  $_COOKIE,
  $_FILES
);

// Crear el contenedor del enrutador
$routerContainer = new RouterContainer();
$map = $routerContainer->getMap();

// Rutas funcionando de blogs
$map->get('home', '/', ['controller' => BlogsController::class, 'action' => "indexAction", 'auth' => false]);
$map->get('about', '/about', ['controller' => BlogsController::class, 'action' => 'aboutAction', 'auth' => false]);
$map->get('addblog', '/addblog', ['controller' => BlogsController::class, 'action' => 'addBlogAction', 'auth' => true]);
$map->post('saveBlog', '/addblog', ['controller' => BlogsController::class, 'action' => 'addBlogAction', 'auth' => true]);
$map->get('contact', '/contact', ['controller' => BlogsController::class, 'action' => 'contactAction', 'auth' => false]);
$map->get('show', '/show', ['controller' => BlogsController::class, 'action' => 'showAction', 'auth' => false]);
$map->post('AgregarComentario', '/postComment', ['controller' => BlogsController::class, 'action' => 'addCommentAction', 'auth' => false]);
$map->get('loginForm', '/login', ['controller' => UsersController::class, 'action' => 'loginFormAction','auth' => false]);
$map->post('login', '/login', ['controller' => UsersController::class,'action' => 'loginAction','auth' => false]);
$map->get('CerrarSesion', '/logout', ['controller' => UsersController::class, 'action' => 'cerrarSesion', 'auth' => true]);
$map->get("formuser", "/register", ['controller' => UsersController::class,'action' => 'registrar', 'auth' => false]);
$map->post("registrar", "/register", ['controller' => UsersController::class,'action' => 'registrar', 'auth' => false]);
$map->get("admin", "/admin", ['controller' => UsersController::class,'action' => 'adminAction', 'auth' => true]);





// Obtener el matcher de Aura Router y hacer coincidir la ruta
$matcher = $routerContainer->getMatcher();
$route = $matcher->match($request);

if (!$route) {
  error_log('Error de ruta: ' . $request->getMethod() . ' ' . $request->getUri()->getPath());
  echo 'Error de ruta';
} else {
  $handlerData = $route->handler;
  $controllerName = $handlerData['controller'];
  $actionName = $handlerData['action'];
  $needsAuth = $handlerData['auth'] ?? false;
  $sessionAuth = $_SESSION['auth'] ?? false;


  // Redirigir a login si la ruta requiere autenticación y el usuario no está autenticado
  if ($needsAuth && !$sessionAuth) {
    header('Location: /login');
    exit();
  }

  // Instanciar el controlador y llamar a la acción correspondiente
  $controller = new $controllerName;
  $response = $controller->$actionName($request);




  // Enviar las cabeceras HTTP de la respuesta
  if ($response instanceof \Psr\Http\Message\ResponseInterface) {
    foreach ($response->getHeaders() as $name => $values) {
        foreach ($values as $value) {
            header(sprintf("%s: %s", $name, $value), false);
        }
    }
    http_response_code($response->getStatusCode());
    echo $response->getBody();
}

}