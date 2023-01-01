<?php

use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get('/auth/redirect', function () {
    return Socialite::driver('github')->stateless()->redirect();
});

Route::get('/auth/callback', function (\Illuminate\Http\Request $request) {
    $user = Socialite::driver('github')->stateless()->user();

    return response()->json([
        "user" => $user
    ]);
});

Route::get("test", function (){
   return Socialite::driver("github")->userFromToken("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");
});
