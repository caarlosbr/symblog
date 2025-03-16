<?php
namespace App\Controllers;

use Laminas\Diactoros\Response\HtmlResponse;
use Respect\Validation\Validator as v;
use App\Models\User;
use App\Models\Blog;
use App\Models\Comment;

class UsersController extends BaseController
{
    // Obtiene datos comunes (blogs, comments, tags)
    public function getData()
    {
        $blogs    = Blog::orderBy('created_at', 'desc')->get();
        $comments = Comment::orderBy('created_at', 'desc')->take(5)->get();
        $tags     = Blog::distinct()->pluck('tags');

        // Si hay userId en sesión, busca el usuario
        $user = null;
        if (isset($_SESSION['userId'])) {
            $user = User::find($_SESSION['userId']);
        }

        return [
            'blogs'    => $blogs,
            'comments' => $comments,
            'tags'     => $tags,
            'auth'     => $_SESSION['auth']  ?? false,
            'user'     => $user,
        ];
    }

    // Muestra el formulario de login
    public function loginFormAction($request)
    {
        // Pasamos 'profile' (y otros datos si quieres)
        return $this->render('login.twig', [
            'title'   => 'Iniciar Sesión',
            'profile' => $_SESSION['perfil'] ?? 'Invitado'
        ]);
    }

    // Procesa el formulario de login
    public function loginAction($reqMethod)
    {
        // Array para guardar datos de la vista
        $data = [];

        if ($reqMethod->getMethod() == 'POST') {
            $validator = v::key('email', v::stringType()->notEmpty())
                         ->key('password', v::stringType()->notEmpty());
            try {
                $validator->assert($reqMethod->getParsedBody());
                $user = User::where('email', $reqMethod->getParsedBody()['email'])->first();

                if ($user && $reqMethod->getParsedBody()['password'] === $user->password) {
                    $_SESSION['user']   = $user->name; 
                    $_SESSION['auth']   = true;
                    $_SESSION['perfil'] = 'usuario';  // o 'admin', según tu lógica

                    // Podrías guardar un ID de usuario si lo deseas:
                    // $_SESSION['userId'] = $user->id;

                    header('Location: /');
                    exit();
                } else {
                    $data['error'] = 'Usuario o contraseña incorrectos';
                }

            } catch (\Exception $e) {
                $data['error'] = 'Error: ' . $e->getMessage();
            }
        } else {
            // Si no es POST, solo renderizamos el formulario de login
            return $this->render('login.twig', [
                'profile' => $_SESSION['perfil'] ?? 'Invitado'
            ]);
        }

        // Renderizamos de nuevo el login con error (si lo hay)
        return $this->render('login.twig', [
            'data' => [
                'error' => $data['error'] ?? null,
                'auth'  => $_SESSION['auth']  ?? false,
                'user'  => $_SESSION['user']  ?? null,
            ],
            'profile' => $_SESSION['perfil'] ?? 'Invitado'
        ]);
    }

    public function cerrarSesion()
    {
        // Cierra la sesión y redirige
        session_start();
        session_destroy();
        session_abort();
        session_unset();
        header('Location: /');
        exit();
    }

    public function registrar($request)
    {
        if ($request->getMethod() == "POST") {
            $validador = v::key('user', v::stringType()->notEmpty())
                          ->key('password', v::stringType()->notEmpty())
                          ->key('email', v::email()->notEmpty());

            try {
                $validador->assert($request->getParsedBody());

                // Crear un nuevo objeto de Usuario
                $user = new User(); 
                $user->nombre   = $request->getParsedBody()['nombre'];
                $user->user     = $request->getParsedBody()['user'];
                $user->password = $request->getParsedBody()['password'];
                $user->email    = $request->getParsedBody()['email'];
                $user->perfil   = 'admin';  // O 'usuario', según tu necesidad

                $user->save();

                header("Location: /login");
                exit;
            } catch (\Exception $e) {
                $response = "Error: " . $e->getMessage();
            }
        }

        // Renderizar la vista de registro
        return $this->render("register.twig", [
            'response' => $response ?? "",
            'profile'  => $_SESSION['perfil'] ?? 'Invitado'
        ]);
    }

    public function adminAction()
    {
        // Verificamos si está autenticado
        if ($_SESSION['auth'] == true) {
            $data = $this->getData();
            echo $this->twig->render('admin.twig', [
                'data'    => $data,
                'profile' => $_SESSION['perfil'] ?? 'Invitado'
            ]);
        } else {
            header('Location: /');
            exit();
        }
    }
}
