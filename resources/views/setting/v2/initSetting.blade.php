@extends('layout')
@section('item', 'link_2')
@section('name_head', 'Настройки → подключение')
@section('content')

    @include('site_header.header')
    @include('notification.notification')


    @yield('child')

@endsection
