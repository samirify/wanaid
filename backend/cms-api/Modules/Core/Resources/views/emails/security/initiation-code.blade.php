@extends('core::layouts.email-master')

@section('content')
    <p>Your verification code</p>
    <h1>{{$code}}</h1>
    <p>Your username</p>
    <h1>{{$username}}</h1>
@endsection
