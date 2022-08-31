<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');
$APPLICATION->SetTitle("Отчёт по задачам");
?>

<?php
$APPLICATION->IncludeComponent(
    "skillline:company_report",
    "",
    [
        'REPORT_TYPE' => 'TASK'
    ],
    false
);
?>

<?php
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php');
?>