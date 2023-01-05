<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tweet extends Model
{
    use HasFactory;
    protected $fillable = ["tweet", "user_id"];

    /**
     * returns author of the tweet
     */
    public function user(){
        return $this->belongsTo(User::class, "user_id", "id");
    }

    /**
     * returns likes of a tweet
     */
    public function likes(){
        return $this->hasMany(Like::class, "tweet_id", "id");
    }

    /**
     * returns true if the logged-in
     * user liked the tweet
     */
    public function likedByCurrentUser(){
        return $this->hasOne(Like::class, "tweet_id", "id")
            ->where("user_id", auth()->user()->id);
    }

    /**
     * returns comments of this tweet
     */
    public function comments(){
        return $this->morphMany(Comment::class, "commentable")
            ->orderBy("updated_at", "desc");
    }
}
