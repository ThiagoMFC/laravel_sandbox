<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Carbon\Carbon;
use App\Lib\HelperClass;
use App\Models\Posts;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
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
            'deleted' => false,
        ]);

        return response([
            'message' => 'post created'
        ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
