@extends('layout')
@section('item', 'link_1')
@section('name_head', 'Возможности приложения')
@section('content')

    @include('site_header.header')
    @include('notification.notification')

    <div class="box">
        <div class="columns">
            <div class="column is-6">
                <div> <strong>НАЛОГОВЫЕ ЛЬГОТЫ</strong></div>
                <div class="">
                    <span> Повышение порога по НДС для пользователей с 30 000 МРП до 114 184 МРП (441 млн. тенге в 2022г.)</span>

                    <div>Повышение порога дохода за налоговый период (полугодие), позволяющего применять «упрощенку» до70 048 МРП(288 млн. тенгев 2022г.)</div>
                </div>
            </div>
            <div class="column is-6">
                <div class="mt"> <strong>РАБОТА С МАРКИРОВАННЫМИ ТОВАРАМИ</strong></div>
                <div class="">
                    Наше решение позволяет отправлять коды маркировки в ОФД для списания с вашего виртуального склада.
                    Фискализация продаж маркированных товаров происходит только через документ Отгрузка.
                    Фискализация возвратов маркированных товаров происходит только через документ Возврат покупателю.
                </div>
            </div>
        </div>

        <div class="columns">
            <div class="column is-6">
                <div class=""> <strong>910 ФОРМА</strong></div>
                <div class="">
                    <div> Автоматическое формирование и заполнение </div>
                    <div> Автоматическая отправка в кабинет налогоплательщика </div>

                </div>
            </div>
            <div class="column is-6">
                <div class=""> <strong>АВТОМАТИЧЕСКОЕ СОЗДАНИЕ ДОКУМЕНТОВ</strong></div>
                <div class="">
                    Вы можете упростить себе жизнь и настроить автоматическое создание Платежных документов (Ордера или Платежи) с выбором счета для Входящих/Исходящих платежей.
                </div>
            </div>
        </div>

        <div class="columns">
            <div class="column is-6">
                <div class=""> <strong>ФИСКАЛИЗАЦИЯ ПРОДАЖ</strong></div>
                <div class="">
                    Можно фискализировать продажи из документов Заказ покупателя и Отгрузка с возможностью скачать или распечатать чек.
                </div>
            </div>
            <div class="column is-6">
                <div class=""> <strong>ФИСКАЛИЗАЦИЯ ВОЗВРАТОВ</strong></div>
                <div class="">
                    Возврат можно произвести как из документов Заказ покупателя и Отгрузка, так и из Возврата покупателю.
                </div>
            </div>
        </div>

        <div class="columns">
            <div class="column is-6">
                <div class=""> <strong>7 ДНЕЙ БЕСПЛАТНО</strong></div>
                <div class="">
                    Мы на 1000% уверены в своем приложении и поэтому готовы предоставить 7 дней, чтобы Вы могли оценить его возможности и уникальность.
                </div>
            </div>
            <div class="column is-6">
                <div class=""> <strong>НОВЫЕ ВОЗМОЖНОСТИ</strong></div>
                <div class="">
                    Мы не стоим на месте, поэтому совсем скоро Вы сможете оценить новые фишки в нашем приложении. Ну и будем признатальны за обратную связь.
                </div>
            </div>
        </div>
    </div>
@endsection
