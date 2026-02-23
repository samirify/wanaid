@extends('core::layouts.email-master')

@section('content')
<p>Your code</p>

<h1>{{$code}}</h1>

<p>Your password reset link</p>
<a href="{{ env('SFY_CMS_ROOT_URL') }}/update-password/{{ $token }}">Click here</a>
@endsection