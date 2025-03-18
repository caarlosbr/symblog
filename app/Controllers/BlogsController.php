<?php
namespace App\Controllers;

use App\Models\Blog;
use App\Models\Comment;
use App\Models\User;
use Respect\Validation\Validator as v;



class BlogsController extends BaseController
{   
    // Función para obtener los datos necesarios para la vista
    public function getData()
    {
        $blogs    = Blog::orderBy('created_at', 'desc')->get(); // Los blogs, desc para los más recientes
        $comments = Comment::orderBy('created_at', 'desc')->take(5)->get(); // Los 5 comentarios más recientes, take para limitar
        $tags     = Blog::distinct()->pluck('tags');   // Los tags, sin repetir, discinct() es para no repetir y pluck para obtener solo los tags

        // Retorna un array con los datos
        return [
            'blogs'    => $blogs,
            'comments' => $comments,
            'tags'     => $tags,
            'auth'     => $_SESSION['auth'] ?? false,
        ];
    }


    // Acción para la página principal
    public function indexAction()
    {
        $data = $this->getData(); // Obtiene los datos necesarios

        // Renderiza la vista 'index.twig' con los datos
        echo $this->twig->render('index_view.twig', [ 
            'data'    => $data,
            'profile' => $_SESSION['perfil'] ?? 'Invitado',
        ]);
    }


    // Acción para la página 'Acerca de'
    public function aboutAction($request)
    {
        $data = $this->getData(); // Obtiene los datos necesarios

        // Renderiza la vista 'about.twig' con los datos
        echo $this->twig->render('about.twig', [ 
            'data'    => $data,
            'profile' => $_SESSION['perfil'] ?? 'Invitado',
        ]);
    }

    // Acción para mostrar el formulario de agregar blog (GET) y procesarlo (POST)
    public function addBlogAction()
    {
        $profile = $_SESSION['perfil'] ?? 'Invitado'; // Perfil actual

        if (isset($_POST['submit'])) { // Si se envió el formulario
            $validation = v::key('title', v::notEmpty()) // Validador de campos, v es el validador, key para validar los campos y notEmpty para que no esten vacios
                ->key('tags', v::notEmpty())
                ->key('author', v::notEmpty())
                ->key('description', v::notEmpty());

            try {
                $validation->assert($_POST); // Aplica la validación

                $image = $_FILES['image']; // Obtiene la imagen
                if (empty($image['name'])) { // Si no se subió una imagen, se asigna una por defecto
                    $image['name'] = 'beach.jpg'; // Imagen por defecto
                }

                $blog = Blog::create([ // Crea un nuevo blog
                    'title' => $_POST['title'], // Campos del blog
                    'author' => $_POST['author'],
                    'blog' => $_POST['description'],
                    'image' => $image['name'],
                    'tags' => $_POST['tags'],
                ]);

                if ($image['error'] === UPLOAD_ERR_OK) { // Si se subió una imagen
                    $imageFileName = $image['name']; // Nombre del archivo
                    move_uploaded_file($image['tmp_name'], "../public/uploads/$imageFileName"); // Mueve la imagen a la carpeta de uploads
                    $blog->image = $imageFileName; // Asigna el nombre de la imagen al blog
                    $blog->save(); // Guarda el blog
                }

                header("Location: /");
                exit();
            } catch (\Exception $e) {
                $errors = $e->getMessage(); // Mensaje de error
                error_log("Error al agregar blog: " . $errors); // Log de errores

                echo $this->twig->render('addBlog.twig', [ // Renderiza la vista 'addBlog.twig' con los errores
                    'data'    => ['errors' => $errors], // Datos con los errores
                    'profile' => $profile, // Perfil actual
                ]);
                return; // Salimos de la función
            }
        }

        // GET: simplemente renderizamos la vista
        $data = $this->getData(); // Obtiene los datos necesarios 
        echo $this->twig->render('addBlog.twig', [ // Renderiza la vista 'addBlog.twig' con los datos
            'data'    => $data,
            'profile' => $profile,
        ]);
    }

    // Funcion para la pagina de contacto
    public function contactAction($request)
    {
        // Retorna o imprime la vista con 'profile'
        echo $this->twig->render('contact.twig', [ // Renderiza la vista 'contact.twig' con los datos
            'title'   => 'Contacto',
            'profile' => $_SESSION['perfil'] ?? 'Invitado',
        ]);
    }

    // Funcion para mostrar un blog en particular
    public function showAction($request) 
    {
        // Obtener datos generales
        $data = $this->getData();
    
        // Determinar el perfil actual (Invitado o usuario)
        $data['profile'] = $_SESSION['perfil'] ?? 'Invitado';
    
        // Recuperar el blogId de la query
        $blogId = $request->getQueryParams()['id'] ?? null; // getQueryParams para obtener los parametros de la query
        if (!$blogId) { // Si no hay blogId, redirigir a la página principal
            header("Location: /");
            exit();
        }
    
        // Buscar el blog por ID
        $blog = Blog::find($blogId);
        if (!$blog) {
            header("Location: /");
            exit();
        }
    
        // Sobrescribir 'comments' con los comentarios específicos de este blog
        $data['blog'] = $blog;
        $data['comments'] = $blog->comments;  // Sobrescribe los últimos 5 con los del blog actual
    
        // Renderizar la vista 'show.twig' con todo en $data
        echo $this->twig->render('show.twig', $data);
    }

    // Función para agregar un comentario
    public function addCommentAction($request)
    {
        // Validador: user, comment, blog_id no vacíos
        $validador = v::key('user', v::stringType()->notEmpty()) // Validador de campos, v es el validador, key para validar los campos y notEmpty para que no esten vacios
            ->key('comment', v::stringType()->notEmpty())
            ->key('blog_id', v::intVal()->positive());
    
        $profile = $_SESSION['perfil'] ?? 'Invitado'; // Perfil actual
    
        try {
            // Aplica la validación
            $validador->assert($request->getParsedBody()); // getParseBody para obtener los parametros del cuerpo de la peticion, assert para aplicar la validacion
    
            // Crea un nuevo comentario, aqui lo que estmos haciendo es crear un nuevo comentario, es como $_POST pero con el objeto request, getParseBody para obtener los parametros del cuerpo de la peticion, es decir los datos del formulario
            $comment = new Comment();
            $comment->user = $request->getParsedBody()['user'];  // Será "Anonimo" si es invitado
            $comment->comment = $request->getParsedBody()['comment'];
            $comment->blog_id = $request->getParsedBody()['blog_id'];
            $comment->save();
    
            // Redirección
            header("Location: /show?id=" . $request->getParsedBody()['blog_id']);
            exit;
        } catch (\Exception $e) {
            $error = "Error: " . $e->getMessage();
            echo $this->twig->render('show.twig', [
                'response' => $error,
                'profile'  => $profile,
            ]);
        }
    }
    
}

