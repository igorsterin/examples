

<?php

define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);
define('CHK_EVENT', true);

if (empty($_SERVER["DOCUMENT_ROOT"]))
    $_SERVER["DOCUMENT_ROOT"] = "/home/bitrix/www";
include($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
include($_SERVER["DOCUMENT_ROOT"] . "/local/lib/apiBX");
require_once($_SERVER["DOCUMENT_ROOT"] . '/local/lib/DoublesAPI.php');
require_once($_SERVER["DOCUMENT_ROOT"] . '/local/lib/DoubleObj.php');
require_once($_SERVER["DOCUMENT_ROOT"] . '/local/lib/MailDoubleLeads.php');
Bitrix\Main\Loader::includeModule('crm');
\Bitrix\Main\Loader::includeModule('tasks');

$row = 0;
if (($handle = fopen("/home/bitrix/www/local/messageDoubles.csv", "r")) !== FALSE) {

    while (($data = fgetcsv($handle, 1000)) !== FALSE) {
        $row++;
        echo ($row . ': ' . $data[0] . "\n");

        $mailDoubleLeads = new MailDoubleLeads($data[0]);

        $mailDoubleLeads->deleteDoubles();
      //  echo json_encode($mailDoubleLeads, 256);

        Log::logFile($row.') mailID: '.$mailDoubleLeads->mailID.', deleteResult: ', $mailDoubleLeads->getDeleteResult()
            , 'deleteDoubles.log');

        @ob_flush();
        flush();
        clearstatcache();
    }
    fclose($handle);
}