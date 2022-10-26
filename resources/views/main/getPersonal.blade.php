<!doctype html>
<html lang="en">
@include('head')
<body style="background-color:#dcdcdc;">



<div class="p-4 mx-1 mt-1 bg-white rounded py-3">

    <div class="row gradient rounded p-2 pb-3">
        <div class="col-2"><img src="https://test.ukassa.kz/_nuxt/img/d2b49fb.svg" width="90%" height="90%"  alt=""></div>
        <div class="col-8" style="margin-top: 0.5rem"> <span class="text-black" style="font-size: 18px"> Информация об персонале </span></div>
        <div class="col-2 mt-2"></div>
    </div>

    <div class="mt-2 text-center row" style="font-size: 16px">
        <div class="col-3 mx-2 bg-warning rounded">Идентификатор</div>
        <div class="col-3 mx-2 bg-warning rounded">Имя</div>
        <div class="col-3 mx-2 bg-warning rounded">Почта</div>
        <div class="col-2 mx-2 bg-warning rounded">Статус</div>
        <hr class="mt-1">
    </div>
    <div class="row text-black" style="font-size: 14px">
        @foreach( $Personal as $item )
            <div class="col-3 mx-2 rounded"> {{$item['accountId']}} </div>
            <div class="col-3 text-center mx-2 rounded"> {{$item['name']}} </div>
            <div class="col-3 text-center mx-2 rounded"> {{$item['email']}} </div>
            @if( $item['status'] == 'активированный' )
                <div class="col-2 text-white text-center bg-success mx-2 rounded"> {{$item['status']}} </div>
            @else
                <div class="col-2 text-center bg-secondary mx-2 rounded"> {{$item['status']}} </div>
            @endif
            <hr class="mt-1">
        @endforeach
    </div>


</div>





</body>
</html>

@include('style')
@include('script')





