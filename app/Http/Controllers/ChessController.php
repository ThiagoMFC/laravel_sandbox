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
            'queen' => ['D', '1'],
            'king' => ['E', '1'],
            'bishop2' => ['F', '1'],
            'knight2' => ['G', '1'],
            'rook2' => ['H', '1'],
            'pawn1' => ['A', '2'],
            'pawn2' => ['B', '2'],
            'pawn3' => ['C', '2'],
            'pawn4' => ['D', '2'],
            'pawn5' => ['E', '2'],
            'pawn6' => ['F', '2'],
            'pawn7' => ['G', '2'],
            'pawn8' => ['H', '2'],
        ];

        $blackPieces = [
            'rook1' => ['A', '8'],
            'knight1' => ['B', '8'],
            'bishop1' => ['C', '8'],
            'queen' => ['D', '8'],
            'king' => ['E', '8'],
            'bishop2' => ['F', '8'],
            'knight2' => ['G', '8'],
            'rook2' => ['H', '8'],
            'pawn1' => ['A', '7'],
            'pawn2' => ['B', '7'],
            'pawn3' => ['C', '7'],
            'pawn4' => ['D', '7'],
            'pawn5' => ['E', '7'],
            'pawn6' => ['F', '7'],
            'pawn7' => ['G', '7'],
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

        $finalPosArray = str_split($position, 1);
        $finalPosChar = $finalPosArray[0];
        $finalPosInt = $finalPosArray[1];

        if($finalPosChar > 'H' ||  $finalPosChar < 'A' || $finalPosInt > 8 || $finalPosInt < 1){
            return response([
                "message" => "position out of bounds"
            ], 400);
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
                //check if user still has this specific pawn!!
                $canMovePiece = $this->canMovePawn($piece, $position, $whitePieces, $blackPieces, $turn);
                break;
            case('knight'):
                $canMovePiece = $this->canMoveKnight($piece, $position, $whitePieces, $blackPieces, $turn);
                break;
            case('rook'):
                $canMovePiece = $this->canMoveRook($piece, $position, $whitePieces, $blackPieces, $turn);
                break;
            case('bishop'):
                $canMovePiece = $this->canMoveBishop($piece, $position, $whitePieces, $blackPieces, $turn);
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
            if($canMovePiece[1][0] && $canMovePiece[1][1] != 'white'){ //is occupied by black piece
                
                $blackPieces = $this->removeFromPieces($position, $blackPieces);
                $bp = serialize($blackPieces);

                $whitePieces = $this->changePiecePosition($position, $whitePieces, $piece);
                $wp = serialize($whitePieces);

                $gameUpdate = ChessGame::where('user_id', '=', $fields['user_id'])->where('user_token', '=', $token)->where('status', '=', 'ongoing')->update(['white_pieces' => $wp, 'black_pieces' => $bp, 'turns' => 1]);

            }else if(!$canMovePiece[1][0]){ //not occupied
                $whitePieces = $this->changePiecePosition($position, $whitePieces, $piece);
                $wp = serialize($whitePieces);

                $gameUpdate = ChessGame::where('user_id', '=', $fields['user_id'])->where('user_token', '=', $token)->where('status', '=', 'ongoing')->update(['white_pieces' => $wp, 'turns' => 1]);
            
            }else{ //is occupied by white piece
                return response([
                    'message' => 'you already have a piece there'
                ], 400);
            }
        }

        //machine move before updating tables!!

        return response([
            'white_pieces' => $whitePieces,
            'black_pieces' => $blackPieces,
            'turns' => $game[0]->turns+1,
        ], 200);

    }

    function canMovePawn($piece, $finalPosition, $whitePieces, $blackPieces, $turn){

        $canMoveThere = false;

        $finalPosArray = str_split($finalPosition, 1);
        $finalPosChar = $finalPosArray[0];
        $finalPosInt = $finalPosArray[1];

        $isOccupied = $this->isOccupied($blackPieces, $finalPosChar, $finalPosInt, $whitePieces);

        $initialPosChar = $whitePieces[$piece][0];
        $initialPosInt = $whitePieces[$piece][1];

        if($turn == 0){
            if(($finalPosChar == $initialPosChar && $finalPosInt == $initialPosInt + 1) || ($finalPosChar == $initialPosChar && $finalPosInt == $initialPosInt + 2)){
                $canMoveThere = true;
            }
        }else{
            if($isOccupied[0] && $isOccupied[1] != 'white'){
                if(($finalPosChar == chr(ord($initialPosChar)+1) && $finalPosInt == $initialPosInt + 1) || ($finalPosChar == chr(ord($initialPosChar)-1) && $finalPosInt == $initialPosInt + 1)){
                    $canMoveThere = true;
                }
            }else if(!$isOccupied[0]){
                if($finalPosChar == $initialPosChar && $finalPosInt == $initialPosInt + 1){
                    $canMoveThere = true;
                }
            }else{
                $canMoveThere = false;
            }
        }

        return [$canMoveThere, $isOccupied];
        //return $canMovePiece;

    }

    function canMoveKnight($piece, $finalPosition, $whitePieces, $blackPieces, $turn){

        $canMoveThere = false;

        $finalPosArray = str_split($finalPosition, 1);
        $finalPosChar = $finalPosArray[0];
        $finalPosInt = $finalPosArray[1];

        $isOccupied = $this->isOccupied($blackPieces, $finalPosChar, $finalPosInt, $whitePieces);

        $initialPosChar = $whitePieces[$piece][0];
        $initialPosInt = $whitePieces[$piece][1];

        //movements (long L forward+right, forward+left, backwards+right, backwards+left / short L forward+right, forward+left, backwards+right, backwards+left)

        if(($finalPosChar == chr(ord($initialPosChar)+1) && $finalPosInt == $initialPosInt + 2) || 
           ($finalPosChar == chr(ord($initialPosChar)-1) && $finalPosInt == $initialPosInt + 2) ||
           ($finalPosChar == chr(ord($initialPosChar)+1) && $finalPosInt == $initialPosInt - 2) || 
           ($finalPosChar == chr(ord($initialPosChar)-1) && $finalPosInt == $initialPosInt - 2) ||
           ($finalPosChar == chr(ord($initialPosChar)+2) && $finalPosInt == $initialPosInt + 1) || 
           ($finalPosChar == chr(ord($initialPosChar)-2) && $finalPosInt == $initialPosInt + 1) ||
           ($finalPosChar == chr(ord($initialPosChar)+2) && $finalPosInt == $initialPosInt - 1) || 
           ($finalPosChar == chr(ord($initialPosChar)-2) && $finalPosInt == $initialPosInt - 1)){
                $canMoveThere = true;
           }

        return [$canMoveThere, $isOccupied];
        //return $canMovePiece;

    }

    function canMoveRook($piece, $finalPosition, $whitePieces, $blackPieces, $turn){
        $canMoveThere = false;
        $isObstructed = false;

        $finalPosArray = str_split($finalPosition, 1);
        $finalPosChar = $finalPosArray[0];
        $finalPosInt = $finalPosArray[1];

        $isOccupied = $this->isOccupied($blackPieces, $finalPosChar, $finalPosInt, $whitePieces);

        $initialPosChar = $whitePieces[$piece][0];
        $initialPosInt = $whitePieces[$piece][1];

        if($finalPosChar == $initialPosChar){
            //vertical movement
            $isObstructed = $this->isObstructed($initialPosChar, $initialPosInt, $finalPosChar, $finalPosInt, 'vertical', $whitePieces, $blackPieces);

            if(!$isObstructed && $isOccupied[1] != 'white'){
                $canMoveThere = true;
            }else if(!$isObstructed && !$isOccupied[0]){
                $canMoveThere = true;
            }

        }else if($finalPosInt == $initialPosInt){
            //horizontal movement
            $isObstructed = $this->isObstructed($initialPosChar, $initialPosInt, $finalPosChar, $finalPosInt, 'horizontal', $whitePieces, $blackPieces);

            if(!$isObstructed && $isOccupied[1] != 'white'){
                $canMoveThere = true;
            }else if(!$isObstructed && !$isOccupied[0]){
                $canMoveThere = true;
            }

        }

        return [$canMoveThere, $isOccupied];
    }

    function canMoveBishop($piece, $finalPosition, $whitePieces, $blackPieces, $turn){

        $canMoveThere = false;

        $finalPosArray = str_split($finalPosition, 1);
        $finalPosChar = $finalPosArray[0];
        $finalPosInt = $finalPosArray[1];

        $initialPosChar = $whitePieces[$piece][0];
        $initialPosInt = $whitePieces[$piece][1];

        $isOccupied = $this->isOccupied($blackPieces, $finalPosChar, $finalPosInt, $whitePieces);
        $isObstructed = $this->isObstructed($initialPosChar, $initialPosInt, $finalPosChar, $finalPosInt, 'diagonal', $whitePieces, $blackPieces);


        if(($finalPosChar > $initialPosChar && $finalPosInt > $initialPosInt) ||
           ($finalPosChar > $initialPosChar && $initialPosInt > $finalPosInt) ||
           ($initialPosChar > $finalPosChar && $finalPosInt > $initialPosInt) ||
           ($initialPosChar > $finalPosChar && $initialPosInt > $finalPosInt)){

                $countChar = 0;
                $countInt = 0;
           
                if($finalPosChar > $initialPosChar){
                    while($initialPosChar != $finalPosChar){
                        $countChar++;
                        $initialPosChar = chr(ord($initialPosChar)+1);
                    }
                }else if($initialPosChar > $finalPosChar){
                    while($finalPosChar != $initialPosChar){
                        $countChar++;
                        $finalPosChar = chr(ord($finalPosChar)+1);
                    }
                }

                if($finalPosInt > $initialPosInt){
                    while($initialPosInt != $finalPosInt){
                        $countInt++;
                        $initialPosInt += 1; 
                    }
                    
                }else if($initialPosInt > $finalPosInt){
                    while($finalPosInt != $initialPosInt){
                        $countInt++;
                        $finalPosInt += 1;
                    }
                   
                }

                if($countChar == $countInt){
                    if(!$isObstructed && $isOccupied[1] != 'white'){
                        $canMoveThere = true;
                    }else if(!$isObstructed && !$isOccupied[0]){
                        $canMoveThere = true;
                    }
                }
        }
        //error_log($countInt);
        //error_log($countChar);
        return [$canMoveThere, $isOccupied];

    }

    function isObstructed($initialPosChar, $initialPosInt, $finalPosChar, $finalPosInt, $direction, $whitePieces, $blackPieces){
        
        $isObstructed = false;
        $isOccupied = [];


        if($direction == 'horizontal'){

            if($finalPosChar < $initialPosChar){
                while($initialPosChar != chr(ord($finalPosChar)+1) && $isObstructed == false){ //don't wanna check if final position is obstructed
                    $initialPosChar = chr(ord($initialPosChar)-1);
                    $isOccupied = $this->isOccupied($blackPieces, $initialPosChar, $initialPosInt, $whitePieces);
                    $isObstructed = $isOccupied[0];
                }
            }else{
                while($initialPosChar != chr(ord($finalPosChar)-1) && $isObstructed == false){
                    $initialPosChar = chr(ord($initialPosChar)+1);
                    $isOccupied = $this->isOccupied($blackPieces, $initialPosChar, $initialPosInt, $whitePieces);
                    $isObstructed = $isOccupied[0];
                }
            }

        }else if($direction == 'vertical'){
            if($finalPosInt < $initialPosInt){
                while($initialPosInt != $finalPosInt+1 && $isObstructed == false){
                    $initialPosInt -= 1;
                    $isOccupied = $this->isOccupied($blackPieces, $initialPosChar, $initialPosInt, $whitePieces);
                    $isObstructed = $isOccupied[0];
                }
            }else{
                while($initialPosInt != $finalPosInt-1 && $isObstructed == false){
                    $initialPosInt += 1;
                    $isOccupied = $this->isOccupied($blackPieces, $initialPosChar, $initialPosInt, $whitePieces);
                    $isObstructed = $isOccupied[0];
                }
            }
           
        }else if($direction == 'diagonal'){
            if($finalPosChar > $initialPosChar && $finalPosInt > $initialPosInt){
                //right-up

                while($initialPosChar != chr(ord($finalPosChar)-1) && $initialPosInt != $finalPosInt -1 && $isObstructed == false){
                    $initialPosChar = chr(ord($initialPosChar)+1);
                    $initialPosInt += 1;
                    $isOccupied = $this->isOccupied($blackPieces, $initialPosChar, $initialPosInt, $whitePieces);
                    $isObstructed = $isOccupied[0];
                }

            }else if($finalPosChar > $initialPosChar && $initialPosInt > $finalPosInt){
                //right-down

                while($initialPosChar != chr(ord($finalPosChar)-1) && $initialPosInt != $finalPosInt +1 && $isObstructed == false){
                    $initialPosChar = chr(ord($initialPosChar)+1);
                    $initialPosInt -= 1;
                    $isOccupied = $this->isOccupied($blackPieces, $initialPosChar, $initialPosInt, $whitePieces);
                    $isObstructed = $isOccupied[0];
                }

            }else if($initialPosChar > $finalPosChar && $finalPosInt > $initialPosInt){
                //left-up

                while($initialPosChar != chr(ord($finalPosChar)+1) && $initialPosInt != $finalPosInt -1 && $isObstructed == false){
                    $initialPosChar = chr(ord($initialPosChar)-1);
                    $initialPosInt += 1;
                    $isOccupied = $this->isOccupied($blackPieces, $initialPosChar, $initialPosInt, $whitePieces);
                    $isObstructed = $isOccupied[0];
                }

            }else if($initialPosChar> $finalPosChar && $initialPosInt > $finalPosInt){
                //left-down

                while($initialPosChar != chr(ord($finalPosChar)+1) && $initialPosInt != $finalPosInt +1 && $isObstructed == false){
                    $initialPosChar = chr(ord($initialPosChar)-1);
                    $initialPosInt -= 1;
                    $isOccupied = $this->isOccupied($blackPieces, $initialPosChar, $initialPosInt, $whitePieces);
                    $isObstructed = $isOccupied[0];
                }

            }
        }

        return $isObstructed;
    }

    //check if final position is occupied
    function isOccupied($blackPieces, $finalPosChar, $finalPosInt, $whitePieces){

        $isOccupied = false;
        $color = '';

        foreach($blackPieces as $piece){
            if($piece[0] == $finalPosChar && $piece[1] == $finalPosInt){
                $isOccupied = true;
                $color = 'black';
                break;
            }
        }

        foreach($whitePieces as $piece){
            if($piece[0] == $finalPosChar && $piece[1] == $finalPosInt){
                $isOccupied = true;
                $color = 'white';
                break;
            }
        }

        return [$isOccupied, $color];
    }

    function removeFromPieces($position, $pieces){

        $posArray = str_split($position, 1);
        $posChar = $posArray[0];
        $posInt = $posArray[1];

        foreach($pieces as $key=>$piece){
            if($piece[0] == $posChar && $piece[1] == $posInt){
                unset($pieces[$key]);
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


    public function endGame(Request $request){

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

        $gameUpdate = ChessGame::where('user_id', '=', $fields['user_id'])->where('user_token', '=', $token)->where('status', '=', 'ongoing')->update(['status' => 'finished', 'result' => 'loss']);

        return response([
            'message' => 'game closed'
        ], 200);
    }


    //npc movement

 

    //function switch pawn when pawn reaches end of board

    //------------------------------------------------------------------------------------------------------------------------------------------
}
