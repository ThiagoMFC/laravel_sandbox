<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RandomController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\PostLikesController;
use App\Http\Controllers\BattleshipController;
use App\Http\Controllers\ChessController;
use App\Http\Controllers\UnoController;


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

Route::get('challenges/max-array-string-diff/{string1}/{string2}', [RandomController::class, 'maxDifference']);

Route::get('challenges/base-converter/{num}/{base}/{convert}', [RandomController::class, 'convertNum']);



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

    Route::get('challenges/battleship-rules', [BattleshipController::class, 'battleshipRules']);

    Route::post('challenges/battleship-start', [BattleshipController::class, 'battleshipStart']);

    Route::post('challenges/battleship-end', [BattleshipController::class, 'battleshipEnd']);

    Route::post('challenges/battleship/{hit}', [BattleshipController::class, 'battleshipHit']);

    Route::post('challenges/battleship-reveal', [BattleshipController::class, 'battleshipReveal']);

    Route::post('challenges/battleship-hint', [BattleshipController::class, 'battleshipHint']);

    //-----------------------------------------------------------------------------------------

    Route::post('challenges/chess-start', [ChessController::class, 'initializeChessBoard']);

    Route::post('challenges/chess-board', [ChessController::class, 'showBoard']);

    Route::post('challenges/chess-move/{piece}/{position}', [ChessController::class, 'movePiece']);

    Route::post('challenges/chess-end', [ChessController::class, 'endGame']);

    //--------------------------------------------------------------------------------------------

    Route::post('challenges/uno-start', [UnoController::class, 'startGame']);

    Route::post('challenges/uno-end', [UnoController::class, 'endGame']);

    Route::post('challenges/uno-play', [UnoController::class, 'userPlay']);

    Route::post('challenges/uno-draw', [UnoController::class, 'userDraw']);

    Route::post('challenges/uno-skip', [UnoController::class, 'userSkip']);

});

/*Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});*/
