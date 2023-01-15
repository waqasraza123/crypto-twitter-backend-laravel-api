<?php

namespace App\Models;

use Database\Factories\BlogFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Blog extends Model
{
    use HasFactory, Searchable;
    protected $table = "blogs";
    protected $fillable = ["title", "content", "user_id"];

    /*
     * get this post's author
     */
    public function user(){
        return $this->belongsTo(User::class, "user_id", "id");
    }

    /*
     * get this post's comments
     */
    public function comments(){
        return $this->morphMany(Comment::class, "commentable")
            ->orderBy("updated_at", "desc");
    }

    /**
     * Create a new factory instance for the model.
     *
     * @return Factory
     */
    protected static function newFactory(): Factory
    {
        return BlogFactory::new();
    }
}
