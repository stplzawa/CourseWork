<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/'.'/resources/scripts/dbmanager.php';

$SqlConfig = json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT']."resources/scripts/environment/mysqlcfg.json"));

$DBMgr = new DBManager();

$SqlString =  <<<SQLDELETE
DELETE FROM AdminAuthSessions WHERE ExpirationTime < #NOWTIME#;
SQLDELETE;

$DBRequest = DBManager::convertToRequest($SqlConfig->AdminAuthSessions);

$DBRequest->QueryString =  DBManagerRequest::formatQueryString($SqlString, array(
    '#NOWTIME#' => time()
));

$DBMgr->sendQuery($DBRequest);
