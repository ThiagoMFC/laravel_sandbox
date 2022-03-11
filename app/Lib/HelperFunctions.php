<?php

if ( ! function_exists('checkUniqueUsername'))
{

    /**
     * check if username exists
     *
     * @param $name
     * @return string
     */
    function checkUniqueUsername($name){
        $i = 0;
        $username = str_replace(" ", "_", $name);
        $usernameUnique = $username + "_" + strval($i);

        for ( ; ; ){
            $user = User::where('username', $usernameUnique)->first();
            if(!$user){
                break;
            }else{
                $i++;
                $usernameUnique = $username + "_" + strval($i);
            }
        }
        
        return $usernameUnique;
    }
}

?>