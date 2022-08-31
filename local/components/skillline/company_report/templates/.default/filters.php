<?php
    $reportType = $arParams["REPORT_TYPE"];
?>
<header class="mb-2">
    <div class="container d-flex justify-content-evenly align-items-center p-3">
        <!-- Настройка периода отчётности -->
        <section class="d-flex flex-column gap-2">
            <!-- Ввод даты начала отчётности и одинарные стрелки -->
            <div class="d-flex justify-content-center">
                <button class="btn p-0 shadow-none">
                    <svg xmlns="http://www.w3.org/2000/svg" id="arrow__month--previous" class="bi bi-chevron-left arrow__month" viewBox="0 0 16 16" >
                        <path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"/>
                    </svg>
                </button>

                <input type="date" id="date-from" class="form-control" value="<?= $dateFrom; ?>">

                <button class="btn p-0 shadow-none">
                    <svg xmlns="http://www.w3.org/2000/svg" id="arrow__month--next" class="bi bi-chevron-right arrow__month" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z"/>
                    </svg>
                </button>

            </div>

            <!-- Ввод даты окончания отчётности и двойные стрелки -->
            <div class="d-flex">
                <button class="btn p-0 shadow-none">
                    <svg xmlns="http://www.w3.org/2000/svg" id="arrow__period--previous" class="bi bi-chevron-double-left arrow__period" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M8.354 1.646a.5.5 0 0 1 0 .708L2.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"/>
                        <path fill-rule="evenodd" d="M12.354 1.646a.5.5 0 0 1 0 .708L6.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"/>
                    </svg>
                </button>

                <input type="date" id="date-to" class="form-control" value="<?= $dateTo; ?>">

                <button class="btn p-0 shadow-none">
                    <svg xmlns="http://www.w3.org/2000/svg" id="arrow__period--next" class="bi bi-chevron-double-right arrow__period" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M3.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L9.293 8 3.646 2.354a.5.5 0 0 1 0-.708z"/>
                        <path fill-rule="evenodd" d="M7.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L13.293 8 7.646 2.354a.5.5 0 0 1 0-.708z"/>
                    </svg>
                </button>

            </div>

            <!-- Кнопка для установки сегодняшней даты -->
            <div class="d-flex justify-content-center">
                <button id="set-today-button" class="btn btn-secondary">Сегодня</button>
            </div>
        </section>


        <!-- Выбор пользователей, по которым строится отчёт -->
        <section class="dropdown-filters">
            <ul class="d-flex flex-wrap gap-3">
                <li>
                    <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuClickableInside" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                        Сотрудники
                    </button>
                    <ul id="user-list-menu" class="dropdown-menu p-2" aria-labelledby="dropdownMenuClickableInside">
                        <!-- Кнопка "Все" -->
                        <li>
                            <input type="checkbox" class="form-check-input" id="user-id-all" checked onchange="switchCheckboxesState('#user-id-all', '.user-id-checkbox')">
                            <label for="user-id-all" class="form-check-label">Все</label>
                        </li>

                        <!-- Поиск сотрудников -->
                        <li><hr class="dropdown-divider"></li>
                        <li><input id="employee-search" onkeyup="findEmployee('<?=$reportType?>')" type="text" class="form-control"></li>
                        <li><hr class="dropdown-divider"></li>

                        <!-- Список сотрудников -->
                        <?php foreach ($arResult["USER_LIST_FILTER"] as $user): ?>
                            <?php
                                $userId = $user['ID'];
                                $userName = $user["LAST_NAME"] . " " . $user["NAME"];
                            ?>
                            <li class="user-filter-option" data-user-id="<?=$userId?>">
                                <input type="checkbox" id="user-id-<?=$userId?>" data-user-id="<?=$userId?>" class="form-check-input user-id-checkbox" checked  onchange="switchAllCheckboxButtonState('#user-id-all', '.user-id-checkbox');">
                                <label for="user-id-<?=$userId?>" class="form-check-label" ><?=$userName?></label>
                            </li>
                        <?php endforeach; ?>

                    </ul>
                </li>

                <!-- Выбор компаний, по которым строится отчёт -->
                <li>
                    <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuClickableInside" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                        Компании
                    </button>
                    <ul id="company-list-menu" class="dropdown-menu p-2" aria-labelledby="dropdownMenuClickableInside">
                        <!-- Кнопка "Все" -->
                        <li>
                            <input type="checkbox" class="form-check-input" id="company-id-all" checked onchange="switchCheckboxesState('#company-id-all', '.company-id-checkbox');">
                            <label for="company-id-all" class="form-check-label">Все</label>
                        </li>

                        <!-- Поиск компаний -->
                        <li><hr class="dropdown-divider"></li>
                        <li><input id="company-search" onkeyup="findCompany()" type="text" class="form-control"></li>
                        <li><hr class="dropdown-divider"></li>

                        <!-- Список компаний -->
                        <?php foreach ($arResult["COMPANY_LIST_FILTER"] as $company): ?>
                            <?php
                                $companyId = $company["ID"];
                                $companyName = $company["TITLE"];
                            ?>
                            <li class="company-filter-option" data-company-id="<?=$companyId?>">
                                <input type="checkbox" id="company-id-<?=$companyId?>" data-company-id="<?=$companyId?>" class="form-check-input company-id-checkbox" checked onchange="switchAllCheckboxButtonState('#company-id-all', '.company-id-checkbox');">
                                <label for="company-id-<?=$companyId?>" class="form-check-label" ><?=$companyName?></label>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </li>

                <!-- Выбор групп, по которым строится отчёт -->
                <li>
                    <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuClickableInside" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                        Группы
                    </button>
                    <ul class="dropdown-menu p-2" aria-labelledby="dropdownMenuClickableInside">
                        <!-- Кнопка "Все" -->
                        <li>
                            <input type="checkbox" class="form-check-input" id="group-id-all" checked onchange="switchCheckboxesState('#group-id-all', '.group-id-checkbox');">
                            <label for="group-id-all" class="form-check-label">Все</label>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <!-- Список групп -->
                        <?php foreach ($arResult["GROUP_LIST"] as $group): ?>
                            <li>
                                <?php
                                    $groupId = $group["ID"];
                                    $groupName = $group["NAME"];
                                ?>
                                <input type="checkbox" id="group-id-<?=$groupId?>" data-group-id="<?=$groupId?>" class="form-check-input group-id-checkbox" checked onchange="switchAllCheckboxButtonState('#group-id-all', '.group-id-checkbox');">
                                <label for="group-id-<?=$groupId?>" class="form-check-label" ><?=$groupName?></label>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </li>

                <!-- Выбор стадий задач, по которым строится отчёт -->
                <li>
                    <button disabled class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuClickableInside" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                        Стадии
                    </button>
                    <ul class="dropdown-menu p-2" aria-labelledby="dropdownMenuClickableInside">
                        <!-- Кнопка "Все" -->
                        <li>
                            <input type="checkbox" class="form-check-input" id="stage-id-all" checked onchange="switchCheckboxesState('#stage-id-all', '.stage-id-checkbox');">
                            <label for="stage-id-all" class="form-check-label">Все</label>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <!-- Список стадий -->
                        <?php foreach ($arResult["STAGE_LIST"] as $stage): ?>
                            <li>
                                <?php
                                    $stageId = $stage["ID"];
                                    $stageName = $stage["NAME"];
                                ?>
                                <input type="checkbox" id="stage-id-<?=$stageId?>" data-stage-id="<?=$stageId?>" class="form-check-input stage-id-checkbox" checked onchange="switchAllCheckboxButtonState('#stage-id-all', '.stage-id-checkbox');">
                                <label for="stage-id-<?=$stageId?>" class="form-check-label" ><?=$stageName?></label>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </li>

                <!-- Фильтр "Без комментариев" -->
                <li>
                    <input type="checkbox" class="btn-check" id="no-comment-only__checkbox" autocomplete="off" checked/>
                    <label id="no-comment-label" class="btn btn-danger" for="no-comment-only__checkbox">Без комментариев</label>
                </li>

                <!-- Сгенерировать отчёт .docx -->
                <li>
                    <button id="generate-docx" class="btn btn-primary" onclick="generateDOCX()">DOCX</button>
                </li>
            </ul>
        </section>

        <button class="btn btn-success text-nowrap" onclick="generateReport();">Сформировать отчёт</button>


    </div>

</header>