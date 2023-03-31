
@extends('layout')
@section('item', 'link_1')
@section('content')

    <div class="p-4 mx-1 mt-1 bg-white rounded py-3">

        @include('div.TopServicePartner') <script> NAME_HEADER_TOP_SERVICE("Информация") </script>

            <div class="mt-2 alert alert-danger text-center">
                Данное приложение доступно только если установлен <a href="https://online.moysklad.ru/app/#apps?id=7bd5e7ce-8d4b-4225-9d8a-4a2568690121">Учёт.ТИС</a>
            </div>

    </div>


@endsection



