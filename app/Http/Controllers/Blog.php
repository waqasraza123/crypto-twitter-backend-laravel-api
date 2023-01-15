<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Blog as BlogModel;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class Blog extends Controller
{
    /**
     * Save a blog post to the database.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        // Validate the request data
        $validatedData = $request->validate([
            "title" => "required|min:6|max:100",
            "content" => "required|min:10|max:1000",
        ]);

        // Get the current user
        $user = $request->user();

        // Create the blog post in the database
        $post = BlogModel::create([
            "title" => $validatedData['title'],
            "content" => $validatedData['content'],
            "user_id" => $user->id
        ]);

        // Set the post author using the belongsTo relationship
        $post->author = $post->author;

        // Return a success response
        return response()->json([
            "message" => "Post created",
            "post" => $post
        ], 200);
    }

    /**
     * Returns a single post and its comments.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function single(Request $request): JsonResponse
    {
        // Get the post ID from the route parameters
        $postId = $request->route()->parameter("id");

        // Validate that the post exists
        Validator::make($request->route()->parameters(), [
            "id" => "required|exists:App\Models\Blog,id"
        ])->validate();

        // Fetch the post from the database
        $post = BlogModel::whereId($postId)
            ->with(["user", "comments.user"])
            ->first();

        // Return the post data in a JSON response
        return response()->json([
            "post" => $post
        ], 200);
    }

    /**
     * Returns all posts.
     *
     * @return JsonResponse
     */
    public function all(): JsonResponse
    {
        // Fetch all blog posts from the database
        $blogs = BlogModel::with("user")->orderBy("updated_at", "desc")->paginate(1);

        // Return the posts in a JSON response
        return response()->json($blogs, 200);
    }
}
