<!doctype html>
<html lang="en" style="background-color:#dcdcdc;">
@include('head')
<body>

<div class="page headfull">
    <div class="sidenav">
        <div class="p-2 gradient pb-3 ">
            <img src="https://tisuchet.kz/images/Tis%20logo.svg" width="90%" alt="">
        </div>
        <br>
        <a id="link_1" href="/{{$accountId}}?isAdmin={{$isAdmin}}">Возможности приложения </a>
        <div id="isAdmin" style="display: none">
           {{-- <a id="link_2" class="mt-1" href="/Setting/initSetting/{{$accountId}}?isAdmin={{$isAdmin}}"> Настройки </a>--}}


            <button id="btn_1" class="mt-1 dropdown-btn">Настройки <i class="fa fa-caret-down"></i></button>
            <div class="dropdown-container">
                <a id="link_2" class="mt-1" href="/Setting/createAuthToken/{{$accountId}}?isAdmin={{$isAdmin}}">
                    Основное </a>
                <a id="link_3" class="mt-1" href="/Setting/Kassa/{{$accountId}}?isAdmin={{$isAdmin}}"> Касса </a>
                <a id="link_4" class="mt-1" href="/Setting/Document/{{$accountId}}?isAdmin={{$isAdmin}}"> Документ </a>
                <a id="link_5" class="mt-1" href="/Setting/Worker/{{$accountId}}?isAdmin={{$isAdmin}}"> Доступ </a>
                <a id="link_6" class="mt-1" href="/Setting/Automation/{{$accountId}}?isAdmin={{$isAdmin}}">
                    Автоматизация </a>
            </div>
        </div>

        <a id="link_7" class="mt-1" href="/kassa/change/{{$accountId}}?isAdmin={{$isAdmin}}"> Смена </a>

        @include('contact', ['address' => 'https://smartuchettis.bitrix24.site/contact/']);
    </div>
    <div id="main_heading" class="main"> <br> @yield('content') </div>
</div>
</body>
</html>

<script>
    let isAdmin = '{{$isAdmin}}';
    if (isAdmin === 'ALL') window.document.getElementById('isAdmin').style.display = 'block'

    let item_sidenav = '@yield('item')'
</script>

@include('style')
@include('script')


