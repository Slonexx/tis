@extends('layout')
@section('item', 'link_3')
@section('content')

    <div class="p-4 mx-1 mt-1 bg-white rounded py-3">

        @include('div.TopServicePartner') <script> NAME_HEADER_TOP_SERVICE("Настройки → касса") </script>

        @isset($message)

            <div class="mt-2 alert alert-danger alert-dismissible fade show in text-center "> {{ $message }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>

        @endisset

        <form class="mt-3" action="/Setting/Kassa/{{ $accountId }}?isAdmin={{ $isAdmin }}" method="post">
        @csrf <!-- {{ csrf_field() }} -->
            <div class="mb-3 row">
                <label for="token" class="col-3 col-form-label"> Выберите кассу </label>
                <div class="col-6">
                    <select id="idKassa" name="idKassa" class="form-select text-black" onchange="get_is_activated()">
                        @foreach( $kassa as $item)
                            <option value="{{ $item->id }}"> {{ $item->name }} </option>
                        @endforeach
                    </select>
                </div>
                <div id="is_activated" class="col-3 p-1 col-form-label text-center rounded"></div>
            </div>

            <div class="mb-3 row">
                <label for="token" class="col-3 col-form-label"> Выберите Отдел/Секцию </label>
                <div class="col-9">
                    <select id="idDepartment" name="idDepartment" class="form-select text-black" onchange="getDepartment()">
                        @foreach( $department as $item)
                            <option value="{{ $item->id }}"> {{ $item->name_ru }} </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <hr>
            <div class='d-flex justify-content-end text-black btnP' >
                <button class="btn btn-outline-dark textHover" data-bs-toggle="modal" data-bs-target="#modal">
                    <i class="fa-solid fa-arrow-down-to-arc"></i> Сохранить </button>
            </div>
        </form>
    </div>

    <script>
        //let url = 'https://tus/';
        let url = 'https://main.smarttis.kz/';
        let accountId = '{{ $accountId }}'
        let kassa = @json($kassa);
        let kassaValue = "{{$kassaValue}}";

        if (kassaValue != undefined || kassaValue != ''){
            window.document.getElementById('idKassa').value = kassaValue
        }

        get_is_activated()

        function get_is_activated(){
            var select = window.document.getElementById('idKassa')
            var option = select.options[select.selectedIndex];
            var kassaID = option.value;


            let index = 0;
            while (index < kassa.length){
                if (kassa[index].id == kassaID) {
                    if (kassa[index].is_activated == true ){
                        window.document.getElementById('is_activated').innerText = "активирована"
                        window.document.getElementById('is_activated').classList.add('bg-success')
                        window.document.getElementById('is_activated').classList.add('text-white')
                    } else {
                        window.document.getElementById('is_activated').classList.add('bg-danger')
                        window.document.getElementById('is_activated').innerText = "не активирована"
                    }


                }
                index++ }
        }

        function getDepartment(){

        }

    </script>

@endsection

