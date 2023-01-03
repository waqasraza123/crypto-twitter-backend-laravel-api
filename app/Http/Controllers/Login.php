<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use App\Models\SocialAccount;
use Laravel\Cashier\Cashier;

class Login extends Controller
{
    /*
     * email, password login
     * POST
     */
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

        if($user){
            //create user as a cashier(stripe) customer
            $cashierUser = Cashier::findBillable($user->stripe_id);
            $cashierUser ?? $user->createAsStripeCustomer();

            //create token for the user
            $token = $user->createToken($email)->plainTextToken;
            $user->accessToken = $token;
            $user->intent = $user->createSetupIntent();

            //record not found
            return response()->json([
                "message" => "Logged in Successfully.",
                "user" => $user
            ])->setStatusCode(200);
        }

        return response()->json([
            "message" => "User not found."
        ], 404);
    }


    /**
     * sets temporary username for the user
     * @param $email
     * @return string
     */
    protected function getTemporaryUsername($email){

        if(empty($email)){
            return "Email is required";
        }

        $emailString = explode("@", $email)[0];
        return substr(md5(rand()), 0, 7).$emailString;
    }

    /**
     * social login
     * $socialName = github, facebook, google, ...
     * GET
     */
    public function socialLogin($socialName){

        $socialUser = Socialite::driver($socialName)->stateless()->user();

        //if the user is empty
        if(is_null($socialUser)){
            return response()->json([
                "message" => "Invalid code."
            ], 400);
        }

        $user = User::firstOrCreate(
            [
                "email" => $socialUser->email
            ],
            [
                "name" => $socialUser->name,
                "username" => $this->getTemporaryUsername($socialUser->email)
            ]
        );

        //create the record in social accounts
        if($user){
            SocialAccount::firstOrCreate(
                [
                    "user_id" => $user->id,
                    "social_id" => $socialUser->id,
                    "social_provider" => $socialName,
                ],
                [
                    "social_name" => $socialUser->name
                ]
            );

            //create user as a cashier(stripe) customer
            $cashierUser = Cashier::findBillable($user->stripe_id);
            $cashierUser ?? $user->createAsStripeCustomer();
        }

        $token = $user->createToken($socialUser->getEmail())->plainTextToken;
        $user->accessToken = $token;
        $user->balance = $user->balance();
        $user->intent = $user->createSetupIntent();

        return response()->json([
            "user" => $user
        ], 200);

    }
}
