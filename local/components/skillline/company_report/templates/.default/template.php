<?php
/**
 * @var $component CompanyReport
 */

use Bitrix\Main\Page\Asset;
use Skillline\Bitrix24\Component\CompanyReport;

// Jquery
Asset::getInstance()->addString('<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>');

// Bootstrap
//Asset::getInstance()->addString('<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-gH2yIJqKdNHPEq0n4Mqa/HGKIhSkIHeL5AyhkYV8i59U5AR6csBvApHHNl/vI1Bx" crossorigin="anonymous">');
//Asset::getInstance()->addString('<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-A3rJD856KowSb7dwlZdYEkO39Gagi7vIsF0jrRAoQmDKKtQBHUuLZ9AsSv4jD4Xa" crossorigin="anonymous"></script>');

//MDB
//Asset::getInstance()->addString('<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet"/>');
//Asset::getInstance()->addString('<link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700&display=swap" rel="stylesheet"/>');
//Asset::getInstance()->addString('<link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/4.4.0/mdb.min.css" rel="stylesheet"/>');
//Asset::getInstance()->addString('<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/4.4.0/mdb.min.js"></script>');

?>

<!-- Bootstrap -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-gH2yIJqKdNHPEq0n4Mqa/HGKIhSkIHeL5AyhkYV8i59U5AR6csBvApHHNl/vI1Bx" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-A3rJD856KowSb7dwlZdYEkO39Gagi7vIsF0jrRAoQmDKKtQBHUuLZ9AsSv4jD4Xa" crossorigin="anonymous"></script>

<!-- MDB -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700&display=swap" rel="stylesheet"/>
<link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/4.4.0/mdb.min.css" rel="stylesheet"/>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/4.4.0/mdb.min.js"></script>


<!-- Общие переменные -->
<?php
$dateFrom = $arResult["DATE_FROM"];
$dateTo = $arResult["DATE_TO"];
$filterUserIds = $arResult["FILTER_USER_IDS"];
$filterCompanyIds = $arResult["FILTER_COMPANY_IDS"];
?>

<!-- Фильтры -->
<?php include_once "filters.php"; ?>
<!-- Тип отчёта -->
<?php
    switch($arParams["REPORT_TYPE"]) {
        case "TOTAL":
            include_once "report-templates/total-report-template.php";
            break;
        case "GROUP":
            include_once "report-templates/task-report-template.php";
            break;
        case "EMPLOYEE":
            include_once "report-templates/employee-report-template.php";
            break;
        case "COMPANY":
            include_once "report-templates/company-report-template.php";
            break;
    }
?>
