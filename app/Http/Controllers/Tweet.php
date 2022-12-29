<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tweet as TweetModel;

class Tweet extends Controller
{

    /*
     * store the tweet in db
     */
    public function store(Request $request){
        $validated = $request->validate([
            "tweet" => "required|min:3|max:100"
        ]);

        $user = $request->user();
        $tweet = TweetModel::create([
            "tweet" => $request->input("tweet"),
            "user_id" => $user->id
        ]);

        $tweet->author = $tweet->author;

        return response()->json([
            "tweet" => $tweet,
            "message" => "Your tweet was posted!"
        ]);

    }

    /*
     * returns all tweets
     */
    public function all(){
        $tweets = TweetModel::with("author")->get();

        return response()->json([
            "tweets" => $tweets
        ]);
    }
}
