<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Blog extends Model {
    protected $table = 'blog';

    // Campos asignables de manera masiva
    protected $fillable = ['title', 'author', 'blog', 'tags', 'image'];

    // Relación uno a muchos con comentarios
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

     // Método para obtener el número de comentarios de un blog
    public function numeroComentarios()
    {
        return $this->comments()->count();
    }


}
