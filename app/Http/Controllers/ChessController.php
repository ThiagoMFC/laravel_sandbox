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

    public function showBoard(Request $request){

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

        $game = DB::table('chess_games as cg')->select('cg.white_pieces as white', 'cg.black_pieces as black', 'cg.turns as turns')->where('user_id', '=', $fields['user_id'])
        ->where('user_token','=', $token)->where('status','=', 'ongoing')->get();

        $whitePieces = unserialize($game[0]->white);
        $blackPieces = unserialize($game[0]->black);

        return response([
            'white_pieces' => $whitePieces,
            'black_pieces' => $blackPieces,
            'turns' => $game[0]->turns,
        ], 200);

    }

    public function movePiece(Request $request, $piece, $position){

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

        $game = DB::table('chess_games as cg')->select('cg.white_pieces as white', 'cg.black_pieces as black', 'cg.turns as turns')->where('user_id', '=', $fields['user_id'])
        ->where('user_token','=', $token)->where('status','=', 'ongoing')->get();

        $whitePieces = unserialize($game[0]->white);
        $blackPieces = unserialize($game[0]->black);
        $turn = $game[0]->turns;

        $canMovePiece = [false, false];

        $p = substr_replace($piece, "", -1);
        switch($p){
            case('pawn'): 
                //check if user still have this specific pawn!!
                $canMovePiece = $this->canMovePawn($piece, $position, $whitePieces, $blackPieces, $turn);
                break;
            
            default: return response([
                'message' => 'invalid piece'
            ], 400);
        } 

        if(!$canMovePiece[0]){
            return response([
                'message' => 'cannot move ' . $piece . ' there'
            ], 400);
        }else{
            if($canMovePiece[1]){
                //remove piece from black_pieces
                $blackPieces = $this->removeFromPieces($position, $blackPieces);
                //$bp = serialize($blackPieces);
                //move piece from white_pieces
                $whitePieces = $this->changePiecePosition($position, $whitePieces, $piece);
                //$wp = serialize($whitePieces);
                //$gameUpdate = ChessGame::::where('user_id', '=', $fields['user_id'])->where('user_token', '=', $token)->where('status', '=', 'ongoing')->update(['white_pieces' => $wp, 'black_pieces' => $bp, 'turns' => 1]);

            }else{
                $whitePieces = $this->changePiecePosition($position, $whitePieces, $piece);
                //$wp = serialize($whitePieces);
                //$gameUpdate = ChessGame::::where('user_id', '=', $fields['user_id'])->where('user_token', '=', $token)->where('status', '=', 'ongoing')->update(['white_pieces' => $wp, 'turns' => 1]);
            }
        }

        //machine move before updating tables!!

        return response([
            'white_pieces' => $whitePieces,
            'black_pieces' => $blackPieces,
            'turns' => $game[0]->turns+1,
        ], 200);

        //piece movement
    }

    function canMovePawn($piece, $finalPosition, $whitePieces, $blackPieces, $turn){

        $canMoveThere = false;

        $finalPosArray = str_split($finalPosition, 1);
        $finalPosChar = $finalPosArray[0];
        $finalPosInt = $finalPosArray[1];

        $isOccupied = $this->isOccupied($blackPieces, $finalPosChar, $finalPosInt);

        $initialPosChar = $whitePieces[$piece][0];
        $initialPosInt = $whitePieces[$piece][1];

        if($turn == 0){
            if(($finalPosChar == $initialPosChar && $finalPosInt == $initialPosInt + 1) || ($finalPosChar == $initialPosChar && $finalPosInt == $initialPosInt + 2)){
                $canMoveThere = true;
            }
        }else{
            if($isOccupied){
                if(($finalPosChar == chr(ord($initialPosChar)+1) && $finalPosInt == $initialPosInt + 1) || ($finalPosChar == chr(ord($initialPosChar)-1) && $finalPosInt == $initialPosInt + 1)){
                    $canMoveThere = true;
                }
            }else{
                if($finalPosChar == $initialPosChar && $finalPosInt == $initialPosInt + 1){
                    $canMoveThere = true;
                }
            }
        }

        return [$canMoveThere, $isOccupied];

    }

    //check if final position is occupied
    function isOccupied($blackPieces, $finalPosChar, $finalPosInt){

        $isOccupied = false;

        foreach($blackPieces as $piece){
            if($piece[0] == $finalPosChar && $piece[1] == $finalPosInt){
                $isOccupied = true;
                break;
            }
        }

        return $isOccupied;
    }

    function removeFromPieces($position, $pieces){

        $posArray = str_split($position, 1);
        $posChar = $posArray[0];
        $posInt = $posArray[1];

        foreach($pieces as $piece){
            if($piece[0] == $posChar && $piece[1] == $posInt){
                unset($pieces[$piece]);
                break;
            }
        }

        return $pieces;
    }

    function changePiecePosition($position, $pieces, $piece){

        $posArray = str_split($position, 1);
        $posChar = $posArray[0];
        $posInt = $posArray[1];

        $pieces[$piece][0] = $posChar;
        $pieces[$piece][1] = $posInt;

        return $pieces;
    }

    

    //function switch pawn when pawn reaches end of board

    //------------------------------------------------------------------------------------------------------------------------------------------
}
