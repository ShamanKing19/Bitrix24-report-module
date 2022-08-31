<main>
    <table class="table table-sm table-striped table-hover table-bordered table-responsive">
        <!-- Заголовки -->
        <thead>
            <tr>
                <th class="text-center align-middle">Компания</th>
                <th class="text-center align-middle">Общее время</th>
                <?php foreach ($arResult["USER_LIST"] as $user): ?>
                    <th class="text-center align-middle">
                        <?= $user["NAME"]; ?>
                    </th>
                <?php endforeach; ?>
            </tr>
        </thead>

        <!-- Данные -->
        <?php
            $companySumTime = 0;
            $userSumTime = []
        ?>
        <tbody class="table-group-divider">
            <?php foreach ($arResult["REPORT_INFO"] as $reportItem): ?>
                <tr>
                    <!-- Компания -->
                    <td class="text-center align-middle text-nowrap">
                        <?= $reportItem["COMPANY_NAME"] ?>
                    </td>
                    <!-- Общее время -->
                    <td class="text-center align-middle">
                        <?= round($reportItem["SUM_TIME"] / 60 / 60, 1); ?>
                    </td>
                    <!-- Пользовательское время -->
                    <?php foreach ($arResult["USER_LIST"] as $user): ?>
                        <?php
                            $userId = $user["ID"];
                            $userTotalTime = $reportItem["ELAPSED_TIME"][$userId]["SECONDS"] ?? 0;
                            $elapsedTime = round((int)$userTotalTime / 60 / 60, 1);;
                            $companySumTime += $elapsedTime;
                        ?>
                        <td class="text-center align-middle">
                            <?= $elapsedTime ?>
                        </td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>

        <!-- Общие данные -->
        <tfoot>
            <tr class="table-active">
                <td class="text-center align-middle">Общее время</td>
                <!-- Общее время по всем проектам -->
                <td class="text-center align-middle">
                    <?= $companySumTime; ?>
                </td>
                <!-- Общее время по каждому пользователю -->
                <?php foreach ($arResult["USER_LIST"] as $user): ?>
                    <td class="text-center align-middle">
                        <?= round($user["SUM_TIME"] / 60 / 60, 1); ?>
                    </td>
                <?php endforeach; ?>
            </tr>
        </tfoot>
    </table>
</main>

<!--<pre>-->
<!--    --><?php //print_r($arResult) ?>
<!--</pre>-->
