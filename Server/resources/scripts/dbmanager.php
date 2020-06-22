<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/' . '/resources/core/scheduler.php';

class DBManagerRequest{
    public $Hostname;
    public $Username;
    public $Password;
    public $Databasename;

    public $QueryString;

    public function __construct($Hostname = "", $Username = "", $Password = "", $Databasename = "")
    {
        $this->Hostname     = $Hostname;
        $this->Username     = $Username;
        $this->Password     = $Password;
        $this->Databasename = $Databasename;
    }

    public static function formatQueryString($String, $Map){
        $ReturnString = $String;
        foreach ($Map as $Template => $Value){
            $ReturnString = str_replace($Template, $Value, $ReturnString);
        }
        return $ReturnString;
    }
}

class DBManager{
    private $QueryResult;

    public function sendQuery(DBManagerRequest $Query = null){
        $MySqlUnit = new mysqli();
        $MySqlUnit->real_connect($Query->Hostname, $Query->Username, $Query->Password, $Query->Databasename);
        $MySqlUnit->real_query($Query->QueryString);
        $QueryData = $MySqlUnit->store_result();
        $this->QueryResult = $QueryData;
        $MySqlUnit->close();
        if ($QueryData === false && $MySqlUnit->errno) return false;
        else return true;
    }

    public function getQueryResult(){
        return $this->QueryResult;
    }

    public static function convertToRequest($Object){
        return new DBManagerRequest($Object->Hostname, $Object->Username, $Object->Password, $Object->Databasename);
    }

    public static function isValidConfig($Object){
        if ($Object->Hostname)      return false;
        if ($Object->Username)      return false;
        if ($Object->Password)      return false;
        if ($Object->Databasename)  return false;
        return true;
    }
}


