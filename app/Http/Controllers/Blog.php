<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Blog as BlogModel;
use Illuminate\Support\Facades\Validator;

class Blog extends Controller
{
    /*
     * save blog post to db
     * POST
     */
    public function store(Request $request){

        //validate the request
        $request->validate([
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
     * returns a single post
     * and its comments
     * GET
     */
    public function single(Request $request){

        $postId = $request->route()->parameter("id");

        //validate if post exists
        Validator::make($request->route()->parameters(), [
            "id" => "required|exists:App\Models\Blog,id"
        ])->validate();

        //fetch the post from db
        $post = BlogModel::whereId($postId)
            ->with(["user", "comments.user"])
            ->first();

        //response
        return response()->json([
            "post" => $post
        ], 200);

    }

    /*
     * returns all posts
     * GET
     */
    public function all(){

        $blogs = BlogModel::with("user")->orderBy("updated_at", "desc")->get();

        return response()->json($blogs, 200);
    }
}
