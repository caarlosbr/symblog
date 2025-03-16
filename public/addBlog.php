<?php
require "../vendor/autoload.php";

use Illuminate\Database\Capsule\Manager as Capsule;
use App\Models\Blog;

// Configuración de la base de datos con Eloquent ORM
$capsule = new Capsule();
$capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => 'localhost',
    'database'  => 'symblog',
    'username'  => 'root',
    'password'  => 'root',
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => '',
]);
$capsule->setAsGlobal();
$capsule->bootEloquent();

// Procesamiento del formulario
$message = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? null;
    $author = $_POST['author'] ?? null;
    $blogContent = $_POST['blog'] ?? null;
    $tags = $_POST['tags'] ?? null;
    $imagePath = null;

    // Manejo de subida de imágenes
    if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = "uploads/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $imagePath = $uploadDir . basename($_FILES["image"]["name"]);
        if (!move_uploaded_file($_FILES["image"]["tmp_name"], $imagePath)) {
            $message = ['text' => 'Error al subir la imagen.', 'color' => 'red'];
        }
    }

    // Guardado en la base de datos
    try {
        $post = Blog::create([
            'title'  => $title,
            'author' => $author,
            'blog'   => $blogContent,
            'image'  => $imagePath,
            'tags'   => $tags
        ]);
        if ($post) {
            $message = ['text' => 'Post guardado correctamente.', 'color' => 'green'];
        }
    } catch (Exception $e) {
        $message = ['text' => 'Error al guardar el post: ' . $e->getMessage(), 'color' => 'red'];
    }
}

// Configurar Twig
$loader = new \Twig\Loader\FilesystemLoader('../templates'); // Asegúrate de que la ruta es correcta
$twig = new \Twig\Environment($loader, [
    // 'cache' => '../cache', // Puedes habilitar el caché en producción
]);

// Renderizar la plantilla y pasar los datos necesarios
echo $twig->render('addBlog.twig', [
    'message' => $message
]);
