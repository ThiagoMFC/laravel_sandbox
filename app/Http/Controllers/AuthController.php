<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class AuthController extends Controller
{
    function checkUniqueUsername($name){
        $i = 0;
        $username = str_replace(" ", "_", $name);
        $usernameUnique = $username . "_" . strval($i);

        for ( ; ; ){
            $user = User::where('username', $usernameUnique)->first();
            if(!$user){
                break;
            }else{
                $i++;
                $usernameUnique = $username . "_" . strval($i);
            }
        }
        
        return $usernameUnique;
    }

    public function register(Request $request){
        $fields = $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|string|unique:users,email',
            'password' => 'required|string|confirmed',
        ]);

        $time_now = Carbon::now();

        $username = $this->checkUniqueUsername($fields['first_name'] . "_" . $fields['last_name']);

        $user = User::create([
            'first_name' => $fields['first_name'],
            'last_name' => $fields['last_name'],
            'username' => $username,
            'email' => $fields['email'],
            'password' => bcrypt($fields['password']),
            'last_login' => $time_now,
            'created_at' => $time_now,
            'status' => 'active',

        ]);

        $token = $user->createToken('myLaravelSandboxToken')->plainTextToken;

        $response = [
            'user' => $user,
            'token' => $token
        ];

        return response($response, 201);
    }

    public function login(Request $request){
        //validate fields
        $fields = $request->validate([
            'email' => 'required|string',
            'password' => 'required|string'
        ]);

        //check email data
        $user = User::where('email', $fields['email'])->first();

        if(!$user || !Hash::check($fields['password'], $user->password)){
            return response([
                'message' => 'invalid credentials',
            ], 401);
        }

        //generate auth token
        $token = $user->createToken('myLaravelSandboxToken')->plainTextToken;

        $response = [
            'user' => $user,
            'token' => $token,
        ];

        return response($response, 201);
    }

    public function logout(Request $request){
        auth()->user()->tokens()->delete();
        
        $response = [
            'message' => 'logout successful',
        ];

        return response($response, 200);
    }

    
}
