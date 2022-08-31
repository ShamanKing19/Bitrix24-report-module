<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');
$APPLICATION->SetTitle("Суммарный отчёт");
?>

<?php
$APPLICATION->IncludeComponent(
    "skillline:company_report",
    "",
    [
        'REPORT_TYPE' => 'TOTAL'
    ],
    false
);
?>

<?php
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php');
?>