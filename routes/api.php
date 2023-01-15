<?php

use Illuminate\Http\Request;
use App\Http\Controllers\Crypto;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Register;
use App\Http\Controllers\Login;
use App\Http\Controllers\UserProfile;
use App\Http\Controllers\Blog;
use App\Http\Controllers\Comment;
use App\Http\Controllers\Tweet;
use App\Http\Controllers\Like;
use App\Http\Controllers\GlobalSearch;

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
Route::get('/auth/redirect/{socialName}', function ($socialName) {
    return Socialite::driver($socialName)->stateless()->redirect()->getTargetUrl();
});
Route::get('/auth/callback/{socialName}', [Login::class, "socialLogin"]);

Route::middleware('auth:sanctum')->group(function () {
    //user profile routes
    Route::post("/user-profile", [UserProfile::class, "updateProfile"]);
    Route::get("/user-profile", [UserProfile::class, "showProfile"]);
    Route::post("/user-profile/password", [UserProfile::class, "updatePassword"]);

    //payment routes
    Route::get('/billing-portal', function (Request $request) {
        return $request->user()->billingPortalUrl();
    });

    //crypto routes
    Route::get("crypto/all", [Crypto::class, "all"]);
    Route::get("crypto/meta/{currencyId}", [Crypto::class, "meta"]);

    //blog routes
    Route::post("blog/posts", [Blog::class, "store"]);
    Route::get("blog/posts", [Blog::class, "all"]);
    Route::get("blog/post/{id}", [Blog::class, "single"]);

    //tweet routes
    Route::post("tweet", [Tweet::class, "store"]);
    Route::get("tweets", [Tweet::class, "all"]);
    Route::get("tweet/{id}", [Tweet::class, "single"]);

    //tweet likes/dislike routes
    Route::post("likes", [Like::class, "store"]);

    //comment routes
    Route::post("comments", [Comment::class, "store"]);
    Route::get("comments/post/{post_id}/{type}", [Comment::class, "getPostComments"]);

    //search routes
    Route::get('global-search/', [GlobalSearch::class, 'search'])
        ->name("search.global");
    Route::get('global-search/{searchTerm}', [GlobalSearch::class, 'search'])
        ->name("search.global");

    //logout route
    Route::post("logout", [UserProfile::class, "logout"]);
});

//fallback route
Route::fallback(function(){
    return response()->json([
        'message' => 'Page Not Found. If error persists, contact website owner'], 404);
});
