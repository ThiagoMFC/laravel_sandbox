<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserFollow;
use App\Models\Posts;
use Carbon\Carbon;
use App\Lib\HelperClass;
use Illuminate\Support\Facades\DB;

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

    public function getProfile($id, Request $request){

        $user = User::where('id', '=', $id)->where('status', '=', 'active')->get();

        if($user->isEmpty()){
            return response([
                'message' => 'cannot retrieve user',
            ], 500);
        }

        $posts = Posts::where('author_id', '=', $id)->where('status', '!=', 'deleted')->limit(10)->get();

        $token = $request->bearerToken(); //get bearer token

        $helper = new HelperClass();
        $userTokenMatchId =  $helper->checkToken($id, $token); //check if user requested own profile or someone else's

        
        if(!$userTokenMatchId){
            $response = [
                'user' => $user,
                'posts' => $posts,
                'profile' => 'else',
            ];
        }else{
            $response = [
                'user' => $user,
                'posts' => $posts,
                'profile' => 'self',
            ];
        }

        return response($response, 200);
    }

    public function getFeed(Request $request){

        $token = $request->bearerToken();
        $user = DB::table('users as u')->select('u.id as userId')->where('remember_token', '=', $token)->get();

        $follows = DB::table('user_follows as uf')->select('uf.following_id as followingId')->where('uf.follower_id', '=', $user[0]->userId)->where('uf.status', '!=', 'inactive')->get();

        $followIDs = [$user[0]->userId];

        for($i = 0; $i < count($follows); $i++){
            array_push($followIDs, $follows[$i]->followingId);
        }

        $posts = DB::table('posts as p')->select(
            'p.id',
            'p.author_id',
            'p.content',
            'p.post_date',
            'p.status',
            'u.first_name',
            'u.last_name',
            'u.username'
        )->leftJoin('users as u', 'u.id', 'p.author_id')->whereIn('p.author_id', $followIDs)->where('p.status', '!=', 'deleted')->where('u.status', '!=', 'deleted')->get();

        


        return response($posts, 200);
    }
}
