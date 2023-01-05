
@extends('popup.index')

@section('content')

    <script>

        const url = 'https://dev.smarttis.kz/Popup/customerorder/show'

        let object_Id = ''
        let accountId = ''
        let entity_type = ''
        let id_ticket = ''
        let html = ''

        let payment_type = ''
        let products_length = ''


        /*let receivedMessage = {
            "name":"OpenPopup",
            "messageId":1,
            "popupName":"fiscalizationPopup",
            "popupParameters":
                {
                    "object_Id":"b9272422-81d3-11ed-0a80-07fe000a0ac9",
                    "accountId":"1dd5bd55-d141-11ec-0a80-055600047495",
                    "entity_type":"customerorder",
                }
        };*/

        window.addEventListener("message", function(event) {
            let receivedMessage = event.data

            newPopup()

            if (receivedMessage.name === 'OpenPopup') {
                object_Id = receivedMessage.popupParameters.object_Id;
                accountId = receivedMessage.popupParameters.accountId;
                entity_type = receivedMessage.popupParameters.entity_type;

                let data = { object_Id: object_Id, accountId: accountId, };

                receivedMessage = null

                let settings = ajax_settings(url, "GET", data);
                console.log(url + ' settings ↓ ')
                console.log(settings)

                $.ajax(settings).done(function (json) {
                    console.log(url + ' response ↓ ')
                    console.log(json)

                    window.document.getElementById("numberOrder").innerHTML = json.name
                    payment_type = json.application.payment_type
                    id_ticket = json.attributes.ticket_id
                    id_ticket = json.attributes.ticket_id
                    products_length = json.products.length

                    let products = json.products;
                    for (let i = 0; i < products.length; i++) {

                        if (products[i].propety === true) {

                            let vat =  products[i].vat + '%'
                            if (products[i].vat === 0)  vat = "без НДС"

                            $('#main').append('<div id="'+i+'" class="divTableRow" >' +
                                '<div class="divTableCell">'+i+'</div>' +
                                '<div id="productId_'+i+'" class="divTableCell" style="display: none">'+products[i].position+'</div>' +
                                '<div id="productName_'+i+'" class="divTableCell"> '+products[i].name+'</div>' +

                                '<div class="divTableCell">' +
                                    '<span><i onclick="updateQuantity('+ i +', "minus")" class="fa-solid fa-circle-minus text-danger" style="cursor: pointer"></i></span>' +
                                    '<span id="productQuantity_'+ i +'" class="mx-3">' + products[i].quantity + '</span>' +
                                    '<span><i onclick="updateQuantity( '+ i +', "plus")" class="fa-solid fa-circle-plus text-success" style="cursor: pointer"></i></span>' +
                                '</div>' +

                                '<div id="productUOM_'+i+'" class="divTableCell">'+products[i].uom['name']+'</div>' +
                                '<div id="productIDUOM_'+i+'" class="divTableCell" style="display: none">'+products[i].uom['id']+'</div>' +

                                '<div id="productPrice_'+ i +'" class="divTableCell"> '+ products[i].price +' </div>' +

                                '<div id="productVat_'+ i +'" class="divTableCell"> '+ vat + ' </div>' +

                                '<div id="productDiscount_'+ i +'" class="divTableCell"> '+ products[i].discount + '%' + ' </div>' +

                                '<div id="productFinal_'+ i +'" class="divTableCell"> '+ products[i].final + ' </div>' +

                                '<span onclick="deleteBTNClick('+ i +')" class="divTableCell" > <i class="fa-solid fa-rectangle-xmark" style="cursor: pointer; margin-left: 2rem" ></i> </span>' +

                                 " </div>")

                            let sum = window.document.getElementById("sum").innerHTML
                            if (!sum) sum = 0
                            window.document.getElementById("sum").innerHTML = roundToTwo(parseFloat(sum) + parseFloat(products[i].final))

                        } else {

                            $('#main').append('<div id="'+i+'" class="divTableRow" style="display: none">' + " </div>")

                            window.document.getElementById("messageAlert").innerText = "Позиции у которых нет ед. изм. не добавились "
                            window.document.getElementById("message").style.display = "block"
                        }
                    }

                    payment_type_on_set_option(payment_type, window.document.getElementById("sum").innerHTML)

                    if (json.attributes != null){
                        if (json.attributes.ticket_id != null){
                            //window.document.getElementById("ShowCheck").style.display = "block";
                            window.document.getElementById("refundCheck").style.display = "block";
                        } else {
                            window.document.getElementById("getKKM").style.display = "block";
                        }
                    } else  window.document.getElementById("getKKM").style.display = "block";

                })
            }
        });



        function sendKKM(pay_type){
            let button_hide = ''
            if (pay_type === 'return') button_hide = 'refundCheck'
            if (pay_type === 'sell') button_hide = 'getKKM'

            window.document.getElementById(button_hide).style.display = "none"
            let modalShowHide = 'show'

            let total = window.document.getElementById('sum').innerText
            let money_card = window.document.getElementById('card').value
            let money_cash = window.document.getElementById('cash').value
            let SelectorInfo = document.getElementById('valueSelector')
            let option = SelectorInfo.options[SelectorInfo.selectedIndex]

            let error_what = option_value_error_fu(option.value, money_cash, money_card)
            if (error_what === true){
                modalShowHide = 'hide'
            }

            if (total-0.01 <= money_card+money_cash){
                let url = 'https://dev.smarttis.kz/Popup/customerorder/send'

                if (modalShowHide === 'show'){
                    $('#downL').modal('toggle')
                    let products = []
                    for (let i = 0; i < products_length; i++) {
                        if (window.document.getElementById(i).style.display !== 'none') {
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

                    console.log(products)

                    let data =  {
                        "accountId": accountId,
                        "object_Id": object_Id,
                        "entity_type": entity_type,

                        "money_card": money_card,
                        "money_cash": money_cash,

                        "pay_type": pay_type,
                        "total": total,

                        "position": JSON.stringify(products),
                    }
                    console.log(url + ' data ↓ ')
                    console.log(data)

                    $.ajax({
                        url: url,
                        method: 'post',
                        dataType: 'json',
                        data: data,
                        success: function(response){
                            $('#downL').modal('hide')
                            console.log(url + ' response ↓ ')
                            console.log(response)

                            let json = response

                            if (json.status === 'Ticket created'){
                                window.document.getElementById("messageGoodAlert").innerText = "Чек создан";
                                window.document.getElementById("messageGood").style.display = "block";
                                window.document.getElementById("ShowCheck").style.display = "block";
                                modalShowHide = 'hide';
                                html = json.postTicket.data.html
                            } else {
                                window.document.getElementById('message').style.display = "block";
                                window.document.getElementById(button_hide).style.display = "block";
                                if (json.hasOwnProperty('errors'))window.document.getElementById('messageAlert').innerText = json.errors.message
                                else window.document.getElementById('messageAlert').innerText = "Ошибка: " + json


                                modalShowHide = 'hide';
                            }
                        }
                    });
                    modalShowHide = 'hide';
                }
                else window.document.getElementById(button_hide).style.display = "block"
            } else {
                window.document.getElementById('messageAlert').innerText = 'Введите сумму больше !'
                window.document.getElementById('message').style.display = "block"
                window.document.getElementById(button_hide).style.display = "block";
                modalShowHide = 'hide'
            }
        }



    </script>


    <div class="main-container">
        <div class="row gradient rounded p-2">
            <div class="col-3">
                <div class="mx-2"> <img src="https://tisuchet.kz/images/Tis%20logo.svg" width="90%"  alt=""></div>
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
              <div class="row p-3">
                  <div class="divTable myTable">
                      <div class="divTableHeading">
                          <div class="divTableRow">

                              <div class="divTableHead text-black">№</div>
                              <div class="divTableHead text-black">Наименование</div>
                              <div class="divTableHead text-black">Кол-во</div>
                              <div class="divTableHead text-black">Ед. Изм.</div>
                              <div class="divTableHead text-black">Цена</div>
                              <div class="divTableHead text-black">НДС</div>
                              <div class="divTableHead text-black">Скидка</div>
                              <div class="divTableHead text-black">Сумма</div>
                              <div class="divTableHead text-black">Учитывать </div>
                              <div class="buttons-container-head mt-1"></div>

                          </div>
                      </div>
                      <div id="main" class="divTableBody">

                      </div>
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
                                <select onchange="SelectorSum(this.value)" id="valueSelector" class="form-select">
                                    <option selected value="1">Наличными</option>
                                    <option value="2">Картой</option>
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
                    <button onclick="sendKKM('sell')" id="getKKM" class="mt-1 btn btn-success">Отправить в ККМ</button>
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

    @include('popup.script_popup_app')
    @include('popup.style_popup_app')
@endsection


{{-- <div class="col-12">
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
                        @for( $i=0; $i<99; $i++)
                            <div id="{{ $i }}" class="mt-2" style="display:block;">
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
                    </div>--}}
