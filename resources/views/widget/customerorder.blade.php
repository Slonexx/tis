
@extends('widget.widget')

@section('content')

    <script>
        const hostWindow = window.parent;
        let Global_messageId = 0;
        let Global_accountId = "{{$accountId}}";
        let Global_object_Id;
        let entity_type = 'customerorder';

        window.addEventListener("message", function(event) {
            const receivedMessage = event.data;
            $('#workerAccess_yes').show();
            //workerAccess();
            if (receivedMessage.name === 'Open') {
                Global_object_Id = receivedMessage.objectId;
                let params = {
                    accountId: Global_accountId,
                    entity_type: entity_type,
                    objectId: Global_object_Id,
                };
                let url = 'https://smarttis.kz/widget/InfoAttributes/';
                let final = url + formatParams(params);

                const xmlHttpRequest = new XMLHttpRequest();
                xmlHttpRequest.addEventListener("load", function() {
                    var json = JSON.parse(this.responseText);
                    console.log(json.ticket_id);
                    let btnF = window.document.getElementById('btnF')
                    if (json.ticket_id == null){
                        btnF.innerText = 'Фискализация';
                    } else btnF.innerText = 'Действие с чеком';

                    var sendingMessage = {
                        name: "OpenFeedback",
                        correlationId: receivedMessage.messageId
                    };
                    hostWindow.postMessage(sendingMessage, '*');
                });
                xmlHttpRequest.open("GET", final);
                xmlHttpRequest.send();
            }

        });

        function formatParams(params) {
            return "?" + Object
                .keys(params)
                .map(function (key) {
                    return key + "=" + encodeURIComponent(params[key])
                })
                .join("&")
        }

        function fiscalization(){

            Global_messageId++;
            var sendingMessage = {
                name: "ShowPopupRequest",
                messageId: Global_messageId,
                popupName: "fiscalizationPopup",
                popupParameters: {
                    object_Id:Global_object_Id,
                    accountId:Global_accountId,
                    entity_type:entity_type,
                },
            };
            logSendingMessage(sendingMessage);
            hostWindow.postMessage(sendingMessage, '*');
        }


        function logSendingMessage(msg) {
            var messageAsString = JSON.stringify(msg);
            console.log("← Sending" + " message: " + messageAsString);
        }


    </script>


        <div class="row gradient rounded p-2">
            <div class="col-6">
                <div class="mx-2"> <img src="https://test.ukassa.kz/_nuxt/img/d2b49fb.svg" width="80%"   alt=""> </div>
            </div>
            <div class="col-2 ">

            </div>
        </div>
        <div id="workerAccess_yes" class="row mt-2 rounded bg-white" style="display:none;">
            <div class="col-1"></div>
            <button id="btnF" onclick="fiscalization()" class="col-10 btn btn-warning text-black rounded-pill">  </button>
        </div>

       {{-- <div id="workerAccess_no" class="row mt-2 rounded bg-white" style="display: none">
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
        </div>--}}


@endsection

    <style>
        .myButton {
            box-shadow: 0px 4px 5px 0px #5d5d5d !important;
            background-image: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); !important;
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
            font-size: 12px;
        }
    </style>

