
    <div id="danger" class="mt-2 alert alert-danger alert-dismissible fade show in text-center " style="display: none"> <span id="message_danger"></span>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>

    <div id="success" class="mt-2 alert alert-success alert-dismissible fade show in text-center " style="display: none"> <span id="message_success"></span>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>

    <div id="primary" class="mt-2 alert alert-success alert-primary fade show in text-center " style="display: none"> <span id="message_primary"></span>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>


    <div id="secondary" class="mt-2 alert alert-success alert-dismissible fade show in text-center " style="display: none"> <span id="message_secondary"></span>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>

    <div id="primary" class="mt-2 alert alert-success alert-primary fade show in text-center " style="display: none"> <span id="message_primary"></span>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>

    <div id="warning" class="mt-2 alert alert-success alert-primary fade show in text-center " style="display: none"> <span id="message_warning"></span>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>

    <div id="info" class="mt-2 alert alert-success alert-primary fade show in text-center " style="display: none"> <span id="message_info"></span>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>

    <script>
        function alertViewByColorName(ColorName, message){
            window.document.getElementById(ColorName).style.display = "block"
            window.document.getElementById("message_"+ColorName).innerText = message
        }
    </script>
