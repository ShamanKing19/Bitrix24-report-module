<main class="container-fluid p-0">
    <!-- Цикл по компаниям -->
    <?php foreach ($arResult["REPORT_INFO"] as $reportItem): ?>
        <?php
            $companyId = $reportItem["COMPANY_ID"];
            $companyName = $reportItem["COMPANY_TITLE"];
            $companySumTimeSeconds = $reportItem["SUM_SECONDS"];
            $companyPricePerHour = $reportItem["PRICE_PER_HOUR"];
            $companySumTimeHours = round($companySumTimeSeconds / 60 / 60, 2);
        ?>

        <section class="task-container mb-6 py-3">
            <div class="company__task--header px-3 d-flex justify-content-between">
                <!-- Название компании и её часовая ставка -->
                <ul class="d-flex gap-2 p-0">
                    <li class="company__name">
                        <a href="/crm/company/details/<?=$companyId?>/" class="btn btn-secondary fs-6">
                            <?=$companyName?>
                        </a>
                    </li>
                    <?php if($companyPricePerHour): ?>
                        <li class="company__hourly-price">
                            <a class="btn btn-secondary fs-6">
                                <?=$companyPricePerHour?> руб. / час
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>

                <!-- Информация о времени работы -->
                <ul class="d-flex flex-column gap-1">
                    <li class="company__total-time btn btn-rounded btn-secondary fs-6">
                        Отработано <?=$companySumTimeHours?> ч.
                    </li>
                    <li class="company__total-sum btn btn-rounded btn-secondary fs-6">
                        Итого <?=$companySumTimeHours * $companyPricePerHour?> руб.
                    </li>
                </ul>
            </div>

            <div class="task-info">
                <!-- Цикл по задачам -->
                <?php foreach ($reportItem["TASK_INFO"] as $task): ?>
                    <?php
                        $taskId = $task["TASK_ID"];
                        $taskTitle = $task["TASK_TITLE"];
                        $taskSumTimeSeconds = (int)$task["TASK_SUM_TIME"];
                        $taskSumTimeHours = round($taskSumTimeSeconds / 60 / 60, 2);
                    ?>

                    <ul class="task__header gap-2 px-3">
                        <li class="task__header--title btn btn-secondary btn-rounded fs-6">
                            <a href="/company/personal/user/0/tasks/task/view/<?=$taskId?>/" class="text-white">
                                <?= $taskTitle ?>
                            </a>
                        </li>
                        <li class="task__header--title btn btn-secondary btn-rounded fs-6">
                            <?=$taskSumTimeHours?> ч.
                        </li>
                    </ul>

                    <table class="table table-sm">
                        <thead>
                        <tr>
                            <th class="text-center">Дата</th>
                            <th class="text-center">Затраченное время</th>
                            <th class="text-center">Исполнитель</th>
                            <th class="text-center">Комментарий</th>
                            <th class="text-center">Редактировать</th>
                        </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($task["ELAPSED_TIME"] as $taskNote): ?>
                                <?php
                                    $userId = $taskNote["USER_ID"];
                                    $userName = $taskNote["USER_NAME"];
                                    $userGroupId = $taskNote["USER_GROUP_ID"];
                                    $userGroupName = $taskNote["USER_GROUP_NAME"];

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
                                    <td class="col-md-1 text-center align-middle"><?= $userName ?></td>
                                    <td class="col-md-3 text-center align-middle" id="comment-container-<?=$elapsedTimeId?>">
                                        <?php if($commentText): ?>
                                            <li id="comment-<?=$elapsedTimeId?>" class="task__body--comment">
                                                <div id="comment-text-container-<?=$elapsedTimeId?>"><?= $commentText ?></div>
                                                <!-- Выпадающая textarea для редактирования комментария -->
                                                <div class="collapse" id="comment-collapse-<?=$elapsedTimeId?>">
                                                    <div class="form">
                                                        <textarea id="textarea-<?=$elapsedTimeId?>" class="form-control" placeholder="Заполните комментарий" rows="4"><?=$commentText?></textarea>
                                                        <button id="save-comment-button-<?=$elapsedTimeId?>" class="btn btn-success" onclick="saveComment(<?=$elapsedTimeId?>, <?=$companyId?>, <?=$currentUserId?>)">
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
                                                        <button id="save-comment-button-<?=$elapsedTimeId?>" class="btn btn-success" onclick="saveComment(<?=$elapsedTimeId?>, <?=$companyId?>, <?=$currentUserId?>)">Сохранить</button>
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
