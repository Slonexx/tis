<div class="box">
    <div class=" columns gradient p-1 " style="margin-top: -1rem">
        <div class="column" style="margin-top: 0.2rem"> <span id="HEAD_TOP_SERVICE" class="text-black" style="font-size: 1.4rem">  </span> </div>
        <div class="column is-3 text-center">
            <img src="{{  ( Config::get("global") )['url'].'fonts/2logoHead.png' }}" width="100%" alt="logo">
        </div>
    </div>
</div>
    <script>
        let name_head_view = '@yield('name_head')' ?? "Настройки"
        NAME_HEADER_TOP_SERVICE(name_head_view)

        function NAME_HEADER_TOP_SERVICE(name){
            window.document.getElementById('HEAD_TOP_SERVICE').innerText = name
        }
    </script>
{{-- Настройки &#8594; настройки интеграции --}}
