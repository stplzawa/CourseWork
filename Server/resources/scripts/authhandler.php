<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/' . '/resources/core/scheduler.php';

    require_once $_SERVER["DOCUMENT_ROOT"]."/". "resources/scripts/authmanager.php";
$SqlConfig = json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT']."resources/scripts/environment/mysqlcfg.json"));

$Response =  new class{
    public $Code;
    public $Comment;
    public $Request;
};

$SqlConfigAccounts = $SqlConfig->AdminAuthAccounts;
$SqlConfigSessions = $SqlConfig->AdminAuthSessions;

$AuthManagerConfig      = new AuthMangerConfig( "86400");
$AuthManagerPatterns    = new AuthManagerPatterns();

if (!empty($_POST['Action']) and $_POST['Action'] == "TryAuth"){

    if (empty($_POST['Login']) or empty($_POST['Password'])){
        $Response->Code = 3;
        exit(
            json_encode(
                get_object_vars($Response)
            )
        );
    }

    $AuthManagerCheckSession = new AuthManager($SqlConfigSessions, $AuthManagerPatterns, $AuthManagerConfig);

    $CheckSessionAction = new AuthManagerAction();
    $CheckSessionAction->setCheckLoggedIn();

    $CheckAlreadyLoggedInResult = $AuthManagerCheckSession->handler($CheckSessionAction);
    if ($CheckAlreadyLoggedInResult){
        $Response->Code = 1;
        exit(
            json_encode(
                get_object_vars($Response)
            )
        );
    }

    $AuthManagerLogin = new AuthManager($SqlConfigAccounts, $AuthManagerPatterns, $AuthManagerConfig);

    $TryLoginAction = new AuthManagerAction();
    $TryLoginAction->setTryLogin();

    $ClientLogin      = $_POST['Login'];
    $ClientPassword   = $_POST['Password'];

    $TryLoginResult = $AuthManagerCheckSession->handler($TryLoginAction, new class($ClientLogin, $ClientPassword){
        public $Login;
        public $Password;
        public function __construct($Login, $Password)
        {
            $this->Login = $Login;
            $this->Password = $Password;
        }
    });

    if ($TryLoginResult)
        $Response->Code = true;
    else
        $Response->Code = false;

    exit(
        json_encode(
            get_object_vars($Response)
        )
    );
}
$Response->Code = 2;
exit(
json_encode(
    get_object_vars($Response)
)
);