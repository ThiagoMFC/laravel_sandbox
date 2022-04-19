<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\ChessGame;
use Carbon\Carbon;
use App\Lib\HelperClass;
use Illuminate\Support\Facades\DB;

class ChessController extends Controller
{
    //CHESS GAME--------------------------------------------------------------------------------------------------------------------------------

    public function initializeChessBoard(Request $request){

        $fields = $request->validate([
            'user_id' => 'required',
        ]);

        $token = $request->bearerToken();

        $helper = new HelperClass();
        $validateUser = $helper->checkToken($fields['user_id'], $token);

        if(!$validateUser){
            return response([
                'message' => 'invalid request, user invalid',
            ], 401);
        }

        $whitePieces = [
            'rook1' => ['A', '1'],
            'knight1' => ['B', '1'],
            'bishop1' => ['C', '1'],
            'king' => ['D', '1'],
            'queen' => ['E', '1'],
            'bishop2' => ['F', '1'],
            'knight2' => ['G', '1'],
            'rook2' => ['H', '1'],
            'pawn1' => ['A', '2'],
            'pawn2' => ['B', '2'],
            'pawn3' => ['C', '2'],
            'pawn4' => ['D', '2'],
            'pawn5' => ['E', '2'],
            'pawn5' => ['F', '2'],
            'pawn5' => ['G', '2'],
            'pawn8' => ['H', '2'],
        ];

        $blackPieces = [
            'rook1' => ['A', '8'],
            'knight1' => ['B', '8'],
            'bishop1' => ['C', '8'],
            'king' => ['D', '8'],
            'queen' => ['E', '8'],
            'bishop2' => ['F', '8'],
            'knight2' => ['G', '8'],
            'rook2' => ['H', '8'],
            'pawn1' => ['A', '7'],
            'pawn2' => ['B', '7'],
            'pawn3' => ['C', '7'],
            'pawn4' => ['D', '7'],
            'pawn5' => ['E', '7'],
            'pawn5' => ['F', '7'],
            'pawn5' => ['G', '7'],
            'pawn8' => ['H', '7'],
        ];

        $serializedWhitePieces = serialize($whitePieces);
        $serializedBlackPieces = serialize($blackPieces);
        $now = Carbon::now();


        $userChess = ChessGame::create([
            'user_id' => $fields['user_id'],
            'user_token' => $token,
            'status' => 'ongoing',
            'result' => 'none',
            'white_pieces' => $serializedWhitePieces,
            'black_pieces' => $serializedBlackPieces,
            'turns' => 0,
            'date_started' => $now
        ]);

        if($userChess){
            return response([
                "message" => "game started",
            ], 201);
        }else{
            return response([
                'message' => 'failed to start game',
            ], 500);
        }
    }

    //------------------------------------------------------------------------------------------------------------------------------------------
}
