<?php

namespace App\Http\Controllers;

use App\Models\Tweet;
use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Http\JsonResponse;
use App\Models\Comment as CommentModel;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class Comment extends Controller
{
    /**
     * store the comment
     * for blog post and/or for tweet
     * @param Request $request
     * @return JsonResponse
     * POST
     */
    public function store(Request $request): JsonResponse
    {
        //validate the request
        $validator = Validator::make($request->all(), [
            "comment" => "required|min:3|max:100",
            "commentable_id" => "required|numeric",
            "commentable_type" => [
                "required",
                Rule::in(["blogs", "tweets"])
            ]
        ]);

        //these rules will be applied when the type is either blogs or tweets
        //if the invalid type is passed both these rules wont be executed
        //and invalid type validation error will be thrown
        $validator->sometimes("commentable_id", "exists:blogs,id", function ($input) {
            return $input->commentable_type === "blogs";
        });

        $validator->sometimes("commentable_id", "exists:tweets,id", function ($input) {
            return $input->commentable_type === "tweets";
        });


        //if validation fails
        //note -> since we made a custom validator by using
        //Validator::make so we have to handle the failed validation ourselves
        if($validator->fails()){
            return response()->json(["errors" => $validator->errors()], 422);
        }

        switch ($request->input("commentable_type")) {
            case "blogs":
                $commentableType = Blog::class;
                break;
            case "tweets":
                $commentableType = Tweet::class;
                break;
            default:
                return response()
                    ->json(['error' => 'Invalid commentable_type, only "blogs" and "tweets" are allowed.'], 422);
        }

        //store the comment
        $comment = CommentModel::create([
            "comment" => $request->input("comment"),
            "commentable_id" => $request->input("commentable_id"),
            "commentable_type" => $commentableType,
            "user_id" => $request->user()->id
        ]);

        //set the author of the comment
        $comment->user = $comment->user;

        return response()->json([
            "message" => "your comment is posted.",
            "comment" => $comment
        ], 200);
    }

    /**
     * returns comments against a post/tweet
     * @param $postId
     * @param $type
     * @return JsonResponse
     * GET
     */
    public function getCommentsForResource($postId, $type): JsonResponse
    {
        //validate the data
        $validator = Validator::make(['post_id' => $postId, 'type' => $type],[
            'post_id' => 'required|numeric',
            'type' => 'required|in:blogs,tweets',
        ]);

        //these rules will be applied when the type is either blogs or tweets
        //if the invalid type is passed both these rules wont be executed
        //and invalid type validation error will be thrown
        $validator->sometimes("post_id", Rule::exists(Blog::class, "id"), function ($input){
            return $input->type === "blogs";
        });

        $validator->sometimes("post_id", Rule::exists(Tweet::class, "id"), function ($input){
            return $input->type === "tweets";
        });

        if ($validator->fails()) {
            return response()->json(["errors" => $validator->errors()], 422);
        }

        switch ($type){
            case "blogs":
                $post = Blog::find($postId);
                $comments = $post->comments()
                    ->with("user")
                    ->orderBy("created_at", "desc")
                    ->get();
                break;
            case "tweets":
                $tweet = Tweet::find($postId);
                $comments = $tweet->comments()
                    ->with("user")
                    ->orderBy("created_at", "desc")
                    ->get();
                break;
            default:
                return response()
                    ->json(['error' => 'Invalid commentable_type, only "blogs" and "tweets" are allowed.'], 422);
        }

        return response()->json([
            "comments" => $comments
        ], 200);
    }
}
