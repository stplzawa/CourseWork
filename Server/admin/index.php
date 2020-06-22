<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/' . '/resources/core/scheduler.php';

require_once $_SERVER['DOCUMENT_ROOT'].'/'. 'resources/scripts/cookiemanager.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/'. 'resources/core/utils.php';
// Get intellisense link
// require_once $_SERVER['DOCUMENT_ROOT'].'/'. 'resources/';

$AuthCommon             = json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'].'/'. 'resources/scripts/environment/authcommoncfg.json'));

/* --------------- ECHO PAGE --------------- */
$PageTemplate           = file_get_contents($_SERVER['DOCUMENT_ROOT'].'/'. 'admin/template_index/page_index.template');
$ScriptLoginTemplate    = file_get_contents($_SERVER['DOCUMENT_ROOT'].'/'. 'admin/template_index/script_auth.template');

$ScriptReplacements = array(
    "#AJAXMODULE#"      => '/resources/scripts/ajax.js',
    "#LOGINFUNCTION#"   => 'login',
    "#STATESTRING#"     => 'StateString',
    "#AUTHHANDLER#"     => '/resources/scripts/authhandler.php',
    "#SUCCESSREDIRECT#" => '/admin/ ',
    "#AUTHFORM#"        => 'AuthForm'
);

$ScriptLoginTemplate = Utils::ReplaceByArray($ScriptLoginTemplate, $ScriptReplacements);

$PageReplacements = array(
    "#PAGECSS#"         => '/resources/styles/adminloginstyle.css',
    "#STATESTRING#"     => 'StateString',
    "#AUTHFORM#"        => 'AuthForm',
    "#LOGINFUNCTION#"   => 'login',
    "#LOGINSCRIPT#"     => $ScriptLoginTemplate
);

$PageTemplate = Utils::ReplaceByArray($PageTemplate, $PageReplacements);
/* ----------------------------------------- */

require_once $_SERVER['DOCUMENT_ROOT'].'/'. 'resources/scripts/checkhandler.php';

if (CheckSessionExist())
    Utils::redirect($AuthCommon->redirectlogin);

echo $PageTemplate;