<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\UserBattleships;
use Carbon\Carbon;
use App\Lib\HelperClass;
use Illuminate\Support\Facades\DB;

class RandomController extends Controller
{

    public function switchcase(Request $request){

        $validated = $request->validate([
            'a' => 'required',
            'b' => 'required',
            'request' => 'required|string',
        ]);


        /*$response = [
            'validated' => $validated,
        ];

        return response($response, 500);*/

        switch($request['request']){
            case('add'): return $this->add($request);
            case('multiply'): return $this->multiply($request);
            case('divide'): return $this->divide($request);
            case('concatenate'): return $this->concatenate($request);
            default: return $this->defaultResponse();
        }
    }

    function add(Request $request): int{
        return $request['a'] + $request['b'];
    }

    function divide(Request $request): int{
        return $request['a'] / $request['b'];
    }

    function multiply(Request $request): int{
        return $request['a'] * $request['b'];
    }

    function concatenate(Request $request): string{
        return $request['a'] . $request['b'];
    }

    function defaultResponse(){
        $response = [
            "message" => "case not recognized",
            "errors" => [
                "request" => "specified action is not supported",
            ],      
        ];

        return response($response, 400);
    }

    function test(){
        return $this->generateRandomString();
    }

    function generateRandomString($length = 20) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    //CHALLENGES

    /*
    Your goal is to implement a difference function, which subtracts one list from another and returns the result.
    It should remove all values from list a, which are present in list b keeping their order.
    */
    public function diff($string1, $string2){

        $array1 = explode(',', $string1);
        $array2 = explode(',', $string2);

        return array_values(array_diff($array1, $array2));
    }

    /*
    Write an algorithm that takes an array and moves all of the zeros to the end, preserving the order of the other elements.
    */

    public function endZero($string){

        $array = explode(',', $string);
        $tempArray = array_diff($array, [0]);

        $finalArray = array_merge($tempArray, array_fill(0, count($array)-count($tempArray), 0));

        return $finalArray;
    }

    /*
    Implement the function unique_in_order which takes as argument a sequence and returns a list of items without any elements 
    with the same value next to each other and preserving the original order of elements.
    */

    public function uniqueInOrder($string){
        
        $array = str_split($string, 1);
        array_push($array, '-'); //mark end of array. lazy, I know. can't allow '-' in original sequence
        
        $array2 = [];

        for($i = 0; $i < count($array)-1; $i++){ //-1 to prevent [$i+1] going out of bounds
            
            if($array[$i] != $array[$i+1]){
                array_push($array2, $array[$i]);
            }
            
            
        }

        return $array2;
    }


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

    /*
    Return the sum of all the multiples of 3 or 5 below the number passed in. Additionally, if the number is negative, return 0
    */

    public function returnSum($number){

        $sum = 0;

        //no reason to have $i be smaller than 3
        for($i = 3; $i < $number; $i++){
            if($i % 3 === 0 || $i % 5 === 0){
                $sum += $i;
            }
        }

        return $sum;
    }

    /*
    Write an algorithm that will identify valid IPv4 addresses in dot-decimal format. 
    IPs should be considered valid if they consist of four octets, with values between 0 and 255, inclusive.
    */

    public function validateIp($ip){

        /*$array = explode('.', $ip);

        if(count($array) == 4){
            foreach($array as $n){
                if($n > 255 || $n < 0 || !ctype_digit($n)){
                    return false;
                }
            }
        } else {
            return false;
        }

        return true;*/

        //why reinvent the wheel
        if(filter_var($ip, FILTER_VALIDATE_IP)){
            return 'true';
        }else{
            return 'false';
        }

    }

    /*
    Convert a string to a new string where each character in the new string is "(" if that character appears only once in the original string, 
    or ")" if that character appears more than once in the original string. Ignore capitalization when determining if a character is a duplicate.
    */

    public function encodeDuplicate($word){

        $word = strtolower($word);
        $array = str_split($word, 1);
        $encodedString = "";
        
        for($i = 0; $i < count($array); $i++){
            $isDuplicate = false;
            for($j = 0; $j < count($array); $j++){
                if($array[$i] == $array[$j] && $i != $j){
                    $isDuplicate = true;
                }
            }
            if($isDuplicate){
                $encodedString .= ")";
            }else{
                $encodedString .= "(";
            }
        }
        
        return $encodedString;

    }

    /*
    CamelCase method for strings. All words must have their first letter capitalized without spaces.
    */

    public function camelCase($string){

        return str_replace(' ', '', ucwords($string));
    }

    /*
     get an integer array as parameter and process every number from this array in a way that 
     if the number has an integer square root, take it, otherwise square the number.
    */

    public function squareRoot($string){ //this case will be a string from the browser

        $array = explode(',', $string); //transform into array and proceed

        $finalArray = [];
  
        for($i = 0; $i < count($array); $i++ ){
        
            if(fmod(sqrt($array[$i]), 1) == 0){
            array_push($finalArray, sqrt($array[$i]));
            
            }else{
            array_push($finalArray, $array[$i]*$array[$i]);
            }
        }
        
        return $finalArray;
    }

    /*
     Morse code decoder. 1 space (' ') = space between characters, 3 spaces ('   ') = space between words.
    */

    public function morseDecoder($string){

        $morseCode = [
            '.-' => 'a',
            '-...' => 'b',
            '-.-.' => 'c',
            '-..' => 'd',
            '.' => 'e',
            '..-.' => 'f',
            '--.' => 'g',
            '....' => 'h',
            '..' => 'i',
            '.---' => 'j',
            '-.-' => 'k',
            '.-..' => 'l',
            '--' => 'm',
            '-.' => 'n',
            '---' => 'o',
            '.--.' => 'p',
            '--.-' => 'q',
            '.-.' => 'r',
            '...' => 's',
            '-' => 't',
            '..-' => 'u',
            '...-' => 'v',
            '.--' => 'w',
            '-..-' => 'x',
            '-.--' => 'y',
            '--..' => 'z',
            '.----' => '1',
            '..---' => '2',
            '...--' => '3',
            '....-' => '4',
            '.....' => '5',
            '-....' => '6',
            '--...' => '7',
            '---..' => '8',
            '----.' => '9',
            '-----' => '0',
            '.-.-.-' => '.',
            '--..--' => ',',
            '..--..' => '?',
        ];


        $words = explode('   ', trim($string));
        foreach ($words as &$word) {
            $letters = explode(' ', trim($word));
            $decodedWord = '';
            foreach ($letters as $letter) {
                $decodedWord .= $morseCode[$letter];
            }
            $word = $decodedWord;
        }

        return implode(' ', $words);

    }

    public function morseEncoder($string){

        $morseCode = [
            'a' => '.-',
            'b' => '-...',
            'c' => '-.-.',
            'd' => '-..',
            'e' => '.',
            'f' => '..-.',
            'g' => '--.',
            'h' => '....',
            'i' => '..',
            'j' => '.---',
            'k' => '-.-',
            'l' => '.-..',
            'm' => '--',
            'n' => '-.',
            'o' => '---',
            'p' => '.--.',
            'q' => '--.-',
            'r' => '.-.',
            's' => '...',
            't' => '-',
            'u' => '..-',
            'v' => '...-',
            'w' => '.--',
            'x' => '-..-',
            'y' => '-.--',
            'z' => '--..',
            '0' => '-----',
            '1' => '.----',
            '2' => '..---',
            '3' => '...--',
            '4' => '....-',
            '5' => '.....',
            '6' => '-....',
            '7' => '--...',
            '8' => '---..',
            '9' => '----.',
            '.' => '.-.-.-',
            ',' => '--..--',
            '?' => '..--..',
            ' ' => '   ',
        ];

        $words = explode(' ', trim($string));
        foreach ($words as &$word) {
            $letters = str_split($word);
            $decodedWord = '';
            foreach ($letters as $letter) {
                $decodedWord .= $morseCode[$letter]. ' ';
            }
            $word = $decodedWord;
        }

        return implode(str_repeat(' ', 3), $words);
        
    }

}
