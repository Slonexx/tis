@extends('layout')
@section('item', 'link_6')
@section('content')

    <div class="p-4 mx-1 mt-1 bg-white rounded py-3">
        @include('div.TopServicePartner')
        <script> NAME_HEADER_TOP_SERVICE("Смена → Х-отчёт") </script>

        @isset($message_good)

            <div class="mt-2 alert alert-success alert-dismissible fade show in text-center "> {{ $message_good }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>

        @endisset

        @isset($message)

            <div class="mt-2 alert alert-danger alert-dismissible fade show in text-center "> {{ $message }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>

        @endisset
        <form class="mt-3" action="/kassa/get_shift_report/{{ $accountId }}?isAdmin={{ $isAdmin }}" method="post">
        @csrf <!-- {{ csrf_field() }} -->

            <div class="row">
                <label for="idKassa" class="col-3 col-form-label"> Выберите кассу </label>
                <div class="col-6">
                    <select id="idKassa" name="idKassa" class="form-select text-black" onchange="get_activated()">
                        @foreach( $kassa as $item)
                            <option value="{{ $item->id }}"> {{ $item->name }} </option>
                        @endforeach
                    </select>
                </div>
                <div id="is_activated" class="col-3 bg-success text-white p-1 col-form-label text-center rounded"> загрузка... </div>
            </div>


            <hr>
            <div class='d-flex justify-content-end text-black btnP' >
                <button class="btn btn-outline-dark textHover"> <i class="fa-solid fa-arrow-down-to-arc"></i> Получить X-отчёт </button>
            </div>
        </form>
    </div>
    <!-- Modal -->
    <div class="modal fade " id="html" tabindex="-1"  role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle">X-отчёт</h5>
                    <div class="close" data-dismiss="modal" aria-label="Close" style="cursor: pointer;"><i class="fa-regular fa-circle-xmark"></i></div>
                </div>
                <div class="modal-body">
                    @isset( $html )
                        <div class="row">
                            <div class="col-2"></div>
                            <div class="col-9">{!! $html !!}</div>
                        </div>
                    @endisset
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">закрыть</button>
                    <button onclick="PrintCheck()" type="button" class="btn btn-primary">Распечатать</button>
                </div>
            </div>
        </div>
    </div>

    <script>

        @php( $showOrHide = 'hide' )
        @isset($html) @php($showOrHide = 'show') @endisset

        let html = "{{$showOrHide}}"
        let accountId = '{{ $accountId }}'


        htmlVue(html)
        function htmlVue(parameter){
            if (parameter == 'show'){
                $('#html').modal('show')
            }
            else {
                $('#html').modal('hide')
            }
        }
        function PrintCheck(){
            let url = 'https://dev.smarttis.kz/kassa/get_shift_report/print';
            let final = url + '/' + accountId;
            window.open(final)
        }

        function formatParams(params) {
            return "?" + Object
                .keys(params)
                .map(function (key) {
                    return key + "=" + encodeURIComponent(params[key])
                })
                .join("&")
        }

        get_activated()

        function get_activated(){
            let idKassa = window.document.getElementById('idKassa').value
            console.log(idKassa)

            let params = {
                idKassa: idKassa,
            };
            let url = 'https://dev.smarttis.kz/kassa/get_shift_report/info/'+accountId;
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

    </script>

@endsection

