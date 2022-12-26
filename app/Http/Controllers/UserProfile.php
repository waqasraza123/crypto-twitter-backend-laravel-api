<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserProfile extends Controller
{
    /*
     * updates user profile
     */
    public function updateProfile(Request $request)
    {

        $user = $request->user();

        //validate the request
        $validated = $request->validate([
            "name" => "required|min:3|max:40",
            "username" => "required|min:3|max:20|unique:users,username,".$user->id,
            "email" => "required|email|unique:users,email,".$user->id,
        ]);

        //update the details in table
        $user = User::whereEmail($request->input("email"))->first();

        //check for the image
        if($request->file('photo')){
            $file = $request->file('photo');
            $filename = date('YmdHi').$file->getClientOriginalName();
            $file->move(public_path('images'), $filename);

            //save the photo on user
            $user->photo = $filename;
        }

        //save the remaining details on user
        $user->name = $request->input("name");
        $user->email = $request->input("email");
        $user->username = $request->input("username");
        $user->bio = $request->input("bio");
        $user->save();

        return response()->json([
            "message" => "User updated successfully.",
            "user" => $user
        ]);
    }

    /*
     * returns info about user profile
     */
    public function showProfile(Request $request)
    {
        $user = $request->user();

        return response()->json([
            "user" => $user
        ]);
    }

    /*
     * updates user password
     */
    public function updatePassword(Request $request){

        //get values from request
        $email = $request->user()->email;
        $oldPassword = $request->input("oldPassword");
        $newPassword = $request->input("password");

        //validate the data
        $validated = $request->validate([
            "password" => "required|min:6|max:50|confirmed",
            "oldPassword" => "required"
        ]);

        $user = User::whereEmail($email)->first();

        //verify if the old passwords match
        if(Hash::check($oldPassword, $user->password)){
            //hash the new password
            $hashedPassword = Hash::make($newPassword);

            //update the password in db
            $user->password = $hashedPassword;
            $user->save();

            return response()->json([
                "message" => "Password updated successfully."
            ])->setStatusCode(200);
        }

        //below error format is explained here
        //https://laravel.com/docs/9.x/validation#validation-error-response-format
        return response()->json([
            "errors" => [
                "password" => [
                    "New and old passwords does not match."
                ]
            ]
        ])->setStatusCode(422); //422 validation error
    }

    /*
     * logout the user
     */
    public function logout()
    {
        //delete all tokens for current user
        auth()->user()->tokens()->delete();

        return response()->json([
            "message" => "Logged Out.",
        ])->setStatusCode(200);
    }
}
