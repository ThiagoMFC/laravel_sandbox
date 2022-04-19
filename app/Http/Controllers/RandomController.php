<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\UserBattleships;
use App\Models\ChessGame;
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

   //moved to BattleshipController

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


    /*
    You are given two arrays a1 and a2 of strings. Each string is composed with letters from a to z. 
    Let x be any string in the first array and y be any string in the second array. Find the biggest differente 
    between x and y. If a1 and/or a2 are empty return -1
    */

    public function maxDifference($string1, $string2){

        $a1 = explode(',', $string1);
        $a2 = explode(',', $string2);

        if(count($a1) == 0 || count($a2) == 0){
            return -1;
        }

        $diff = 0;
    
        for($i = 0; $i < count($a1); $i++){
            for($j = 0; $j < count($a2); $j++){
            
                $temp = strlen($a1[$i]) - strlen($a2[$j]);
                
                if($temp < 0){
                    $temp = -1 * $temp;
                }
                
                if($temp > $diff){
                    $diff = $temp;
                }
            
            }
        }
        
        return $diff;
    }

    /*
    Convert number from any base to any base
    */

    public function convertNum($num, $currentBase, $convertTo){

        if($currentBase < 2 || $currentBase > 36 || $convertTo < 2 || $convertTo > 36){
            return 'bases can only be between 2 and 36';
        }

        if($currentBase == $convertTo){
            return $num;
        }

        return base_convert($num, $currentBase, $convertTo);
    }

    

}
