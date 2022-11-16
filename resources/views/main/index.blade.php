@extends('layout')

@section('content')
    <script>
        let url = 'https://tus/';
        //let url = 'https://smarttis.kz/';
        let accountId = '{{ $accountId }}'

    </script>
    <div class="p-4 mx-1 mt-1 bg-white rounded py-3">
        @if ( request()->isAdmin != null and request()->isAdmin != 'ALL' )
            <div class="mt-2 alert alert-danger alert-dismissible fade show in text-center  "> Доступ к настройкам есть только у администратора
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

            <div id="message" class="mt-2 alert alert-info alert-dismissible fade show in text-center" style="display: none">
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>

            <div class="row gradient rounded p-2 pb-3">
                <div class="col-2"><img src="https://test.ukassa.kz/_nuxt/img/d2b49fb.svg" width="90%" height="90%"  alt=""></div>
                <div class="col-8" style="margin-top: 0.9rem"> <span class="text-black" style="font-size: 18px"> Возможности интеграции </span></div>
                {{--<div class="col-2 mt-2">
                    <button onclick="sendCollection('show')" type="button" class="btn btn-outline-info text-black" data-toggle="modal">
                        Сбор информации
                    </button>
                </div>--}}
            </div>

            <div class="row mt-3">
                <div class="col-6">
                    <div class="row">
                        <div> <strong>ФИСКАЛИЗАЦИЯ ПРОДАЖ</strong></div>
                        <div class="">
                            Можно фискализировать продажи из документов Заказ покупателя и Отгрузка с отправкой чека на WhatsApp или почту, также можно скачать или распечатать его.
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="mt"> <strong>ФИСКАЛИЗАЦИЯ ВОЗВРАТОВ</strong></div>
                    <div class="">
                        Возврат можно произвести как из документов Заказ покупателя и Отгрузка, так и из Возврата покупателю.
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-6">
                    <div class="row">
                        <div class=""> <strong>РАБОТА С МАРКИРОВАННЫМИ ТОВАРАМИ</strong></div>
                        <div class="">
                            Наше решение позволяет отправлять коды маркировки в ОФД для списания с вашего виртуального склада.
                            Фискализация продаж маркированныйх товаров происходит только через документ Отгрузка.
                            Фискализация возвратов маркированныйх товаров происходит только через документ Возврат покупателю.
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class=""> <strong>АВТОМАТИЧЕСКОЕ СОЗДАНИЕ ДОКУМЕНТОВ</strong></div>
                    <div class="">
                        Вы можете упростить себе жизнь и настроить автоматическое создание Платежных документов (Ордера или Платежи) с выбором счета для Входящих/Исходящих платежей.
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-6">
                    <div class=""> <strong>7 ДНЕЙ БЕСПЛАТНО</strong></div>
                    <div class="">
                        Мы на 1000% уверены в своем приложении и поэтому готовы предоставить 7 дней, чтобы Вы могли оценить его возможности и уникальность.
                    </div>
                </div>
                <div class="col-6">
                    <div class=""> <strong>НОВЫЕ ВОЗМОЖНОСТИ</strong></div>
                    <div class="">
                        Мы не стоим на месте, поэтому совсем скоро Вы сможете оценить новые фишки в нашем приложении. Ну и будем признатальны за обратную связь.
                    </div>
                </div>
            </div>




    </div>
    <script>
        let hideOrShow = "{{ $hideOrShow }}"

        document.getElementById('message').style.display = 'none'
        sendCollection(hideOrShow);

        function sendCollection(hideOrShow){
            if (hideOrShow === 'show') {
                sendCollectionPersonal()
            }

            if (hideOrShow === 'hide') {
                //('#sendCollectionOfPersonalInformation').modal('hide');
            }

        }

        function sendCollectionPersonal(){
                let final = url + 'collectionOfPersonalInformation/' + accountId;
                let xmlHttpRequest = new XMLHttpRequest();
                xmlHttpRequest.addEventListener("load", function () {
                    let json = JSON.parse(this.responseText);
                });
                xmlHttpRequest.open("GET", final);
                xmlHttpRequest.send();

        }
    </script>
@endsection

