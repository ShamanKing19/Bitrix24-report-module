<main class="container-fluid p-0">

    <!-- Цикл по пользователям -->
    <?php foreach ($arResult["REPORT_INFO"] as $reportItem): ?>
        <?php
            $userId = $reportItem["USER_ID"];
            $userName = $reportItem["USER_NAME"];
            $userGroupId = $reportItem["USER_GROUP_ID"];
            $userGroupName = $reportItem["USER_GROUP_NAME"];
            $userHourlyPayment = $reportItem["USER_HOURLY_PAYMENT"] ?? 0;
            $userSumTimeSeconds = $reportItem["SUM_SECONDS"];
            $userSumTimeHours = round($userSumTimeSeconds / 60 / 60, 2);
            $userDefaultProductivitySeconds = $reportItem["DEFAULT_PRODUCTIVITY"];
            $userDefaultProductivityHours = round($userDefaultProductivitySeconds / 60 / 60, 2);
        ?>

        <section class="task-container mb-6 py-3">
            <div class="user__task--header px-3 d-flex justify-content-between">
                <!-- Имя и группа пользователя -->
                <ul class="d-flex gap-2 p-0">
                    <li class="user__name">
                        <a href="/company/personal/user/<?=$userId?>/" class="btn btn-secondary fs-6">
                            <?=$userName?>
                        </a>
                    </li>
                    <li class="user__group">
                        <a href="/workgroups/group/<?=$userGroupId?>/" class="btn btn-secondary fs-6">
                            <?=$userGroupName?>
                        </a>
                    </li>
                    <?php if($userHourlyPayment): ?>
                        <li class="user__hourly-payment">
                            <div class="btn btn-secondary fs-6">
                                <?=$userHourlyPayment?> руб. / час
                            </div>
                        </li>
                    <?php endif; ?>
                </ul>

                <!-- Информация о времени работы -->
                <ul class="d-flex flex-column gap-1">
                    <li class="user__total-time btn btn-rounded btn-secondary fs-6">
                        Отработано <?=$userSumTimeHours?> / <?=$userDefaultProductivityHours?> ч.
                    </li>
                    <?php if($userDefaultProductivityHours > $userSumTimeHours): ?>
                        <li class="user__total-time btn btn-rounded btn-danger fs-6">
                            Недоработано <?=round($userDefaultProductivityHours - $userSumTimeHours, 2) ?> ч.
                        </li>
                    <?php else: ?>
                        <li class="user__total-time btn btn-rounded btn-success fs-6">
                            Переработано <?=round($userSumTimeHours - $userDefaultProductivityHours, 2)?> ч.
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
            <div class="user__task--info">
                <!-- Цикл по задачам пользователя -->
                <?php foreach ($reportItem["TASK_INFO"] as $task): ?>
                    <?php
                        $taskId = $task["TASK_ID"];
                        $taskTitle = $task["TASK_TITLE"];
                        $taskSumSeconds = $task["TASK_SUM_SECONDS"];
                        $taskSumHours = round($taskSumSeconds / 60 / 60, 2);
                        $companyId = $task["COMPANY_ID"];
                        $companyTitle = $task["COMPANY_TITLE"];
                        $companyPricePerHour = $task["PRICE_PER_HOUR"];
                    ?>

                    <ul class="task__header gap-2 px-3">
                        <li class="task__header--title btn btn-secondary btn-rounded fs-6">
                            <a href="/company/personal/user/<?= $userId ?>/tasks/task/view/<?= $taskId ?>/" class="text-white">
                                <?= $taskTitle ?>
                            </a>
                        </li>
                        <li class="task__header--company btn btn-secondary btn-rounded fs-6">
                            <a href="/crm/company/details/<?=$companyId?>/" class="text-white">
                                <?= $companyTitle ?>
                            </a>
                        </li>
                        <li class="task__header--company btn btn-secondary btn-rounded fs-6">
                            <?= $taskSumHours ?> ч.
                        </li>
                        <?php if($companyPricePerHour): ?>
                            <li class="task__header--company-price-per-hour btn btn-secondary btn-rounded fs-6">
                                <?= $companyPricePerHour ?> руб./час.
                            </li>
                        <?php endif; ?>
                    </ul>

                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th class="text-center">Дата</th>
                                <th class="text-center">Затраченное время</th>
                                <th class="text-center">Комментарий</th>
                                <th class="text-center">Редактировать</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($task["ELAPSED_TIME"] as $taskNote): ?>
                                <?php
                                    $elapsedTimeId = $taskNote["ID"];
                                    $seconds = $taskNote["SECONDS"];
                                    $hours = round($seconds / 60 / 60, 2);
                                    $commentText = $taskNote["COMMENT"];
                                    $commentDate = $taskNote["COMMENT_DATE"];
                                    global $USER;
                                    $currentUserId = $USER->IsAdmin() ? 777 : $USER->GetID();
                                ?>

                                <tr>
                                    <td class="col-md-1 text-center align-middle"><?= $commentDate ?></td>
                                    <td class="col-md-1 text-center align-middle"><?= $hours ?> ч.</td>
                                    <td class="col-md-4 text-center align-middle" id="comment-container-<?=$elapsedTimeId?>">
                                        <?php if($commentText): ?>
                                            <li id="comment-<?=$elapsedTimeId?>" class="task__body--comment">
                                                <div id="comment-text-container-<?=$elapsedTimeId?>"><?= $commentText ?></div>
                                                <!-- Выпадающая textarea для редактирования комментария -->
                                                <div class="collapse" id="comment-collapse-<?=$elapsedTimeId?>">
                                                    <div class="form">
                                                        <textarea id="textarea-<?=$elapsedTimeId?>" class="form-control" placeholder="Заполните комментарий" rows="4"><?=$commentText?></textarea>
                                                        <button id="save-comment-button-<?=$elapsedTimeId?>" class="btn btn-success" onclick="saveComment(<?=$elapsedTimeId?>, <?=$userId?>, <?=$currentUserId?>)">
                                                            Сохранить
                                                        </button>
                                                    </div>
                                                </div>
                                            </li>
                                        <?php else: ?>
                                            <li id="comment-<?=$elapsedTimeId?>" class="task__body--comment">
                                                <button id="no-comment-button-<?=$elapsedTimeId?>" class="btn btn-danger" type="button" data-bs-toggle="collapse" data-bs-target="#comment-collapse-<?=$elapsedTimeId?>" aria-expanded="false" aria-controls="comment-collapse-<?=$elapsedTimeId?>">
                                                    Нет комментария
                                                </button>
                                                <div class="collapse" id="comment-collapse-<?=$elapsedTimeId?>">
                                                    <div class="form">
                                                        <textarea id="textarea-<?=$elapsedTimeId?>" class="form-control" placeholder="Заполните комментарий" rows="4"></textarea>
                                                        <button id="save-comment-button-<?=$elapsedTimeId?>" class="btn btn-success" onclick="saveComment(<?=$elapsedTimeId?>, <?=$userId?>, <?=$currentUserId?>)">Сохранить</button>
                                                    </div>
                                                </div>
                                            </li>
                                        <?php endif; ?>
                                    </td>
                                    <!-- Кнопка "Редактировать -->
                                    <td class="col-md-1 text-center align-middle" id="edit-comment-container-<?=$elapsedTimeId?>">
                                        <?php if($commentText): ?>
                                            <button id="no-comment-button-<?=$elapsedTimeId?>" class="btn btn-secondary px-0" onclick="hideCommentContainer(<?=$elapsedTimeId?>)" type="button" data-bs-toggle="collapse" data-bs-target="#comment-collapse-<?=$elapsedTimeId?>" aria-expanded="false" aria-controls="comment-collapse-<?=$elapsedTimeId?>">
                                                <i class="fa-solid fa-pen-to-square mx-2"></i>
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach;?>
                        </tbody>
                    </table>
                <?php endforeach; ?>
            </div>

        </section>
    <?php endforeach; ?>

</main>
