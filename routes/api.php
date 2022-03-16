<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RandomController;
use App\Http\Controllers\UserController;


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


//random controller -> random stuff just for fun

Route::get('/test', [RandomController::class, 'test']);

Route::post('/random', [RandomController::class, 'switchcase']);

// end of random stuff


//protected routes

Route::group(['middleware' => ['auth:sanctum']], function(){

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::post('/follow/{follower_id}/{following_id}', [UserController::class, 'follow']);

    Route::post('/unfollow/{follower_id}/{following_id}', [UserController::class, 'unfollow']);

});

/*Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});*/
