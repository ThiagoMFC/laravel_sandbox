<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Lib\HelperClass;
use App\Models\Posts;
use App\Models\PostLikes;

class PostController extends Controller
{   
    public function store(Request $request)
    {
        $fields = $request->validate([
            'author_id' => 'required|string',
            'content' => 'required|string',
        ]);

        $token = $request->bearerToken(); //get bearer token

        $helper = new HelperClass();
        $userTokenMatchId =  $helper->checkToken($fields['author_id'], $token);

        if(!$userTokenMatchId){
            return response([
                'message' => 'invalid request, user invalid',
            ], 401);
        }

        $time_now = Carbon::now();

        $post = Posts::create([
            'author_id' => $fields['author_id'],
            'content' => $fields['content'],
            'post_date' => $time_now,
            'status' => 'normal',
        ]);

        return response([
            'message' => 'post created'
        ], 201);
    }

    
    public function show(Request $request, $id)
    {

        $post = DB::table('posts as p')->select(
            'p.id as postID',
            'p.author_id as authorID', 
            'p.content as content', 
            'p.post_date as date', 
            'p.status as status', 
            'u.first_name as authorFName',
            'u.last_name as authorLName',
            'u.username as authorUsername',
        )->leftjoin('users as u', 'u.id', '=', 'p.author_id')->where('p.id', '=', $id)->where('p.status','!=','deleted')->get();


        if($post->isEmpty()){
            return response([
                'message' => 'post not found',
            ], 500);
        }

        $comments = DB::table('comments as c')->select(
            'c.id as commentID',
            'c.post_id as postID',
            'c.author_id as commenterID',
            'c.content as content',
            'c.date_posted as postedDate',
            'u.first_name as commenterFName',
            'u.last_name as commenterLName',
            'u.username as commenterUsername',
        )->leftjoin('users as u', 'u.id', 'c.author_id')->where('c.post_id', '=', $id)->where('c.status', '!=', 'deleted')->get();

        if($comments->isEmpty()){
            $comments = [
                'message' => 'no comments on this post',
            ];
        }

        $postLikes = PostLikes::where('post_id', '=', $id)->where('status', '!=', 'deleted')->count();

        $token = $request->bearerToken();
        $userLiked = false;

        if($token){
            $user = DB::table('users as u')->select('u.id as userId')->where('remember_token', '=', $token)->get();
            
            $checkUserLiked = PostLikes::where('user_id', '=', $user[0]->userId)->where('post_id', '=', $id)->where('status', '!=', 'deleted')->exists();

            if($checkUserLiked){
                $userLiked = true;
            }
        }

        
        $response = [
            'post' => $post,
            'likes' => $postLikes,
            'has_user_liked' => $userLiked,
            'comments' => $comments,
        ];

        return response($response, 200);
    }

   
    public function update($id, Request $request)
    {
        $fields = $request->validate([
            'author_id' => 'required|string',
            'content' => 'required',
        ]);

        $token = $request->bearerToken();
        $helper = new HelperClass();
        $userTokenMatchId =  $helper->checkToken($fields['author_id'], $token);

        if(!$userTokenMatchId){
            return response([
                'message' => 'invalid request, user invalid',
            ], 401);
        }

        $post = Posts::where('id', '=', $id)->where('author_id', '=', $fields['author_id'])->update(['content' => $fields['content'], 'status' => 'edited']);

        if($post){
            return response([
                'message' => 'update successful',
            ], 201);
        }else{
            return response([
                'message' => 'update failed',
            ], 500);
        }
    }


    public function destroy($id, Request $request)
    {
        $fields = $request->validate([
            'author_id' => 'required|string',
        ]);

        $token = $request->bearerToken();
        $helper = new HelperClass();
        $userTokenMatchId =  $helper->checkToken($fields['author_id'], $token);

        if(!$userTokenMatchId){
            return response([
                'message' => 'invalid request, user invalid',
            ], 401);
        }

        $post = Posts::where('id', '=', $id)->where('author_id', '=', $fields['author_id'])->update(['status' => 'deleted']);

        if($post){
            return response([
                'message' => 'delete successful',
            ], 201);
        }else{
            return response([
                'message' => 'delete failed',
            ], 500);
        }
    }
}
