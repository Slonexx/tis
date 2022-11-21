@extends('layout')
@section('item', 'link_6')
@section('content')

    <div class="p-4 mx-1 mt-1 bg-white rounded py-3">

        <div class="row gradient rounded p-2 pb-2" style="margin-top: -1rem">
            <div class="col-10" style="margin-top: 1.2rem"> <span class="text-black" style="font-size: 20px"> Смена </span> </div>
            <div class="col-2 text-center">
                <img src="https://smarttis.kz/Config/logo.png" width="50%"  alt="">
                <div style="font-size: 11px; margin-top: 8px"> <b>Топ партнёр сервиса МойСклад</b> </div>
            </div>
        </div>

            <div id="message_good" class="mt-2 alert alert-success alert-dismissible fade show in text-center" style="display: none">
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>

            <div id="message" class="mt-2 alert alert-danger alert-dismissible fade show in text-center" style="display: none">
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>


        <form class="mt-3" action="" method="post">
        @csrf <!-- {{ csrf_field() }} -->
            <div class="row">
                <label for="idKassa" class="col-3 col-form-label"> Выберите кассу </label>
                <div class="col-6">
                    <select id="idKassa" name="idKassa" class="form-select text-black" onchange="idKassaCheck()">
                        @foreach( $kassa as $item)
                            <option value="{{ $item->id }}"> {{ $item->name }} </option>
                        @endforeach
                    </select>
                </div>
                <div id="is_activated" class="col-3 bg-success text-white p-1 col-form-label text-center rounded"> загрузка... </div>
            </div>

            <hr>

            <div class='text-black text-center' >
                <div class="row ">
                    <button formaction="/kassa/get_shift_report/{{ $accountId }}?isAdmin={{ $isAdmin }}" class="col-2 btn btn-outline-dark textHover"> Получить X-отчёт </button>
                    <div class="col-2"></div>
                    <div onclick="activate_btn('cash')" class="col-4 btn btn-outline-dark textHover"> Внесение/Изъятие </div>
                    <div class="col-2"></div>
                    <button formaction="/operation/close_z_shift/{{ $accountId }}?isAdmin={{ $isAdmin }}" class="col-2 btn btn-outline-dark textHover"> Получить Z-отчёт </button>
                </div>
            </div>
        </form>
    </div>

    <script>
        let idKassa
        let accountId = '{{ $accountId }}'
        idKassaCheck()

        function idKassaCheck(){
            idKassa = window.document.getElementById('idKassa').value

            let params = {
                idKassa: idKassa,
            };
            let url = 'https://smarttis.kz/kassa/get_shift_report/info/'+accountId;
            //let url = 'https://tus/kassa/get_shift_report/info/'+accountId;
            let final = url + formatParams(params);

            const xmlHttpRequest = new XMLHttpRequest();
            xmlHttpRequest.addEventListener("load", function() {
                var json = JSON.parse(this.responseText);
                if (json.status == true){
                    window.document.getElementById('is_activated').innerText = 'Активна'
                    window.document.getElementById('is_activated').classList.add('bg-success')
                    window.document.getElementById('is_activated').classList.add('text-white')
                } else  {
                    window.document.getElementById('is_activated').classList.add('bg-danger')
                    window.document.getElementById('is_activated').innerText = "Смена закрыта"
                }

            });
            xmlHttpRequest.open("GET", final);
            xmlHttpRequest.send();

        }





        function saveValCash(){
            idKassa = window.document.getElementById('idKassa').value

            let params = {
                idKassa: idKassa,
                operation_type: window.document.getElementById('operations').value,
                amount: window.document.getElementById('inputSum').value,
            };
            let url = 'https://smarttis.kz/operation/cash_operation/'+accountId;
            //let url = 'https://tus/operation/cash_operation/'+accountId;
            let final = url + formatParams(params);
            console.log(final)
            const xmlHttpRequest = new XMLHttpRequest();
            xmlHttpRequest.addEventListener("load", function() {
                let json = JSON.parse(this.responseText);
                if (json.status == true){
                    console.log('true')
                    let message_good = window.document.getElementById('message_good');
                    message_good.style.display = 'block'
                    message_good.innerText = JSON.stringify(json.message_good)
                    closeModal('cash')
                } else if (json.status == false){
                    console.log('false')
                    window.document.getElementById('message').style.display = 'block'
                    window.document.getElementById('message').innerText = JSON.stringify(json.message)
                    closeModal('cash')
                }
            });
            xmlHttpRequest.open("POST", final);
            xmlHttpRequest.send();

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

    <div class="modal fade" id="cash" tabindex="-1"  role="dialog" aria-labelledby="cashTitle" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cashTitle">Внесение</h5>
                    <div class="close" data-dismiss="modal" aria-label="Close" style="cursor: pointer;"><i onclick="closeModal('cash')" class="fa-regular fa-circle-xmark"></i></div>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <label for="operations" class="col-5 col-form-label"> Выберите операцию </label>
                        <div class="col-7">
                            <select id="operations" name="operations" class="form-select text-black" onchange="valueCash(this.value)">
                                <option value="0"> Внесение </option>
                                <option value="1"> Изъятие </option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <label for="operations" class="col-5 col-form-label">
                            <span id="inputGroupText" class="p-2 text-white bg-success rounded">Введите сумму </span>
                        </label>
                        <div class="col-7 input-group mt-1">
                            <input id="inputSum" name="inputSum" onkeypress="return isNumber(event)" type="text" class="form-control" aria-label="">
                            <div class="input-group-append">
                                <span class="input-group-text">.00</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button onclick="closeModal('cash')" type="button" class="btn btn-secondary" data-dismiss="modal">Отмена</button>
                    <button onclick="saveValCash()" type="button" class="btn btn-primary">Сохранить</button>
                </div>
            </div>
        </div>
    </div>


    <script>
        function isNumber(evt){
            var charCode = (evt.which) ? evt.which : event.keyCode
            if (charCode == 46){
                var inputValue = $("#card").val();
                var count = (inputValue.match(/'.'/g) || []).length;
                if(count<1){
                    if (inputValue.indexOf('.') < 1){
                        return true;
                    }
                    return false;
                }else{
                    return false;
                }
            }
            if (charCode != 46 && charCode > 31 && (charCode < 48 || charCode > 57)){
                return false;
            }
            return true;
        }

        function activate_btn(params){
            if (params == 'cash'){
                $('#cash').modal('show')
                window.document.getElementById('inputSum').value = 0
            }
        }

        function closeModal(params) {
            if (params == 'cash'){
                $('#cash').modal('hide')
            }
        }

        function valueCash(val){

            if (val == 0 ) {
                window.document.getElementById('cashTitle').innerText = 'Внесение'
                document.getElementById('inputGroupText').classList.add('bg-success')
                document.getElementById('inputGroupText').classList.remove('bg-danger')
            }
            if (val == 1) {
                window.document.getElementById('cashTitle').innerText = 'Изъятие'
                document.getElementById('inputGroupText').classList.add('bg-danger')
                document.getElementById('inputGroupText').classList.remove('bg-success')
            }
        }

    </script>
@endsection

