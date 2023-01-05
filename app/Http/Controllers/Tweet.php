<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tweet as TweetModel;

class Tweet extends Controller
{

    /**
     * store the tweet in db
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request){

        //validate the incoming data/request
        $request->validate([
            "tweet" => "required|min:3|max:100"
        ]);

        $user = $request->user();
        $tweet = TweetModel::create([
            "tweet" => $request->input("tweet"),
            "user_id" => $user->id
        ]);

        $tweet->user = $tweet->user;

        return response()->json([
            "tweet" => $tweet,
            "message" => "Your tweet was posted!"
        ]);

    }

    /**
     * returns all tweets
     * @return \Illuminate\Http\JsonResponse
     */
    public function all(){

        $tweets = TweetModel::with(["user", "likes", "comments.user", "likedByCurrentUser"])
            ->orderBy("updated_at", "desc")
            ->get();

        return response()->json([
            "tweets" => $tweets
        ]);
    }


    /**
     * returns single tweet
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     * GET
     */
    public function single($id){
        try{
            $tweet = TweetModel::whereId($id)
                ->with(["user", "comments.user", "likes", "likedByCurrentUser"])
                ->first();

            return response()->json([
                "tweet" => $tweet
            ], 200);

        }catch (\Throwable $exception){
            return response()->json($exception, 500);
        }

    }
}
