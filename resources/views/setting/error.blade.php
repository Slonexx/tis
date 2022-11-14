
@extends('layout')

@section('content')

    <div class="p-4 mx-1 mt-1 bg-white rounded py-3">

        <div class="row gradient rounded p-2 pb-3">
            <div class="col-2"><img src="https://test.ukassa.kz/_nuxt/img/d2b49fb.svg" width="90%" height="90%"  alt=""></div>
            <div class="col-8" style="margin-top: 0.5rem"> <span class="text-black" style="font-size: 18px"> ошибка </span></div>
        </div>

            <div class="mt-2 alert alert-danger text-center"> {{$message}}</div>

    </div>


@endsection



