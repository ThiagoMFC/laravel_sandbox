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

        if($piece == 'queen' || $piece == 'king'){
            $p = $piece;
        }else{
            $p = substr_replace($piece, "", -1);
        }

        
        switch($p){
            case('pawn'): 
                if(array_key_exists($piece, $whitePieces)){
                    $canMovePiece = $this->canMovePawn($piece, $position, $whitePieces, $blackPieces, $turn, 'white');
                }
                break;
            case('knight'):
                if(array_key_exists($piece, $whitePieces)){
                    //error_log('got here');
                    $canMovePiece = $this->canMoveKnight($piece, $position, $whitePieces, $blackPieces, 'white');
                }
                break;
            case('rook'):
                if(array_key_exists($piece, $whitePieces)){
                    $canMovePiece = $this->canMoveRook($piece, $position, $whitePieces, $blackPieces, 'white');
                }
                break;
            case('bishop'):
                if(array_key_exists($piece, $whitePieces)){
                    $canMovePiece = $this->canMoveBishop($piece, $position, $whitePieces, $blackPieces, 'white');
                }
                break;
            case('queen'):
                if(array_key_exists($piece, $whitePieces)){
                    $canMovePiece = $this->canMoveQueen($piece, $position, $whitePieces, $blackPieces, 'white');
                }
                break;
            case('king'):
                if(array_key_exists($piece, $whitePieces)){ 
                    $canMovePiece = $this->canMoveKing($piece, $position, $whitePieces, $blackPieces, 'white');
                }
                break;
            default: return response([
                'message' => 'invalid piece'
            ], 400);
        } 


        //check if player will be in check before allowing piece to move
        /*$check = $this->checkCondition($whitePieces, $blackPieces, 'black');
        if($check){
            return response([
                'message' => 'Cannot move ' . $piece . ' to '. $position . '. You will be in check!'
            ], 200);
        }*/ //user can never lose with this in place

        
        if(!$canMovePiece[0]){
            return response([
                'message' => 'cannot move ' . $piece . ' to '. $position
            ], 400);
        }else{
            if($canMovePiece[1][0] && $canMovePiece[1][1] != 'white'){ //is occupied by black piece
                
                $blackPieces = $this->removeFromPieces($position, $blackPieces);

                $whitePieces = $this->changePiecePosition($position, $whitePieces, $piece);
                
            }else if(!$canMovePiece[1][0]){ //not occupied

                $whitePieces = $this->changePiecePosition($position, $whitePieces, $piece);
               
            }else{ //is occupied by white piece
                return response([
                    'message' => 'you already have a piece there'
                ], 400);
            }
        }

        $turn = $game[0]->turns+1;

        if(!array_key_exists('king', $blackPieces)){

            $wp = serialize($whitePieces);
            $bp = serialize($blackPieces);

            $gameUpdate = ChessGame::where('user_id', '=', $fields['user_id'])->where('user_token', '=', $token)->where('status', '=', 'ongoing')
            ->update(['white_pieces' => $wp, 'black_pieces' => $bp, 'turns' => $turn, 'status' => 'finished', 'result' => 'win']);

            return response([
                'message' => 'Checkmate, you won!'
            ], 200);

        }

        //machine move before updating tables!!
        $endTurn = $this->npcMove($whitePieces, $blackPieces);
        $wp = serialize($endTurn[0]);
        $bp = serialize($endTurn[1]);
        $turn = $turn + 1;


        if(!array_key_exists('king', $endTurn[0])){

            $gameUpdate = ChessGame::where('user_id', '=', $fields['user_id'])->where('user_token', '=', $token)->where('status', '=', 'ongoing')
            ->update(['white_pieces' => $wp, 'black_pieces' => $bp, 'turns' => $turn, 'status' => 'finished', 'result' => 'loss']);

            return response([
                'message' => 'Checkmate, you lost'
            ], 200);

        }

        $gameUpdate = ChessGame::where('user_id', '=', $fields['user_id'])->where('user_token', '=', $token)->where('status', '=', 'ongoing')
        ->update(['white_pieces' => $wp, 'black_pieces' => $bp, 'turns' => $turn]);

        //check if player is in check after npc moves
        $check = $this->checkCondition($endTurn[0], $endTurn[1], 'black');

        if($gameUpdate){
            return response([
                'is in check?' => $check[0]. ' ' . $check[1],
                'white_pieces' => $endTurn[0],
                'black_pieces' => $endTurn[1],
                'turns' => $turn,
            ], 200);
        }else{
            return response([
                'error' => 'error updating game board'
            ], 500);
        }
    }

    function canMovePawn($piece, $finalPosition, $activeSet, $waitingSet, $turn, $targetColor){

        $canMoveThere = false;

        $finalPosArray = str_split($finalPosition, 1);
        $finalPosChar = $finalPosArray[0];
        $finalPosInt = $finalPosArray[1];

        $isOccupied = $this->isOccupied($waitingSet, $finalPosChar, $finalPosInt, $activeSet, $targetColor);

        $initialPosChar = $activeSet[$piece][0];
        $initialPosInt = $activeSet[$piece][1];
        

        if($turn == 0){
            if(($finalPosChar == $initialPosChar && $finalPosInt == $initialPosInt + 1) || ($finalPosChar == $initialPosChar && $finalPosInt == $initialPosInt + 2)){
                $canMoveThere = true;
            }
        }else{
            if($isOccupied[0] && $isOccupied[1] != $targetColor){
                if($targetColor == 'white'){
                    
                    if(($finalPosChar == chr(ord($initialPosChar)+1) && $finalPosInt == $initialPosInt + 1) || ($finalPosChar == chr(ord($initialPosChar)-1) && $finalPosInt == $initialPosInt + 1)){
                        $canMoveThere = true;
                    }
                }else{
                    if(($finalPosChar == chr(ord($initialPosChar)+1) && $finalPosInt == $initialPosInt - 1) || ($finalPosChar == chr(ord($initialPosChar)-1) && $finalPosInt == $initialPosInt - 1)){
                        $canMoveThere = true;
                    }
                }
                
            }else if(!$isOccupied[0]){
                if($targetColor == 'white'){
                   
                    if($finalPosChar == $initialPosChar && $finalPosInt == $initialPosInt + 1){
                        $canMoveThere = true;
                    }
                }else{
                    
                    if($finalPosChar == $initialPosChar && $finalPosInt == $initialPosInt - 1){
                        $canMoveThere = true;
                    }
                }
            }else{
                
                $canMoveThere = false;
            }
        }

        if($finalPosChar > 'H' || $finalPosChar < 'A' || $finalPosInt > 8 || $finalPosInt < 1){
            $canMoveThere = false;
        }

        return [$canMoveThere, $isOccupied];
        //return $canMovePiece;

    }

    function canMoveKnight($piece, $finalPosition, $activeSet, $waitingSet, $targetColor){

        $canMoveThere = false;

        $finalPosArray = str_split($finalPosition, 1);
        $finalPosChar = $finalPosArray[0];
        $finalPosInt = $finalPosArray[1];

        

        $isOccupied = $this->isOccupied($waitingSet, $finalPosChar, $finalPosInt, $activeSet, $targetColor);

        $initialPosChar = $activeSet[$piece][0];
        $initialPosInt = $activeSet[$piece][1];

        if((!$isOccupied[0]) || ($isOccupied[0] && $isOccupied[1] != $targetColor)){
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
        }

        

        if($finalPosChar > 'H' || $finalPosChar < 'A' || $finalPosInt > 8 || $finalPosInt < 1){
            $canMoveThere = false;
        }

        return [$canMoveThere, $isOccupied];
        //return $canMovePiece;

    }

    function canMoveRook($piece, $finalPosition, $activeSet, $waitingSet, $targetColor){
        $canMoveThere = false;
        $isObstructed = false;

        $finalPosArray = str_split($finalPosition, 1);
        $finalPosChar = $finalPosArray[0];
        $finalPosInt = $finalPosArray[1];

        //error_log('canMoveRook to '. $finalPosChar.$finalPosInt);

        $isOccupied = $this->isOccupied($waitingSet, $finalPosChar, $finalPosInt, $activeSet, $targetColor);

        //error_log('isOccupied '. $isOccupied[0].$isOccupied[1]);

        if((!$isOccupied[0]) || ($isOccupied[0] && $isOccupied[1] != $targetColor)){

            $initialPosChar = $activeSet[$piece][0];
            $initialPosInt = $activeSet[$piece][1];

            if($finalPosChar == $initialPosChar && $finalPosInt != $initialPosInt){
                //vertical movement
                $isObstructed = $this->isObstructed($initialPosChar, $initialPosInt, $finalPosChar, $finalPosInt, 'vertical', $activeSet, $waitingSet, $targetColor);
                //error_log('isObstructed '.$isObstructed);
                if(!$isObstructed && $isOccupied[1] != $targetColor){
                    $canMoveThere = true;
                }else if(!$isObstructed && !$isOccupied[0]){
                    $canMoveThere = true;
                }

            }else if($finalPosInt == $initialPosInt && $finalPosChar != $initialPosChar){
                //horizontal movement
                $isObstructed = $this->isObstructed($initialPosChar, $initialPosInt, $finalPosChar, $finalPosInt, 'horizontal', $activeSet, $waitingSet, $targetColor);

                if(!$isObstructed && $isOccupied[1] != $targetColor){
                    $canMoveThere = true;
                }else if(!$isObstructed && !$isOccupied[0]){
                    $canMoveThere = true;
                }

            }
        }

        if($finalPosChar > 'H' || $finalPosChar < 'A' || $finalPosInt > 8 || $finalPosInt < 1){
            $canMoveThere = false;
        }

        return [$canMoveThere, $isOccupied];
    }

    function canMoveBishop($piece, $finalPosition, $activeSet, $waitingSet, $targetColor){

        $canMoveThere = false;

        $finalPosArray = str_split($finalPosition, 1);
        $finalPosChar = $finalPosArray[0];
        $finalPosInt = $finalPosArray[1];

        $initialPosChar = $activeSet[$piece][0];
        $initialPosInt = $activeSet[$piece][1];

        $isOccupied = $this->isOccupied($waitingSet, $finalPosChar, $finalPosInt, $activeSet, $targetColor);
        $isObstructed = $this->isObstructed($initialPosChar, $initialPosInt, $finalPosChar, $finalPosInt, 'diagonal', $activeSet, $waitingSet, $targetColor);

        if((!$isOccupied[0]) || ($isOccupied[0] && $isOccupied[1] != $targetColor)){

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
                    if(!$isObstructed && $isOccupied[1] != $targetColor){
                        $canMoveThere = true;
                    }else if(!$isObstructed && !$isOccupied[0]){
                        $canMoveThere = true;
                    }
                }
            }
        }
        
        //error_log($countInt);
        //error_log($countChar);

        if($finalPosChar > 'H' || $finalPosChar < 'A' || $finalPosInt > 8 || $finalPosInt < 1){
            $canMoveThere = false;
        }
        return [$canMoveThere, $isOccupied];

    }

    function canMoveQueen($piece, $finalPosition, $activeSet, $waitingSet, $targetColor){
        $canMoveThere = false;

        $finalPosArray = str_split($finalPosition, 1);
        $finalPosChar = $finalPosArray[0];
        $finalPosInt = $finalPosArray[1];

        $initialPosChar = $activeSet[$piece][0];
        $initialPosInt = $activeSet[$piece][1];

        $isOccupied = $this->isOccupied($waitingSet, $finalPosChar, $finalPosInt, $activeSet, $targetColor);

        if((!$isOccupied[0]) || ($isOccupied[0] && $isOccupied[1] != $targetColor)){

            if($finalPosChar == $initialPosChar){
                //vertical movement
                $isObstructed = $this->isObstructed($initialPosChar, $initialPosInt, $finalPosChar, $finalPosInt, 'vertical', $activeSet, $waitingSet, $targetColor);
    
                if(!$isObstructed && $isOccupied[1] != $targetColor){
                    $canMoveThere = true;
                }else if(!$isObstructed && !$isOccupied[0]){
                    $canMoveThere = true;
                }
    
            }else if($finalPosInt == $initialPosInt){
                //horizontal movement
                $isObstructed = $this->isObstructed($initialPosChar, $initialPosInt, $finalPosChar, $finalPosInt, 'horizontal', $activeSet, $waitingSet, $targetColor);
    
                if(!$isObstructed && $isOccupied[1] != $targetColor){
                    $canMoveThere = true;
                }else if(!$isObstructed && !$isOccupied[0]){
                    $canMoveThere = true;
                }
    
            }else{
    
                $isObstructed = $this->isObstructed($initialPosChar, $initialPosInt, $finalPosChar, $finalPosInt, 'diagonal', $activeSet, $waitingSet, $targetColor);
    
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
                    if(!$isObstructed && $isOccupied[1] != $targetColor){
                        $canMoveThere = true;
                    }else if(!$isObstructed && !$isOccupied[0]){
                        $canMoveThere = true;
                    }
                }
            }
        }
        
        if($finalPosChar > 'H' || $finalPosChar < 'A' || $finalPosInt > 8 || $finalPosInt < 1){
            $canMoveThere = false;
        }

        return [$canMoveThere, $isOccupied];

    }

    function canMoveKing($piece, $finalPosition, $activeSet, $waitingSet, $targetColor){
        $canMoveThere = false;

        $finalPosArray = str_split($finalPosition, 1);
        $finalPosChar = $finalPosArray[0];
        $finalPosInt = $finalPosArray[1];

        $initialPosChar = $activeSet[$piece][0];
        $initialPosInt = $activeSet[$piece][1];

        $isOccupied = $this->isOccupied($waitingSet, $finalPosChar, $finalPosInt, $activeSet, $targetColor);

        if((!$isOccupied[0]) || ($isOccupied[0] && $isOccupied[1] != $targetColor)){

            //error_log($isOccupied[0]);
            //error_log($isOccupied[1]);

            if($finalPosChar == chr(ord($initialPosChar)+1) && $finalPosInt == $initialPosInt || //right
            $finalPosChar == chr(ord($initialPosChar)-1) && $finalPosInt == $initialPosInt || //left
            $finalPosChar == chr(ord($initialPosChar)+1) && $finalPosInt == $initialPosInt + 1 || //right-up
            $finalPosChar == chr(ord($initialPosChar)-1) && $finalPosInt == $initialPosInt - 1 || //left-down
            $finalPosChar == chr(ord($initialPosChar)+1) && $finalPosInt == $initialPosInt - 1 || //right-down
            $finalPosChar == chr(ord($initialPosChar)-1) && $finalPosInt == $initialPosInt + 1 || //left-up
            $finalPosInt == $initialPosInt + 1 && $finalPosChar == $initialPosChar || //up
            $finalPosInt == $initialPosInt - 1 && $finalPosChar == $initialPosChar){ //down
                $canMoveThere = true;
            }
        }

        if($finalPosChar > 'H' || $finalPosChar < 'A' || $finalPosInt > 8 || $finalPosInt < 1){
            $canMoveThere = false;
        }

        return [$canMoveThere, $isOccupied];

    }

    function isObstructed($initialPosChar, $initialPosInt, $finalPosChar, $finalPosInt, $direction, $activeSet, $waitingSet, $targetColor){
        
        $isObstructed = false;
        $isOccupied = [];

        //error_log('isObstructed ' . $finalPosChar.$finalPosInt);

        if($direction == 'horizontal'){

            if($finalPosChar < $initialPosChar){
                while($initialPosChar != chr(ord($finalPosChar)+1) && $isObstructed == false){ //don't wanna check if final position is obstructed
                    $initialPosChar = chr(ord($initialPosChar)-1);
                    $isOccupied = $this->isOccupied($waitingSet, $initialPosChar, $initialPosInt, $activeSet, $targetColor);
                    $isObstructed = $isOccupied[0];
                }
            }else{
                while($initialPosChar != chr(ord($finalPosChar)-1) && $isObstructed == false){
                    $initialPosChar = chr(ord($initialPosChar)+1);
                    $isOccupied = $this->isOccupied($waitingSet, $initialPosChar, $initialPosInt, $activeSet, $targetColor);
                    $isObstructed = $isOccupied[0];
                }
            }

        }else if($direction == 'vertical'){
            if($finalPosInt < $initialPosInt){
                while($initialPosInt != $finalPosInt+1 && $isObstructed == false){
                    $initialPosInt -= 1;
                    $isOccupied = $this->isOccupied($waitingSet, $initialPosChar, $initialPosInt, $activeSet, $targetColor);
                    $isObstructed = $isOccupied[0];
                }
            }else{
                while($initialPosInt != $finalPosInt-1 && $isObstructed == false){
                    $initialPosInt += 1;
                    $isOccupied = $this->isOccupied($waitingSet, $initialPosChar, $initialPosInt, $activeSet, $targetColor);
                    $isObstructed = $isOccupied[0];
                }
            }
           
        }else if($direction == 'diagonal'){
            if($finalPosChar > $initialPosChar && $finalPosInt > $initialPosInt){
                //right-up

                while($initialPosChar != chr(ord($finalPosChar)-1) && $initialPosInt != $finalPosInt -1 && $isObstructed == false){
                    $initialPosChar = chr(ord($initialPosChar)+1);
                    $initialPosInt += 1;
                    $isOccupied = $this->isOccupied($waitingSet, $initialPosChar, $initialPosInt, $activeSet, $targetColor);
                    $isObstructed = $isOccupied[0];
                }

            }else if($finalPosChar > $initialPosChar && $initialPosInt > $finalPosInt){
                //right-down

                while($initialPosChar != chr(ord($finalPosChar)-1) && $initialPosInt != $finalPosInt +1 && $isObstructed == false){
                    $initialPosChar = chr(ord($initialPosChar)+1);
                    $initialPosInt -= 1;
                    $isOccupied = $this->isOccupied($waitingSet, $initialPosChar, $initialPosInt, $activeSet, $targetColor);
                    $isObstructed = $isOccupied[0];
                }

            }else if($initialPosChar > $finalPosChar && $finalPosInt > $initialPosInt){
                //left-up

                while($initialPosChar != chr(ord($finalPosChar)+1) && $initialPosInt != $finalPosInt -1 && $isObstructed == false){
                    $initialPosChar = chr(ord($initialPosChar)-1);
                    $initialPosInt += 1;
                    $isOccupied = $this->isOccupied($waitingSet, $initialPosChar, $initialPosInt, $activeSet, $targetColor);
                    $isObstructed = $isOccupied[0];
                }

            }else if($initialPosChar> $finalPosChar && $initialPosInt > $finalPosInt){
                //left-down

                while($initialPosChar != chr(ord($finalPosChar)+1) && $initialPosInt != $finalPosInt +1 && $isObstructed == false){
                    $initialPosChar = chr(ord($initialPosChar)-1);
                    $initialPosInt -= 1;
                    $isOccupied = $this->isOccupied($waitingSet, $initialPosChar, $initialPosInt, $activeSet, $targetColor);
                    $isObstructed = $isOccupied[0];
                }

            }
        }

        return $isObstructed;
    }

    //check if final position is occupied
    function isOccupied($waitingSet, $finalPosChar, $finalPosInt, $activeSet, $targetColor){

        $isOccupied = false;
        $color = '';

        /*$targetColor is the color of piece being moved. if player is moving, it's white. if npc is moving, it's black*/

        foreach($waitingSet as $piece){
            //error_log('isOccupied ' . $piece[0].$piece[1]);
            if($piece[0] == $finalPosChar && $piece[1] == $finalPosInt){
                $isOccupied = true;

                //if $targetColor is 'white', means $waitingSet is the black set

                if($targetColor == 'white'){
                    $color = 'black';
                }else{
                    $color = 'white';
                }
                
                break;
            }
        }

        if(!$isOccupied){
            foreach($activeSet as $piece){
                if($piece[0] == $finalPosChar && $piece[1] == $finalPosInt){
                    $isOccupied = true;
    
                    if($targetColor == 'white'){
                        $color = 'white';
                    }else{
                        $color = 'black';
                    }
                    
                    
                    break;
                }
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

     /*
    NPC MOVEMENT
        receive black and white pieces positions
        check open spot around white king
        if open spot then check if any piece can get there
        if not open or no piece can get there check if any piece is at risk
        if not at risk check if can take any white piece following priorities (queen, bishop, rook, knight, pawn)
    */

    function npcMove($whitePieces, $blackPieces){

        
        $result = [false, false, false];

        $check = $this->checkCondition($blackPieces, $whitePieces, 'white');
        if($check[0]){
            $priorityMoveList = ['king', 'pawn1', 'pawn2', 'pawn3', 'pawn4', 'pawn5', 'pawn6', 'pawn7', 'pawn8', 'knight1', 'knight2', 'bishop1', 'bishop2', 'rook1', 'rook2', 'queen'];
            $priorityAttackList = [$check[1], 'king', 'queen', 'bishop1', 'bishop2', 'rook1', 'rook2', 'knight1', 'knight2', 'pawn1', 'pawn2', 'pawn3', 'pawn4', 'pawn5', 'pawn6', 'pawn7', 'pawn8'];
        }else{
            $priorityMoveList = ['pawn1', 'pawn2', 'pawn3', 'pawn4', 'pawn5', 'pawn6', 'pawn7', 'pawn8', 'knight1', 'knight2', 'bishop1', 'bishop2', 'rook1', 'rook2', 'queen', 'king'];
            $priorityAttackList = ['king', 'queen', 'bishop1', 'bishop2', 'rook1', 'rook2', 'knight1', 'knight2', 'pawn1', 'pawn2', 'pawn3', 'pawn4', 'pawn5', 'pawn6', 'pawn7', 'pawn8'];
        }

        

        foreach($priorityAttackList as $priority){
            if(array_key_exists($priority, $whitePieces)){
                $target = $whitePieces[$priority][0] . $whitePieces[$priority][1];
                //error_log($target);
                $result = $this->npcPieceMovement($priorityMoveList, $blackPieces, $whitePieces, $target);
    
                if($result[2]){
    
                    $whitePieces = $result[0];
                    $blackPieces = $result[1];
    
                    break;
                    //goto end;
                }
            }
        }

        if(!$result[2]){
            $blackPieces = $this->npcRandomMovement($priorityMoveList, $blackPieces, $whitePieces);
        }

        //print_r([$whitePieces, $blackPieces]);

        return [$whitePieces, $blackPieces];

    }

    function npcRandomMovement($piecesToMove, $activeSet, $waitingSet){

        $repeat = true;
        $target = "";
        $movementMap = [];
        $canMovePiece = [false];
        
        while($repeat){

            //select a random piece from $piecesToMove
            $piece = array_rand(array_flip($piecesToMove), 1);
            //$piece = 'rook1';
            //get its position from $blackPieces

            if(array_key_exists($piece, $activeSet)){
                $positionChar = $activeSet[$piece][0];
                $positionInt = $activeSet[$piece][1];

                if($piece == 'queen' || $piece == 'king'){
                    $p = $piece;
                }else{
                    $p = substr_replace($piece, "", -1);
                }
                error_log($piece);
                switch($p){
                    case('pawn'):
                        $target = $positionChar . $positionInt-1;
                        $canMovePiece = $this->canMovePawn($piece, $target, $activeSet, $waitingSet, 1, 'black');
                    break;
                    case('king'):
                    
                        $movementMap = [
                            chr(ord($positionChar)+1) . $positionInt,
                            chr(ord($positionChar)-1) . $positionInt,
                            $positionChar . $positionInt + 1,
                            $positionChar . $positionInt - 1,
                            chr(ord($positionChar)+1) . $positionInt + 1,
                            chr(ord($positionChar)+1) . $positionInt - 1,
                            chr(ord($positionChar)-1) . $positionInt + 1,
                            chr(ord($positionChar)-1) . $positionInt - 1
                        ];
                        shuffle($movementMap);
                        foreach($movementMap as $target){
                            $canMovePiece = $this->canMoveKing($piece, $target, $activeSet, $waitingSet, 'black');
                            if($canMovePiece[0]){
                                break;
                            }
                        }
                        
                    break;
                    case('knight'):
                    
                        $movementMap = [
                            chr(ord($positionChar)+1) . $positionInt + 2,
                            chr(ord($positionChar)-1) . $positionInt + 2,
                            chr(ord($positionChar)+1) . $positionInt - 2,
                            chr(ord($positionChar)-1) . $positionInt - 2,
                            chr(ord($positionChar)+2) . $positionInt + 1,
                            chr(ord($positionChar)-2) . $positionInt + 1,
                            chr(ord($positionChar)+2) . $positionInt - 1, 
                            chr(ord($positionChar)-2) . $positionInt - 1
                        ];
                        shuffle($movementMap);
                        foreach($movementMap as $target){
                            $canMovePiece = $this->canMoveKnight($piece, $target, $activeSet, $waitingSet, 'black');
                            if($canMovePiece[0]){
                                break;
                            }
                        }
                        
                    break;
                    case('rook'):
                        for($i = 1; $i < 9; $i++){
                            array_push($movementMap, $positionChar . $i);
                        }
                        $letters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
                        foreach($letters as $letter){
                            array_push($movementMap, $letter . $positionInt);
                        }
                        shuffle($movementMap);
                        foreach($movementMap as $target){
                            error_log($target);
                            $canMovePiece = $this->canMoveRook($piece, $target, $activeSet, $waitingSet, 'black');
                            if($canMovePiece[0]){
                                break;
                            }
                        } 

                    break;
                    case('bishop'):
                        $numbers = [1, 2, 3, 4, 5, 6, 7, 8];
                        $letters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
                        foreach($letters as $k=>$letter){
                            foreach($numbers as $j=>$number){
                                if($k % 2 === 0 && $j % 2 !== 0){
                                    array_push($movementMap, $letter . $number);
                                }else if($k % 2 !== 0 && $j % 2 === 0){
                                    array_push($movementMap, $letter . $number);
                                }
                            }
                        }
                        shuffle($movementMap);
                        foreach($movementMap as $target){
                            $canMovePiece = $this->canMoveBishop($piece, $target, $activeSet, $waitingSet, 'black');
                            if($canMovePiece[0]){
                                break;
                            }
                        }
                    break;
                    case('queen'):
                    
                        $numbers = [1, 2, 3, 4, 5, 6, 7, 8];
                        $letters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
                        foreach($letters as $k=>$letter){
                            foreach($numbers as $j=>$number){
                                array_push($movementMap, $letter . $number);
                            }
                        }
                        shuffle($movementMap);
                        foreach($movementMap as $target){
                            $canMovePiece = $this->canMoveQueen($piece, $target, $activeSet, $waitingSet, 'black');
                            if($canMovePiece[0]){
                                break;
                            }
                        }
                    
                    break;
                    default:
                        $canMovePiece[0] = false;
                }
            }

            

            //error_log($canMovePiece[0]);

            if($canMovePiece[0]){

                $repeat = false;
       
                $activeSet = $this->changePiecePosition($target, $activeSet, $piece);

                break ;
            }else{ 

                $key = array_search($piece, $piecesToMove);
                if ($key != ''){
                    unset($piecesToMove[$key]);
                }
                
            }
        }
        
        return $activeSet;

    }

    function npcPieceMovement($pieces, $activeSet, $waitingSet, $target){

        $canMovePiece = [false];
        $moved = false;

        foreach($pieces as $piece){

            if($piece == 'queen' || $piece == 'king'){
                $p = $piece;
            }else{
                $p = substr_replace($piece, "", -1);
            }

            //error_log($piece);
                switch($p){
                    case('bishop'):
                        if(array_key_exists($piece, $activeSet)){
                            $canMovePiece = $this->canMoveBishop($piece, $target, $activeSet, $waitingSet, 'black');
                        }
                        break;
                    case('queen'):
                        if(array_key_exists($piece, $activeSet)){
                            $canMovePiece = $this->canMoveQueen($piece, $target, $activeSet, $waitingSet, 'black');
                        }
                        break;
                    case('rook'):
                        if(array_key_exists($piece, $activeSet)){
                            $canMovePiece = $this->canMoveRook($piece, $target, $activeSet, $waitingSet, 'black');
                        }
                        break;
                    case('knight'):
                        if(array_key_exists($piece, $activeSet)){
                            $canMovePiece = $this->canMoveKnight($piece, $target, $activeSet, $waitingSet, 'black');
                        }
                        break;
                    case('king'):
                        if(array_key_exists($piece, $activeSet)){
                            $canMovePiece = $this->canMoveKing($piece, $target, $activeSet, $waitingSet, 'black');
                        }
                        break;
                    case('pawn'):
                        if(array_key_exists($piece, $activeSet)){
                            $canMovePiece = $this->canMovePawn($piece, $target, $activeSet, $waitingSet, 1, 'black');  
                        }
                        break;
                }

            if($canMovePiece[0]){

                //checkCondition!!!!!!

                $moved = true;

                $waitingSet = $this->removeFromPieces($target, $waitingSet);
       
                $activeSet = $this->changePiecePosition($target, $activeSet, $piece);

                break ;
            }
        }

        return [$waitingSet, $activeSet, $moved];
    }

    function checkCondition($activeSet, $waitingSet, $color){

        $check = false;
        $attacker = '';
        $canMovePiece = [false];

        $target = $activeSet['king'][0].$activeSet['king'][1];

        foreach($waitingSet as $piece){
            if($piece == 'queen' || $piece == 'king'){
                $p = $piece;
            }else{
                $p = substr_replace($piece, "", -1);
            }

            switch($p){
                case('bishop'):
                    if(array_key_exists($piece, $waitingSet)){
                        $canMovePiece = $this->canMoveBishop($piece, $target, $activeSet, $waitingSet, $color);
                    }
                    break;
                case('queen'):
                    if(array_key_exists($piece, $waitingSet)){
                        $canMovePiece = $this->canMoveQueen($piece, $target, $activeSet, $waitingSet, $color);
                    }
                    break;
                case('rook'):
                    if(array_key_exists($piece, $waitingSet)){
                        $canMovePiece = $this->canMoveRook($piece, $target, $activeSet, $waitingSet, $color);
                    }
                    break;
                case('knight'):
                    if(array_key_exists($piece, $waitingSet)){
                        $canMovePiece = $this->canMoveKnight($piece, $target, $activeSet, $waitingSet, $color);
                    }
                    break;
                case('king'):
                    if(array_key_exists($piece, $waitingSet)){
                        $canMovePiece = $this->canMoveKing($piece, $target, $activeSet, $waitingSet, $color);
                    }
                    break;
                case('pawn'):
                    if(array_key_exists($piece, $waitingSet)){
                        $canMovePiece = $this->canMovePawn($piece, $target, $activeSet, $waitingSet, 1, $color);  
                    }
                    break;
            }

            if($canMovePiece[0]){

                $check = true;
                $attacker = $piece;
            }
        }


        return [$check, $attacker];

    }

    function checkSurroundings($piece, $whitePieces, $blackPieces){

        $pieceSurrounding = [];
        $possibleTargets = [];

        $piecePosChar = $whitePieces[$piece][0];
        $piecePosInt = $whitePieces[$piece][1];

        if($piecePosChar != 'H' && $piecePosChar != 'A' && $piecePosInt != 8 && $piecePosInt != 1){
            $pieceSurrounding = [
                'right' => [chr(ord($piecePosChar)+1), $piecePosInt],
                'left' => [chr(ord($piecePosChar)-1), $piecePosInt],
                'up' => [$piecePosChar, $piecePosInt +1],
                'down' => [$piecePosChar, $piecePosInt -1],
                'right-up' => [chr(ord($piecePosChar)+1), $piecePosInt + 1],
                'right-down' => [chr(ord($piecePosChar)+1), $piecePosInt - 1],
                'left-up' => [chr(ord($piecePosChar)-1), $piecePosInt + 1],
                'left-down' => [chr(ord($piecePosChar)-1), $piecePosInt - 1],
            ];
        }else if(($piecePosChar == 'H' && $piecePosInt != 1) && ($piecePosChar == 'H' && $piecePosInt != 8)){
            $pieceSurrounding = [
                'left' => [chr(ord($piecePosChar)-1), $piecePosInt],
                'up' => [$piecePosChar, $piecePosInt +1],
                'down' => [$piecePosChar, $piecePosInt -1],
                'left-up' => [chr(ord($piecePosChar)-1), $piecePosInt + 1],
                'left-down' => [chr(ord($piecePosChar)-1), $piecePosInt - 1],
            ];
        }else if($piecePosChar == 'H' && $piecePosInt == 1){
            $pieceSurrounding = [
                'left' => [chr(ord($piecePosChar)-1), $piecePosInt],
                'up' => [$piecePosChar, $piecePosInt +1],
                'left-up' => [chr(ord($piecePosChar)-1), $piecePosInt + 1],
            ];
        }else if($piecePosChar == 'H' && $piecePosInt == 8){
            $pieceSurrounding = [
                'left' => [chr(ord($piecePosChar)-1), $piecePosInt],
                'down' => [$piecePosChar, $piecePosInt -1],
                'left-down' => [chr(ord($piecePosChar)-1), $piecePosInt - 1],
            ];
        }else if(($piecePosChar == 'A' && $piecePosInt != 1) && ($piecePosChar == 'A' && $piecePosInt != 8)){
            $pieceSurrounding = [
                'right' => [chr(ord($piecePosChar)+1), $piecePosInt],
                'up' => [$piecePosChar, $piecePosInt +1],
                'down' => [$piecePosChar, $piecePosInt -1],
                'right-up' => [chr(ord($piecePosChar)+1), $piecePosInt + 1],
                'right-down' => [chr(ord($piecePosChar)+1), $piecePosInt - 1],
            ];
        }else if($piecePosChar == 'A' && $piecePosInt == 1){
            $pieceSurrounding = [
                'right' => [chr(ord($piecePosChar)+1), $piecePosInt],
                'up' => [$piecePosChar, $piecePosInt +1],
                'right-up' => [chr(ord($piecePosChar)+1), $piecePosInt + 1],
            ];
        }else if($piecePosChar == 'A' && $piecePosInt == 8){
            $pieceSurrounding = [
                'right' => [chr(ord($piecePosChar)+1), $piecePosInt],
                'down' => [$piecePosChar, $piecePosInt -1],
                'right-down' => [chr(ord($piecePosChar)+1), $piecePosInt - 1],
            ];
        }else if($piecePosChar != 'A' && $piecePosChar != 'H' && $piecePosInt == 1){
            $pieceSurrounding = [
                'right' => [chr(ord($piecePosChar)+1), $piecePosInt],
                'left' => [chr(ord($piecePosChar)-1), $piecePosInt],
                'up' => [$piecePosChar, $piecePosInt +1],
                'right-up' => [chr(ord($piecePosChar)+1), $piecePosInt + 1],
                'left-up' => [chr(ord($piecePosChar)-1), $piecePosInt + 1]
            ];
        }else if($piecePosChar != 'A' && $piecePosChar != 'H' && $piecePosInt == 8){
            $pieceSurrounding = [
                'right' => [chr(ord($piecePosChar)+1), $piecePosInt],
                'left' => [chr(ord($piecePosChar)-1), $piecePosInt],
                'down' => [$piecePosChar, $piecePosInt -1],
                'right-down' => [chr(ord($piecePosChar)+1), $piecePosInt - 1],
                'left-down' => [chr(ord($piecePosChar)-1), $piecePosInt - 1]
            ];
        }
  
        foreach($pieceSurrounding as $key=>$position){
            $isOccupied = $this->isOccupied($blackPieces, $position[0], $position[1], $whitePieces);
            if(!$isOccupied[0]){
                array_push($possibleTargets, $position);
            }
        }

        return $possibleTargets;

    }

    //function switch pawn when pawn reaches end of board

    //------------------------------------------------------------------------------------------------------------------------------------------
}
