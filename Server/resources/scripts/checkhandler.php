<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/' . '/resources/core/scheduler.php';
require_once $_SERVER["DOCUMENT_ROOT"] . "/" . "resources/scripts/authmanager.php";

function CheckSessionExist()
{
    $SqlConfig = json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . "resources/scripts/environment/mysqlcfg.json"));

    $SqlConfigSessions = $SqlConfig->AdminAuthSessions;

    $AuthManagerConfig = new AuthMangerConfig("86400");
    $AuthManagerPatterns = new AuthManagerPatterns();

    $AuthManagerCheckSession = new AuthManager($SqlConfigSessions, $AuthManagerPatterns, $AuthManagerConfig);

    $CheckSessionAction = new AuthManagerAction();
    $CheckSessionAction->setCheckLoggedIn();

    return $AuthManagerCheckSession->handler($CheckSessionAction);
}