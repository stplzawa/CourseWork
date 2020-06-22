<?php
include_once $_SERVER['DOCUMENT_ROOT'].'/'.'/resources/core/scheduler.php';

require_once $_SERVER["DOCUMENT_ROOT"]."/"."resources/scripts/dbmanager.php";
require_once $_SERVER["DOCUMENT_ROOT"]."/"."resources/scripts/cookiemanager.php";

$SqlConfig      = json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT']."resources/scripts/environment/mysqlcfg.json"));

class AuthManagerStatus{
    private $State;

    public function setLoggedOut(){
        $this->State = 0;
    }
    public function setLoggedIn(){
        $this->State = 1;
    }

    public function getStatus(){
        return $this->State;
    }
}

class AuthManagerAction{
    private $Action;

    public function setCheckLoggedIn(){
        $this->Action = 1;
    }

    public function setTryLogin(){
        $this->Action = 2;
    }

    public function getStateAction()
    {
        return $this->Action;
    }
}

class AuthManagerPatterns{
    /*public $CHECKAUTHSTATE  = (object)array(
        "QUERY" => <<<CHECKACCEXIST
SELECT * FROM AdminAuthAccounts WHERE Login='#LOGIN#' AND Password='#PASS#' LIMIT 1
CHECKACCEXIST,
        "REPLACEMENTS"  => array(
            "#LOGIN#" => "",
            "#PASS#" => ""
        )
    );*/
////////////////////////////////////////////////

    public $CHECKACCEXIST       = null;
    public $CHECKSESSIONEXIST   = null;
    public $INSERTSESSION       = null;

    public function __construct()
    {
        $this->CHECKACCEXIST = (object)array(
            "QUERY" => 'SELECT * FROM AdminAuthAccounts WHERE Login=\'#LOGIN#\' AND Password=\'#PASS#\' LIMIT 1;',
            "REPLACEMENTS"  => array(
                "#LOGIN#" => "",
                "#PASS#" => ""
            )
        );

        $this->CHECKSESSIONEXIST   = (object)array(
            "QUERY" => 'SELECT * FROM AdminAuthSessions WHERE AuthToken=\'#AUTHTOKEN#\' AND IPAddr=\'#IPADDR#\' LIMIT 1;',
            "REPLACEMENTS"  => array(
                "#AUTHTOKEN#"   => "",
                "#IPADDR#"      => ""
            )
        );

        $this->INSERTSESSION   = (object)array(
            "QUERY" => 'INSERT INTO AdminAuthSessions (AuthToken, IPAddr, ExpirationTime) VALUES (\'#AUTHTOKEN#\', \'#IPADDR#\', \'#EXPTMSTAMP#\');',
            "REPLACEMENTS"  => array(
                "#AUTHTOKEN#"   => "",
                "#IPADDR#"      => "",
                "#EXPTMSTAMP#"    => ""
            )
        );
    }

    public function makeReplacements($Replacements, $ValuesForReplace){
        $ReturnReplacements = $Replacements;
        foreach ($ValuesForReplace as $key => $value){
            if (array_key_exists($key, $Replacements)) $ReturnReplacements[$key] = $value;
        }
        return $ReturnReplacements;
    }
}

class AuthMangerConfig{
    private $ExpirationTime;
    private $StorageCookieName;

    public function __construct($ExpirationTime = 86440, $StorageCookieName = "authtoken")
    {
        if (intval($ExpirationTime) < 1 )   throw new Exception("Wrong expiration time");
        if (empty($StorageCookieName))      throw new Exception("Wrong storage cookie name");

        $this->ExpirationTime       = $ExpirationTime;
        $this->StorageCookieName    = $StorageCookieName;
    }

    public function getConfig(){
        return new class($this->ExpirationTime, $this->StorageCookieName){
            public $ExpirationTime;
            public $StorageCookieName;
            public function __construct($ExpirationTime, $StorageCookieName)
            {
                $this->ExpirationTime       = $ExpirationTime;
                $this->StorageCookieName    = $StorageCookieName;
            }
        };
    }
}


class AuthManager{
    private $Patterns;
    private $DBConfig;
    private $ManagerConfig;

    public function __construct($DBConfig, AuthManagerPatterns $Patterns, AuthMangerConfig $MangerConfig)
    {
        if (DBManager::isValidConfig($DBConfig))    throw new Exception("Wrong config object given");
        if (!$this::isValidPattern($Patterns))       throw new Exception("Wrong patterns object given");
        $this->DBConfig             = $DBConfig;
        $this->Patterns             = $Patterns;
        $this->ManagerConfig        = $MangerConfig->getConfig();
    }

    private function makeQuery($PreparedPattern){
        $Request = DBManager::convertToRequest($this->DBConfig);

        $Request->QueryString = DBManagerRequest::formatQueryString(
                                        $PreparedPattern->QUERY,
                                        $PreparedPattern->REPLACEMENTS
                                    );
        $DBMgr = new DBManager();
        if ($DBMgr->sendQuery($Request) && $DBMgr->getQueryResult()->num_rows != 0){
            /*$DBRecord = new class{
                public $AuthToken;
                public $IPAddr;
                public $CreateTimeStamp;
            };
            $DBRecord->AuthToken        = $DBWMgr->getQueryResult()->fetch_all()[0][0];
            $DBRecord->IPAddr           = $DBWMgr->getQueryResult()->fetch_all()[0][1];
            $DBRecord->CreateTimeStamp  = $DBWMgr->getQueryResult()->fetch_all()[0][2];*/
            return true;
        }
        return false;
    }

    private function checkState(){
        $State = new AuthManagerStatus();
        $StorageData = CookieManager::getCookie($this->ManagerConfig->StorageCookieName);

        if (empty($StorageData)){
            $State->setLoggedOut();
            return $State;
        }

        $ValuesForReplacements = array(
            "#AUTHTOKEN#"   => md5($StorageData),
            "#IPADDR#"      => md5(md5($_SERVER['REMOTE_ADDR']))
        );
        $this->Patterns->CHECKSESSIONEXIST->REPLACEMENTS = $this->Patterns->makeReplacements(
                                                                $this->Patterns->CHECKSESSIONEXIST->REPLACEMENTS,
                                                                $ValuesForReplacements
                                                            );
        if ($this->makeQuery($this->Patterns->CHECKSESSIONEXIST))
            $State->setLoggedIn();
        else
            $State->setLoggedOut();
        return $State;
    }

    public function handler(AuthManagerAction $Action = null, $ExtraData = null){
        $Action = $Action->getStateAction();
        $ReturnResult = false;
        switch ($Action){
            case 1:
                $State = $this->checkState()->getStatus();
                switch ($State){
                    case 0:
                        CookieManager::deleteCookie($this->ManagerConfig->StorageCookieName);
                        $ReturnResult = false;
                        break;
                    case 1:
                        $ReturnResult = true;
                        break;
                }
                break;
            case 2:
                if (empty($ExtraData) or empty($ExtraData->Login) or empty($ExtraData->Password))
                    throw new Exception("Wrong data for password and login");

                $State = $this->tryLoginClient($ExtraData)->getStatus();
                switch ($State){
                    case 0:
                        CookieManager::deleteCookie($this->ManagerConfig->StorageCookieName);
                        $ReturnResult = false;
                        break;
                    case 1:
                        $ReturnResult = true;
                        break;
                }
                break;
        }
        return $ReturnResult;
    }

    private function tryLoginClient($ExtraData){
        $ResultState = new AuthManagerStatus();

        $ValuesForReplacements = array(
            "#LOGIN#"   => md5($ExtraData->Login),
            "#PASS#"    => md5($ExtraData->Password)
        );
        $this->Patterns->CHECKACCEXIST->REPLACEMENTS = $this->Patterns->makeReplacements(
            $this->Patterns->CHECKACCEXIST->REPLACEMENTS,
            $ValuesForReplacements
        );

        $IsCorrectPair = $this->makeQuery($this->Patterns->CHECKACCEXIST);

        if (!$IsCorrectPair) {
            $ResultState->setLoggedOut();
            return $ResultState;
        }

        $AuthToken = $this->generateAuthToken();
        $ExpirationTimestamp = $this->getExpirationTimestamp();

        $ValuesForReplacements = array(
            "#IPADDR#"      => md5(md5($_SERVER['REMOTE_ADDR'])),
            "#AUTHTOKEN#"   => md5($AuthToken),
            "#EXPTMSTAMP#"  => $ExpirationTimestamp
        );

        $this->Patterns->INSERTSESSION->REPLACEMENTS = $this->Patterns->makeReplacements(
            $this->Patterns->INSERTSESSION->REPLACEMENTS,
            $ValuesForReplacements
        );

        $IsCorrectPair = $this->makeQuery($this->Patterns->INSERTSESSION);

        $AuthCookie = new CookieUnit($this->ManagerConfig->StorageCookieName,
                                        $AuthToken,
                                        $ExpirationTimestamp,
                                        false
                                    );
        CookieManager::setCookie($AuthCookie);

        $ResultState->setLoggedIn();

        return $ResultState;
    }

    private function generateAuthToken(){
        return md5($_SERVER['REMOTE_ADDR'].strval(time()).rand());
    }

    private function getExpirationTimestamp(){
        return intval(time() + $this->ManagerConfig->ExpirationTime);
    }

    public function isValidPattern(AuthManagerPatterns $PatternToCheck){
        if ($PatternToCheck->CHECKACCEXIST == null or
            empty($PatternToCheck->CHECKACCEXIST->QUERY) or
            $PatternToCheck->CHECKACCEXIST->REPLACEMENTS == null or empty($PatternToCheck->CHECKACCEXIST->REPLACEMENTS) ) return false;

        if ($PatternToCheck->CHECKSESSIONEXIST == null or
            empty($PatternToCheck->CHECKSESSIONEXIST->QUERY) or
            $PatternToCheck->CHECKSESSIONEXIST->REPLACEMENTS == null or empty($PatternToCheck->CHECKSESSIONEXIST->REPLACEMENTS) ) return false;

        if ($PatternToCheck->INSERTSESSION == null or
            empty($PatternToCheck->INSERTSESSION->QUERY) or
            $PatternToCheck->INSERTSESSION->REPLACEMENTS == null or empty($PatternToCheck->INSERTSESSION->REPLACEMENTS) ) return false;

        //All checks completed successfully
        return true;
    }
}