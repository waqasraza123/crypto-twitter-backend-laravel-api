<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserProfile extends Controller
{
    public function updateProfile(Request $request){
        dd($request->file("photo"));
        return ($request->all());
    }

    public function showProfile(){
        return "here 2dss";
    }

    /*
     * logout the user
     */
    public function logout(){

        //delete all tokens for current user
        auth()->user()->tokens()->delete();

        return response()->json([
            "message" => "Logged Out.",
        ])->setStatusCode(200);
    }
}
