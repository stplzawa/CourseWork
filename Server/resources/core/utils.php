<?php

class Utils{
    public static function ReplaceByArray($String, $MapArray){
        $NewString = $String;
        foreach ($MapArray as $replacement => $value){
            $NewString = str_replace($replacement, $value, $NewString);
        }
        return $NewString;
    }

    public static function redirect($Link){
        header("Location: ".$Link, true, 302);
    }

    public static function getPostJson(){
        $JsonStream = file_get_contents('php://input');
        return json_decode($JsonStream, true);
    }
}
