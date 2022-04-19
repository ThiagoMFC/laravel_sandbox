<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\UserBattleships;
use Carbon\Carbon;
use App\Lib\HelperClass;
use Illuminate\Support\Facades\DB;

class BattleshipController extends Controller
{
    /*Battleship-like game -------------------------------------------------------------------------------------------------------------------------------------------------*/

    public function battleshipRules(){

        return response([
            "rules" => [
                "1" => "ships have sequential positions and variable in length (two 3s, three 5s, one 6)",
                "2" => "ships can be positioned either in horizontal (i.e A1, A2, A3...) or vertical (i.e A1, B1, C1...)",
                "3" => "positions are designated by a letter (A to E) and a number (1 to 20)",
                "4" => "after 40 guesses game ends and user loses if haven't found and sank all ships",
                "5" => 'you have to be logged in to play'
            ],
        ], 200);
    }

    function checkRepeatedValueOnTable($position, $table){

        $repeatedPosition = false;

        for($k = 0; $k < sizeOf($table); $k++){
            for($l = 0; $l < sizeOf($table[$k]); $l++){
                if($position == $table[$k][$l]){
                   
                    $repeatedPosition = true;
                    break 2;
                }
            }
        }

        return $repeatedPosition;
    }

    function removeFromTableWithValue($position, $table){

        for($k = 0; $k < sizeOf($table); $k++){
            for($l = 0; $l < sizeOf($table[$k]); $l++){
                if($position == $table[$k][$l]){

                    unset($table[$k][$l]);
                    $table[$k] = array_values($table[$k]);
                   
                    break 2;
                }
            }
        }

        return $table;
    }

    function sankShips($table){
        $count = 0;

        for($i = 0; $i < sizeOf($table); $i++){
            if(empty($table[$i])){
                $count++;
            }
        }

        return $count;
    }

    public function battleshipStart(Request $request){

        $fields = $request->validate([
            'user_id' => 'required',
        ]);

        $token = $request->bearerToken();

        $orientationAxis = ['vertical', 'horizontal'];
        $verticalPositionArray = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];
        $horizontalPositionArray = [1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20];
        $arrayShipSize = [3,5,6];
        $numberOfShips = 6;
        $table = [];
        $repeatedPosition = false;
        $loopCount = 0;
        //
        $errorCount = 0;
        $errorArray = [];
        

        for($i = 0; $i < $numberOfShips; $i++){
            $ships = [];
            $axis = array_rand($orientationAxis);
            $size = array_rand($arrayShipSize);
            $offset = 0;
            $repeatedPosition = false;

            if($orientationAxis[$axis] == 'vertical'){

                //select random position to start
                $letter = array_rand($verticalPositionArray);
                $number = array_rand($horizontalPositionArray); // orientation is vertical so only needs one number

                if($letter+$arrayShipSize[$size] > sizeOf($verticalPositionArray)){
                    $offset = 0 - $arrayShipSize[$size]; //in case $letter + $size go beyond array bounds, set slice to start from the end of array until position equivalent to $size.
                }else{
                    $offset = $letter; //in case it doesn't go out of bounds, set slice to begin at $letter index.
                }

                $positions = array_slice($verticalPositionArray, $offset, $arrayShipSize[$size]);

                for($j = 0; $j < sizeOf($positions); $j++){

                    $position = $positions[$j] . $horizontalPositionArray[$number];

                    $repeatedPosition = $this->checkRepeatedValueOnTable($position, $table);

                    if($repeatedPosition){
                        break;
                    }

                    //try to find if position is already taken by other ship. sometimes it catches, sometimes doesn't. no idea why
                    /*if(array_search($position, array_column($table, 1)) === false){
                        array_push($ships, $position);
                    }else{
                        $errorCount++;
                        array_push($errorArray, $position);
                        $repeatedPosition = true;
                        break;
                    }*/

                    /*for($k = 0; $k < sizeOf($table); $k++){
                        for($l = 0; $l < sizeOf($table[$k]); $l++){
                            if($position == $table[$k][$l]){
                                $errorCount++;
                                array_push($errorArray, $position);
                                $repeatedPosition = true;
                                break 3;
                            }
                        }
                    }*/

                    array_push($ships, $position);

                }

            }else{

                $letter = array_rand($verticalPositionArray); // orientation is horizontal so only needs one letter
                $number = array_rand($horizontalPositionArray); 

                if($number+$arrayShipSize[$size] > sizeOf($horizontalPositionArray)){
                    $offset = 0 - $arrayShipSize[$size]; //in case $number + $size go beyond array bounds, set slice to start from the end of array until position equivalent to $size.
                }else{
                    $offset = $number; //in case it doesn't go out of bounds, set slice to begin at $number index.
                }

                $positions = array_slice($horizontalPositionArray, $offset, $arrayShipSize[$size]);

                for($j = 0; $j < sizeOf($positions); $j++){

                    $position = $verticalPositionArray[$letter] . $positions[$j];

                    $repeatedPosition = $this->checkRepeatedValueOnTable($position, $table);

                    if($repeatedPosition){
                        break;
                    }

                    /*if(array_search($position, array_column($table, 1)) === false){
                        array_push($ships, $position);
                    }else{
                        $errorCount++;
                        array_push($errorArray, $position);
                        $repeatedPosition = true;
                        break;
                    }*/

                    /*for($k = 0; $k < sizeOf($table); $k++){
                        for($l = 0; $l < sizeOf($table[$k]); $l++){
                            if($position == $table[$k][$l]){
                                $errorCount++;
                                array_push($errorArray, $position);
                                $repeatedPosition = true;
                                break 3;
                            }
                        }
                    }*/

                    array_push($ships, $position);

                }

            }

            if($repeatedPosition == false){
                array_push($table, $ships);
            }else{
                $loopCount++;
                $i--;
                if($loopCount == 20){
                    return response([
                        'message' => 'loop exception'
                    ], 500);
                }
            }

            
        }

        $serializedTable = serialize($table);
        $now = Carbon::now();


        $userBattleships = UserBattleships::create([
            'user_id' => $fields['user_id'],
            'user_token' => $token,
            'status' => 'ongoing',
            'result' => 'none',
            'ships' => $serializedTable,
            'hits' => 0,
            'misses' => 0,
            'date_started' => $now
        ]);

        if($userBattleships){
            return response([
                "message" => "game started",
                "ships" => $numberOfShips,
            ], 201);
        }else{
            return response([
                'message' => 'failed to start game',
            ], 500);
        }

    }

    public function battleshipEnd(Request $request){

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

        $battleships = UserBattleships::where('user_id', '=', $fields['user_id'])->where('user_token', '=', $token)->where('status', '=', 'ongoing')->update(['status' => 'finished', 'result' => 'loss']);

        if($battleships){
            return response([
                'message' => 'game closed',
            ], 201);
        }else{
            return response([
                'message' => 'failed to close game',
            ], 500);
        }

    }

    public function battleshipHit(Request $request, $hit){
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

        $ships = DB::table('user_battleships as ub')->select('ub.ships as ships', 'ub.hits as hits', 'ub.misses as misses')->where('user_id', '=', $fields['user_id'])
        ->where('user_token','=', $token)->where('status','=', 'ongoing')->get();

        $table = unserialize($ships[0]->ships);

        $didHit = $this->checkRepeatedValueOnTable($hit, $table);

        if($didHit){

            $table = $this->removeFromTableWithValue($hit, $table);

            $serializedTable = serialize($table);
            $hits = $ships[0]->hits + 1;

            //win condition
            $sankships = $this->sankShips($table);
            if($sankships == 6){
                $ships2 = UserBattleships::where('user_id', '=', $fields['user_id'])->where('user_token', '=', $token)
                ->where('status', '=', 'ongoing')->update(['ships' => $serializedTable, 'hits' => $hits, 'status' => 'finished', 'result' => 'win']);

                return response([
                    "message" => "that's a hit! no more ships, you won!",
                    //"table" => $table
                ]);

            }else{
                $ships2 = UserBattleships::where('user_id', '=', $fields['user_id'])->where('user_token', '=', $token)
                ->where('status', '=', 'ongoing')->update(['ships' => $serializedTable, 'hits' => $hits]);

                return response([
                    "message" => "that's a hit! ". 6-$sankships . " ships to go!",
                    //"table" => $table
                ]);
            }
        }else{

            $misses = $ships[0]->misses + 1;

            //lose condition
            if($misses >= 20){

                $ships2 = UserBattleships::where('user_id', '=', $fields['user_id'])->where('user_token', '=', $token)
                ->where('status', '=', 'ongoing')->update(['misses' => $misses, 'status' => 'finished', 'result' => 'loss']);

                return response([
                    "message" => "aww that's a miss. you have no more guesses. you lost",
                    "ship positions" => $table
                ]);

            }

            $ships2 = UserBattleships::where('user_id', '=', $fields['user_id'])->where('user_token', '=', $token)
            ->where('status', '=', 'ongoing')->update(['misses' => $misses]);

            return response([
                "message" => "aww that's a miss. you have ". 20-$misses . " wrong guesses left",
                //"table" => $table
            ]);
        }

    }

    public function battleshipReveal(Request $request){
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

        $ships = DB::table('user_battleships as ub')->select('ub.ships as ships', 'ub.hits as hits', 'ub.misses as misses')->where('user_id', '=', $fields['user_id'])
        ->where('user_token','=', $token)->where('status','=', 'ongoing')->get();

        $table = unserialize($ships[0]->ships);

        $battleships = UserBattleships::where('user_id', '=', $fields['user_id'])->where('user_token', '=', $token)->where('status', '=', 'ongoing')->update(['status' => 'finished', 'result' => 'loss']);

        if($battleships){
            return response([
                'message' => 'oh no, you gave up!',
                'ship positions' => $table
            ], 201);
        }else{
            return response([
                'message' => 'failed to close game',
            ], 500);
        }

    }

    public function battleshipHint(Request $request){

        $token = $request->bearerToken();
        $fields = $request->validate([
            'user_id' => 'required'
        ]);

        $helper = new HelperClass();
        $validateUser = $helper->checkToken($fields['user_id'], $token);

        if(!$validateUser){
            return response([
                'message' => 'invalid request, user invalid',
            ], 401);
        }

        $ships = DB::table('user_battleships as ub')->select('ub.ships as ships', 'ub.hits as hits', 'ub.misses as misses')->where('user_id', '=', $fields['user_id'])
        ->where('user_token','=', $token)->where('status','=', 'ongoing')->get();

        $table = unserialize($ships[0]->ships);

        $shipsDetails = $this->remainingShips($table);

        return response($shipsDetails, 200);

    }

    function remainingShips($table){
        $ships = [];
        
        $shipCount = 0;
        
        for($i = 0; $i < sizeOf($table); $i++){
            if(!empty($table[$i])){
                $shipCount++;
                $sizeCount = 0;
                for($j = 0; $j < sizeOf($table[$i]); $j++){
                    $sizeCount++;
                }

                $shipDetail = [$shipCount => $sizeCount];
                array_push($ships, $shipDetail);
            }
        }

        return $ships;

    }

    /*-----------------------------------------------------------------------------------------------------------------------------------------------------*/
}
