<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Blog extends Model {
    protected $table = 'blog';

    // Campos asignables de manera masiva
    protected $fillable = ['title', 'author', 'blog', 'tags', 'image'];

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function numeroComentarios()
    {
        return $this->comments()->count();
    }

/*     public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    // Método para obtener los blogs ordenados por fecha de creación
    public static function getOrderedBlogs()
    {
        return self::orderBy('created_at', 'desc')->get();
    }

    // Método para obtener la cantidad de comentarios de un blog
    public function cantidadComentarios()
    {
        return $this->comments()->count();
    }

    // Método para obtener los tags como un array
    public function getTagsArray()
    {
        return array_map('trim', explode(',', $this->tags));
    }

    // Método estático para obtener todos los tags únicos de la base de datos
    public static function getAllTags()
    {
        $tags = self::pluck('tags')->toArray();

        $allTags = [];
        foreach ($tags as $tagString) {
            $splitTags = array_map('trim', explode(',', $tagString));
            $allTags = array_merge($allTags, $splitTags);
        }

        return array_unique($allTags);
    }

    public function blog(){
        return $this->belongsTo(Blog::class, 'blog_id');    
    } */
}
