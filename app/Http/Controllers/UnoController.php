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

        //all cards with their colors and actions. '-' will be pushed to discard pile($cardOnTable) as action whenever a player 
        //is skipped (either because of +2, +4 or block) and/or color is changed.
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
                $cardOnTable = [$cards[$key]];
                unset($cards[$key]);
                break;
            }
        }


        /*
        '-' will be pushed to discard pile($cardOnTable) as action whenever a player 
        is skipped (either because of +2, +4 or block) and/or color is changed.
        below possible expected sequence of play
        first card on table | player 4 plays | player 4 choses color | player 3 is skipped | player 2 plays
        [['red', 'reverse'],  ['wild', '+4'],     ['green', '+4'],       ['green', '-'],     ['green', '2']];
        */

        //$cardOnTable = [['red', 'reverse']];

        $npcHands = [$player1hand, $player2hand, $player3hand];

        if($cardOnTable[0][1] == 'reverse'){
            //plays 4 to 0
            $direction = 'counterClockWise';
            
            //starts round by npc 3 (player 4)
            $play = 2;
            //reverse card loop. let npcPlay while theyre using reverse cards among themselves.
            while($play < 3 && $play >= 0){
                $npcPlay = $this->npcPlay($npcHands[$play], $cards, $cardOnTable);
                $npcHands[$play] = $npcPlay[0];
                $cards = $npcPlay[1];
                $cardOnTable = $npcPlay[2];
                if(end($cardOnTable)[1] != 'reverse'){
                    $play -= 1;
                }else{
                    $play += 1;
                }
            }
        }else{
            //plays 0 to 4
            $direction = 'clockWise';
            if($cardOnTable[0][1] == 'block'){
                //skip player 0 (user) and let npcPlay
                array_push($cardOnTable, [$cardOnTable[0][0], '-']);
                //starts round by npc player 1
                $play = 0;
                while($play < 3 && $play >= 0){
                    $npcPlay = $this->npcPlay($npcHands[$play], $cards, $cardOnTable);
                    $npcHands[$play] = $npcPlay[0];
                    $cards = $npcPlay[1];
                    $cardOnTable = $npcPlay[2];
                    if(end($cardOnTable)[1] != 'reverse'){
                        $play += 1;
                    }else{
                        $play -= 1;
                    }
                }
            }
        }

        $p0h = serialize($player0hand);
        $p1h = serialize($npcHands[0]);
        $p2h = serialize($npcHands[1]);
        $p3h = serialize($npcHands[2]);
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
                'your cards' => $player0hand,
                'your points' => 0,
                'player 2 hand' => count($npcHands[0]),
                //'player 2 hand' => $npcHands[0],
                'player 2 points' => 0,
                'player 3 hand' => count($npcHands[1]),
                //'player 3 hand' => $npcHands[1],
                'player 3 points' => 0,
                'player 4 hand' => count($npcHands[2]),
                //'player 4 hand' => $npcHands[2],
                'player 4 points' => 0,
                'deck' => $cards
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

    public function userPlay(Request $request){

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


    }

    function npcPlay($hand, $deck, $pile){

        //hand, deck and pile are subsets of the same original array so same structure
        
        $topPileColor = end($pile)[0];
        $topPileAction = end($pile)[1];

        //check if special card
        if($topPileAction == '+4'){
            //if +4 draws 4 and misses turn

            for($i = 0; $i < 4; $i++){
                array_push($hand, [end($deck)[0], end($deck)[1]]);
                unset($deck[array_key_last($deck)]);
            }
            
            array_push($pile, [$topPileColor, '-']);

            return [$hand, $deck, $pile];

        }else if($topPileAction == '+2'){
            //if +2 draws 2 and misses turn

            for($i = 0; $i < 2; $i++){
                array_push($hand, [end($deck)[0], end($deck)[1]]);
                unset($deck[array_key_last($deck)]);
            }

            array_push($pile, [$topPileColor, '-']);

            return [$hand, $deck, $pile];

        }else if($topPileAction == 'block'){
            //misses turn

            array_push($pile, [$topPileColor, '-']);

            return [$hand, $deck, $pile];
        }

        //case user has played wild card and selected color, otherwise it's empty
        /*if($color != ''){
            $topPileColor = $color;
        }*/

        $playableCard = false;

        foreach($hand as $key=>$card){
            if($card[0] == $topPileColor || $card[1] == $topPileAction){
                //play one card matching the discard (pile) in color, number, or symbol
                array_push($pile, $card);
                unset($hand[$key]);
                $playableCard = true;
                break;
            }else if($card[0] == 'wild' && $card[1] != '+4'){
                //play a Wild card except wild +4
                array_push($pile, $card);
                unset($hand[$key]);
                $playableCard = true;

                //pick color to be played by next player
                $colors = ['red', 'green', 'blue', 'yellow'];
                $color = array_rand(array_flip($colors), 1);

                array_push($pile, [$color, '-']);

                break;
            }
        }

        if(!$playableCard){

            $wild4Key = array_search('+4', array_column($hand, 1));

            if($wild4Key != ''){
                //if wild +4 available on hand, play it

                array_push($pile, $hand[$wild4Key]);
                unset($hand[$wild4Key]);

                //pick color to be played by next player
                $colors = ['red', 'green', 'blue', 'yellow'];
                $color = array_rand(array_flip($colors), 1);

                array_push($pile, [$color, '+4']);

            }else if($topPileColor == end($deck)[0] || $topPileAction == end($deck)[1]){
                //draw card, play it if possible (color)

                array_push($pile, [end($deck)[0], end($deck)[1]]);

                unset($deck[array_key_last($deck)]);

            }else if(end($deck)[0] == 'wild'){
                //draw card, play it if possible (wild)

                array_push($pile, [end($deck)[0], end($deck)[1]]);

                //pick color to be played by next player
                $colors = ['red', 'green', 'blue', 'yellow'];
                $color = array_rand(array_flip($colors), 1);

                array_push($pile, [$color, '-']);

                unset($deck[array_key_last($deck)]);
 

            }else{
                //draw card, can't play. add to hand

                array_push($hand, [end($deck)[0], end($deck)[1]]);

                unset($deck[array_key_last($deck)]);

            }
        }


        return [$hand, $deck, $pile];

    }
}
