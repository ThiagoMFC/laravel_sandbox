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
}
