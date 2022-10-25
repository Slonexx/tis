@extends('layout')

@section('content')

    <div class="p-4 mx-1 mt-1 bg-white rounded py-3">
        @if ( request()->isAdmin != null and request()->isAdmin != 'ALL' )
            <div class="mt-2 alert alert-danger alert-dismissible fade show in text-center  "> Доступ к настройкам есть только у администратора
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

            <div class="row gradient rounded p-2 pb-3">
                <div class="col-2"><img src="https://test.ukassa.kz/_nuxt/img/d2b49fb.svg" width="100%" height="100%"  alt=""></div>
                <div class="col-8 mt-2"> <span class="text-black" style="font-size: 18px"> Возможности интеграции </span></div>
            </div>


    </div>
@endsection

