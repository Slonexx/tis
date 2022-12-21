
@extends('widget.widget')

@section('content')


    <div class="row gradient rounded p-2">
        <div class="col-6">
            <div class="mx-2"> <img src="https://tisuchet.kz/images/Tis%20logo.svg" width="90%"   alt=""> </div>
        </div>
        <div class="col-2 ">

        </div>
    </div>

    <div class="row mt-4 rounded bg-white">
        <div class="col-1"></div>
        <div class="col-10">
            <div class="text-center">
                <div class="p-3 mt-1 bg-danger text-white">
                    <span id="errorMessage" class="s-min-10">

                    </span>
                    <span> <i class="fa-solid fa-ban "></i></span>
                </div>
            </div>
        </div>
    </div>



@endsection

<script>
    window.document.getElementById('errorMessage').innerText = '{{$message}}'
</script>

<style>
    .s-min-10 {
        font-size: 10px;
    }
</style>
