<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Blog extends Model
{
    use HasFactory;
    protected $table = "blogs";
    protected $fillable = ["title", "content", "user_id"];

    /*
     * get this post's author
     */
    public function author(){
        return $this->belongsTo(User::class, "user_id", "id");
    }

    /*
     * get this post's comments
     */
    public function comments(){
        return $this->morphMany(Comment::class, "commentable");
    }
}
