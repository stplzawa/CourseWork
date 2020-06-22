<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/' . '/resources/core/scheduler.php';

require_once $_SERVER['DOCUMENT_ROOT'].'/'. 'resources/core/utils.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/'. 'resources/scripts/dbmanager.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/'. 'resources/scripts/checkhandler.php';

$SLEEPTIME = 2500;

$_POST = Utils::getPostJson();

/*
 * 10x  - Ошибки заполнения формы
 *      101 - Action в POST-запросе - пуст
 *      102 - Некорректный Action в POST-запросе
 *      103 - Некорректно заполнены данные для запроса
 * 20x  - Ответы на запрос
 *      200 - OK
 *      201 - FALSE REQUEST
 * 300  - Ошибка доступа
 * 400  - Серверная ошибка
 */


$SqlConfig      = json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'].'/'.'resources/scripts/environment/mysqlcfg.json'));

$SqlTemplateCheckProductNotFree = new class{
    public $Pattern = <<<SQLCHECK
SELECT * FROM ProductAccess WHERE HashedKey='#HASHEDKEY#' AND HashedControlInfo='#HASHEDCONTROLINFO#';
SQLCHECK;
    public $Replacement = array(
        "#HASHEDKEY#"   => "",
        "#HASHEDCONTROLINFO#" => ""
    );
};

$SqlTemplateCheckProductFree = new class{
    public $Pattern = <<<SQLCHECK
SELECT * FROM ProductAccess WHERE HashedKey='#HASHEDKEY#' AND IsFree=true;
SQLCHECK;
    public $Replacement = array(
        "#HASHEDKEY#"   => ""
    );
};

$SqlTemplateInsertNew = new class{
    public $Pattern = <<<SQLINSERT
INSERT INTO ProductAccess (HashedKey, RealKey, HashedControlInfo, RealControlInfo, IsFree) VALUES ('#HASHEDKEY#','#REALKEY#', '#HASHEDCONTROLINFO#', '#REALCONTROLINFO#', #ISFREE#);
SQLINSERT;
    public $Replacement = array(
        "#HASHEDKEY#"           => "",
        "#REALKEY#"             => "",
        "#HASHEDCONTROLINFO#"   => "",
        "#REALCONTROLINFO#"     => "",
        "#ISFREE#"              => ""
    );
};

$SqlTemplateDelete = new class{
    public $Pattern = <<<SQLDELETE
DELETE FROM ProductAccess WHERE HashedKey='#HASHEDKEY#';
SQLDELETE;
    public $Replacement = array(
        "#HASHEDKEY#"   => ""
    );
};

$SqlTemplateUpdate = new class{
    public $Pattern = <<<SQLDELETE
UPDATE ProductAccess SET IsFree=false, RealControlInfo='#REALCONTROLINFO#', HashedControlInfo='#HASHEDCONTROLINFO#' WHERE HashedKey='#HASHEDKEY#';
SQLDELETE;
    public $Replacement = array(
        "#REALCONTROLINFO#"     => "",
        "#HASHEDCONTROLINFO#"   => "",
        "#HASHEDKEY#"           => ""
    );
};

$SqlTemplateReturnAll = new class{
    public $Pattern = <<<SQLSELECTALL
SELECT * FROM ProductAccess LIMIT #TOLIMIT# OFFSET #FROMOFFSET#;
SQLSELECTALL;
    public $Replacement = array(
        "#FROMOFFSET#" => "",
        "#TOLIMIT#" => ""
    );
};

$SqlTemplateCount = new class{
    public $Pattern = <<<SQLCOUNT
SELECT COUNT(*) FROM ProductAccess;
SQLCOUNT;
    public $Replacement = array(
    );
};


$DBMgr = new DBManager();

$DBRequest = DBManager::convertToRequest($SqlConfig->Products);

$IsAdmin = CheckSessionExist();

$Response =  new class{
    public $Code;
    public $Comment;
    public $Request;
};

if (empty($_POST['Action'])){
    $Response->Code = 101;
    exit(
        json_encode(
            get_object_vars($Response)
        )
    );
}

switch ($_POST['Action']){
    case "checkproductkey":
////////////////////////////////////////////////////////////////////////////////////////

        if (!empty($_POST['ProductKey']) and !empty($_POST['ControlInfo'])){
            $HashedProductKey     = md5($_POST['ProductKey']);
            $HashedControlInfo    = md5($_POST['ControlInfo']);

            $SqlTemplateCheckProductNotFree->Replacement = array(
                "#HASHEDKEY#"           => $HashedProductKey,
                "#HASHEDCONTROLINFO#"   => $HashedControlInfo
            );
            $DBRequest->QueryString = DBManagerRequest::formatQueryString(
                $SqlTemplateCheckProductNotFree->Pattern,
                $SqlTemplateCheckProductNotFree->Replacement
            );
            if ($DBMgr->sendQuery($DBRequest) && $DBMgr->getQueryResult()->num_rows != 0){
                $Response->Code = 200;
                exit(
                    json_encode(
                        get_object_vars($Response)
                    )
                );
            }
            else{
                $SqlTemplateCheckProductFree->Replacement = array(
                    "#HASHEDKEY#"  => $HashedProductKey
                );
                $DBRequest->QueryString = DBManagerRequest::formatQueryString(
                    $SqlTemplateCheckProductFree->Pattern,
                    $SqlTemplateCheckProductFree->Replacement
                );
                if ($DBMgr->sendQuery($DBRequest) && $DBMgr->getQueryResult()->num_rows != 0){

                    $SqlTemplateUpdate->Replacement = array(
                        "#REALCONTROLINFO#"     => $_POST['ControlInfo'],
                        "#HASHEDCONTROLINFO#"   => md5($_POST['ControlInfo']),
                        "#HASHEDKEY#"           => $HashedProductKey
                    );

                    $DBRequest->QueryString = DBManagerRequest::formatQueryString(
                        $SqlTemplateUpdate->Pattern,
                        $SqlTemplateUpdate->Replacement
                    );

                    $DBMgr->sendQuery($DBRequest);

                    $Response->Code = 200;
                }
                else{
                    $Response->Code = 201;
                    usleep($SLEEPTIME); // For security reasons. Anti-bruteforce delay.
                }

                exit(
                    json_encode(
                        get_object_vars($Response)
                    )
                );
            }
        }
        else{
            $Response->Code = 103;
            exit(
                json_encode(
                    get_object_vars($Response)
                )
            );
        }
////////////////////////////////////////////////////////////////////////////////////////
        break;
    case "getkeyslist":
////////////////////////////////////////////////////////////////////////////////////////
        if ($IsAdmin){
            if (!empty($_POST['FromValue']) and !empty($_POST['ToValue'])){
                $DBRequest->QueryString = $SqlTemplateCount->Pattern;
                if ($DBMgr->sendQuery($DBRequest) && $DBMgr->getQueryResult()->num_rows != 0){
                    $ToValue = 0;
                    $FromValue = 0;

                    $PostFromValue  = intval($_POST['FromValue']);
                    $PostToValue    = intval($_POST['ToValue']);

                    $RowsCount = intval($DBMgr->getQueryResult()->fetch_all()[0][0]);

                    if ($PostFromValue > 1)
                        $FromValue = $PostFromValue;
                    else
                        $FromValue = 0;

                    if ($PostToValue <= $RowsCount and $PostToValue >= $PostFromValue)
                        $ToValue = $PostToValue;
                    else
                        $ToValue = $RowsCount;

                    $SqlTemplateReturnAll->Replacement = array(
                        "#FROMOFFSET#"  => $FromValue,
                        "#TOLIMIT#"     => $ToValue - $FromValue
                    );

                    $DBRequest->QueryString = DBManagerRequest::formatQueryString(
                                                    $SqlTemplateReturnAll->Pattern,
                                                    $SqlTemplateReturnAll->Replacement
                                                );

                    if ($DBMgr->sendQuery($DBRequest) && $DBMgr->getQueryResult()->num_rows != 0){
                        $Response->Code = 200;
                        $Response->Comment = $DBMgr->getQueryResult()->fetch_all();
                        exit(
                            json_encode(
                                get_object_vars($Response)
                            )
                        );
                    }
                    else{
                        $Response->Code = 400;
                        exit(
                            json_encode(
                                get_object_vars($Response)
                            )
                        );
                    }
                }
                else{
                    $Response->Code = 400;
                    exit(
                        json_encode(
                            get_object_vars($Response)
                        )
                    );
                }
            }
            else{
                $Response->Code = 103;
                exit(
                    json_encode(
                        get_object_vars($Response)
                    )
                );
            }
        }
        else{
            $Response->Code = 300;
            exit(
                json_encode(
                    get_object_vars($Response)
                )
            );
        }
////////////////////////////////////////////////////////////////////////////////////////
        break;
    case "addkey":
////////////////////////////////////////////////////////////////////////////////////////
        if ($IsAdmin){
            $RealKey            = '';
            $HashedKey          = '';
            $RealControlInfo    = '';
            $HashedControlInfo  = '';
            $IsFree             = '';

            if (!empty($_POST['ProductKey']))
                $RealKey = $_POST['ProductKey'];
            else
                $RealKey = md5(strval(time()).rand().rand()); // Generating random key

            $HashedKey = md5($RealKey);

            if (!empty($_POST['ControlInfo'])){
                $RealControlInfo    = $_POST['ControlInfo'];
                $HashedControlInfo  = md5($RealControlInfo);
                $IsFree             = 'false';
            }
            else{
                $RealControlInfo    = '';
                $HashedControlInfo  = '';
                $IsFree             = 'true';
            }

            $SqlTemplateInsertNew->Replacement = array(
                "#HASHEDKEY#"           => $HashedKey,
                "#REALKEY#"             => $RealKey,
                "#HASHEDCONTROLINFO#"   => $HashedControlInfo,
                "#REALCONTROLINFO#"     => $RealControlInfo,
                "#ISFREE#"              => $IsFree
            );

            $DBRequest->QueryString = DBManagerRequest::formatQueryString(
                $SqlTemplateInsertNew->Pattern,
                $SqlTemplateInsertNew->Replacement
            );

            $DBMgr->sendQuery($DBRequest);

            $Response->Code = 200;
            exit(
                json_encode(
                    get_object_vars($Response)
                )
            );
        }
        else{
            $Response->Code = 300;
            exit(
                json_encode(
                    get_object_vars($Response)
                )
            );
        }
////////////////////////////////////////////////////////////////////////////////////////
        break;
    case "deletekey":
////////////////////////////////////////////////////////////////////////////////////////
        if ($IsAdmin){
            if (empty($_POST['ProductKey'])){
                $Response->Code = 103;
                exit(
                    json_encode(
                        get_object_vars($Response)
                    )
                );
            }
            $SqlTemplateDelete->Replacement = array(
                "#HASHEDKEY#"           => md5($_POST['ProductKey'])
            );
            $DBRequest->QueryString = DBManagerRequest::formatQueryString(
                $SqlTemplateDelete->Pattern,
                $SqlTemplateDelete->Replacement
            );
            $DBMgr->sendQuery($DBRequest);

            $Response->Code = 200;
            exit(
                json_encode(
                    get_object_vars($Response)
                )
            );
        }
        else{
            $Response->Code = 300;
            exit(
                json_encode(
                    get_object_vars($Response)
                )
            );
        }

////////////////////////////////////////////////////////////////////////////////////////
        break;
    default:
        $Response->Code = 102;
        exit(
            json_encode(
                get_object_vars($Response)
            )
        );
        break;
}