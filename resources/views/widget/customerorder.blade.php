
@extends('widget.widget')

@section('content')

    <script>
        const hostWindow = window.parent
        let Global_messageId = 0
        let Global_accountId = "{{$accountId}}"
        let Global_object_Id
        let entity_type = 'customerorder'

        window.addEventListener("message", function(event) {

            window.document.getElementById('messageGoodAlert').style.display = 'none'

            const receivedMessage = event.data;
            if (receivedMessage.name === 'Open') {

                Global_object_Id = receivedMessage.objectId;
                let data = {
                    accountId: Global_accountId,
                    entity_type: entity_type,
                    objectId: Global_object_Id,
                };

                let settings = ajax_settings('https://dev.smarttis.kz/widget/InfoAttributes/', 'GET', data)
                console.log('widget setting attributes: ↓')
                console.log(settings)

                $.ajax(settings).done(function (response) {
                    console.log("https://dev.smarttis.kz/widget/InfoAttributes/" + ' response ↓ ')
                    console.log(settings)

                    let sendingMessage = {
                        name: "OpenFeedback",
                        correlationId: receivedMessage.messageId
                    };
                    hostWindow.postMessage(sendingMessage, '*');


                    let btnF = window.document.getElementById('btnF')
                    let TIS_search = window.document.getElementById('TIS_search')

                    if (response.ticket_id == null){
                        btnF.innerText = 'Фискализация';
                        window.document.getElementById('messageGoodAlert').style.display = 'none'
                        window.document.getElementById("messageGoodAlert").innerText = ""
                        TIS_search.style.display = 'none'
                    } else {
                        btnF.innerText = 'Действие с чеком';
                        window.document.getElementById('messageGoodAlert').style.display = 'block'
                        window.document.getElementById("messageGoodAlert").innerText = "Чек уже создан. Фискальный номер:  " + response.ticket_id
                        TIS_search.style.display = 'block'
                    }

                });
            }
            if(receivedMessage.popupResolution === "closedByUser" && receivedMessage.name === 'ShowPopupResponse'){
                window.location.reload()
                location.reload()
            }
        });

        function fiscalization(){

            Global_messageId++;
            let sendingMessage = {
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

    </script>


        <div class="row gradient rounded p-2">
            <div class="col-6">
                <div class="mx-2"> <img src="https://tisuchet.kz/images/Tis%20logo.svg" width="90%"   alt=""> </div>
            </div>
            <div class="col-2 ">

            </div>
        </div>

        <div id="messageGoodAlert" class=" mt-1 mx-3 p-2 alert alert-success text-center " style="display: none; font-size: 12px; margin-bottom: 5px !important;">    </div>



        <div  class="mt-1 mx-4 text-center">
            <div class="row">
                <div class="col-6">
                    <button id="btnF" onclick="fiscalization()" class="btn p-1 btn-warning text-white rounded-pill" style="font-size: 14px">  </button>
                </div>
                <div class="col-6">
                    <button id="TIS_search" onclick="getSearchToTIS()" class="btn p-1 btn-info text-white rounded-pill" style="font-size: 14px"> Посмотреть в кассе </button>
                </div>
            </div>
        </div>



    <script>
        function logSendingMessage(msg) {
            let messageAsString = JSON.stringify(msg);
            console.log("← Sending" + " message: " + messageAsString);
        }
        function getSearchToTIS(){
            window.open('https://test.ukassa.kz/kassa/report/search/')
        }


        function ajax_settings(url, method, data){
            return {
                 "url": url,
                 "method": "GET",
                 "timeout": 0,
                 "headers": {"Content-Type": "application/json",},
                 "data": data,
             }
        }

    </script>

@endsection
