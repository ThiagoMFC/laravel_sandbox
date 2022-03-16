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

        $token = $request->bearerToken(); //get bearer token

        $userTokenMatchId = User::where('id', '=', $follower)->where('remember_token', '=', $token)->get(); //check if id and token match records

        if($userTokenMatchId->isEmpty()){
            return response([
                'message' => 'invalid request, user invalid',
            ], 401);
        }

        $isAlreadyFollowing = UserFollow::where('follower_id', '=', $follower)->where('following_id', '=', $following)->get();

        if(!$isAlreadyFollowing->isEmpty()){
            return response([
                'message' => 'already following',
            ], 400);
        }

        $time_now = Carbon::now();

        $follow = UserFollow::create([
            'follower_id' => $follower,
            'following_id' => $following,
            'status' => 'active',
            'follow_date' => $time_now
        ]);

        return response([
            'message' => 'followed',
        ], 201);
    }

    //unfollow user

    //user profile

    //user main page/feed

    //other user main page



}
