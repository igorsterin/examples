<?php

include($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

CModule::IncludeModule("im");

$listAdminsToHideInChat = SmartSonetChat::getListAdminsToHideInChat();

/*echo CUtil::PhpToJSObject([
     'list' => $listAdminsToHideInChat
] );*/

echo json_encode($listAdminsToHideInChat, 256);

//echo 'zfzfc';

