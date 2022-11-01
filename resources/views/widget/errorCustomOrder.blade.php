
@extends('widget.widget')

@section('content')


    <div class="row gradient rounded p-2">
        <div class="col-10">
            <div class="mx-2"> <img src="https://app.rekassa.kz/static/logo.png" width="35" height="35"  alt="">
                <span class="text-white"> re:Kassa </span>
            </div>
        </div>
        <div class="col-2 ">
            <button type="submit" onclick="" class="myButton btn "> <i class="fa-solid fa-arrow-rotate-right"></i> </button>
        </div>
    </div>
    <div class="row mt-4 rounded bg-white">
        <div class="col-1"></div>
        <div class="col-10">
            <div class="text-center">
                <div class="p-3 mb-2 bg-danger text-white">
                    <span class="s-min-10">
                        Настройки фискализации не были пройдены
                        <i class="fa-solid fa-ban "></i>
                    </span>
                </div>
            </div>
        </div>
    </div>



@endsection

<style>
    .myButton {
        box-shadow: 0px 4px 5px 0px #5d5d5d !important;
        background-image: radial-gradient( circle farthest-corner at 10% 20%,  rgba(14,174,87,1) 0%, rgba(12,116,117,1) 90% ) !important;
        color: white !important;
        border-radius:50px !important;
        display:inline-block !important;
        cursor:pointer !important;
        padding:5px 5px !important;
        text-decoration:none !important;
    }
    .myButton:hover {
        filter: invert(1);

        color: #111111 !important;
    }
    .myButton:active {
        position: relative !important;
        top: 1px !important;
    }
    .s-min-10 {
        font-size: 10px;
    }
</style>

