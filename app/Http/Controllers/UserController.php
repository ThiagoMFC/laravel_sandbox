<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserFollow;
use Carbon\Carbon;
use App\Lib\HelperClass;

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

        //remember when I said I was gonna use sanctum token as an extra layer of user validation? here it is lol. feels so wrong for whatever reason.
        $helper = new HelperClass();
        $userTokenMatchId =  $helper->checkToken($follower, $token);

        if(!$userTokenMatchId){
            return response([
                'message' => 'invalid request, user invalid',
            ], 401);
        }

        $isAlreadyFollowing = UserFollow::where('follower_id', '=', $follower)->where('following_id', '=', $following)->where('status', '=', 'active')->get();

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

    public function unfollow($follower, $following, Request $request){

        $token = $request->bearerToken(); //get bearer token

        $helper = new HelperClass();
        $userTokenMatchId =  $helper->checkToken($follower, $token);

        if(!$userTokenMatchId){
            return response([
                'message' => 'invalid request, user invalid',
            ], 401);
        }
        $time_now = Carbon::now();

        $userFollow = UserFollow::where('follower_id', '=', $follower)->where('following_id', '=', $following)->update(['status' => 'inactive', 'unfollow_date' => $time_now]);

        return response([
            'message' => 'unfollowed',
        ], 200);

    }

    //user profile

    //user main page/feed

    //other user main page



}
