@extends('layout')
@section('item', 'link_5')
@section('content')

    <script>

    </script>
    <div class=" main-container content-container">
        <div class="content p-4 mt-2 bg-white text-Black rounded">
            @include('div.TopServicePartner')
            @include('div.alert')
            @isset($message)
                <script>alertViewByColorName("success", "{{ $message }}")</script>
            @endisset


            <form action="/Setting/Worker/{{$accountId}}?isAdmin={{ $isAdmin }}" method="post" class="mt-3">
                @csrf <!-- {{ csrf_field() }} -->
                <div id="Workers">
                    <div class=" mb-4"> <h5> <i class="fa-solid fa-eye text-success"></i> Доступ к фискализации </h5></div>
                    <div class="mx-1 mb-3 row mt-2 bg-warning p-1 rounded">
                        <div class="col-1 mx-3"> № </div>
                        <div class="col-5"> Фамилия сотрудника </div>
                        <div class="col-5 mx-2"> Выберите доступ </div>
                    </div>
                    <div id="row" class="row"></div>
                </div>

                <hr class="href_padding">
                <div class='d-flex justify-content-end text-black btnP' >
                    <button class="btn btn-outline-dark textHover" data-bs-toggle="modal" data-bs-target="#modal"> Сохранить </button>
                </div>

            </form>
        </div>
    </div>

    <script>
        NAME_HEADER_TOP_SERVICE("Настройки → доступа")

        let row = @json($employee);
        let workers = @json($workers);

        for (let index = 0; row.length > index; index++){
            let value
            if (workers != ""){
                if (workers[row[index].id] != undefined) {
                    if (workers[row[index].id].access == 1) {
                        value = '<div class="mx-1 row mt-2"> <div class="col-1 mx-3 mt-1">'+ index +'</div> <div class="col-5 mt-1"> ' + row[index].fullName + ' </div> <div class="col-5"> <select id="'+row[index].id+'" name="'+row[index].id+'" class="form-select text-black"> <option value="0">Запретить доступ </option> <option selected value="1">Предоставить доступ</option> </select> </div> </div>'
                    } else  {
                        value = '<div class="mx-1 row mt-2"> <div class="col-1 mx-3 mt-1">'+ index +'</div> <div class="col-5 mt-1"> ' + row[index].fullName + ' </div> <div class="col-5"> <select id="'+row[index].id+'" name="'+row[index].id+'" class="form-select text-black"> <option selected value="0">Запретить доступ </option> <option value="1">Предоставить доступ</option> </select> </div> </div>'
                    }
                } else  value = '<div class="mx-1 row mt-2"> <div class="col-1 mx-3 mt-1">'+ index +'</div> <div class="col-5 mt-1"> ' + row[index].fullName + ' </div> <div class="col-5"> <select id="'+row[index].id+'" name="'+row[index].id+'" class="form-select text-black"> <option selected value="0">Запретить доступ </option> <option value="1">Предоставить доступ</option> </select> </div> </div>'
            } else value = '<div class="mx-1 row mt-2"> <div class="col-1 mx-3 mt-1">'+ index +'</div> <div class="col-5 mt-1"> ' + row[index].fullName + ' </div> <div class="col-5"> <select id="'+row[index].id+'" name="'+row[index].id+'" class="form-select text-black"> <option selected value="0">Запретить доступ </option> <option value="1">Предоставить доступ</option> </select> </div> </div>'


            $('#row').append(value)
        }



    </script>

@endsection



