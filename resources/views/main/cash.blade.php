@extends('layout')
@section('item', 'link_6')
@section('content')

    <div class="p-4 mx-1 mt-1 bg-white rounded py-3">

        <div class="row gradient rounded p-2 pb-3">
            <div class="col-2"><img src="https://tisuchet.kz/images/Tis%20logo.svg" width="90%" height="90%"  alt=""></div>
            <div class="col-8" style="margin-top: 0.5rem"> <span class="text-black" style="font-size: 18px"> Смена &#8594; <span id="cash"> Внесение/Изъятие</span> </span></div>
        </div>

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
        <form class="mt-3" action="/operation/cash_operation/{{ $accountId }}?isAdmin={{ $isAdmin }}" method="post">
        @csrf <!-- {{ csrf_field() }} -->

            <div class="row">
                <label for="idKassa" class="col-3 col-form-label"> Выберите кассу </label>
                <div class="col-9">
                    <select id="idKassa" name="idKassa" class="form-select text-black">
                        @foreach( $kassa as $item)
                            <option value="{{ $item->id }}"> {{ $item->name }} </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mt-1 row">
                <label for="operations" class="col-3 col-form-label"> Выберите операцию </label>
                <div class="col-9">
                    <select id="operations" name="operations" class="form-select text-black" onchange="valueCash(this.value)">
                            <option value="0"> Внесение </option>
                            <option value="1"> Изъятие </option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-3"></div>
                <div class="col-9">
                    <div class="input-group mt-1">
                        <div class="input-group-prepend">
                            <span id="inputGroupText" class="input-group-text text-white bg-success">Введите сумму наличных</span>
                        </div>
                        <input id="inputSum" name="inputSum" onkeypress="return isNumber(event)" type="text" class="form-control" aria-label="" required>
                        <div class="input-group-append">
                            <span class="input-group-text">.00</span>
                        </div>
                    </div>

                </div>
            </div>


            <hr>
            <div class='d-flex justify-content-end text-black btnP' >
                <button class="btn btn-outline-dark textHover" data-bs-toggle="modal" data-bs-target="#modal">
                    <i class="fa-solid fa-arrow-down-to-arc"></i> Провести операцию </button>
            </div>
        </form>
    </div>

    <script>
        valueCash(window.document.getElementById('operations').value)

        function valueCash(value){
            if (value == 0 ) {
                window.document.getElementById('cash').innerText = 'Внесение'
                document.getElementById('inputGroupText').classList.add('bg-success')
                document.getElementById('inputGroupText').classList.remove('bg-danger')
            }
            if (value == 1) {
                window.document.getElementById('cash').innerText = 'Изъятие'
                document.getElementById('inputGroupText').classList.add('bg-danger')
                document.getElementById('inputGroupText').classList.remove('bg-success')
            }

        }

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

    </script>

@endsection

