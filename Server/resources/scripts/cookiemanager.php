<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/' . '/resources/core/scheduler.php';

class CookieUnit{
    private $FromNow = false;
    private $Name, $Value, $LiveTime, $Path;

    public function __construct($Name, $Value, $LiveTime, $FromNow = true, $Path = "/")
    {
        $this->FromNow  = $FromNow;

        $this->Name     = $Name;
        $this->Value    = $Value;
        $this->LiveTime = $LiveTime;
        $this->Path     = $Path;
    }

    public function getCookie(){
        $arr = array(
            "Name"  => $this->Name,
            "Value" => $this->Value,
            "Time"  =>  $this->FromNow ? time() + $this->LiveTime : $this->LiveTime,
            "Path"  => $this->Path
        );
        return (object)$arr;
    }

    public static function fromDays($Days){
        return $Days * self::fromHours(24);
    }
    public static function fromHours($Hours){
        return $Hours * self::fromMinutes(60);
    }
    public static function fromMinutes($Minutes){
        return $Minutes * 60;
    }
}

class CookieManager{
    public static function setCookie(CookieUnit $NewCookie){
        $CookieInfo =  $NewCookie->getCookie();
        setcookie($CookieInfo->Name, $CookieInfo->Value, $CookieInfo->Time, $CookieInfo->Path);
    }

    public static function getCookie($Name){
        return $_COOKIE[$Name];
    }

    public static function checkCookie($Name){
        return self::getCookie($Name) == null ? false : true;
    }

    public static function deleteCookie($CookieName){
        setcookie($CookieName, "", 1, '/');
    }
}