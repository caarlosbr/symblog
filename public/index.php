<?php
/* session_start();
 */
// SI no hay una sesión iniciada, se crea una con el valor de autenticación en falso y con perfil de invitado
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
$map->get('home', '/', ['controller' => BlogsController::class, 'action' => "indexAction", 'auth' => false]);  // Ruta para la página principal
$map->get('about', '/about', ['controller' => BlogsController::class, 'action' => 'aboutAction', 'auth' => false]); // Ruta para la página 'Acerca de'
$map->get('addblog', '/addblog', ['controller' => BlogsController::class, 'action' => 'addBlogAction', 'auth' => true]); // Ruta para agregar un blog
$map->post('saveBlog', '/addblog', ['controller' => BlogsController::class, 'action' => 'addBlogAction', 'auth' => true]); // Ruta para guardar un blog
$map->get('contact', '/contact', ['controller' => BlogsController::class, 'action' => 'contactAction', 'auth' => false]); // Ruta para la página de contacto
$map->get('show', '/show', ['controller' => BlogsController::class, 'action' => 'showAction', 'auth' => false]); // Ruta para mostrar un blog
$map->post('AgregarComentario', '/postComment', ['controller' => BlogsController::class, 'action' => 'addCommentAction', 'auth' => false]); // Ruta para agregar un comentario
$map->get('loginForm', '/login', ['controller' => UsersController::class, 'action' => 'loginFormAction','auth' => false]); // Ruta para mostrar el formulario de login
$map->post('login', '/login', ['controller' => UsersController::class,'action' => 'loginAction','auth' => false]); // Ruta para procesar el login
$map->get('CerrarSesion', '/logout', ['controller' => UsersController::class, 'action' => 'cerrarSesion', 'auth' => true]);  // Ruta para cerrar sesión
$map->get("formuser", "/register", ['controller' => UsersController::class,'action' => 'registrar', 'auth' => false]); // Ruta para mostrar el formulario de registro
$map->post("registrar", "/register", ['controller' => UsersController::class,'action' => 'registrar', 'auth' => false]); // Ruta para procesar el registro
$map->get("admin", "/admin", ['controller' => UsersController::class,'action' => 'adminAction', 'auth' => true]); // Ruta para la página de administración



$matcher = $routerContainer->getMatcher(); // Crear el matcher, getMatcher() es para obtener el matcher
$route = $matcher->match($request);

if (!$route) {
  error_log('Error de ruta: ' . $request->getMethod() . ' ' . $request->getUri()->getPath());
  echo 'Error de ruta';
} else {
  $handlerData = $route->handler; // Obtener los datos del controlador y la acción
  $controllerName = $handlerData['controller']; // Obtener el nombre del controlador
  $actionName = $handlerData['action']; // Obtener el nombre de la acción
  $needsAuth = $handlerData['auth'] ?? false; // Obtener si la ruta requiere autenticación 
  $sessionAuth = $_SESSION['auth'] ?? false;  // Obtener si el usuario está autenticado


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