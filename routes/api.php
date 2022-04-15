<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RandomController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\PostLikesController;


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

Route::get('/challenges/diff/{array1}/{array2}', [RandomController::class, 'diff']);

Route::get('/challenges/zeroes-to-end/{array}', [RandomController::class, 'endZero']);

Route::get('challenges/unique-in-order/{string}', [RandomController::class, 'uniqueInOrder']);

Route:: get('challenges/return-sum-multiples-3-5/{number}', [RandomController::class, 'returnSum']);

Route::get('challenges/validate-ip/{ip}', [RandomController::class, 'validateIp']);

Route::get('challenges/encode-duplicate/{word}', [RandomController::class, 'encodeDuplicate']);

Route::get('challenges/camelcase/{string}', [RandomController::class, 'camelCase']);

Route::get('challenges/square-root-or-not/{string}', [RandomController::class, 'squareRoot']);

Route::get('challenges/morse-decoder/{string}', [RandomController::class, 'morseDecoder']);

Route::get('challenges/morse-encoder/{string}', [RandomController::class, 'morseEncoder']);



// end of random stuff


//protected routes

Route::group(['middleware' => ['auth:sanctum']], function(){

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::post('/follow/{follower_id}/{following_id}', [UserController::class, 'follow']);

    Route::post('/unfollow/{follower_id}/{following_id}', [UserController::class, 'unfollow']);

    Route::get('users/profile/{id}', [UserController::class, 'getProfile']);

    Route::get('users/feed', [UserController::class, 'getFeed']);

    Route::post('/post', [PostController::class, 'store']);

    Route::post('post/edit/{post_id}', [PostController::class, 'update']);

    Route::post('/post/delete/{post_id}', [PostController::class, 'destroy']);

    Route::post('/post/comment/{post_id}', [CommentController::class, 'store']);

    Route::post('/comment/{comment_id}', [CommentController::class, 'update']);

    Route::post('/comment/delete/{comment_id}', [CommentController::class, 'destroy']);

    Route::post('post/like/{post_id}', [PostLikesController::class, 'addLike']);

    Route::post('post/remove/like/{post_id}', [PostLikesController::class, 'removeLike']);

 //random stuff that need auth

    Route::get('challenges/battleship-rules', [RandomController::class, 'battleshipRules']);

    Route::post('challenges/battleship-start', [RandomController::class, 'battleshipStart']);

    Route::post('challenges/battleship-end', [RandomController::class, 'battleshipEnd']);

    Route::post('challenges/battleship/{hit}', [RandomController::class, 'battleshipHit']);

    Route::post('challenges/battleship-reveal', [RandomController::class, 'battleshipReveal']);

    Route::post('challenges/battleship-hint', [RandomController::class, 'battleshipHint']);

});

/*Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});*/
