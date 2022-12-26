<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Blog as BlogModel;

class Blog extends Controller
{
    /*
     * save blog post to db
     */
    public function store(Request $request){

        //validate the request
        $validated = $request->validate([
            "title" => "required|min:6|max:100",
            "content" => "required|min:10|max:1000",
        ]);

        //get current user id
        $user = $request->user();

        //save the post in db
        //$user->blogs()->create($validated);

        //it's equivalent to above one liner
        $post = BlogModel::create([
            "title" => $request->input("title"),
            "content" => $request->input("content"),
            "user_id" => $user->id
        ]);

        //set the post author using belongs to
        $post->author = $post->author;

        //response
        return response()->json([
            "message" => "Post created",
            "post" => $post
        ], 200);
    }

    /*
     * returns all posts
     */
    public function all(){

        $blogs = BlogModel::with("author")->get();

        return response()->json($blogs, 200);
    }
}
