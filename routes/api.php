<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RandomController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CommentController;


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

//public routes

/*Route::get('/test', function(){
    return 'works';
});*/

Route::post('/register', [AuthController::class, 'register']);

Route::post('/login', [AuthController::class, 'login']);

Route::get('/users/search/{name}', [UserController::class, 'search']);

Route::get('/posts/show/{id}', [PostController::class, 'show']);


//random controller -> random stuff just for fun

Route::get('/test', [RandomController::class, 'test']);

Route::post('/random', [RandomController::class, 'switchcase']);

// end of random stuff


//protected routes

Route::group(['middleware' => ['auth:sanctum']], function(){

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::post('/follow/{follower_id}/{following_id}', [UserController::class, 'follow']);

    Route::post('/unfollow/{follower_id}/{following_id}', [UserController::class, 'unfollow']);

    Route::get('users/profile/{id}', [UserController::class, 'getProfile']);

    Route::post('/post', [PostController::class, 'store']);

    Route::post('post/edit/{post_id}', [PostController::class, 'update']);

    Route::post('/post/delete/{post_id}', [PostController::class, 'destroy']);

    Route::post('/post/comment/{post_id}', [CommentController::class, 'store']);

    Route::post('/comment/{comment_id}', [CommentController::class, 'update']);


    

});

/*Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});*/
