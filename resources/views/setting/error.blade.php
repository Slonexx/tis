
@extends('layout')

@section('content')

    <div class="p-4 mx-1 mt-1 bg-white rounded py-3">
        @include('div.TopServicePartner')
        <div class="mt-2 alert alert-danger text-center"> {{$message}} </div>
    </div>


    <script>
        NAME_HEADER_TOP_SERVICE("Ошибка")
    </script>
@endsection



