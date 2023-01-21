<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;
use App\Models\SocialAccount;
use Laravel\Cashier\Cashier;
use Illuminate\Http\JsonResponse;

class Login extends Controller
{

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {

        $email = $request->input("email");

        //validate the incoming data
        $validated = $request->validate([
            "email" => ["required", "email", Rule::exists(User::class, "email")],
            "password" => [
                Password::required(),
                Password::min(6)
                    ->numbers()
                    ->letters()
                    ->mixedCase()
                    ->symbols()
                ]
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
            $user->balance = $user->balance();
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
    public function getTemporaryUsername($email): string
    {
        if(empty($email)){
            return "Email is required";
        }

        $emailString = explode("@", $email)[0];
        return substr(md5(rand()), 0, 7).$emailString;
    }

    /**
     * social login
     * @param $socialName = github, google, twitter...
     * @return JsonResponse
     * GET
     * @throws ValidationException
     */
    public function socialLogin(Request $request, $socialName): JsonResponse
    {

        //validate the social provider name
        Validator::make($request->route()->parameters(), [
            "socialName" => [
                "required",
                Rule::in(["google", "facebook", "github", "twitter"]),
                "min:3",
                "max:20",
            ]
        ])->validate();

        $socialUser = Socialite::driver($socialName)->stateless()->user();

        //if the user is empty
        if(is_null($socialUser)){
            return response()->json([
                "message" => "Invalid code."
            ], 400);
        }

        try {
            $user = User::firstOrCreate(
                //find by email or create a new record
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

                //
                $token = $user->createToken($socialUser->email)->plainTextToken;
                $user->accessToken = $token;
                $user->balance = $user->balance();
                $user->intent = $user->createSetupIntent();
            }

            return response()->json([
                "message" => "Logged In",
                "user" => $user
            ], 200);

        }catch (\Throwable $exception){
            return response()->json([
                "errors" => $exception->getMessage()
            ], 500);
        }
    }
}
