<?php
namespace App\Controllers;

use App\Models\Blog;
use App\Models\Comment;
use App\Models\User;
use Respect\Validation\Validator as v;

class BlogsController extends BaseController
{
    // Acción para la página principal
    public function indexAction()
    {
        $data = $this->getData();

        // Pasa 'profile' a la vista (si no lo haces en BaseController)
        echo $this->twig->render('index_view.twig', [
            'data'    => $data,
            'profile' => $_SESSION['perfil'] ?? 'Invitado',
        ]);
    }

    public function getData()
    {
        $blogs    = Blog::orderBy('created_at', 'desc')->get();
        $comments = Comment::orderBy('created_at', 'desc')->take(5)->get();
        $tags     = Blog::distinct()->pluck('tags');

        return [
            'blogs'    => $blogs,
            'comments' => $comments,
            'tags'     => $tags,
            'auth'     => $_SESSION['auth'] ?? false,
        ];
    }

    // Acción "Acerca de"
    public function aboutAction($request)
    {
        $data = $this->getData();
        echo $this->twig->render('about.twig', [
            'data'    => $data,
            'profile' => $_SESSION['perfil'] ?? 'Invitado',
        ]);
    }

    // Acción para mostrar el formulario de agregar blog (GET) y procesarlo (POST)
    public function addBlogAction()
    {
        $profile = $_SESSION['perfil'] ?? 'Invitado';

        if (isset($_POST['submit'])) {
            $validation = v::key('title', v::notEmpty())
                ->key('tags', v::notEmpty())
                ->key('author', v::notEmpty())
                ->key('description', v::notEmpty());

            try {
                $validation->assert($_POST);

                $image = $_FILES['image'];
                if (empty($image['name'])) {
                    $image['name'] = 'beach.jpg';
                }

                $blog = Blog::create([
                    'title' => $_POST['title'],
                    'author' => $_POST['author'],
                    'blog' => $_POST['description'],
                    'image' => $image['name'],
                    'tags' => $_POST['tags'],
                ]);

                if ($image['error'] === UPLOAD_ERR_OK) {
                    $imageFileName = $image['name'];
                    move_uploaded_file($image['tmp_name'], "../public/uploads/$imageFileName");
                    $blog->image = $imageFileName;
                    $blog->save();
                }

                header("Location: /");
                exit();
            } catch (\Exception $e) {
                $errors = $e->getMessage();
                error_log("Error al agregar blog: " . $errors);

                echo $this->twig->render('addBlog.twig', [
                    'data'    => ['errors' => $errors],
                    'profile' => $profile,
                ]);
                return; // Salimos de la función
            }
        }

        // GET: simplemente renderizamos la vista
        $data = $this->getData();
        echo $this->twig->render('addBlog.twig', [
            'data'    => $data,
            'profile' => $profile,
        ]);
    }

    public function contactAction($request)
    {
        // Retorna o imprime la vista con 'profile'
        echo $this->twig->render('contact.twig', [
            'title'   => 'Contacto',
            'profile' => $_SESSION['perfil'] ?? 'Invitado',
        ]);
    }

    public function showAction($request)
    {
        // 1. Obtener datos generales
        $data = $this->getData();
    
        // 2. Determinar el perfil actual (Invitado o usuario)
        $data['profile'] = $_SESSION['perfil'] ?? 'Invitado';
    
        // 3. Recuperar el blogId de la query
        $blogId = $request->getQueryParams()['id'] ?? null;
        if (!$blogId) {
            header("Location: /");
            exit();
        }
    
        // 4. Buscar el blog por ID
        $blog = Blog::find($blogId);
        if (!$blog) {
            header("Location: /");
            exit();
        }
    
        // 5. Sobrescribir 'comments' con los comentarios específicos de este blog
        $data['blog'] = $blog;
        $data['comments'] = $blog->comments;  // Sobrescribe los últimos 5 con los del blog actual
    
        // 6. Renderizar la vista 'show.twig' con todo en $data
        echo $this->twig->render('show.twig', $data);
    }

    public function addCommentAction($request)
    {
        // Validador: user, comment, blog_id no vacíos
        $validador = v::key('user', v::stringType()->notEmpty())
            ->key('comment', v::stringType()->notEmpty())
            ->key('blog_id', v::intVal()->positive());
    
        $profile = $_SESSION['perfil'] ?? 'Invitado';
    
        try {
            // Aplica la validación
            $validador->assert($request->getParsedBody());
    
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

