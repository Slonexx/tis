@extends('setting.v2.initSetting')
@section('name_head', 'Кассы')
@section('child')

    <div class="box p-1 ">

        <div class="tabs is-right gradient_a">
            <ui>
                <li><a href="/Setting/createSetting/{{ $accountId }}?isAdmin={{ $isAdmin }}">Добавить</a></li>
            </ui>
            <ul>
                <li><a href="/Setting/initSetting/{{ $accountId }}?isAdmin={{ $isAdmin }}">Автоматизация</a></li>
            </ul>
        </div>


    </div>

    <div id="html" class="box">
        <div class="box mb-3 columns p-0 has-background-info rounded text-white">
            <div class="column is-1"> &nbsp; # </div>
            <div class="column is-3"> Компания </div>
            <div class="column is-3"> Название кассы </div>
            <div class="column is-3"> Заводской номер </div>
            <div class="d-flex justify-content-end column is-2"> Активирована </div>
        </div>

        <div id="mainCreate">

        </div>

        <br>

    </div>


    <script>
        let accountId = '{{$accountId}}';
        let list = @json($info_list);
        $(document).ready(function () { leading() });

        function leading(){
            $('#mainCreate').text('');
            if (list != []) {
                list.forEach(function (item, index){
                    let kassaName = 'Не пройдена настройка'
                    let factory = ''
                    let isActivity = ''

                    if (item.kassaName != null) kassaName = item.kassaName
                    if (item.factory != null) factory = item.factory
                    if (item.isActivity != null) if (item.isActivity == 1) isActivity = '✓'

                    $('#mainCreate').append(
                        `<a href="/Setting/Update/connect/${item.id}/${accountId}?isAdmin=${isAdmin}" class="mt-0 box columns addStyleColumns">
                            <div class="column is-1">${index}</div>
                            <div class="column is-3"> ${item.companyName} </div>
                            <div class="column is-3"> ${kassaName} </div>
                            <div class="column is-3"> ${factory} </div>
                            <div class="d-flex justify-content-end column is-2"> ${isActivity}   </div>

                        </a>`);
                })
            }


        }

    </script>

@endsection




