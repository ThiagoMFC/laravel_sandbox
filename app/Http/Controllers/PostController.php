<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Carbon\Carbon;
use App\Lib\HelperClass;
use App\Models\Posts;

class PostController extends Controller
{
    
    public function index()
    {
        //
    }

    
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

    
    public function show($id)
    {
        //
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
