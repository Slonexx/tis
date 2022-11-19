
@extends('widget.widget')

@section('content')

    <script>
        const hostWindow = window.parent;
        let Global_messageId = 0;
        let Global_accountId = "{{$accountId}}";


        window.addEventListener("message", function(event) {
            const receivedMessage = event.data;
                    var sendingMessage = {
                        name: "OpenFeedback",
                        correlationId: receivedMessage.messageId
                    };
                    hostWindow.postMessage(sendingMessage, '*');
        });


    </script>


    <div class="row gradient rounded p-2">
        <div class="col-6">
            <div class="mx-2"> <img src="https://tisuchet.kz/images/Tis%20logo.svg" width="90%"   alt=""> </div>
        </div>
        <div class="col-2 ">

        </div>
    </div>

    <div id="messageGoodAlert" class=" mt-1 mx-3 p-2 alert alert-success text-center " style="display: none; font-size: 12px; margin-bottom: 5px !important;">    </div>

    <div id="workerAccess_no" class="row mt-2 rounded bg-white" style="display: none">
        <div class="col-1"></div>
        <div class="col-10">
            <div class="text-center">
                <div class="p-3 mb-2 bg-danger text-white">
                        <span class="s-min-10">
                        У вас нет доступа к данному виджету, сообщите администратору, чтоб он вам предоставил доступ
                        <i class="fa-solid fa-ban "></i>
                    </span>
                </div>
            </div>
        </div>
    </div>


@endsection

<style>
    .s-min-10 {
        font-size: 12px;
    }
</style>

