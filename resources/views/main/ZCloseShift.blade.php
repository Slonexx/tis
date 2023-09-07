@extends('layout')
@section('item', 'link_6')
@section('content')

    <div class="p-4 mx-1 mt-1 bg-white rounded py-3">
        @include('div.TopServicePartner')<script>NAME_HEADER_TOP_SERVICE("Смена → Z-отчёт ")</script>
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
        <form class="mt-3" action="/operation/close_z_shift/{{ $accountId }}?isAdmin={{ $isAdmin }}" method="post">
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


            <hr>
            <div class='d-flex justify-content-end text-black btnP' >
                <button class="btn btn-outline-dark textHover" data-bs-toggle="modal" data-bs-target="#modal">
                    <i class="fa-solid fa-arrow-down-to-arc"></i> Закрыть смену</button>
            </div>
        </form>
    </div>
    <!-- Modal -->
    <div class="modal fade " id="html" tabindex="-1"  role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle">Z-отчёт</h5>
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
            let url = 'https://dev.smarttis.kz/operation/close_z_shift/print';
            let final = url + '/' + accountId;
            window.open(final)
        }


    </script>

@endsection

