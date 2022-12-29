<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tweet extends Model
{
    use HasFactory;
    protected $fillable = ["tweet", "user_id"];

    public function author(){
        return $this->belongsTo(User::class, "user_id", "id");
    }
}
