<?php

use Illuminate\Http\Request;
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

Route::post("register", [Register::class, "register"]);
Route::post("login", [Login::class, "login"]);

Route::middleware('auth:sanctum')->group(function () {
    Route::post("/user-profile", [UserProfile::class, "updateProfile"]);
    Route::get("/user-profile", [UserProfile::class, "showProfile"]);

    Route::post("logout", [UserProfile::class, "logout"]);
});

//fallback route
Route::fallback(function(){
    return response()->json([
        'message' => 'Page Not Found. If error persists, contact website owner'], 404);
});
