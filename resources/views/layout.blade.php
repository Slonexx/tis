<!doctype html>
<html lang="en">
    @include('head')
    <body style="background-color:#dcdcdc;">

            <div class="page headfull">
                <div class="sidenav">

                    <div class="p-2 gradient pb-3 ">
                        <img src="https://test.ukassa.kz/_nuxt/img/d2b49fb.svg" width="90%"  alt="">
                    </div>
                    <br>
                        <div class="toc-list-h1">
                            <a class="mt-2 mb-2" href="/{{$accountId}}?isAdmin={{ request()->isAdmin }}">Главная </a>
                            <div>
                                @if ( request()->isAdmin == null )
                                @else
                                    @if( request()->isAdmin == 'ALL')
                                            <button class="dropdown-btn">Настройки <i class="fa fa-caret-down"></i> </button>
                                            <div class="dropdown-container">
                                                <a href="/Setting/createAuthToken/{{$accountId}}?isAdmin={{ request()->isAdmin }}"> Основное </a>
                                                <a href="/Setting/Kassa/{{$accountId}}?isAdmin={{ request()->isAdmin }}"> Касса </a>
                                                <a href="/Setting/Document/{{$accountId}}?isAdmin={{ request()->isAdmin }}"> Документ </a>
                                                {{--<a href="/Setting/Worker/{{$accountId}}?isAdmin={{ request()->isAdmin }}"> Сотрудники </a>--}}
                                            </div>
                                            <button class="dropdown-btn">Смена <i class="fa fa-caret-down"></i> </button>
                                            <div class="dropdown-container">
                                                <a href="/operation/cash_operation/{{$accountId}}?isAdmin={{ request()->isAdmin }}"> Внесение/Изъятие </a>
                                                <a href="/kassa/get_shift_report/{{$accountId}}?isAdmin={{ request()->isAdmin }}"> X-отчёт </a>
                                                <a href="/operation/close_z_shift/{{$accountId}}?isAdmin={{ request()->isAdmin }}"> Z-отчёт </a>
                                            </div>
                                    @endif
                                @endif
                            </div>
                        </div>

                    <div class="mt-2 mb-2" >
                        <button class="dropdown-btn">Помощь <i class="fa fa-caret-down"></i> </button>
                            <div class="dropdown-container">
                                <a target="_blank" href="https://smartrekassa.bitrix24.site/contact/">
                                    <i class="fa-solid fa-address-book"></i>
                                    Контакты </a>
                                <a target="_blank" href="https://api.whatsapp.com/send/?phone=77232400545&text=" >
                                    <i class="fa-brands fa-whatsapp"></i>
                                    Написать на WhatsApp </a>
                                <a target="_blank" href="https://smartrekassa.bitrix24.site/instruktsiiponastroyke" >
                                    <i class="fa-solid fa-chalkboard-user"></i>
                                     Инструкция </a>
                            </div>
                    </div>

                </div>
                <div class="main head-full"> @yield('content') </div>
            </div>

    </body>
</html>

@include('style')
@include('script')


