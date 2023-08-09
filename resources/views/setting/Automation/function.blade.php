<script>

    function createScript() {
        let i = 0
        if (gridChild.some((element) => element === false)) {
            i = gridChild.findIndex((element) => element === false)
            let entity = "entity_" + i
            let status = "status_" + i
            let project = "optionsListProject_" + i
            let saleschannel = "optionsListSaleschannel_" + i


            $('#mainCreate').append('<div id="child_' + i + '" class="mt-2 row">' +
                '<div class="col-2"> <select onchange="FU_statusAutomation(' + status + ', ' + entity + ', ' + project + ', ' + saleschannel + ')" id="entity_' + i + '" name="entity_' + i + '" class="form-select text-black"> <option value="0">Заказ покупателя</option> <option value="1">Отгрузки</option> <option value="2">Возврат покупателя</option> </select> </div>' +
                '<div class="col-2"> <select id="status_' + i + '" name="status_' + i + '" class="form-select text-black"> </select> </div>' +
                '<div class="col-2"> <select id="payment_' + i + '" name="payment_' + i + '" class="form-select text-black"> <option value="0">Наличные</option> <option value="1">Карта</option> <option value="3">От выбора справочника</option> </select> </div>' +
                '<div class="row col-5 text-center"> ' +
                '<div class="col-6"> ' +
                '<div class="btn btn-light border border-2 dropdown-toggle"  class="form-select text-black" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> Выбрать канал продаж </div>' +
                '<div class="dropdown-menu" id="optionsListSaleschannel_' + i + '"> </div>' +
                '</div>' +
                '<div class="col-6"> ' +
                '<div class="btn btn-light border border-2 dropdown-toggle"  class="form-select text-black" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> Выбрать проект </div>' +
                '<div class="dropdown-menu" id="optionsListProject_' + i + '"> </div>' +
                '</div>' +
                '</div>' +
                '<div class="col-1 text-center"> <span onclick="deleteScript(' + i + ')" class="fa-solid fa-rectangle-xmark" style="font-size: 30px; cursor: pointer"> </span> </div>' +
                '</div>')
            gridChild[i] = true
            FU_statusAutomation(window.document.getElementById('status_' + i), window.document.getElementById('entity_' + i), window.document.getElementById('optionsListProject_' + i), window.document.getElementById('optionsListSaleschannel_' + i))
            $('#hiddenAllComponent').append(' <input id="saleschannel_' + i + '"  name="saleschannel_' + i + '" value="">');
            $('#hiddenAllComponent').append(' <input id="project_' + i + '"  name="project_' + i + '" value="">');
        }
    }


    if (Saved.length > 0) {
        for (let i = 0; i < Saved.length; i++) {
            if (gridChild.some((element) => element === false)) {
                i = gridChild.findIndex((element) => element === false)
                let entity = "entity_" + i
                let status = "status_" + i
                let payment = "payment_" + i
                let project = "optionsListProject_" + i
                let saleschannel = "optionsListSaleschannel_" + i


                $('#mainCreate').append('<div id="child_' + i + '" class="mt-2 row">' +
                    '<div class="col-2"> <select onchange="FU_statusAutomation(' + status + ', ' + entity + ', ' + project + ', ' + saleschannel + ')" id="entity_' + i + '" name="entity_' + i + '" class="form-select text-black"> <option value="0">Заказ покупателя</option> <option value="1">Отгрузки</option> <option value="2">Возврат покупателя</option> </select> </div>' +
                    '<div class="col-2"> <select id="status_' + i + '" name="status_' + i + '" class="form-select text-black"> </select> </div>' +
                    '<div class="col-2"> <select id="payment_' + i + '" name="payment_' + i + '" class="form-select text-black"> <option value="0">Наличные</option> <option value="1">Карта</option> <option value="3">От выбора справочника</option> </select> </div>' +
                    '<div class="row col-5 text-center"> ' +
                    '<div class="col-6"> ' +
                    '<div class="btn btn-light border border-2 dropdown-toggle"  class="form-select text-black" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> Выбрать канал продаж </div>' +
                    '<div class="dropdown-menu" id="optionsListSaleschannel_' + i + '"> </div>' +
                    '</div>' +
                    '<div class="col-6"> ' +
                    '<div class="btn btn-light border border-2 dropdown-toggle"  class="form-select text-black" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> Выбрать проект </div>' +
                    '<div class="dropdown-menu" id="optionsListProject_' + i + '"> </div>' +
                    '</div>' +
                    //'<div class="col-5"> <select onchange="FU_projectAutomation(' + project + ', ' + entity + ')" id="project_' + i + '" name="project_' + i + '" class="form-select text-black "> <option value="0"> Не выбирать</option> </select> </div>' +
                    '</div>' +
                    '<div class="col-1 text-center"> <span onclick="deleteScript(' + i + ')" class="fa-solid fa-rectangle-xmark" style="font-size: 30px; cursor: pointer"> </span> </div>' +
                    '</div>')
                gridChild[i] = true
                window.document.getElementById(entity).value = Saved[i].entity
                FU_statusAutomation(window.document.getElementById('status_' + i), window.document.getElementById('entity_' + i), window.document.getElementById('optionsListProject_' + i), window.document.getElementById('optionsListSaleschannel_' + i))
                window.document.getElementById(status).value = Saved[i].status
                window.document.getElementById(payment).value = Saved[i].payment



                $('#hiddenAllComponent').append(' <input id="saleschannel_' + i + '"  name="saleschannel_' + i + '" value="">');
                $('#hiddenAllComponent').append(' <input id="project_' + i + '"  name="project_' + i + '" value="">');


                let privateSaleschannel = (Saved[i].saleschannel).split('/').filter(function(part) { return part !== ""; });
                privateSaleschannel.forEach((item) => {
                    FU_saleschannelAutomation(true, item, i, 'saleschannel_' + i + '_' + "option_" + item)
                });


                let privateProject = (Saved[i].project).split('/').filter(function(part) { return part !== ""; });
                privateProject.forEach((item) => {
                    FU_projectAutomation(true, item, i, 'project_' + i + '_' + "option_" + item)
                });
            }
        }
    }

    function FU_statusAutomation(selectElement, entityName, selectProject, selectSalesChannel) {
        function createOptions(data, targetElement) {
            data.forEach((item) => {
                let option1 = document.createElement("option")
                option1.text = item.name
                option1.value = item.id
                targetElement.appendChild(option1)
            });
        }

        function createOptionsCustom(data, targetElement) {

            data.forEach((item) => {
                let option
                let select
                let reset
                let value = item.id
                let saleschannelOrProject
                let id = (targetElement.id).substring((targetElement.id).lastIndexOf('_') + 1)

                if ((targetElement.id).includes('Saleschannel')) {
                    saleschannelOrProject = 'saleschannel';
                    option = saleschannelOrProject + '_' + id + '_' + "option_" + item.id
                    select = 'FU_saleschannelAutomation(' + true + ',\'' + value + '\', \'' + id + '\', \'' + option + '\')';
                    reset = 'FU_saleschannelAutomation(' + false + ',\'' + value + '\', \'' + id + '\', \'' + option + '\')';
                }
                if ((targetElement.id).includes('Project')) {
                    saleschannelOrProject = 'project';
                    option = saleschannelOrProject + '_' + id + '_' + "option_" + item.id
                    select = 'FU_projectAutomation(' + true + ',\'' + value + '\', \'' + id + '\', \'' + option + '\')';
                    reset = 'FU_projectAutomation(' + false + ',\'' + value + '\', \'' + id + '\', \'' + option + '\')';
                }


                $(targetElement).append(
                    '<div id="' + option + '" class="option-item text-center mt-2"> ' +
                    '<i onclick="' + select + '" class="float-left ml-2 fa-solid fa-circle-check" style="font-size: 20px; cursor: pointer"></i> ' +
                    '<span class="col-10" style="cursor: default"> ' + item.name + ' </span> ' +
                    '<i onclick="' + reset + '" onclick="" class="float-right mr-2 fa-solid fa-circle-xmark" style="font-size: 20px; cursor: pointer"></i>' +
                    '</div>')
            });
        }

        let value = entityName.value
        let params = entityName.options[value].text

        while (selectElement.firstChild) {
            selectElement.removeChild(selectElement.firstChild);
        }
        selectProject.innerText = ''
        selectSalesChannel.innerText = ''

        switch (params) {
            case 'Заказ покупателя':
                createOptions(status_arr_meta.customerorder, selectElement)
                createOptionsCustom(project_arr_meta.customerorder, selectProject)
                createOptionsCustom(saleschannel_arr_meta.customerorder, selectSalesChannel)
                break;

            case 'Отгрузки':
                createOptions(status_arr_meta.demand, selectElement)
                createOptionsCustom(project_arr_meta.demand, selectProject)
                createOptionsCustom(saleschannel_arr_meta.demand, selectSalesChannel)
                break;

            case 'Возврат покупателя':
                createOptions(status_arr_meta.salesreturn, selectElement)
                createOptionsCustom(project_arr_meta.salesreturn, selectProject)
                createOptionsCustom(saleschannel_arr_meta.salesreturn, selectSalesChannel)
                break;

            default:
                break;
        }
    }
    function FU_saleschannelAutomation(status, value, indexArraySaleschannel, option) {

        if (status) {
            window.document.getElementById(option).style.backgroundColor = "rgb(103,255,106)"
            if (value === "0") {
                window.document.getElementById('saleschannel_' + indexArraySaleschannel).value = value;
                let salesChannelElements = (window.document.getElementById('optionsListSaleschannel_' + indexArraySaleschannel)).querySelectorAll('[id^="saleschannel_"]')
                salesChannelElements.forEach(function (element) {
                    element.style.backgroundColor = ""
                });
                window.document.getElementById(option).style.backgroundColor = "rgb(103,255,106)"
                arrayDeleteOrRecovery(true, ConstSaleschannel_arr_meta, saleschannel_arr_meta, window.document.getElementById('entity_' + indexArraySaleschannel), value)
            } else {
                window.document.getElementById('saleschannel_' + indexArraySaleschannel + '_option_0').style.backgroundColor = ""
                if (window.document.getElementById('saleschannel_' + indexArraySaleschannel).value === '0' || window.document.getElementById('saleschannel_' + indexArraySaleschannel).value === '') {
                    window.document.getElementById('saleschannel_' + indexArraySaleschannel).value = value;
                } else window.document.getElementById('saleschannel_' + indexArraySaleschannel).value = window.document.getElementById('saleschannel_' + indexArraySaleschannel).value + '/' + value;

                window.document.getElementById(option).style.backgroundColor = "rgb(103,255,106)"
                arrayDeleteOrRecovery(true, ConstSaleschannel_arr_meta, saleschannel_arr_meta, window.document.getElementById('entity_' + indexArraySaleschannel), value)
            }

        } else {
            window.document.getElementById('saleschannel_' + indexArraySaleschannel).value = (window.document.getElementById('saleschannel_' + indexArraySaleschannel).value).replace(value, "");
            window.document.getElementById(option).style.backgroundColor = ""
            arrayDeleteOrRecovery(false, ConstSaleschannel_arr_meta, saleschannel_arr_meta, window.document.getElementById('entity_' + indexArraySaleschannel), value)
        }

    }

    function FU_projectAutomation(status, value, indexArraySaleschannel, option) {
        if (status) {
            window.document.getElementById(option).style.backgroundColor = "rgb(103,255,106)"
            if (value === "0") {
                window.document.getElementById('project_' + indexArraySaleschannel).value = value;
                let salesChannelElements = (window.document.getElementById('optionsListProject_' + indexArraySaleschannel)).querySelectorAll('[id^="project_"]')
                salesChannelElements.forEach(function (element) {
                    element.style.backgroundColor = ""
                });
                window.document.getElementById(option).style.backgroundColor = "rgb(103,255,106)"
                arrayDeleteOrRecovery(true, ConstProject_arr_meta, project_arr_meta, window.document.getElementById('entity_' + indexArraySaleschannel), value)
            } else {
                window.document.getElementById('project_' + indexArraySaleschannel + '_option_0').style.backgroundColor = ""
                if (window.document.getElementById('project_' + indexArraySaleschannel).value === '0' || window.document.getElementById('project_' + indexArraySaleschannel).value === '') {
                    window.document.getElementById('project_' + indexArraySaleschannel).value = value;
                } else window.document.getElementById('project_' + indexArraySaleschannel).value = window.document.getElementById('project_' + indexArraySaleschannel).value + '/' + value;

                window.document.getElementById(option).style.backgroundColor = "rgb(103,255,106)"
                arrayDeleteOrRecovery(true, ConstProject_arr_meta, project_arr_meta, window.document.getElementById('entity_' + indexArraySaleschannel), value)
            }

        } else {
            window.document.getElementById('project_' + indexArraySaleschannel).value = (window.document.getElementById('project_' + indexArraySaleschannel).value).replace(value, "");
            window.document.getElementById(option).style.backgroundColor = ""
            arrayDeleteOrRecovery(false, ConstProject_arr_meta, project_arr_meta, window.document.getElementById('entity_' + indexArraySaleschannel), value)
        }

    }

    function arrayDeleteOrRecovery(status, Const, arr_meta, name, id) {
        switch (name.value) {
            case '0': {
                if (status) {
                    if (id === '0') {
                        arr_meta.customerorder = Const.customerorder
                    } else {
                        arr_meta.customerorder = (arr_meta.customerorder).filter((obj) => obj.id !== id)
                    }
                } else {
                    (arr_meta.customerorder).push((Const.customerorder).find(item => item.id === id));
                }
                break
            }
            case '1' : {

                if (status) {
                    if (id === '0') {
                        arr_meta.demand = Const.demand
                    } else {
                        arr_meta.demand = (arr_meta.demand).filter((obj) => obj.id !== id)
                    }
                } else {
                    (arr_meta.demand).push((Const.demand).find(item => item.id === id));
                }
                break
            }
            case '2' : {

                if (status) {
                    if (id === '0') {
                        arr_meta.salesreturn = Const.salesreturn
                    } else {
                        arr_meta.salesreturn = (arr_meta.salesreturn).filter((obj) => obj.id !== id)
                    }
                } else {
                    (arr_meta.salesreturn).push((Const.salesreturn).find(item => item.id === id));
                }
                break
            }
        }
    }


    function deleteScript(id) {
        window.document.getElementById('child_' + id).remove();
        window.document.getElementById('saleschannel_' + id).remove();
        window.document.getElementById('project_' + id).remove();
        gridChild[id] = false
    }


    function showAddingOff() {
        isMouseDown = true;
        addingOff.style.display = "none";
        addingOn.style.display = "block";
    }

    function showAddingOn() {
        isMouseDown = false;
        addingOff.style.display = "block";
        addingOn.style.display = "none";
    }

    document.addEventListener("mouseup", function () {
        if (isMouseDown) {
            showAddingOn();
        }
    });

</script>
