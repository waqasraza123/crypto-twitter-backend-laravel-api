<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Login extends Controller
{
    public function login(Request $request){

        $email = $request->input("email");
        $password = $request->input("password");

        //validate the incoming data
        $validated = $request->validate([
            "email" => ["required", "email"],
            "password" => ["required"]
        ]);

        //failed to login the user
        if(!Auth::attempt($validated)){

            return response()->json([
                'message' => 'Invalid Credentials'
            ])->setStatusCode(401);
        }

        $user = User::where("email", $email)->firstOrFail();

        //create token for the user
        $token = $user->createToken($email)->plainTextToken;
        $user->accessToken = $token;

        //record not found
        return response()->json([
            "message" => "Logged in Successfully.",
            "user" => $user
        ])->setStatusCode(200);
    }
}
