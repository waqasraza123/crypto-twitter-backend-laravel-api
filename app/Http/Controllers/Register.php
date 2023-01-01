<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class Register extends Controller
{
    public function register(Request $request){

        //validate the incoming data
        $validated = $request->validate([
            "name" => "required|min:3|max:40",
            "username" => "required|min:3|max:20|unique:users,username",
            "email" => "required|email|unique:users,email",
            "password" => "required|min:6|max:50"
        ]);

        //data is validated
        //store in the db
        $user = new User();
        $user->name = $request->input("name");
        $user->username = $request->input("username");
        $user->email = $request->input("email");
        $user->password = Hash::make($request->input("password"));
        $user->save();

        Auth::login($user);

        //create token for the user
        $token = $user->createToken($request->email)->plainTextToken;
        $user->accessToken = $token;

        //return response
        return response()->json([
            "message" => "User Registered",
            "user" => $user
        ]);
    }
}
