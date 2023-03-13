@extends('layout')
@section('item', 'link_4')
@section('content')

    <div class="p-4 mx-1 mt-1 bg-white rounded py-3">
        @include('div.TopServicePartner')

        @isset($message)

            <div class="mt-2 {{$message['alert']}}"> {{ $message['message'] }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>

        @endisset

        <form action="/Setting/Document/{{ $accountId }}?isAdmin={{ $isAdmin }}" method="post" class="mt-3">
        @csrf <!-- {{ csrf_field() }} -->

            <div class="row p-1 gradient_invert rounded text-black">
                <div class="col-11">
                    <div style="font-size: 20px">Документ</div>
                </div>
                <div onclick="toggleClick(1)" class="col-1 d-flex justify-content-end " style="font-size: 30px; cursor: pointer">
                    <i id="toggle_off" class="fa-solid fa-toggle-off" style="display: block"></i>
                    <i id="toggle_on"  class="fa-solid fa-toggle-on" style="display: none"></i>
                </div>
            </div>
            <div id="DOCUMENT" class="mt-2 mx-2 mb-2" style="display: block">
                <div class="row">
                    <div class="col-6">
                        <label class="mt-1 mx-4"> Выберите какой тип платежного документа создавать: </label>
                    </div>
                    <div class="col-6">
                        <select id="createDocument_asWay" name="createDocument_asWay" class="form-select text-black" >
                            <option value="0"> Не создавать </option>
                            <option value="1">Приходной ордер</option>
                            <option value="2">Входящий платёж </option>
                            <option value="3"> От выбора типа оплаты </option>
                        </select>
                    </div>
                </div>
            </div>


            <div class="row p-1 mt-1 gradient_invert rounded text-black ">
                <div class="col-11">
                    <div style="font-size: 20px">Тип оплаты по умолчанию</div>
                </div>
                <div onclick="toggleClick(2)" class="col-1 d-flex justify-content-end " style="font-size: 30px; cursor: pointer">
                    <i id="toggle_off_2" class="fa-solid fa-toggle-off" style="display: block"></i>
                    <i id="toggle_on_2"  class="fa-solid fa-toggle-on" style="display: none"></i>
                </div>
            </div>
            <div id="Default_payment_type" class="mt-2 mx-2 mb-2" style="display: block">
                <div class="row">
                    <div class="col-6">
                        <label class="mt-1 mx-4"> Выберите тип оплаты </label>
                    </div>
                    <div class="col-6">
                        <select id="payment_type" name="payment_type" class="form-select text-black" >
                            <option value="1"> Оплата наличными </option>
                            <option value="2"> Оплата картой </option>
                        </select>
                    </div>
                </div>
            </div>


            <hr class="href_padding">

            <button class="btn btn-outline-dark "><i class="fa-solid fa-arrow-down-to-arc"></i> Сохранить </button>
        </form>
    </div>

    <script>

        let createDocument = "{{ $paymentDocument }}" > 0 ? "{{ $paymentDocument }}" : '0'
        let payment_type = "{{ $payment_type }}" > 0 ? "{{ $payment_type }}" : '1'
        NAME_HEADER_TOP_SERVICE("Настройки → Документ")

        console.log(payment_type);

        loading(createDocument, payment_type)

        function loading(createDocument, payment_type){
            window.document.getElementById('createDocument_asWay').value = createDocument
            window.document.getElementById('payment_type').value = payment_type

        }

        function oldWay(params){

        }

        function toggleClick(id){

            if (id === 1){
                let toggle_off = window.document.getElementById('toggle_off')
                let toggle_on = window.document.getElementById('toggle_on')

                let DOCUMENT = window.document.getElementById('DOCUMENT')

                if (toggle_off.style.display == "none"){
                    toggle_on.style.display = "none"
                    toggle_off.style.display = "block"

                    DOCUMENT.style.display = 'block'
                } else {
                    toggle_on.style.display = "block"
                    toggle_off.style.display = "none"

                    DOCUMENT.style.display = 'none'
                }
            }

            if (id === 2) {
                let toggle_off_2 = window.document.getElementById('toggle_off_2')
                let toggle_on_2 = window.document.getElementById('toggle_on_2')

                let  Default_payment_type = window.document.getElementById('Default_payment_type')
                if (toggle_off_2.style.display == 'none'){
                    toggle_on_2.style.display = "none"
                    toggle_off_2.style.display = "block"

                    Default_payment_type.style.display = 'block'
                } else {
                    toggle_on_2.style.display = "block"
                    toggle_off_2.style.display = "none"

                    Default_payment_type.style.display = 'none'
                }
            }



        }

    </script>


@endsection



