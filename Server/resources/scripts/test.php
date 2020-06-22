<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/'. 'resources/core/utils.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/'. 'resources/scripts/dbmanager.php';

$SqlConfig      = json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'].'/'.'resources/scripts/environment/mysqlcfg.json'));

$DBMgr = new DBManager();

$DBRequest = DBManager::convertToRequest($SqlConfig->Products);

$DBRequest->QueryString = "SELECT * FROM ProductAccess;";

$DBMgr->sendQuery($DBRequest);