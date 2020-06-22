<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/' . '/resources/core/scheduler.php';

require_once $_SERVER['DOCUMENT_ROOT'].'/'. 'resources/scripts/checkhandler.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/'. 'resources/core/utils.php';

$AuthCommon = json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'].'/'. 'resources/scripts/environment/authcommoncfg.json'));

if (!CheckSessionExist())
    Utils::redirect($AuthCommon->redirectlogout);

$PageTemplate = file_get_contents($_SERVER['DOCUMENT_ROOT'].'/'. 'admin/panel/template_index/page_index.template');

echo $PageTemplate;