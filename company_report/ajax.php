<?php

use Bitrix\Crm\CompanyTable;
use Bitrix\Main\UserTable;

define('PUBLIC_AJAX_MODE', true);
define('NO_AGENT_CHECK', true);
define('DisableEventsCheck', true);
define("STOP_STATISTICS", true);

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');
header('Content-Type: application/json');

if(!CModule::IncludeModule('tasks') || !CModule::IncludeModule("crm")){
    return false;
}


// Запуск скрипта для генерации отчёта
if (isset($_POST["REPORT_INFO"]) && isset($_POST["REPORT_FILE_TYPE"])) {
    $output = shell_exec("python reporter.py");
    die(json_encode(["data" => $output]));
}


// Фильтр компаний
if (isset($_POST["COMPANY_FILTER"])) {
    $nameFilter = $_POST["COMPANY_FILTER"];
    $searchFilter = [];

    if ($nameFilter) {
        $searchFilter = [
            "LOGIC" => "OR",
            "%TITLE" => $nameFilter
        ];
    }

    $companyList = getCompanyList($searchFilter);

    die(json_encode($companyList));
}


// Фильтр пользователей
if (isset($_POST["EMPLOYEE_FILTER"])) {
    $nameFilter = $_POST["EMPLOYEE_FILTER"];
    $reportType = $_POST["REPORT_TYPE"];
    $searchFilter = [];

    if ($nameFilter) {
        $searchFilter = [
            "LOGIC" => "OR",
            "%NAME" => $nameFilter,
            "%LAST_NAME" => $nameFilter
        ];
    }

    global $USER;
    if (!$USER->IsAdmin() && $reportType !== "TOTAL") {
        $searchFilter = [
            "ID" => $USER->GetID()
        ];
    }

    $userList = getUserList($searchFilter);

    die(json_encode($userList));
}


// Обновление комментария
if (isset($_POST["ELAPSED_TIME_ID"]) && isset($_POST["ELAPSED_TIME_COMMENT"])) {

    $id = $_POST["ELAPSED_TIME_ID"];
    $newComment = $_POST["ELAPSED_TIME_COMMENT"];

    $response = updateElapsedTimeListRow($id, $newComment);

    $data = [
        "UPDATED_ID" => $id,
        "NEW_COMMENT" => $newComment,
    ];

    die(json_encode($data, JSON_PRETTY_PRINT));
}


function updateElapsedTimeListRow(int $id, string $newComment) {
    $response = \Bitrix\Tasks\ElapsedTimeTable::update(
        $id,
        [
            "fields" => [
                "COMMENT_TEXT" => $newComment
            ]
        ]
    );

    return $response;
}


function getCompanyList($filter = []): array
{
    $request = CompanyTable::getList([
        "filter" => $filter,
        "select" => ["ID", "TITLE"],
    ]);

    return $request->fetchAll();
}


function getUserList($filter = []): array
{
    $request = UserTable::getList([
        "filter" => $filter,
        "select" => ["ID", "NAME", "LAST_NAME"] // "SECOND_NAME" - отчество
    ]);

    return $request->fetchAll();
}