<?php

use Bitrix\Crm\Category\DealCategory;
use Bitrix\Crm\CompanyTable;
use Bitrix\Main\UserTable;
use Bitrix\Socialnetwork\WorkgroupTable;
use Bitrix\Tasks\ElapsedTimeTable;
use Bitrix\Tasks\TaskTable;

use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;


class CompanyReport extends CBitrixComponent
{
    public $arResult = [];
    private string $companyAffiliationUserField = "UF_CRM_1658408595"; // Принадлежность к компании
    private string $isVisibleInReportUserField = "UF_CRM_1658408431"; // Показывать в отчётности
    private string $taskShortName = "UF_CRM_1658410103"; // Короткое название для задач
    private string $companyEmployee = "UF_CRM_1658410161"; // Сотрудники компании
    private string $hourlyPrice = "UF_CRM_1658410223"; // Стоимость часа

    private string $reportType;
    private bool $isAdmin;
    private $userId;

    private array $companyList;
    private array $userList;
    private array $reportInfo;
    private array $groupList;
    private array $stageList;

    private int $reportYearFrom;
    private int $reportYearTo;
    private int $reportMonthFrom;
    private int $reportMonthTo;



    public function onPrepareComponentParams($arParams): array
    {
        return $arParams;
    }


    public function executeComponent()
    {
        $this->setUserFieldsForLkSkillline();
        /* Сбор данных для запросов */
        $this->reportType = $this->arParams["REPORT_TYPE"];

        global $USER;
        $this->isAdmin = $USER->IsAdmin();
        $this->userId = $USER->GetId();
        $this->setCurrentMonthDateFilters();

        // Вывод только тех компаний, которые нужно выводить в отчёте
        $companyFilter = [$this->isVisibleInReportUserField => "1"];
//        $this->companyList = $this->getCompanyList($companyFilter);
        $this->companyList = $this->getCompanyList();
        $this->groupList = $this->getGroupList();
        $this->stageList = $this->getStageList();
        // Обычный пользователь может посмотреть отчёт только по себе
        $userFilter = [
            "ID" => $this->userId
        ];

        if ($this->reportType == "TOTAL") {
            $this->userList = $this->getUserList();
        }
        else {
            $this->userList = $this->isAdmin ? $this->getUserList() : $this->getUserList($userFilter);
        }

        // Сбор данных из GET запроса
        $taskFilter = [];
        $userFilterIds = explode(";", $_REQUEST["USERS"]);
        $companyFilterIds = explode(";", $_REQUEST["COMPANIES"]);
        $groupFilterIds = explode(";", $_REQUEST["GROUPS"]);
        $dateFrom = implode(".", array_reverse(explode("-", $_REQUEST["DATE_FROM"])));
        $dateTo = implode(".", array_reverse(explode("-", $_REQUEST["DATE_TO"])));
        $isNoComment = $_REQUEST["NO_COMMENT"] ?? "false";

        $this->reportYearFrom = (int)explode(".", $dateFrom)[2];
        $this->reportMonthFrom = (int)explode(".", $dateFrom)[1];
        $this->reportYearTo = (int)explode(".", $dateTo)[2];
        $this->reportMonthTo = (int)explode(".", $dateTo)[1];

        // Установка фильтров для запроса
        $taskFilter[] = $this->getDateFilter($dateFrom, $dateTo);
        $taskFilter[] = $this->getUserFilter($userFilterIds);
        $taskFilter[] = $this->getCompanyFilter($companyFilterIds);
        $taskFilter[] = $this->getGroupFilter($groupFilterIds);
        $taskFilter[] = $this->getNoCommentFilter($isNoComment);
        $this->reportInfo = $this->getTaskList($taskFilter);

        // Добавление названия компании элементу отчёта
        foreach($this->reportInfo as &$reportItem) {
            $ownedBy = $reportItem["OWNER_ID_LIST"];
            if (!$ownedBy) {
                unset($reportItem["OWNER_ID_LIST"]);
                continue;
            }

            // Добавление названия компании вместо ID
            foreach ($ownedBy as $owner) {
                $ownerInfo = explode("_", $owner);
                if ($ownerInfo[0] === "CO") {
                    $companyId = $ownerInfo[1];
                    $company = $this->companyList[$companyId];
                    $ownerTitle = $company["TITLE"];
                    $ownerPricePerHour = explode("|", $company[$this->hourlyPrice])[0];
                    $reportItem["VISIBLE"] = $company[$this->isVisibleInReportUserField]; // Отображать ли в отчёте, остальные UF_* тоже можно здесь доставать
                    $reportItem["OWNER"] = [
                        "COMPANY_ID" => $companyId,
                        "COMPANY_NAME" => $ownerTitle,
                        "COMPANY_PRICE_PER_HOUR" => $ownerPricePerHour
                    ];
                }
            }
            unset($reportItem["OWNER_ID_LIST"]);
        }
        /* Конец сбора данных для запросов */


        switch ($this->reportType) {
            case "TOTAL":
                $this->initTotalReportData();
                break;

            case "TASK":
                $this->initTaskReportData();
                break;

            case "EMPLOYEE":
                $this->initEmployeeReportData();
                break;

            case "COMPANY":
                $this->initCompanyReportData();
                break;

            default:
                echo "Нет такого отчёта";
                break;
        }
        $this->includeComponentTemplate();
    }


    /* Отчёт по всем сотрудникам и компаниям (Суммарный отчёт) */
    private function initTotalReportData(): void
    {
        $reportInfo = [];
        $reportUserList = [];
        foreach($this->reportInfo as $taskInfo) {
            $companyId = $taskInfo["OWNER"]["COMPANY_ID"] ?? null;
            $userElapsedTime = (int)$taskInfo["SECONDS"] ?? null;
            if (!$companyId || !$userElapsedTime) continue;
            $userId = $taskInfo["USER_ID"];
            $userFullName = $taskInfo["USER_LASTNAME"] . " " . $taskInfo["USER_FIRSTNAME"];
            $companyId = $taskInfo["OWNER"]["COMPANY_ID"];
            $companyName = $taskInfo["OWNER"]["COMPANY_NAME"];

            $reportInfo[$companyId]["COMPANY_ID"] = $companyId;
            $reportInfo[$companyId]["COMPANY_NAME"] = $companyName;
            $reportInfo[$companyId]["SUM_TIME"] += $userElapsedTime;

            $reportInfo[$companyId]["ELAPSED_TIME"][$userId]["USER_ID"] = $userId;
            $reportInfo[$companyId]["ELAPSED_TIME"][$userId]["USER_NAME"] = $userFullName;
            $reportInfo[$companyId]["ELAPSED_TIME"][$userId]["SECONDS"] += $userElapsedTime;

            // Здесь только то, по чему есть отчётность
            $reportUserList[$userId]["ID"] = $userId;
            $reportUserList[$userId]["NAME"] = $userFullName;
            $reportUserList[$userId]["SUM_TIME"] += $userElapsedTime;
        }
        // Сортировка по общему времени на проект
        usort($reportInfo, function($a, $b) {
            return $b["SUM_TIME"] - $a["SUM_TIME"];
        });

        // Сортировка по отработанному времени
        usort($reportUserList, function($a, $b) {
            return $b["SUM_TIME"] - $a["SUM_TIME"];
        });

        $this->arResult["REPORT_INFO"] = $reportInfo;
        $this->arResult["USER_LIST"] = $reportUserList; // Нужно для шапки и порядка расположения данных
        $this->arResult["GROUP_LIST"] = $this->groupList;
        $this->arResult["COMPANY_LIST_FILTER"] = $this->companyList;
        $this->arResult["USER_LIST_FILTER"] = $this->userList;
    }


    /* Отчёт по задачам */
    private function initTaskReportData(): void
    {
        /* Начало формирования информации для отчёта */
        $reportInfo = [];
        foreach ($this->reportInfo as $task) {
            $userId = $task["USER_ID"];
            $userName = $task["USER_LASTNAME"] . " " . $task["USER_FIRSTNAME"];

            // Отдел
            $groupId = $task["GROUP_ID"];
            $groupName = $task["GROUP_OWNER_NAME"];

            // Компания
            $companyId = $task["OWNER"]["COMPANY_ID"];
            $companyTitle = $task["OWNER"]["COMPANY_NAME"];
            $companyPricePerHour = $task["OWNER"]["COMPANY_PRICE_PER_HOUR"];

            // Задача
            $taskId = $task["ID"];
            $taskTitle = $task["TITLE"];
            $taskSeconds = $task["SECONDS"];
            $taskElapsedTimeId = $task["ELAPSED_TIME_ID"];
            $taskComment = $task["COMMENT"];
            $taskCommentDate = $task["COMMENT_DATE"];
            $taskElapsedTime = [
                "ID" => $taskElapsedTimeId,
                "SECONDS" => $taskSeconds,
                "COMMENT" => $taskComment,
                "COMMENT_DATE" => $taskCommentDate,
                "USER_ID" => $userId,
                "USER_NAME" => $userName,
                "USER_GROUP_ID" => $groupId,
                "USER_GROUP_NAME" => $groupName,
            ];

            $reportInfo[$companyId]["COMPANY_ID"] = $companyId;
            $reportInfo[$companyId]["COMPANY_TITLE"] = $companyTitle;
            $reportInfo[$companyId]["PRICE_PER_HOUR"] = $companyPricePerHour;
            $reportInfo[$companyId]["SUM_SECONDS"] += $taskSeconds;

            $reportInfo[$companyId]["TASK_INFO"][$taskId]["TASK_ID"] = $taskId;
            $reportInfo[$companyId]["TASK_INFO"][$taskId]["TASK_TITLE"] = $taskTitle;
            $reportInfo[$companyId]["TASK_INFO"][$taskId]["TASK_SUM_TIME"] += $taskSeconds;
            $reportInfo[$companyId]["TASK_INFO"][$taskId]["ELAPSED_TIME"][] = $taskElapsedTime;
        }

        /* Конец формирования информации для отчёта */
        $this->arResult["REPORT_INFO"] = $reportInfo;
        $this->arResult["USER_LIST_FILTER"] = $this->userList;
        $this->arResult["COMPANY_LIST_FILTER"] = $this->companyList;
        $this->arResult["GROUP_LIST"] = $this->groupList;
    }


    /* Отчёт по сотруднику */
    private function initEmployeeReportData(): void
    {
        $defaultProductivity = $this->getDefaultProductivity();
        /* Начало формирования информации для отчёта */
        $reportInfo = [];
        foreach ($this->reportInfo as $task) {
            $userId = $task["USER_ID"];
            $userName = $task["USER_LASTNAME"] . " " . $task["USER_FIRSTNAME"];

            // Отдел
            $groupId = $task["GROUP_ID"];
            $groupName = $task["GROUP_OWNER_NAME"];

            // Компания
            $companyId = $task["OWNER"]["COMPANY_ID"];
            $companyTitle = $task["OWNER"]["COMPANY_NAME"];
            $companyPricePerHour = $task["OWNER"]["COMPANY_PRICE_PER_HOUR"];

            // Задача
            $taskId = $task["ID"];
            $taskTitle = $task["TITLE"];
            $taskSeconds = $task["SECONDS"];
            $taskElapsedTimeId = $task["ELAPSED_TIME_ID"];
            $taskComment = $task["COMMENT"];
            $taskCommentDate = $task["COMMENT_DATE"];
            $taskElapsedTime = [
                "ID" => $taskElapsedTimeId,
                "SECONDS" => $taskSeconds,
                "COMMENT" => $taskComment,
                "COMMENT_DATE" => $taskCommentDate,
            ];

            // Данные для отчёта
            $reportInfo[$userId]["USER_ID"] = $userId;
            $reportInfo[$userId]["USER_NAME"] = $userName;
            $reportInfo[$userId]["USER_GROUP_ID"] = $groupId;
            $reportInfo[$userId]["USER_GROUP_NAME"] = $groupName;

            $reportInfo[$userId]["SUM_SECONDS"] += $taskSeconds;
            $reportInfo[$userId]["DEFAULT_PRODUCTIVITY"] = $defaultProductivity;

            $reportInfo[$userId]["TASK_INFO"][$taskId]["TASK_ID"] = $taskId;
            $reportInfo[$userId]["TASK_INFO"][$taskId]["TASK_TITLE"] = $taskTitle;
            $reportInfo[$userId]["TASK_INFO"][$taskId]["TASK_SUM_SECONDS"] += $taskSeconds;
            $reportInfo[$userId]["TASK_INFO"][$taskId]["COMPANY_ID"] = $companyId;
            $reportInfo[$userId]["TASK_INFO"][$taskId]["COMPANY_TITLE"] = $companyTitle;
            $reportInfo[$userId]["TASK_INFO"][$taskId]["PRICE_PER_HOUR"] = $companyPricePerHour;
            $reportInfo[$userId]["TASK_INFO"][$taskId]["ELAPSED_TIME"][] = $taskElapsedTime;
        }
        /* Конец формирования информации для отчёта */

        $this->arResult["REPORT_INFO"] = $reportInfo;
        $this->arResult["GROUP_LIST"] = $this->groupList;
        $this->arResult["USER_LIST_FILTER"] = $this->userList;
        $this->arResult["COMPANY_LIST_FILTER"] = $this->companyList;
    }


    /* Отчёт по компаниям */
    private function initCompanyReportData(): void
    {
        /* Начало формирования информации для отчёта */
        $reportInfo = [];
        foreach ($this->reportInfo as $task) {
            $userId = $task["USER_ID"];
            $userName = $task["USER_LASTNAME"] . " " . $task["USER_FIRSTNAME"];

            // Отдел
            $groupId = $task["GROUP_ID"];
            $groupName = $task["GROUP_OWNER_NAME"];

            // Компания
            $companyId = $task["OWNER"]["COMPANY_ID"];
            $companyTitle = $task["OWNER"]["COMPANY_NAME"];
            $companyPricePerHour = $task["OWNER"]["COMPANY_PRICE_PER_HOUR"];

            // Задача
            $taskId = $task["ID"];
            $taskTitle = $task["TITLE"];
            $taskSeconds = $task["SECONDS"];
            $taskElapsedTimeId = $task["ELAPSED_TIME_ID"];
            $taskComment = $task["COMMENT"];
            $taskCommentDate = $task["COMMENT_DATE"];
            $taskElapsedTime = [
                "ID" => $taskElapsedTimeId,
                "SECONDS" => $taskSeconds,
                "COMMENT" => $taskComment,
                "COMMENT_DATE" => $taskCommentDate,
                "USER_ID" => $userId,
                "USER_NAME" => $userName,
                "USER_GROUP_ID" => $groupId,
                "USER_GROUP_NAME" => $groupName,
            ];

            $reportInfo[$companyId]["COMPANY_ID"] = $companyId;
            $reportInfo[$companyId]["COMPANY_TITLE"] = $companyTitle;
            $reportInfo[$companyId]["PRICE_PER_HOUR"] = $companyPricePerHour;
            $reportInfo[$companyId]["SUM_SECONDS"] += $taskSeconds;

            $reportInfo[$companyId]["TASK_INFO"][$taskId]["TASK_ID"] = $taskId;
            $reportInfo[$companyId]["TASK_INFO"][$taskId]["TASK_TITLE"] = $taskTitle;
            $reportInfo[$companyId]["TASK_INFO"][$taskId]["TASK_SUM_TIME"] += $taskSeconds;
            $reportInfo[$companyId]["TASK_INFO"][$taskId]["ELAPSED_TIME"][] = $taskElapsedTime;
        }

        /* Конец формирования информации для отчёта */
        $this->arResult["REPORT_INFO"] = $reportInfo;
        $this->arResult["USER_LIST_FILTER"] = $this->userList;
        $this->arResult["COMPANY_LIST_FILTER"] = $this->companyList;
        $this->arResult["GROUP_LIST"] = $this->groupList;
        $this->arResult["STAGE_LIST"] = $this->stageList;
    }


    private function getElapsedTimeList($filter = []): array
    {
        $request = ElapsedTimeTable::getList([
            "order" => [],
            "filter" => $filter,
            "select" => [
                "ID", "USER_ID", "SECONDS", "COMMENT_TEXT", "CREATED_DATE", "TASK_ID", "DATE_START", "DATE_STOP",
                "U_NAME" => "USER.NAME",
                "U_LAST_NAME" => "USER.LAST_NAME",
                "UF_*"
            ],
        ]);

        return $request->fetchAll();
    }


    private function getTaskList($filter = []): array
    {
        // STAGE_ID 53-Новые, 54-Выполняются, 55-Сделаны
        $request = TaskTable::getList([
            "filter" => $filter,
            "select" => [
                "ID", "TITLE", "STAGE_ID", "RESPONSIBLE_ID", "CREATED_DATE", "GROUP_ID",
                "ZOMBIE", // Если удалена, то будет просто помечена ZOMBIE = true
                "ELAPSED_TIME_ID" => "ELAPSED_TIME.ID",
                "SECONDS" => "ELAPSED_TIME.SECONDS",
                "COMMENT" => "ELAPSED_TIME.COMMENT_TEXT",
                "COMMENT_DATE" => "ELAPSED_TIME.CREATED_DATE",
                "USER_ID" => "USER_INFO.ID",
                "USER_FIRSTNAME" => "USER_INFO.NAME",
                "USER_LASTNAME" => "USER_INFO.LAST_NAME",
                "GROUP_OWNER_ID" => "GROUP_INFO.OWNER_ID",
                "GROUP_OWNER_NAME" => "GROUP_INFO.NAME",
                "OWNER_ID_LIST" => "UF_CRM_TASK"
            ],
            "runtime" => [
                new Reference(
                    "ELAPSED_TIME",
                    ElapsedTimeTable::class,
                    Join::on('this.ID', 'ref.TASK_ID'),
                ),
                new Reference(
                    "USER_INFO",
                    UserTable::class,
                    Join::on("this.ELAPSED_TIME.USER_ID", "ref.ID")
                ),
                new Reference(
                    "GROUP_INFO",
                    WorkgroupTable::class,
                    Join::on("this.GROUP_ID", "ref.ID")
                )
            ]
        ]);

        return $request->fetchAll();
    }


    private function getStageList($filter = []): array
    {
        $request = DealCategory::getStageGroupInfos();
        return $request;
    }


    private function getGroupList($filter = []): array
    {
        $request = WorkgroupTable::getList([
            "filter" => $filter,
            "select" => ["ID", "NAME"]
        ]);


        $result = $request->fetchAll();
        return $result;
    }


    private function getCompanyList($filter = []): array
    {
        $request = CompanyTable::getList([
            "filter" => $filter,
            "select" => ["ID", "TITLE", "UF_*"],
        ]);

        $result = $request->fetchAll();

        // Замена ключей элемента на id компании для более быстрого поиска
        $companyList = [];
        foreach($result as $company) {
            $companyList[$company["ID"]] = $company;
        }

        return $companyList;
    }


    // TODO: Сортировать по алфавиту
    private function getUserList($filter = []): array
    {
        $request = UserTable::getList([
            "filter" => $filter,
            "select" => ["ID", "NAME", "LAST_NAME"] // "SECOND_NAME" - отчество
        ]);

        return $request->fetchAll();
    }

    // Берёт данные из GET запроса
    private function getNoCommentFilter(string $isNoComment): array {
        if ($isNoComment == "true") {
            $filter = [
                "ELAPSED_TIME.COMMENT_TEXT" => ""
            ];
            return $filter;
        }
        return [];
    }


    // Берёт данные из GET запроса, без параметров возвращает фильтр для текущего месяца
    // $dateFrom - yyyy-mm-dd
    // $dateTo - yyyy-mm-dd
    private function getDateFilter(string $dateFrom = "", string $dateTo = ""): array
    {
        if ($dateFrom && $dateTo){
            $taskFilter = [
                'LOGIC' => 'AND',
                '>=ELAPSED_TIME.CREATED_DATE' => $dateFrom,
                '<=ELAPSED_TIME.CREATED_DATE' => $dateTo,
            ];
        }
        else {
            $taskFilter = [
                'LOGIC' => 'AND',
                '>=ELAPSED_TIME.CREATED_DATE' => date("Y-m-01"),
                '<=ELAPSED_TIME.CREATED_DATE' => date("Y-m-t")
            ];
        }

        return $taskFilter;
    }


    // Берёт данные из GET запроса
    private function getCompanyFilter(array $companyIds): array
    {
        if (!$companyIds){
            return [];
        }

        $filter = [
            'LOGIC' => 'OR',
        ];

        foreach($companyIds as $companyId) {
            $filter[] = [
                "=UF_CRM_TASK" => "CO_" . $companyId
            ];
        }

        if (!$filter[0]) {
            unset($filter["LOGIC"]);
        }

        return $filter;
    }


    // Берёт данные из GET запроса
    private function getUserFilter(array $userIds): array
    {
        if (!$userIds) {
            return [];
        }

        $filter = [
            'LOGIC' => 'OR',
        ];

        foreach($userIds as $userId) {
            $filter[] = [
                "=USER_INFO.ID" => $userId
            ];
        }

        return $filter;
    }


    private function getGroupFilter(array $groupIds): array
    {
        if (!$groupIds) {
            return [];
        }

        $filter = [
            'LOGIC' => 'OR',
        ];

        foreach($groupIds as $groupId) {
            $filter[] = [
                "=GROUP_ID" => $groupId
            ];
        }

        return $filter;
    }

    // TODO: Делать это на стороне JS (не в приоритете)
    private function setCurrentMonthDateFilters(): void
    {
        $currentMonthFirstDay = date("Y-m-01");
        $currentMonthLastDay = date("Y-m-t");

        if ($_REQUEST["DATE_FROM"] && $_REQUEST["DATE_TO"]) {
            $currentMonthFirstDay = $_REQUEST["DATE_FROM"];
            $currentMonthLastDay = $_REQUEST["DATE_TO"];
        }

        $this->arResult["DATE_FROM"] = $currentMonthFirstDay;
        $this->arResult["DATE_TO"] = $currentMonthLastDay;
    }


    private function initializeUserFields(): void
    {
        $this->arResult["COMPANY_AFFILIATION"] = $this->companyAffiliationUserField; // Принадлежность к компании
        $this->arResult["IS_VISIBLE_IN_REPORT"] = $this->isVisibleInReportUserField; // Показывать в отчётности
        $this->arResult["TASK_SHORT_NAME"] = $this->taskShortName; // Короткое название для задач
        $this->arResult["COMPANY_EMPLOYEE"] = $this->companyEmployee; // Сотрудники компании
        $this->arResult["HOURLY_PRICE"] = $this->hourlyPrice; // Стоимость часа
    }


    private function getDefaultProductivity(): int
    {
        $workCalendar = $this->getWorkCalendar("../work-calendar.csv");
        $defaultProductivity = 0;

        $year = $this->reportYearFrom;
        $month = $this->reportMonthFrom;

        while ($year <= $this->reportYearTo) {
            $defaultProductivity += ((int)$workCalendar[$year][$month]["HOURS"]) * 60 * 60;

            if ($month > 12) {
                $year++;
                $month = 0;
            }

            if($year == $this->reportYearTo && $month >= $this->reportMonthTo) {
                break;
            }
            $month++;
        }

        return $defaultProductivity;
    }


    private function getWorkCalendar(string $filepath): array
    {
        $calendar = [];
        $csv = file($filepath);
        $headers = explode(";", array_shift($csv));

        foreach ($csv as $row) {
            $row = explode(";", $row);
            $calendar[$row[0]][$row[1]] = [
                strtoupper($headers[0]) => $row[0],
                strtoupper($headers[1]) => $row[1],
                strtoupper(trim($headers[2])) => $row[2],
            ];
        }
        return $calendar;
    }


    private function setUserFieldsForLkSkillline() {
        if ($_SERVER["SERVER_NAME"] == "lk.skillline.ru") {
            $this->companyAffiliationUserField = "UF_CRM_1620395970"; // Принадлежность к компании (для lk.skillline.ru = UF_CRM_1620395970)
            $this->isVisibleInReportUserField = "UF_CRM_1620395888338"; // Показывать в отчётности (для lk.skillline.ru = UF_CRM_1620395888338)
            $this->hourlyPrice = "UF_CRM_1620395764254"; // Стоимость часа (для lk.skillline.ru = UF_CRM_1620395764254)
            unset($this->taskShortName);
            unset($this->companyEmployee);
        }
    }

}
    // <pre>print_r($var)</pre>
    function dd($var): void
    {
        echo "<pre>";
        print_r($var);
        echo "</pre>";
    }
