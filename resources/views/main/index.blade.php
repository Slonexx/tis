@extends('layout')

@section('content')
    <script>
        //let url = 'https://tus/';
        let url = 'https://smarttis.kz/';
        let accountId = '{{ $accountId }}'

    </script>
    <div class="p-4 mx-1 mt-1 bg-white rounded py-3">
        @if ( request()->isAdmin != null and request()->isAdmin != 'ALL' )
            <div class="mt-2 alert alert-danger alert-dismissible fade show in text-center  "> Доступ к настройкам есть только у администратора
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

            <div id="message" class="mt-2 alert alert-info alert-dismissible fade show in text-center" style="display: none">
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>

            <div class="row gradient rounded p-2 pb-3">
                <div class="col-2"><img src="https://test.ukassa.kz/_nuxt/img/d2b49fb.svg" width="90%" height="90%"  alt=""></div>
                <div class="col-8" style="margin-top: 0.9rem"> <span class="text-black" style="font-size: 18px"> Возможности интеграции </span></div>
                <div class="col-2 mt-2">
                    <button onclick="sendCollection('show')" type="button" class="btn btn-outline-info text-black" data-toggle="modal">
                        Сбор информации
                    </button>
                </div>
            </div>




            <!-- Modal -->
            <div class="modal fade bd-example-modal-sm" id="sendCollectionOfPersonalInformation" data-bs-keyboard="false" data-bs-backdrop="static"
                 tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLongTitle"><i class="fa-solid fa-handshake text-success"></i> Сбор информации</h5>
                            <div class="close" data-dismiss="modal" aria-label="Close" style="cursor: pointer;"><i class="fa-regular fa-circle-xmark"></i></div>
                        </div>
                        <div class="modal-body">
                            Разрешение на сбор личных данных об аккаунте (email, имя) для дальнейшей обратной связи с вами.
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Нет</button>
                            <button onclick="sendCollectionPersonal()" type="button" class="btn btn-primary">Разрешаю</button>
                        </div>
                    </div>
                </div>
            </div>

    </div>
    <script>
        let hideOrShow = "{{ $hideOrShow }}"

        document.getElementById('message').style.display = 'none'
        sendCollection(hideOrShow);

        function sendCollection(hideOrShow){
            if (hideOrShow === 'show') {
                $('#sendCollectionOfPersonalInformation').modal('show');
            }

            if (hideOrShow === 'hide') {
                $('#sendCollectionOfPersonalInformation').modal('hide');
            }

        }

        function sendCollectionPersonal(){
                let final = url + 'collectionOfPersonalInformation/' + accountId;
                let xmlHttpRequest = new XMLHttpRequest();
                xmlHttpRequest.addEventListener("load", function () {
                    let json = JSON.parse(this.responseText);
                    document.getElementById('message').innerText =  json.message
                    document.getElementById('message').style.display = 'block'
                    $('#sendCollectionOfPersonalInformation').modal('hide');
                    sendCollection('hide')
                });
                xmlHttpRequest.open("GET", final);
                xmlHttpRequest.send();

            $('#sendCollectionOfPersonalInformation').modal('hide');
            sendCollection('hide')
            $('.close').click();


        }
    </script>
@endsection

