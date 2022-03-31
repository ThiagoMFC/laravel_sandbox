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
        array_push($array, '-'); //mark end of array. lazy, I know. can't allow it in original sequence
        
        $array2 = [];

        for($i = 0; $i < count($array)-1; $i++){ //-1 to prevent [$i+1] going out of bounds
            
            if($array[$i] != $array[$i+1]){
                array_push($array2, $array[$i]);
            }
            
            
        }

        return $array2;
    }
}
