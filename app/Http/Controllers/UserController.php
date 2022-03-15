<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserFollow;
use Carbon\Carbon;

class UserController extends Controller
{
    //search user
    public function search($name){
        return User::where('first_name', 'like', '%'.$name.'%')->orWhere('last_name', 'like', '%'.$name.'%')
        ->select('first_name', 'last_name', 'username', 'last_login', 'status')->get();
    }

    //follow user
    public function follow($follower, $following, Request $request){

        //check if already following*** INDIVIDUAL AUTHENTICATION
        $token = $request->bearerToken();

        $user = User::where('id', '=', $follower)->where('remember_token', '=', $token)->get();

        if($user->isEmpty()){
            return response([
                'message' => 'invalid request, user invalid',
            ], 401);
        }


        $time_now = Carbon::now();

        $follow = UserFollow::create([
            'follower_id' => $follower,
            'following_id' => $following,
            'status' => 'active',
            'follow_date' => $time_now
        ]);

        $response = [
            'message' => 'followed',
        ];

        return response($response, 201);
    }

    //unfollow user

    //user profile

    //user main page/feed

    //other user main page



}
