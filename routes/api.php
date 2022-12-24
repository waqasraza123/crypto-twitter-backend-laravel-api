<?php

use App\Http\Controllers\Crypto;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Register;
use App\Http\Controllers\Login;
use App\Http\Controllers\UserProfile;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//auth routes
Route::post("register", [Register::class, "register"]);
Route::post("login", [Login::class, "login"]);

Route::middleware('auth:sanctum')->group(function () {
    //user profile routes
    Route::post("/user-profile", [UserProfile::class, "updateProfile"]);
    Route::get("/user-profile", [UserProfile::class, "showProfile"]);

    //crypto routes
    Route::get("crypto/all", [Crypto::class, "all"]);
    Route::get("crypto/meta/{currencyId}", [Crypto::class, "meta"]);

    //logout route
    Route::post("logout", [UserProfile::class, "logout"]);
});

//fallback route
Route::fallback(function(){
    return response()->json([
        'message' => 'Page Not Found. If error persists, contact website owner'], 404);
});
