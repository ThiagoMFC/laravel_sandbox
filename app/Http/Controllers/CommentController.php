<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Lib\HelperClass;
use Carbon\Carbon;
use App\Models\Comments;
use App\Models\Posts;

class CommentController extends Controller
{
    public function store($id, Request $request)
    {

        $fields = $request->validate([
            'commenter_id' => 'required|string',
            'content' => 'required|string',
        ]);

        $token = $request->bearerToken();

        $helper = new HelperClass();
        $validateUser = $helper->checkToken($fields['commenter_id'], $token);

        if(!$validateUser){
            return response([
                'message' => 'invalid request, user invalid',
            ], 401);
        }

        $post = Posts::where('id', '=', $id)->where('status', '!=', 'deleted')->get();

        if($post->isEmpty()){
            return response([
                'message' => 'post not found',
            ], 500);
        }

        $time_now = Carbon::now();

        $comment = Comments::create([
            'post_id' => $id,
            'author_id' => $fields['commenter_id'],
            'content' => $fields['content'],
            'date_posted' => $time_now,
            'status' => 'active',
        ]);

        return response([
            'message' => 'comment created'
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $fields = $request->validate([
            'commenter_id' => 'required|string',
            'content' => 'required',
        ]);

        $token = $request->bearerToken();
        $helper = new HelperClass();
        $userTokenMatchId =  $helper->checkToken($fields['commenter_id'], $token);

        if(!$userTokenMatchId){
            return response([
                'message' => 'invalid request, user invalid',
            ], 401);
        }

        $comment = Comments::where('id', '=', $id)->where('author_id', '=', $fields['commenter_id'])->update(['content' => $fields['content'], 'status' => 'edited']);

        if($comment){
            return response([
                'message' => 'update successful',
            ], 201);
        }else{
            return response([
                'message' => 'update failed',
            ], 500);
        }
    }

    
    public function destroy($id)
    {
        //
    }
}
