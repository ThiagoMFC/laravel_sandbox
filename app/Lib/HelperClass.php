<?php

namespace App\Lib;
use App\Models\User;

class HelperClass{
    
    public function checkUniqueUsername($name){
        $i = 0;
        $username = str_replace(" ", "_", $name);
        $usernameUnique = $username . "_" . strval($i);


        //I'm aware this is unbelievably inneficient and would never fly in Prod. Did it just because...
        for ( ; ; ){
            $user = User::where('username', $usernameUnique)->first();
            if(!$user){
                break;
            }else{
                $i++;
                $usernameUnique = $username . "_" . strval($i);
            }
        }
            
        return $usernameUnique;
    }


    public function checkToken($id, $token){
        $userTokenMatchId = User::where('id', '=', $id)->where('remember_token', '=', $token)->get();
        if($userTokenMatchId->isEmpty()){
            return false;
        }

        return true;
    }
}



?>