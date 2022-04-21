@extends('Layout::user')
@section('content')
  <a href="/user/booking-history">< Вернуться к бронированиям</a>
    <iframe width="100%" style="height: calc(100vh - 100px);border:1px solid; border-color:#000;margin:0px!important;" src="{{route(config('chatify.path'),['user_id'=>request('user_id')])}}" frameborder="0"></iframe>
@endsection