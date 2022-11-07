
@extends('popup.index')

@section('content')

    <script>

        //const url = 'http://tus/Popup/customerorder/show';

        const url = 'https://smarttis.kz/Popup/customerorder/show';
        let object_Id = '';
        let accountId = '';
        let entity_type = '';
        let id_ticket = '';
        let html = '';

        window.addEventListener("message", function(event) { openDown();

            /*var receivedMessage = {
                "name":"OpenPopup","messageId":1,"popupName":"fiscalizationPopup","popupParameters":
                    {"object_Id":"4f4a2e5a-4f6c-11ed-0a80-09be0003f312","accountId":"1dd5bd55-d141-11ec-0a80-055600047495"}
            }; */
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
                console.log('receivedMessage = ' + final);
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

                            window.document.getElementById('productUOM_' + i).innerHTML = products[i].uom['name']
                            window.document.getElementById('productIDUOM_' + i).innerHTML = products[i].uom['id'];

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



        function sendKKM(pay_type){
            let button_hide = ''
            if (pay_type == 'return') button_hide = 'refundCheck'
            if (pay_type == 'sell') button_hide = 'getKKM'

            window.document.getElementById(button_hide).style.display = "none"
            let modalShowHide = 'show'

            let total = window.document.getElementById('sum').innerText
            let money_card = window.document.getElementById('card').value
            let money_cash = window.document.getElementById('cash').value
            let SelectorInfo = document.getElementById('valueSelector')
            let option = SelectorInfo.options[SelectorInfo.selectedIndex]

            if (option.value == 0){if (!money_cash) {
                window.document.getElementById('messageAlert').innerText = 'Вы не ввели сумму наличных'
                window.document.getElementById('message').style.display = "block"
                modalShowHide = 'hide'
            }
                if (money_cash <= parseFloat(window.document.getElementById('sum').innerText)){
                    window.document.getElementById('messageAlert').innerText = 'Введите сумму больше !'
                    window.document.getElementById('message').style.display = "block"
                    modalShowHide = 'hide'
                }
            }
            if (option.value == 1){if (!money_card) {
                window.document.getElementById('messageAlert').innerText = 'Вы не ввели сумму карты'
                window.document.getElementById('message').style.display = "block"
                modalShowHide = 'hide'
            }
            }
            if (option.value == 2){if (!money_card && !money_cash){
                window.document.getElementById('messageAlert').innerText = 'Вы не ввели сумму'
                window.document.getElementById('message').style.display = "block"
                modalShowHide = 'hide'
                if (money_card + money_cash < parseFloat(window.document.getElementById('sum').innerText)){
                    window.document.getElementById('messageAlert').innerText = 'Введите сумму больше !'
                    window.document.getElementById('message').style.display = "block"
                    modalShowHide = 'hide'
                }
            }
            }

            //let url = 'https://tus/Popup/customerorder/send'
            let url = 'https://smarttis.kz/Popup/customerorder/send'

            if (modalShowHide === 'show'){
                $('#downL').modal('toggle')
                let products = []
                for (let i = 0; i < 20; i++) {
                    if ( window.document.getElementById(i).style.display === 'block' ) {
                        products[i] = {
                            id:window.document.getElementById('productId_'+i).innerText,
                            name:window.document.getElementById('productName_'+i).innerText,
                            quantity:window.document.getElementById('productQuantity_'+i).innerText,
                            UOM:window.document.getElementById('productIDUOM_'+i).innerText,
                            price:window.document.getElementById('productPrice_'+i).innerText,
                            is_nds:window.document.getElementById('productVat_'+i).innerText,
                            discount:window.document.getElementById('productDiscount_'+i).innerText
                        }
                    }
                }
                let params = {
                    accountId: accountId,
                    object_Id: object_Id,
                    entity_type: entity_type,
                    money_card: money_card,
                    money_cash: money_cash,
                    //money_mobile: money_mobile,
                    pay_type: pay_type,
                    total: total,
                    position: JSON.stringify(products),
                };
                let final = url + formatParams(params);
                console.log('send to kkm = ' + final);
                let xmlHttpRequest = new XMLHttpRequest();
                xmlHttpRequest.addEventListener("load", function () {
                    $('#downL').modal('hide');
                    let json = JSON.parse(this.responseText);
                    if (json.status === 'Ticket created'){
                        window.document.getElementById("messageGoodAlert").innerText = "Чек создан";
                        window.document.getElementById("messageGood").style.display = "block";
                        window.document.getElementById("ShowCheck").style.display = "block";
                        modalShowHide = 'hide';
                        html = json.postTicket.data.html
                    } else {
                        window.document.getElementById('messageAlert').innerText = json.errors.message;
                        window.document.getElementById('message').style.display = "block";
                        window.document.getElementById(button_hide).style.display = "block";
                        modalShowHide = 'hide';
                    }
                });
                xmlHttpRequest.open("GET", final);
                xmlHttpRequest.send();
                modalShowHide = 'hide';
            }
            else window.document.getElementById(button_hide).style.display = "block"
        }

        function PrintCheck(){
            //let url = 'http://rekassa/Popup/customerorder/closeShift';
            let url = 'https://smarttis.kz/Popup/customerorder/print';
            let final = url + '/' + accountId;
            window.open(final)
        }

        function SelectorSum(Selector){
            window.document.getElementById("cash").value = ''
            window.document.getElementById("card").value = ''
            let option = Selector.options[Selector.selectedIndex];
            if (option.value === "0") {
                document.getElementById('Visibility_Cash').style.display = 'block';
                document.getElementById('Visibility_Card').style.display = 'none';
            }
            if (option.value === "1") {
                document.getElementById('Visibility_Card').style.display = 'block';
                document.getElementById('Visibility_Cash').style.display = 'none';
                let card =  window.document.getElementById("card");
                card.value = window.document.getElementById("sum").innerText
                window.document.getElementById("card").disabled = true
            }
            if (option.value === "2") {
                document.getElementById('Visibility_Cash').style.display = 'block';
                document.getElementById('Visibility_Card').style.display = 'block';
                //document.getElementById('Visibility_Mobile').style.display = 'block';
                window.document.getElementById("card").disabled = false
            }

        }

        function updateQuantity(id, params){
            let object_Quantity = window.document.getElementById('productQuantity_'+id);
            let Quantity = parseInt(object_Quantity.innerText)

            let object_price = window.document.getElementById('productPrice_'+id).innerText;
            let object_Final = window.document.getElementById('productFinal_'+id);

            let object_sum = window.document.getElementById('sum');
            let sum = parseFloat(object_sum.innerText - object_Final.innerText)

            if (params === 'plus'){
                object_Quantity.innerText = Quantity + 1
                object_Final.innerText = object_Quantity.innerText * object_price
                object_sum.innerText = parseFloat(sum + object_Final.innerText)
            }
            if (params === 'minus'){
                object_Quantity.innerText = Quantity - 1
                object_Final.innerText = object_Quantity.innerText * object_price
                object_sum.innerText = parseFloat(sum + object_Final.innerText)
            }
        }

    </script>


    <div class="main-container">
        <div class="row gradient rounded p-2">
            <div class="col-3">
                <div class="mx-2"> <img src="https://test.ukassa.kz/_nuxt/img/d2b49fb.svg" width="90%"  alt=""></div>
            </div>
            <div class="col-6 text-black " style="font-size: 22px; margin-top: 1.2rem !important;">
                <span> Заказ покупателя № </span>
                <span id="numberOrder" class="text-black"></span>
            </div>
            <div class="col-3"></div>
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
                            <div class="col-1 text-black">№</div>
                            <div class="col-4 text-black">Наименование</div>
                            <div class="col-1 text-black">Кол-во</div>
                            <div class="col-1 text-black">Ед. Изм.</div>
                            <div class="col-1 text-black">Цена</div>
                            <div class="col-1 text-black">НДС</div>
                            <div class="col-1 text-black">Скидка</div>
                            <div class="col-1 text-black">Сумма</div>
                            <div class="col-1 text-black">Учитывать </div>
                            <div class="buttons-container-head mt-1"></div>
                        </div>
                    </div>
                    <div id="products" class="col-12 text-black">
                        @for( $i=0; $i<20; $i++)
                            <div id="{{ $i }}" class="row mt-2" style="display:block;">
                                <div class="row">
                                    <div class="col-1">{{ $i + 1 }}</div>
                                    <div id="{{'productId_'.$i}}" style="display:none;"></div>
                                    <div id="{{ 'productName_'.$i }}"  class="col-4"></div>
                                    <div class="col-1 text-center row">
                                        <div class="col-4"><i onclick="updateQuantity( '{{ $i }}', 'minus')" class="fa-solid fa-circle-minus text-danger" style="cursor: pointer"></i></div>
                                        <div id="{{ 'productQuantity_'.$i }}" class="col-4"></div>
                                        <div class="col-4"><i onclick="updateQuantity( '{{ $i }}', 'plus')" class="fa-solid fa-circle-plus text-success" style="cursor: pointer"></i></div>
                                    </div>
                                    <div id="{{ 'productUOM_'.$i }}"  class="col-1 text-center"></div>
                                    <div id="{{ 'productIDUOM_'.$i }}"  class="col-1 text-center" style="display: none"></div>
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
                <div class="row">
                    <div class="col-3">
                        <div class="row">
                            <div class="col-5">
                                <div class="mx-1 mt-1 bg-warning p-1 rounded text-center">Тип оплаты</div>
                            </div>
                            <div class="col-7">
                                <select onchange="SelectorSum(valueSelector)" id="valueSelector" class="form-select">
                                    <option selected value="0">Наличными</option>
                                    <option value="1">Картой</option>
                                    {{--<option value="2">Мобильная</option>--}}
                                    <option value="2">Смешанная</option>
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
                        </div>
                    </div>
                    <div class="col-1"></div>
                    <div class="col-2 d-flex justify-content-end">
                        <button onclick="PrintCheck()" id="ShowCheck" class="btn btn-success">Распечатать чек</button>
                    </div>
                </div>
                <div class="col-7 row mt-2">
                    <div class="row">
                        <div class="col-12 mx-2 ">
                            <div class="col-5 bg-info text-white p-1 rounded">
                                <span class="mx-2"> Итого: </span>
                                <span id="sum"></span>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="col-3"></div>
                <div class="col-2 d-flex justify-content-end">
                    <button onclick="sendKKM('return')" id="refundCheck" class="btn btn-danger">возврат</button>
                    <button onclick="sendKKM('sell')" id="getKKM" class="btn btn-success">Отправить в ККМ</button>
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



    <script>
        function newPopup(){
            window.document.getElementById("sum").innerHTML = ''

            window.document.getElementById("message").style.display = "none"
            window.document.getElementById("messageGood").style.display = "none"

            window.document.getElementById("refundCheck").style.display = "none"
            window.document.getElementById("getKKM").style.display = "none"
            window.document.getElementById("ShowCheck").style.display = "none"

            window.document.getElementById("cash").value = ''
            window.document.getElementById("card").value = ''

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

        function openDown(){
            $('#lDown').modal('show');
        }function closeDown(){
            $('#lDown').modal('hide');
            $('#downL').modal('hide');
        }

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
        }function isNumberKeyCard(evt){
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
    </script>




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
        background-color: rgba(76, 175, 237, 0.86);
        padding-top: 3px;
        min-height: 3px;
    }
    .buttons-container {
        padding-top: 10px;
        min-height: 100px;
    }

</style>
