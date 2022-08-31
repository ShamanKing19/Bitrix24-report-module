$(document).ready(() => {
    setTodayButton();
    setArrowsMonthSwitch();
    setArrowsPeriodSwitch();
    switchUserCheckboxes();
    switchCompanyCheckboxes();
    switchGroupCheckboxes();
    switchStageCheckboxes();
    switchNoCommentCheckbox();
    bindEnterToGenerateReport();
});

function generateReport() {
    let userCheckboxes = $(".user-id-checkbox");
    let companyCheckboxes = $(".company-id-checkbox");
    let groupCheckboxes = $(".group-id-checkbox");
    let stageCheckboxes = $(".stage-id-checkbox");
    let dateFrom = $("#date-from").val();
    let dateTo = $("#date-to").val();
    let isNoCommentOnly = !$("#no-comment-only__checkbox").prop("checked"); // Инвертировано для отображения

    let userIds = [];
    let companyIds = [];
    let groupIds = [];
    let stageIds = [];

    for (let userCheckbox of userCheckboxes) {
        if (userCheckbox.checked){
            id = userCheckbox.getAttribute("data-user-id");
            userIds.push(id);
        }
    }

    for (let companyCheckbox of companyCheckboxes) {
        if (companyCheckbox.checked) {
            id = companyCheckbox.getAttribute("data-company-id");
            companyIds.push(id);
        }
    }

    for (let groupCheckbox of groupCheckboxes) {
        if (groupCheckbox.checked) {
            id = groupCheckbox.getAttribute("data-group-id");
            groupIds.push(id);
        }
    }

    for (let stageCheckbox of stageCheckboxes) {
        if (stageCheckbox.checked) {
            id = stageCheckbox.getAttribute("data-stage-id");
            stageIds.push(id);
        }
    }

    let userIdsString = userIds.join(";");
    let companyIdsString = companyIds.join(";");
    let groupIdsString = groupIds.join(";");
    let stageIdsString = stageIds.join(";");
    link = location.href.split("?")[0];
    // TODO: Хэшировать строку запроса
    location.href = link + `?DATE_FROM=${dateFrom}&DATE_TO=${dateTo}&USERS=${userIdsString}&COMPANIES=${companyIdsString}&GROUPS=${groupIdsString}&STAGES=${stageIdsString}&${isNoCommentOnly}&NO_COMMENT=${isNoCommentOnly}`;
}


function saveComment(elapsedTimeId, userId, currentUserId) {
    let textarea = $(`#textarea-${elapsedTimeId}`);
    let comment = textarea.val();

    if (currentUserId !== 777 && userId !== currentUserId) {
        alert("Вы не можете редактировать эту запись!");
        return;
    }

    if (comment === "") {
        alert("Нельзя сохранить пустой комментарий!");
        return;
    }

    $.ajax({
        url: "../ajax.php",
        method: "post",
        dataType: "json",
        data: {
            "ELAPSED_TIME_ID": elapsedTimeId,
            "ELAPSED_TIME_COMMENT": comment
        },
        success: function(data) {
            clearCommentInputFields(data, userId, currentUserId);
        },
        error: function(err) {
            showCommentInputFieldError(err);
        }
    });
}


function generateDOCX() {
    $.ajax({
        url: "../ajax.php",
        method: "post",
        dataType: "json",
        data: {
            "REPORT_INFO": 1905,
            "REPORT_FILE_TYPE": "DOCX"
        },
        success: function(data) {
            console.log(data);
        },
        error: function(error) {
            console.log("Ошибка!", error);
        }


    })
}


function bindEnterToGenerateReport() {
    document.onkeydown = function(e) {
        switch (e.key) {
            case "Enter":
                generateReport();
                break;
        }
    };
}


function findCompany() {
    let input = $("#company-search");
    let inputValue = input.val();

    $.ajax({
        url: "../ajax.php",
        method: "post",
        dataType: "json",
        data: {
            "COMPANY_FILTER": inputValue,
        },
        success: function(data) {
            updateCompanyList(data)
        },
        error: function(error) {
            // console.log(error.responseText);
        }
    })
}


function updateCompanyList(companyList) {
    let companyMenu = document.querySelector("#company-list-menu");
    let companyCheckboxes = document.querySelectorAll(".company-filter-option");
    let defaultCheckboxState = document.querySelector("#company-id-all").checked;

    companyCheckboxes.forEach(checkbox => checkbox.remove());

    for (let company of companyList) {
        let companyId = company.ID;
        let companyName = company.TITLE;

        let newLiElement = document.createElement("li");
        newLiElement.setAttribute("class", "company-filter-option");
        newLiElement.setAttribute("data-company-id", companyId);

        let newInputElement = document.createElement("input");
        newInputElement.setAttribute("type", "checkbox");
        newInputElement.setAttribute("id", `company-id-${companyId}`);
        newInputElement.setAttribute("data-company-id", companyId);
        newInputElement.setAttribute("class", "form-check-input company-id-checkbox");
        newInputElement.setAttribute("onchange", "switchAllCheckboxButtonState('#company-id-all', '.company-id-checkbox');");
        newInputElement.checked = defaultCheckboxState;

        let newLabelElement = document.createElement("label");
        newLabelElement.setAttribute("for", `company-id-${companyId}`);
        newLabelElement.setAttribute("class", "form-check-label");
        newLabelElement.innerText = companyName;

        newLiElement.appendChild(newInputElement);
        newLiElement.appendChild(newLabelElement);

        companyMenu.append(newLiElement);
    }
}


function findEmployee(reportType) {
    let input = $("#employee-search");
    let inputValue = input.val();

    $.ajax({
        url: "../ajax.php",
        method: "post",
        dataType: "json",
        data: {
            "EMPLOYEE_FILTER": inputValue,
            "REPORT_TYPE": reportType
        },
        success: function(data) {
            updateUserList(data)
        },
        error: function(error) {
            // console.log(error.responseText);
        }
    })
}


function updateUserList(userList) {
    let userMenu = document.querySelector("#user-list-menu");
    let userCheckboxes = document.querySelectorAll(".user-filter-option");
    let defaultCheckboxState = document.querySelector("#user-id-all").checked;

    userCheckboxes.forEach(checkbox => checkbox.remove());

    for (let user of userList) {
        let userId = user.ID;
        let userName = user.LAST_NAME + " " +  user.NAME;

        let newLiElement = document.createElement("li");
        newLiElement.setAttribute("class", "user-filter-option");
        newLiElement.setAttribute("data-user-id", userId);

        let newInputElement = document.createElement("input");
        newInputElement.setAttribute("type", "checkbox");
        newInputElement.setAttribute("id", `user-id-${userId}`);
        newInputElement.setAttribute("data-user-id", userId);
        newInputElement.setAttribute("class", "form-check-input user-id-checkbox");
        newInputElement.setAttribute("onchange", "switchAllCheckboxButtonState('#user-id-all', '.user-id-checkbox');");
        newInputElement.checked = defaultCheckboxState;

        let newLabelElement = document.createElement("label");
        newLabelElement.setAttribute("for", `user-id-${userId}`);
        newLabelElement.setAttribute("class", "form-check-label");
        newLabelElement.innerText = userName;

        newLiElement.appendChild(newInputElement);
        newLiElement.appendChild(newLabelElement);

        userMenu.append(newLiElement);
    }
}


function showCommentInputFieldError(error) {
    console.log("Something went wrong while updating row...");
}


function hideCommentContainer(elapsedTimeId) {
    let commentTextContainer = $(`#comment-text-container-${elapsedTimeId}`);
    if (commentTextContainer.prop("style")["display"] === "none") {
        commentTextContainer.show();
    }
    else {
        commentTextContainer.hide();
    }
}


function clearCommentInputFields(data, userId, currentUserId){
    let elapsedTimeId = data["UPDATED_ID"];
    let comment = data["NEW_COMMENT"];

    let commentContainer = $(`#comment-container-${elapsedTimeId}`);
    let commentTextContainer = $(`#comment-text-container-${elapsedTimeId}`);
    let oldComment = $(`#comment-${elapsedTimeId}`);
    let noCommentButton = $(`#no-comment-button-${elapsedTimeId}`);
    let saveCommentButton = $(`#save-comment-button-${elapsedTimeId}`);
    let editCommentButtonContainer = $(`#edit-comment-container-${elapsedTimeId}`);
    let textarea = $(`#textarea-${elapsedTimeId}`);

    oldComment.remove();
    noCommentButton.remove();
    saveCommentButton.remove();
    textarea.remove();
    commentTextContainer.show();

    let commentElement = `
        <li id="comment-${elapsedTimeId}" class="task__body--comment">
            <div id="comment-text-container-${elapsedTimeId}">${comment}</div>
            <div class="collapse" id="comment-collapse-${elapsedTimeId}">
                <div class="form">
                    <textarea id="textarea-${elapsedTimeId}" class="form-control" placeholder="Заполните комментарий" rows="4">${comment}</textarea>
                    <button id="save-comment-button-${elapsedTimeId}" class="btn btn-success" onclick="saveComment(${elapsedTimeId}, ${userId}, ${currentUserId})">
                        Сохранить
                    </button>
                </div>
            </div>
        </li>
    `;

    let editCommentButton = `
        <button id="no-comment-button-${elapsedTimeId}" onclick="hideCommentContainer(${elapsedTimeId})" class="btn btn-secondary px-0" type="button" data-bs-toggle="collapse" data-bs-target="#comment-collapse-${elapsedTimeId}" aria-expanded="false" aria-controls="comment-collapse-${elapsedTimeId}">
            <i class="fa-solid fa-pen-to-square mx-2"></i>
        </button>
    `;

    commentContainer.append(commentElement);
    editCommentButtonContainer.append(editCommentButton);
}


function switchNoCommentCheckbox() {
    let url = new URL(location.href);
    let isNoComment = url.searchParams.get("NO_COMMENT");
    let noCommentCheckbox = $("#no-comment-only__checkbox");
    if (isNoComment === "true") {
        noCommentCheckbox.click()
    }
}


function switchStageCheckboxes() {
    let url = new URL(location.href);
    let stageIdsString = url.searchParams.get("STAGES");
    if (!stageIdsString) {
        return;
    }
    let stageCheckboxAll = $("#stage-id-all");
    let stageIds = stageIdsString.split(";");
    let stageCheckboxes = $(".stage-id-checkbox");
    for (let checkbox of stageCheckboxes) {
        let stageId = checkbox.getAttribute("data-stage-id");
        if (!stageIds.includes(stageId)) {
            checkbox.checked = false;
            stageCheckboxAll.prop("checked", false)
        }
    }
}


function switchGroupCheckboxes() {
    let url = new URL(location.href);
    let groupIdsString = url.searchParams.get("GROUPS");
    if (!groupIdsString) {
        return;
    }
    let groupCheckboxAll = $("#group-id-all");
    let groupIds = groupIdsString.split(";");
    let groupCheckboxes = $(".group-id-checkbox");
    for (let checkbox of groupCheckboxes) {
        let groupId = checkbox.getAttribute("data-group-id");
        if (!groupIds.includes(groupId)) {
            checkbox.checked = false;
            groupCheckboxAll.prop("checked", false)
        }
    }
}


function switchCompanyCheckboxes() {
    let url = new URL(location.href);
    let companyIdsString = url.searchParams.get("COMPANIES");
    if (!companyIdsString) {
        return;
    }
    let companyCheckboxAll = $("#company-id-all");
    let companyIds = companyIdsString.split(";");
    let companyCheckboxes = $(".company-id-checkbox");
    for (let checkbox of companyCheckboxes) {
        let companyId = checkbox.getAttribute("data-company-id");
        if (!companyIds.includes(companyId)) {
            checkbox.checked = false;
            companyCheckboxAll.prop("checked", false)
        }
    }
}


function switchUserCheckboxes() {
    let url = new URL(location.href);

    let userIdsString = url.searchParams.get("USERS");
    if (!userIdsString) {
        return;
    }
    let userCheckboxAll = $("#user-id-all");
    let userIds = userIdsString.split(";");
    let userCheckboxes = $(".user-id-checkbox");
    for (let checkbox of userCheckboxes) {
        let userId = checkbox.getAttribute("data-user-id");
        if (!userIds.includes(userId)) {
            checkbox.checked = false;
            userCheckboxAll.prop("checked", false);
        }
    }
}


function switchAllCheckboxButtonState(checkboxAllId, checkboxElementClass) {
    let switchAllCheckbox = $(checkboxAllId)[0];
    let checkboxes = $(checkboxElementClass);

    for (let element of checkboxes) {
        if (!element.checked) {
            switchAllCheckbox.checked = false;
            break;
        }
        switchAllCheckbox.checked = true;
    }
}


function switchCheckboxesState(checkboxAllId, checkboxElementClass) {
    let switchAllCheckbox = $(checkboxAllId)[0];
    let checkboxes = $(checkboxElementClass);

    if (switchAllCheckbox.checked) {
        for (let element of checkboxes) {
            element.checked = true;
        }
    }
    else {
        for (let element of checkboxes) {
            element.checked = false;
        }
    }
}


function setArrowsPeriodSwitch() {
    let periodFrom = $("#date-from");
    let periodTo = $("#date-to");
    $("#arrow__period--previous").click(() => {
        let periodFromValue = periodFrom.val();
        let periodToValue = periodTo.val();
        let newFromMonthValue = getPreviousPeriodDate(periodFromValue, periodToValue, true);
        let newToMonthValue = getPreviousPeriodDate(periodFromValue, periodToValue);
        periodFrom.val(newFromMonthValue);
        periodTo.val(newToMonthValue);
    });

    $("#arrow__period--next").click(() => {
        let periodFromValue = periodFrom.val();
        let periodToValue = periodTo.val();
        let newFromMonthValue = getNextPeriodDate(periodFromValue, periodToValue, true);
        let newToMonthValue = getNextPeriodDate(periodFromValue, periodToValue);
        periodFrom.val(newFromMonthValue);
        periodTo.val(newToMonthValue);
    });
}


function getNextPeriodDate(dateFromString, dateToString, isFromPeriod = false) {
    let dateFromValue = new Date(dateFromString).valueOf();
    let dateToValue = new Date(dateToString).valueOf();
    let dateDifference = dateToValue - dateFromValue;
    let step = dateDifference === 0 ? 86400000 : dateDifference
    let newDate = isFromPeriod ? new Date(dateFromValue + step) : new Date(dateToValue + step);
    let newDateFormatted = newDate.toLocaleDateString().split(".").reverse().join("-");
    return newDateFormatted;
}


function getPreviousPeriodDate(dateFromString, dateToString, isFromPeriod = false) {
    let dateFromValue = new Date(dateFromString).valueOf();
    let dateToValue = new Date(dateToString).valueOf();
    let dateDifference = dateToValue - dateFromValue;
    let step = dateDifference === 0 ? 86400000 : dateDifference
    let newDate = isFromPeriod ? new Date(dateFromValue - step) : new Date(dateToValue - step);
    let newDateFormatted = newDate.toLocaleDateString().split(".").reverse().join("-");
    return newDateFormatted;
}


function setArrowsMonthSwitch() {
    let periodFrom = $("#date-from");
    let periodTo = $("#date-to");
    $("#arrow__month--previous").click(() => {
        let newFromMonthValue = getPreviousMonthDate(periodFrom.val());
        let newToMonthValue = getPreviousMonthDate(periodTo.val(), true);
        periodFrom.val(newFromMonthValue);
        periodTo.val(newToMonthValue);
    });


    $("#arrow__month--next").click(() => {
        let newFromMonthValue = getNextMonthDate(periodFrom.val());
        let newToMonthValue = getNextMonthDate(periodTo.val(), true);
        periodFrom.val(newFromMonthValue);
        periodTo.val(newToMonthValue);
    });

}


function getNextMonthDate(date, isLastDay = false) {
    let nextMonthDate = new Date(date);
    let year = Number(nextMonthDate.getFullYear());
    let month = Number(nextMonthDate.getMonth()) + 1;

    let newMonth = month + 1 !== 13 ? month + 1 : 1 ;
    let newMonthFormatted = newMonth > 9 ? newMonth : `0${newMonth}`;
    let newYear = newMonth !== 1 ? year : year + 1;
    let newDay = isLastDay ? getDaysInMonth(newMonth-1, year) : "01";

    let newDateString = `${newYear}-${newMonthFormatted}-${newDay}`;
    return newDateString;
}


function getPreviousMonthDate(date, isLastDay = false) {
    let previousMonthDate = new Date(date);
    let year = Number(previousMonthDate.getFullYear());
    let month = Number(previousMonthDate.getMonth()) + 1;

    let newMonth = month - 1 !== 0 ? month - 1 : 12;
    let newMonthFormatted = newMonth > 9 ? newMonth : `0${newMonth}`;
    let newYear = newMonth !== 12 ? year : year - 1;
    let newDay = isLastDay ? getDaysInMonth(newMonth-1, year) : "01";

    let newDateString = `${newYear}-${newMonthFormatted}-${newDay}`;
    return newDateString;
}


function setTodayButton() {
    $("#set-today-button").on('click', (e) => {
        const today = new Date();
        let tomorrow = new Date();
        tomorrow.setDate(Number(today.getDate())+1);
        $("#date-from").val(formatDate(today));
        $("#date-to").val(formatDate(tomorrow));
    });
}


function getDaysInMonth(monthNumber, year) {
    let daysMonthNow = new Date(year, monthNumber, 1);  // 1 число месяца отсчёта
    let daysMonthPrev = new Date(year, monthNumber + 1, 1);  // 1 число следующего месяца
    let daysNumber = Math.round((daysMonthPrev - daysMonthNow) / 1000 / 3600 / 24);  // Количество дней в текущем месяце
    if (daysMonthPrev.getMonth() === 2) {
        return daysMonthNow.getFullYear() % 4 === 0 ? 29 : 28;
    }
    return daysNumber;
}


// Задаёт формат объекту Date() для вставки в input в формате yyyy-mm-dd
function formatDate(date) {
    let year = Number(date.getFullYear());
    let month = Number(date.getMonth())+1;
    let day = Number(date.getDate());

    month = month < 10 ? `0${month}` : month;
    day = day < 10 ? `0${day}` : day;

    return `${year}-${month}-${day}`;
}