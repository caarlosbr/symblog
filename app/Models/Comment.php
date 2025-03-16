<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model as Eloquent;




class Comment extends Eloquent
{
   
    protected $table = 'comment';
    protected $fillable = ['user', 'comment', 'blog_id'];
    
    

}