<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;


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

Route::get('/test', function(){
    return 'works';
});

/*Route::post('/multiply', function(Request $request){
    return $request['a']*$request['b'];
});

Route::post('/divide', function(Request $request){
    return $request['a']/$request['b'];
});

Route::post('/add', function(Request $request){
    return $request['a'] + $request['b'];
});*/

Route::post('/register', [AuthController::class, 'register']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
