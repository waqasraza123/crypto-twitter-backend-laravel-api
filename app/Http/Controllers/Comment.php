<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use App\Models\Comment as CommentModel;

class Comment extends Controller
{
    /*
     * store the comment
     * for blog post and/or for tweet
     * POST
     */
    public function store(Request $request){

        //validate the request
        $validator = Validator::make($request->all(), [
            "comment" => "required|min:3|max:100",
            "commentable_id" => "required",
            "commentable_type" => [
                "required",
                Rule::in(["blogs", "tweets"])
            ]
        ]);

        $validator->sometimes("commentable_id", "unique:App\Blog,id", function ($input){
            return $input->commentable_type == "blogs";
        });

        $validator->sometimes("commentable_id", "unique:App\Tweet,id", function ($input){
            return $input->commentable_type == "tweets";
        });

        $commentableType = $request->input("commentable_type") === "blogs" ? "App\Models\Blog" : "App\Models\Tweet";

        //store the comment
        $comment = CommentModel::create([
            "comment" => $request->input("comment"),
            "commentable_id" => $request->input("commentable_id"),
            "commentable_type" => $commentableType,
            "user_id" => $request->user()->id
        ]);

        //set the comment user
        $comment->user = $comment->user;

        return response()->json([
            "message" => "your comment is posted.",
            "comment" => $comment
        ], 200);
    }

    /**
     * returns comments against a post
     * @param $postId
     * @param $type
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPostComments($postId, $type){

        $comments = CommentModel::with("user")
            ->where("commentable_id", $postId)
            ->where("commentable_type", $type)
            ->orderBy("created_at", "desc")
            ->get();

        return response()->json([
            "comments" => $comments
        ], 200);
    }
}
