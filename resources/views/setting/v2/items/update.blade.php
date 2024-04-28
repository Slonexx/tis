@extends('setting.v2.initSetting')
@section('name_head', 'Обновление')
@section('child')

    <div class="box p-1">
        <div class="tabs is-right gradient_a">
            <ui>
                <li><a href="/Setting/initSetting/{{ $accountId }}?isAdmin={{ $isAdmin }}">Назад</a></li>
            </ui>
            <ul>
                <li><a  class="is-active" href="/Setting/createSetting/{{ $accountId }}?isAdmin={{ $isAdmin }}">Подключение</a></li>
                <li><a href="/Setting/Update/kassa/{{ $uid }}/{{ $accountId }}?isAdmin={{ $isAdmin }}"> Касса </a></li>
                <li><a> Документ </a></li>
                <li><a> Доступ </a></li>
            </ul>
        </div>
    </div>

    <div id="html" class="box">
        <form action="/Setting/Update/connect/{{ $uid }}/{{ $accountId }}?isAdmin={{ $isAdmin }}" method="post">
            @csrf <!-- {{ csrf_field() }} -->

            <div class="columns field">
                <div class="column is-3 mt-1" > Электронная почта </div>
                <div class="column">
                    <div class="w-75 is-small is-link">
                        <input id="email" class="input is-small is-link" type="email" name="email" placeholder="example@gmail.com"/>
                    </div>
                </div>
                <div class="mt-1 tag is-medium is-Light">
                    <span class="icon has-text-info"> <i class="fas fa-info-circle"></i> </span>
                    <span>введите электронную почту от учёт.онлайн.кассы</span>
                </div>
            </div>
            <div class="columns field">
                <div class="column is-3 mt-1" > Пароль </div>
                <div class="column">
                    <div class="w-75 is-small is-link">
                        <input id="pass" class="input is-small is-link" type="password" name="pass" placeholder="123456789"/>
                    </div>
                </div>
                <div class="column is-1">
                    <div onclick="hideOrViewPass()" class="tag is-medium is-outlined gradient_focus "> <i class="fas fa-user-ninja"></i> </div>
                </div>
                <div class="mt-1 tag is-medium is-Light">
                    <span class="icon has-text-info"> <i class="fas fa-info-circle"></i> </span>
                    <span>введите пароль от учёт.онлайн.кассы</span>
                </div>
            </div>

            <button class="button is-outlined gradient_focus"> сохранить</button>
        </form>

    </div>


    <script>
        let accountId = '{{$accountId}}';
        let model = @json($model);
        $(document).ready(function () { leadingUpdate() });




        function leadingUpdate(){
            $('#email').val(model.email);
            $('#pass').val(model.pass);
        }


        function hideOrViewPass(){
            let pass = window.document.getElementById('pass')
            if (pass.type === 'text') pass.type = 'password'
            else pass.type = 'text'
        }
    </script>

@endsection

