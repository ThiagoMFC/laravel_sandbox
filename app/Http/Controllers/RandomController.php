<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

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


    /*Battleship-like game*/

    public function battleshipRules(){
        return response([
            "rules" => [
                "1" => "ships have sequential positions and variable in length (two 3s, three 5s, one 6)",
                "2" => "ships can be positioned either in horizontal (i.e A1, A2, A3...) or vertical (i.e A1, B1, C1...)",
                "3" => "positions are designated by a letter (A to E) and a number (1 to 20)",
                "4" => "after 40 guesses game ends and user loses if haven't found and sank all ships"
            ],
        ], 200);
    }

    public function battleshipStart(){
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

                    //try to find if position is already taken by other ship. sometimes it catches, sometimes doesn't. no idea why
                    if(array_search($position, array_column($table, 1)) === false){
                        array_push($ships, $position);
                    }else{
                        $errorCount++;
                        array_push($errorArray, $position);
                        $repeatedPosition = true;
                        break;
                    }

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

                    if(array_search($position, array_column($table, 1)) === false){
                        array_push($ships, $position);
                    }else{
                        $errorCount++;
                        array_push($errorArray, $position);
                        $repeatedPosition = true;
                        break;
                    }

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


        return response([
            "a" => $table,
            "b" => $errorCount,
            "c" => $errorArray,
        ], 200);
    }

}
