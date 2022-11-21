
@extends('layout')

@section('content')

    <div class="p-4 mx-1 mt-1 bg-white rounded py-3">

        <div class="row gradient rounded p-2 pb-2" style="margin-top: -1rem">
            <div class="col-10" style="margin-top: 1.2rem"> <span class="text-black" style="font-size: 20px"> Ошибка </span> </div>
            <div class="col-2 text-center">
                <img src="https://smarttis.kz/Config/logo.png" width="50%"  alt="">
                <div style="font-size: 11px; margin-top: 8px"> <b>Топ партнёр сервиса МойСклад</b> </div>
            </div>
        </div>

            <div class="mt-2 alert alert-danger text-center"> {{$message}}</div>

    </div>


@endsection



