@extends('layout')
@section('item', 'link_2')
@section('content')
    <script>
        //let url = 'https://tus/';
        let url = 'https://main.smarttis.kz/';
        let accountId = '{{ $accountId }}'
    </script>
    <div class="p-4 mx-1 mt-1 bg-white rounded py-3">

        <div class="row gradient rounded p-2 pb-2" style="margin-top: -1rem">
            <div class="col-10" style="margin-top: 1.2rem"> <span class="text-black" style="font-size: 20px"> Настройки &#8594; настройки интеграции </span> </div>
            <div class="col-2 text-center">
                <img src="https://main.smarttis.kz/Config/logo.png" width="50%"  alt="">
                <div style="font-size: 11px; margin-top: 8px"> <b>Топ партнёр сервиса МойСклад</b> </div>
            </div>
        </div>

        @isset($message)

            <div class="mt-2 alert alert-danger alert-dismissible fade show in text-center "> {{ $message }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>

        @endisset

        <div id="mainMessage" class="mt-2 alert alert-warning alert-dismissible fade show in text-center" style="display: none">  </div>

        <form class="mt-3" action="/Setting/createAuthToken/{{ $accountId }}?isAdmin={{ $isAdmin }}" method="post">
        @csrf <!-- {{ csrf_field() }} -->
            <div class="mb-3 row">
                <label for="token" class="col-3 col-form-label"> Токен учет онлайн кассы </label>
                <div class="col-9">
                    <input id="token" type="text" name="token" placeholder="ключ доступа к Учёт онлайн кассы" class="form-control form-control-orange"
                           required maxlength="255" value="{{ $token }}">
                </div>
            </div>
            <hr>
            <div class='d-flex justify-content-end text-black btnP' >
                <button class="btn btn-outline-dark textHover" data-bs-toggle="modal" data-bs-target="#modal">
                    <i class="fa-solid fa-arrow-down-to-arc"></i> Сохранить </button>
            </div>
        </form>
    </div>

    <div class="modal fade bd-example-modal-sm" id="sendTokenByEmailAndPassword" data-bs-keyboard="false" data-bs-backdrop="static"
         tabindex="-1" role="dialog" aria-labelledby="..." aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa-solid fa-handshake text-success"></i> Получить токен учёт онлайн кассы </h5>
                    <div class="close" data-dismiss="modal" aria-label="Close" style="cursor: pointer;"><i class="fa-regular fa-circle-xmark"></i></div>
                </div>
                <div class="modal-body">

                    <div id="message" class="mt-2 alert alert-info alert-dismissible fade show in text-center" style="display: none">  </div>

                    <div class="row">
                        <label class="col-3 col-form-label"> Email </label>
                        <div class="col-9">
                            <input id="sendEmail" type="email" name="email" placeholder=" почта@gmail.com " class="form-control form-control-orange">
                        </div>
                        <label class="col-3 col-form-label"> Пароль </label>
                        <div class="col-9">
                            <div class="input-group">
                            <input id="sendPassword" type="password" name="password" placeholder=" *********** " class="form-control form-control-orange">
                                <div class="input-group-append">
                                    <button onclick="eye_password()" class="btn btn-outline-secondary" type="button"><i class="fa-solid fa-eye"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer"> <button onclick="sendToken()" type="button" class="btn btn-primary">Получить</button> </div>
            </div>
        </div>
    </div>

    <script>
        let token = window.document.getElementById('token')
        if (token.value !== ''){

        } else {  sendCollection('show') }

        function sendToken(){
            let email = document.getElementById('sendEmail')
            let password = document.getElementById('sendPassword')
            let message = document.getElementById('message')

            if (email.value === '' || password.value === '' ){
                message.innerText = 'Введите логин или пароль'
                message.style.display = 'block'
            } else {
                message.innerText = ''
                message.style.display = 'none'

                let params = { email: email.value, password: password.value };
                let final = url + 'get/createAuthToken/'+ accountId + formatParams(params);

                console.log("url sendToken = " + final);

                let xmlHttpRequest = new XMLHttpRequest();
                xmlHttpRequest.addEventListener("load", function () {
                    let json = JSON.parse(this.responseText);
                    console.log(json);
                    console.log(JSON.stringify(json));
                    if (json.status === 200) {
                        message.style.display = 'none'
                        window.document.getElementById('token').value = json.auth_token
                        window.document.getElementById('mainMessage').innerText = json.full_name + ' ваш токен создан, не забудьте нажать на кнопку сохранить'
                        window.document.getElementById('mainMessage').style.display = 'block'
                        $('.close').click();
                    } else {
                        message.innerText = 'Не верный email или пароль'
                        message.style.display = 'block'
                    }
                });
                xmlHttpRequest.open("GET", final);
                xmlHttpRequest.send();
            }
        }

        function eye_password(){
            let input = document.getElementById('sendPassword')
            console.log('Object.type = ' + input.type)
            if (input.type === "password"){
                input.type = "text"
            } else {
                input.type = "password"
            }
        }
        function sendCollection(hideOrShow){
            console.log('its sendCollection = ' + hideOrShow)
            if (hideOrShow === 'show') {
                $('#sendTokenByEmailAndPassword').modal({backdrop: 'static', keyboard: false})
                $('#sendTokenByEmailAndPassword').modal('show')
            }

            if (hideOrShow === 'hide') {
                $('#sendTokenByEmailAndPassword').modal('hide')
            }

        }
        function formatParams(params) {
            return "?" + Object
                .keys(params)
                .map(function (key) {
                    return key + "=" + encodeURIComponent(params[key])
                })
                .join("&")
        }
    </script>

@endsection

