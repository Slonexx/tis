
@extends('layout')

@section('content')

    <div class="p-4 mx-1 mt-1 bg-white rounded py-3">
        @include('div.TopServicePartner')
        <div class="mt-2 alert alert-danger text-center"> <i class="fa-solid fa-screwdriver-wrench"></i>
            Сначала нужно пройти основные настройки
        </div>

    </div>

    <script>
        NAME_HEADER_TOP_SERVICE("Настройки → настройки интеграции ")
    </script>

@endsection



