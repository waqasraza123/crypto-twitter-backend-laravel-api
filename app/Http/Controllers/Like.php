<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Like as LikeModel;

class Like extends Controller
{
    /**
     * like/unlike a tweet
     * @param Request $request
     * @return JsonResponse
     * POST
     */
    public function store(Request $request){

        //validate the incoming data
        $request->validate([
            "tweet_id" => "required|integer|exists:tweets,id"
        ]);

        //get the tweet id from request
        $tweetId = $request->input("tweet_id");

        try{
            $like = LikeModel::where("user_id", $request->user()->id)->where("tweet_id", $tweetId)->first();

            //delete the record if already liked
            if($like){
                $like->delete();
                return response()->json(["message" => "Tweet unliked.", "liked" => false], 200);
            }
            //create the new record
            else{
                $like = LikeModel::create([
                    "user_id" => $request->user()->id,
                    "tweet_id" => $tweetId
                ]);
                return response()->json([
                    "message" => "You liked the tweet with id " . $tweetId,
                    "like" => $like,
                    "liked" => true
                ], 200);
            }
        }catch (\Throwable $exception){
            return response()->json($exception, 500);
        }
    }
}
