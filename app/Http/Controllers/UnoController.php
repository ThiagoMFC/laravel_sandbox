<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\UnoGame;
use Carbon\Carbon;
use App\Lib\HelperClass;
use Illuminate\Support\Facades\DB;

class UnoController extends Controller
{
    public function startGame(Request $request){

        $fields = $request->validate([
            'user_id' => 'required',
        ]);

        $token = $request->bearerToken();

        $helper = new HelperClass();
        $validateUser = $helper->checkToken($fields['user_id'], $token);

        if(!$validateUser){
            return response([
                'message' => 'invalid request, user invalid',
            ], 401);
        }

        $cards = [
            ['red', '0'],
            ['red', '1'],
            ['red', '1'],
            ['red', '2'],
            ['red', '2'],
            ['red', '3'],
            ['red', '3'],
            ['red', '4'],
            ['red', '4'],
            ['red', '5'],
            ['red', '5'],
            ['red', '6'],
            ['red', '6'],
            ['red', '7'],
            ['red', '7'],
            ['red', '8'],
            ['red', '8'],
            ['red', '9'],
            ['red', '9'],
            ['red', 'block'],
            ['red', 'block'],
            ['red', 'reverse'],
            ['red', 'reverse'],
            ['red', '+2'],
            ['red', '+2'],
            ['green', '1'],
            ['green', '0'],
            ['green', '1'],
            ['green', '2'],
            ['green', '2'],
            ['green', '3'],
            ['green', '3'],
            ['green', '4'],
            ['green', '4'],
            ['green', '5'],
            ['green', '5'],
            ['green', '6'],
            ['green', '6'],
            ['green', '7'],
            ['green', '7'],
            ['green', '8'],
            ['green', '8'],
            ['green', '9'],
            ['green', '9'],
            ['green', 'block'],
            ['green', 'block'],
            ['green', 'reverse'],
            ['green', 'reverse'],
            ['green', '+2'],
            ['green', '+2'],
            ['blue', '0'],
            ['blue', '1'],
            ['blue', '1'],
            ['blue', '2'],
            ['blue', '2'],
            ['blue', '3'],
            ['blue', '3'],
            ['blue', '4'],
            ['blue', '4'],
            ['blue', '5'],
            ['blue', '5'],
            ['blue', '6'],
            ['blue', '6'],
            ['blue', '7'],
            ['blue', '7'],
            ['blue', '8'],
            ['blue', '8'],
            ['blue', '9'],
            ['blue', '9'],
            ['blue', 'block'],
            ['blue', 'block'],
            ['blue', 'reverse'],
            ['blue', 'reverse'],
            ['blue', '+2'],
            ['blue', '+2'],
            ['yellow', '0'],
            ['yellow', '1'],
            ['yellow', '1'],
            ['yellow', '2'],
            ['yellow', '2'],
            ['yellow', '3'],
            ['yellow', '3'],
            ['yellow', '4'],
            ['yellow', '4'],
            ['yellow', '5'],
            ['yellow', '5'],
            ['yellow', '6'],
            ['yellow', '6'],
            ['yellow', '7'],
            ['yellow', '7'],
            ['yellow', '8'],
            ['yellow', '8'],
            ['yellow', '9'],
            ['yellow', '9'],
            ['yellow', 'block'],
            ['yellow', 'block'],
            ['yellow', 'reverse'],
            ['yellow', 'reverse'],
            ['yellow', '+2'],
            ['yellow', '+2'],
            ['wild', 'change'],
            ['wild', 'change'],
            ['wild', 'change'],
            ['wild', 'change'],
            ['wild', '+4'],
            ['wild', '+4'],
            ['wild', '+4'],
            ['wild', '+4']
        ];

        shuffle($cards);

        $player0hand = [];
        $player1hand = [];
        $player2hand = [];
        $player3hand = [];

        // 7 cards to each player
        for($i = 0; $i < 28; $i+=4){
            array_push($player0hand, $cards[$i]);
            unset($cards[$i]);
            array_push($player1hand, $cards[$i+1]);
            unset($cards[$i+1]);
            array_push($player2hand, $cards[$i+2]);
            unset($cards[$i+2]);
            array_push($player3hand, $cards[$i+3]);
            unset($cards[$i+3]);
        }

        //cant have a wild +4 as first on discard pile
        foreach($cards as $key=>$card){
            if($card[1] != '+4'){
                $cardOnTable = $cards[$key];
                unset($cards[$key]);
                break;
            }
        }

        if($cardOnTable[1] == 'reverse'){
            //plays 4 to 0
            $direction = 'counterClockWise';
        }else{
            //plays 0 to 4
            $direction = 'clockWise';
            //if($cardOnTable[1] == 'block'){skip player 0}
        }

        //if clockwise -> player plays first
        //if counterclockwise -> player plays last
        //first to play can be skipped

        $p0h = serialize($player0hand);
        $p1h = serialize($player1hand);
        $p2h = serialize($player2hand);
        $p3h = serialize($player3hand);
        $pile = serialize($cardOnTable);
        $deck = serialize($cards);

        $now = Carbon::now();

        $game = UnoGame::create([
            'user_id' => $fields['user_id'],
            'user_token' => $token,
            'status' => 'ongoing',
            'result' => 'none',
            'player0' => $p0h,
            'player0points' => 0,
            'player1' => $p1h,
            'player1points' => 0,
            'player2' => $p2h,
            'player2points' => 0,
            'player3' => $p3h, 
            'player3points' => 0,
            'deck' => $deck, 
            'pile' => $pile,
            'turns' => 0,
            'direction' => $direction,
            'date_started' => $now
        ]);

        if($game){
            return response([
                'message' => 'game started and is running '. $direction,
                'on table' => $cardOnTable,
                'player' => $player0hand,
                'player 2 hand' => count($player1hand),
                'player 3 hand' => count($player2hand),
                'player 4 hand' => count($player3hand)
            ], 201);
        }else{
            return response([
                'message' => 'failed to start game',
            ], 500);
        }

    }

    public function endGame(Request $request){

        $fields = $request->validate([
            'user_id' => 'required',
        ]);

        $token = $request->bearerToken();

        $helper = new HelperClass();
        $validateUser = $helper->checkToken($fields['user_id'], $token);

        if(!$validateUser){
            return response([
                'message' => 'invalid request, user invalid',
            ], 401);
        }

        $gameUpdate = UnoGame::where('user_id', '=', $fields['user_id'])->where('user_token', '=', $token)->where('status', '=', 'ongoing')->update(['status' => 'finished', 'result' => 'no winner']);

        return response([
            'message' => 'game closed'
        ], 200);
    }
}
