<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Lib\HelperClass;
use Carbon\Carbon;
use App\Models\Posts;
use App\Models\PostLikes;

class PostLikesController extends Controller
{
    public function addLike(Request $request, $postId){

        $fields = $request->validate([
            'user_id' => 'required',
        ]);

        $token = $request->bearerToken();

        $helper = new HelperClass();
        $validateUser = $helper->checkToken($fields['user_id'], $token);

        if(!$validateUser){
            return response([
                'message' => 'invalid request, invalid user',
            ], 401);
        }

        $post = Posts::where('id', '=', $postId)->where('status', '!=', 'deleted')->exists();

        if(!$post){
            return response([
                'message' => 'Post not found',
            ], 404);
        }

        $isAlreadyLiked = PostLikes::where('user_id', '=', $fields['user_id'])->where('post_id', '=', $postId)->exists();

        if($isAlreadyLiked){
            return response([
                'message' => 'user already likes this',
            ], 401);
        }

        $now = Carbon::now();

        $like = PostLikes::create([
            'post_id' => $postId,
            'user_id' => $fields['user_id'],
            'date_posted' => $now,
            'status' => 'normal',
        ]);

        return response([
            'message' => 'like successful',
        ], 201);

    }
}
