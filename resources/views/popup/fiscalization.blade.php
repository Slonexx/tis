
@extends('popup.index')

@section('content')

    <script>

        //const url = 'http://rekassa/Popup/customerorder/show';

        const url = 'https://smarttis.kz/Popup/customerorder/show';
        let object_Id = '';
        let accountId = '';
        let entity_type = '';
        let id_ticket = '';

        window.addEventListener("message", function(event) { openDown();
            //var receivedMessage = {"name":"OpenPopup","messageId":1,"popupName":"fiscalizationPopup","popupParameters":{"object_Id":"4b1034eb-28e1-11ed-0a80-02ab00186962","accountId":"1dd5bd55-d141-11ec-0a80-055600047495"}}; /*event.data;*/
            var receivedMessage = event.data;
            newPopup();
            if (receivedMessage.name === 'OpenPopup') {
                object_Id = receivedMessage.popupParameters.object_Id;
                accountId = receivedMessage.popupParameters.accountId;
                entity_type = receivedMessage.popupParameters.entity_type;
                let params = {
                    object_Id: object_Id,
                    accountId: accountId,
                };
                let final = url + formatParams(params);

                let xmlHttpRequest = new XMLHttpRequest();
                xmlHttpRequest.addEventListener("load", function () { $('#lDown').modal('hide');
                    let json = JSON.parse(this.responseText);
                    id_ticket = json.attributes.ticket_id;
                    window.document.getElementById("numberOrder").innerHTML = json.name;

                    let products = json.products;
                    for (var i = 0; i < products.length; i++) {

                        if (products[i].propety === true) {
                            window.document.getElementById('productId_' + i).innerHTML = products[i].position;
                            window.document.getElementById('productName_' + i).innerHTML = products[i].name;
                            window.document.getElementById('productQuantity_' + i).innerHTML = products[i].quantity;
                            window.document.getElementById('productPrice_' + i).innerHTML = products[i].price;
                            if (products[i].vat === 0)  window.document.getElementById('productVat_' + i).innerHTML = "без НДС";
                            else window.document.getElementById('productVat_' + i).innerHTML = products[i].vat + '%';
                            window.document.getElementById('productDiscount_' + i).innerHTML = products[i].discount + '%';
                            window.document.getElementById('productFinal_' + i).innerHTML = products[i].final;

                            let sum = window.document.getElementById("sum").innerHTML;
                            if (!sum) sum = 0;
                            window.document.getElementById("sum").innerHTML = roundToTwo(parseFloat(sum) + parseFloat(products[i].final));
                            window.document.getElementById(i).style.display = "block";
                        } else {
                            window.document.getElementById("messageAlert").innerText = "Позиции у которых нет ед. изм. не добавились ";
                            window.document.getElementById("message").style.display = "block";
                        }
                    }

                    if (json.attributes != null){
                        if (json.attributes.ticket_id != null){
                            window.document.getElementById("ShowCheck").style.display = "block";
                            window.document.getElementById("refundCheck").style.display = "block";
                        } else {
                            window.document.getElementById("getKKM").style.display = "block";
                        }
                    } else  window.document.getElementById("getKKM").style.display = "block";
                });
                xmlHttpRequest.open("GET", final);
                xmlHttpRequest.send();
            }
             });

            function formatParams(params) {
                return "?" + Object
                    .keys(params)
                    .map(function (key) {
                        return key + "=" + encodeURIComponent(params[key])
                    })
                    .join("&")
            }
            function deleteBTNClick(Object){


                let sum = document.getElementById("sum").innerHTML;
                let final = document.getElementById('productFinal_' + Object).innerHTML;
                window.document.getElementById("sum").innerHTML = sum-final;


                window.document.getElementById('productName_' + Object).innerHTML = '';
                window.document.getElementById('productQuantity_' + Object).innerHTML = '';
                window.document.getElementById('productPrice_' + Object).innerHTML = '';
                window.document.getElementById('productVat_' + Object).innerHTML = '';
                window.document.getElementById('productDiscount_' + Object).innerHTML = '';
                window.document.getElementById('productFinal_' + Object).innerHTML = '';
                window.document.getElementById(Object).style.display = "none";
            }

        function roundToTwo(num) {
            return +(Math.round(num + "e+2")  + "e-2");
        }
        function isNumberKeyCash(evt){
            var charCode = (evt.which) ? evt.which : event.keyCode
            if (charCode == 46){
                var inputValue = $("#cash").val();
                var count = (inputValue.match(/'.'/g) || []).length;
                if(count<1){
                    if (inputValue.indexOf('.') < 1){
                        return true;
                    }
                    return false;
                }else{
                    return false;
                }
            }
            if (charCode != 46 && charCode > 31 && (charCode < 48 || charCode > 57)){
                return false;
            }
            return true;
        }
        function isNumberKeyCard(evt){
            var charCode = (evt.which) ? evt.which : event.keyCode
            if (charCode == 46){
                var inputValue = $("#card").val();
                var count = (inputValue.match(/'.'/g) || []).length;
                if(count<1){
                    if (inputValue.indexOf('.') < 1){
                        return true;
                    }
                    return false;
                }else{
                    return false;
                }
            }
            if (charCode != 46 && charCode > 31 && (charCode < 48 || charCode > 57)){
                return false;
            }
            return true;
        }
        function isNumberKeyMobile(evt){
            var charCode = (evt.which) ? evt.which : event.keyCode
            if (charCode == 46){
                var inputValue = $("#mobile").val();
                var count = (inputValue.match(/'.'/g) || []).length;
                if(count<1){
                    if (inputValue.indexOf('.') < 1){
                        return true;
                    }
                    return false;
                }else{
                    return false;
                }
            }
            if (charCode != 46 && charCode > 31 && (charCode < 48 || charCode > 57)){
                return false;
            }
            return true;
        }

        function sendKKM(pay_type){
            let button_hide = '';
                if (pay_type == 'return') button_hide = 'refundCheck';
                if (pay_type == 'sell') button_hide = 'getKKM';

            window.document.getElementById(button_hide).style.display = "none";
            let modalShowHide = 'show';

            let money_card = window.document.getElementById('card').value;
            let money_cash = window.document.getElementById('cash').value;
            let money_mobile = window.document.getElementById('mobile').value;
            let SelectorInfo = document.getElementById('valueSelector');
            let option = SelectorInfo.options[SelectorInfo.selectedIndex];

            if (option.value == 0){
                if (!money_cash) {
                    window.document.getElementById('messageAlert').innerText = 'Вы не ввели сумму наличных';
                    window.document.getElementById('message').style.display = "block";
                    modalShowHide = 'hide'
                }
            }
            if (option.value == 1){
                if (!money_card) {
                    window.document.getElementById('messageAlert').innerText = 'Вы не ввели сумму карты';
                    window.document.getElementById('message').style.display = "block";
                    modalShowHide = 'hide'
                }
            }
            if (option.value == 2){
                if (!money_mobile){
                    window.document.getElementById('messageAlert').innerText = 'Вы не ввели сумму мобильных';
                    window.document.getElementById('message').style.display = "block";
                    modalShowHide = 'hide'
                }
            }
            if (option.value == 3){
                if (!money_card && !money_cash && !money_mobile){
                    window.document.getElementById('messageAlert').innerText = 'Вы не ввели сумму';
                    window.document.getElementById('message').style.display = "block";
                    modalShowHide = 'hide'
                }
            }

            let url = 'https://smarttis.kz/Popup/customerorder/send';

            if (modalShowHide === 'show'){
                $('#downL').modal('toggle');
                let products = [];
                for (let i = 0; i < 20; i++) {
                    if ( window.document.getElementById(i).style.display === 'block' ) {
                        products[i] = window.document.getElementById('productId_'+i).innerText
                    }
                }
                let params = {
                    accountId: accountId,
                    object_Id: object_Id,
                    entity_type: entity_type,
                    money_card: money_card,
                    money_cash: money_cash,
                    money_mobile: money_mobile,
                    pay_type: pay_type,
                    position: JSON.stringify(products),
                };
                let final = url + formatParams(params);
                console.log('send to kkm = ' + final);
                let xmlHttpRequest = new XMLHttpRequest();
                xmlHttpRequest.addEventListener("load", function () {
                    $('#downL').modal('hide');
                    let json = JSON.parse(this.responseText);
                    if (json.message === 'Ticket created!'){
                        window.document.getElementById("messageGoodAlert").innerText = "Чек создан";
                        window.document.getElementById("messageGood").style.display = "block";
                        window.document.getElementById("ShowCheck").style.display = "block";
                        window.document.getElementById("closeShift").style.display = "block";
                        modalShowHide = 'hide';
                        let response = json.response;
                        id_ticket = response.id;
                    } else {
                        window.document.getElementById('messageAlert').innerText = "Ошибка 400";
                        window.document.getElementById('message').style.display = "block";
                        window.document.getElementById(button_hide).style.display = "block";
                        modalShowHide = 'hide';
                    }
                });
                xmlHttpRequest.open("GET", final);
                xmlHttpRequest.send();
                modalShowHide = 'hide';
            }
            else window.document.getElementById(button_hide).style.display = "block";
        }

        function closeShift(){

            let pinCode = window.document.getElementById('pin_code').value;

            let params = {
                accountId: accountId,
                pincode: pinCode,
            };
            //let url = 'http://rekassa/Popup/customerorder/closeShift';
            let url = 'https://smarttis.kz/Popup/customerorder/closeShift';
let final = url + formatParams(params);

            console.log("final = " + final);

            let xmlHttpRequest = new XMLHttpRequest();
            xmlHttpRequest.addEventListener("load", function () {
                let json = JSON.parse(this.responseText);
                if (json.statusCode === 200){
                    window.document.getElementById('messageAlert').innerText = json.message;
                    window.document.getElementById('message').style.display = "block";
                } else {
                    console.log(' Error = ' + url + ' message = ' + JSON.stringify(json.message))
                    window.document.getElementById('messageAlert').innerText = "ошибка";
                    window.document.getElementById('message').style.display = "block";
                }
            });
            xmlHttpRequest.open("GET", final);
            xmlHttpRequest.send();
        }

        function ShowCheck(){
            let urlrekassa = 'https://app.rekassa.kz/'
            //let url = 'http://rekassa/Popup/customerorder/closeShift';
            let url = 'https://smarttis.kz/api/ticket';
            let params = {
                accountId: accountId,
                id_ticket: id_ticket,
            };
            let final = url + formatParams(params);
            let xmlHttpRequest = new XMLHttpRequest();
            xmlHttpRequest.addEventListener("load", function () {
                window.open(urlrekassa + this.responseText);
            });
            xmlHttpRequest.open("GET", final);
            xmlHttpRequest.send();
        }

        function updatePopup(){
            let params = {
                object_Id: object_Id,
                accountId: accountId,
            };
            let final = url + formatParams(params);

            let xmlHttpRequest = new XMLHttpRequest();
            xmlHttpRequest.addEventListener("load", function () {

                let json = JSON.parse(this.responseText);


            });
            xmlHttpRequest.open("GET", final);
            xmlHttpRequest.send();
        }

        function SelectorSum(Selector){
            window.document.getElementById("cash").value = ''
            window.document.getElementById("card").value = ''
            window.document.getElementById("mobile").value = ''
            let option = Selector.options[Selector.selectedIndex];
            if (option.value === "0") {
                document.getElementById('Visibility_Cash').style.display = 'block';
                document.getElementById('Visibility_Card').style.display = 'none';
                document.getElementById('Visibility_Mobile').style.display = 'none';
            }
            if (option.value === "1") {
                document.getElementById('Visibility_Card').style.display = 'block';
                document.getElementById('Visibility_Cash').style.display = 'none';
                document.getElementById('Visibility_Mobile').style.display = 'none';
                let card =  window.document.getElementById("card");
                card.value = window.document.getElementById("sum").innerText
                window.document.getElementById("card").disabled = true
            }
            if (option.value === "2") {
                document.getElementById('Visibility_Cash').style.display = 'none';
                document.getElementById('Visibility_Card').style.display = 'none';
                document.getElementById('Visibility_Mobile').style.display = 'block';
                let mobile =  window.document.getElementById("mobile");
                mobile.value = window.document.getElementById("sum").innerText
                window.document.getElementById("mobile").disabled = true
            }
            if (option.value === "3") {
                document.getElementById('Visibility_Cash').style.display = 'block';
                document.getElementById('Visibility_Card').style.display = 'block';
                document.getElementById('Visibility_Mobile').style.display = 'block';
                window.document.getElementById("card").disabled = false
                window.document.getElementById("mobile").disabled = false
            }

        }
        function openDown(){
            $('#lDown').modal('show');
        }
        function closeDown(){
            $('#lDown').modal('hide');
            $('#downL').modal('hide');
        }



        function newPopup(){
            window.document.getElementById("sum").innerHTML = ''

            window.document.getElementById("message").style.display = "none"
            window.document.getElementById("messageGood").style.display = "none"

            window.document.getElementById("refundCheck").style.display = "none"
            window.document.getElementById("getKKM").style.display = "none"
            window.document.getElementById("ShowCheck").style.display = "none"

            window.document.getElementById("cash").value = ''
            window.document.getElementById("card").value = ''
            window.document.getElementById("mobile").value = ''

            window.document.getElementById("cash").style.display = "block"
            let thisSelectorSum = window.document.getElementById("valueSelector")
            thisSelectorSum.value = 0;
            SelectorSum(thisSelectorSum)

            for (var i = 0; i < 20; i++) {
                window.document.getElementById(i).style.display = "none"
                window.document.getElementById('productName_' + i).innerHTML = ''
                window.document.getElementById('productQuantity_' + i).innerHTML = ''
                window.document.getElementById('productPrice_' + i).innerHTML = ''
                window.document.getElementById('productVat_' + i).innerHTML = ''
                window.document.getElementById('productDiscount_' + i).innerHTML = ''
                window.document.getElementById('productFinal_' + i).innerHTML = ''
            }
        }
    </script>


    <div class="main-container">
        <div class="row gradient rounded p-2">
            <div class="col-3">
                <div class="mx-2"> <img src="https://test.ukassa.kz/_nuxt/img/d2b49fb.svg" width="90%"  alt="">
                </div>
            </div>
            <div class="col-6">
                <span class="mx-5 text-white">Заказ покупателя №</span>
                <span id="numberOrder" class="text-white"></span>
            </div>
            <div class="col-3">
                <div class="row">
                    <div class="col-8">

                    </div>
                    <div class="col-3 text-right">
                        <button type="submit" onclick="updatePopup()" class="myButton btn "> <i class="fa-solid fa-arrow-rotate-right"></i> </button>
                    </div>
                </div>
            </div>
        </div>
        <div id="message" class="mt-2 row" style="display:none;" >
            <div class="col-12">
                <div id="messageAlert" class=" mx-3 p-2 alert alert-danger text-center ">
                </div>
            </div>
        </div>
        <div id="messageGood" class="mt-2 row" style="display:none;" >
            <div class="col-12">
                <div id="messageGoodAlert" class=" mx-3 p-2 alert alert-success text-center ">
                </div>
            </div>
        </div>
        <div class="content-container">
            <div class=" rounded bg-white">
                <div id="main" class="row p-3">
                    <div class="col-12">
                        <div class="row">
                            <div class="col-1 text-success">№</div>
                            <div class="col-5 text-success">Наименование</div>
                            <div class="col-1 text-success">Кол-во</div>
                            <div class="col-1 text-success">Цена</div>
                            <div class="col-1 text-success">НДС</div>
                            <div class="col-1 text-success">Скидка</div>
                            <div class="col-1 text-success">Сумма</div>
                            <div class="col-1 text-success">Учитывать </div>
                            <hr class="mt-1 text-success" style="background-color: #0c7d70; height: 3px; border: 0;">
                        </div>
                    </div>
                    <div id="products" class="col-12 text-black">
                        @for( $i=0; $i<20; $i++)
                            <div id="{{ $i }}" class="row mt-2" style="display:block;">
                                <div class="row">
                                    <div class="col-1">{{ $i + 1 }}</div>
                                    <div id="{{'productId_'.$i}}" style="display:none;"></div>
                                    <div id="{{ 'productName_'.$i }}"  class="col-5"></div>
                                    <div id="{{ 'productQuantity_'.$i }}"  class="col-1 text-center"></div>
                                    <div id="{{ 'productPrice_'.$i }}"  class="col-1 text-center"></div>
                                    <div id="{{ 'productVat_'.$i }}"  class="col-1 text-center"></div>
                                    <div id="{{ 'productDiscount_'.$i }}"  class="col-1 text-center"></div>
                                    <div id="{{ 'productFinal_'.$i }}"  class="col-1 text-center"></div>
                                    <div class="col-1 text-center">
                                        <button onclick="deleteBTNClick( {{ $i }} )" class="btn btn-danger">Убрать</button>
                                    </div>
                                </div>
                            </div>
                        @endfor
                    </div>
                </div>
            </div>
        </div>
        <div class="buttons-container-head"></div>
        <div class="buttons-container">
            <div class="row">

                <div class="col-7 row">
                    <div class="row">
                        <div class="col-12 mx-2 ">
                            <div class="col-5 bg-success text-white p-1 rounded">
                                <span> Итого: </span>
                                <span id="sum"></span>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="col-1">

                    </div>
                <div class="col-2">

                </div>
                <div class="col-2 d-flex justify-content-end">
                    <button onclick="sendKKM('return')" id="refundCheck" class="btn btn-danger">возврат</button>
                    <button onclick="sendKKM('sell')" id="getKKM" class="btn btn-success">Отправить в ККМ</button>
                </div>

                <div class="row mt-2">
                    <div class="col-3">
                        <div class="row">
                            <div class="col-5">
                                <div class="mx-1 mt-1 bg-warning p-1 rounded text-center">Тип оплаты</div>
                            </div>
                            <div class="col-7">
                                <select onchange="SelectorSum(valueSelector)" id="valueSelector" class="form-select">
                                    <option selected value="0">Наличными</option>
                                    <option value="1">Картой</option>
                                    <option value="2">Мобильная</option>
                                    <option value="3">Смешанная</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="row">
                            <div class="col-4"> <div id="Visibility_Cash" class="mx-2" style="display: none">
                                    <input id="cash" type="number" step="0.1" placeholder="Сумма наличных"  onkeypress="return isNumberKeyCash(event)"
                                           class="form-control float" required maxlength="255" value="">
                                </div> </div>
                            <div class="col-4"> <div id="Visibility_Card" class="mx-2" style="display: none">
                                    <input id="card" type="number" step="0.1"  placeholder="Сумма картой" onkeypress="return isNumberKeyCard(event)"
                                           class="form-control float" required maxlength="255" value="">
                                </div> </div>
                            <div class="col-4"> <div id="Visibility_Mobile" class="mx-2" style="display: none">
                                    <input id="mobile" type="number" step="0.1"  placeholder="Сумма мобильных" onkeypress="return isNumberKeyMobile(event)"
                                           class="form-control float" required maxlength="255" value="">
                                </div> </div>
                        </div>
                    </div>
                    <div class="col-1"></div>
                    <div class="col-2 d-flex justify-content-end">
                        <button onclick="ShowCheck()" id="ShowCheck" class="btn btn-success">Показать чек</button>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <div class="modal fade" id="modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"> Закрытие смены
                        <i class="fa-solid fa-circle-question text-danger"></i>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                        <div class="row mt-2">
                            <div class="col-1"></div>
                            <div class="col-10">
                                <label> Введите пин код для закрытия смены</label>
                                <input id="pin_code" type="number" placeholder="PIN code"
                                  class="form-control float" required maxlength="10" value="">
                            </div>
                        </div>
                </div>
                <div class="modal-footer">
                    <button  onclick="closeShift()" id="closeShift"
                             data-bs-dismiss="modal" class="btn btn-danger">Закрыть смену</button>
                </div>
            </div>
        </div>
    </div>
    <div id="downL" class="modal fade bd-example-modal-sm" data-bs-keyboard="false" data-bs-backdrop="static"
         tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"> <i class="fa-solid fa-circle-exclamation text-danger"></i>
                        Отправка
                    </h5>
                </div>
                <div class="modal-body text-center" style="background-color: #e5eff1">
                    <div class="row">
                        <img style="width: 100%" src="https://i.gifer.com/1uoA.gif" alt="">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="lDown" class="modal fade bd-example-modal-sm" data-bs-keyboard="false" data-bs-backdrop="static"
         tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"> <i class="fa-solid fa-circle-exclamation text-danger"></i>
                        Загрузка
                    </h5>
                </div>
                <div class="modal-body text-center" style="background-color: #e5eff1">
                    <div class="row">
                        <img style="width: 100%" src="https://i.gifer.com/1uoA.gif" alt="">
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

<style>

    body {
        overflow: hidden;
    }
    .main-container {
        display: flex;
        flex-direction: column;
        height: 100vh;
    }
    .content-container {
        overflow-y: auto;
        overflow-x: hidden;
        flex-grow: 1;
    }
    .buttons-container-head{
        background-color: rgba(12, 125, 112, 0.27);
        padding-top: 3px;
        min-height: 3px;
    }
    .buttons-container {
        padding-top: 10px;
        min-height: 100px;
    }

    .myButton {
        box-shadow: 0px 4px 5px 0px #5d5d5d !important;
        background-image: radial-gradient( circle farthest-corner at 10% 20%,  rgba(14,174,87,1) 0%, rgba(12,116,117,1) 90% ) !important;
        color: white !important;
        border-radius:50px !important;
        display:inline-block !important;
        cursor:pointer !important;
        padding:5px 5px !important;
        text-decoration:none !important;
    }
    .myButton:hover {
        filter: invert(1);

        color: #111111 !important;
    }
    .myButton:active {
        position: relative !important;
        top: 1px !important;
    }
</style>
