@extends('setting.v2.initSetting')
@section('name_head', 'Обновление')
@section('child')

    <div class="box p-1">
        <div class="tabs is-right gradient_a">
            <ui>
                <li><a href="/Setting/initSetting/{{ $accountId }}?isAdmin={{ $isAdmin }}">Назад</a></li>
            </ui>
            <ul>
                <li><a  href="/Setting/createSetting/{{ $accountId }}?isAdmin={{ $isAdmin }}">Подключение</a></li>
                <li><a class="is-active" href="/Setting/Update/kassa/{{ $accountId }}?isAdmin={{ $isAdmin }}"> Касса </a></li>
                <li><a> Документ </a></li>
                <li><a> Доступ </a></li>
            </ul>
        </div>
    </div>

    <div id="html" class="box">
        <form action="/Setting/Update/kassa/{{ $uid }}/{{ $accountId }}?isAdmin={{ $isAdmin }}" method="post">
            @csrf <!-- {{ csrf_field() }} -->

            <div class="columns field">
                <div class="column is-3 mt-1" > Касса  </div>
                <div class="column">
                    <div class="select w-50 is-small is-link">
                        <select id="kassaId" class="w-100">
                        </select>
                    </div>
                </div>
                <div class="mt-1 tag is-medium is-Light">
                    <span class="icon has-text-info"> <i class="fas fa-info-circle"></i> </span>
                    <span>Выберите кассу</span>
                </div>
                <div class="mt-1 tag is-medium is-Light">
                    <span class="icon has-text-info"> <i class="fas fa-info-circle"></i> </span>
                    <span>Выберите кассу</span>
                </div>
            </div>

            <button class="button is-outlined gradient_focus"> сохранить </button>
        </form>

    </div>


    <script>
        let accountId = '{{$accountId}}';
        let model = @json($model);
        $(document).ready(function () { leadingKassa() });


    </script>

@endsection

