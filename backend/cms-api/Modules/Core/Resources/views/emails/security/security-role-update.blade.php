@extends('core::layouts.email-master')

@section('content')
<p>Your security roles have been updated!</p>

<h1>Previous Roles</h1>

<ul>
    @foreach($roles['previous'] as $role)
    <li>{{ $role['name'] }}</li>
    @endforeach
</ul>

<h1>Current Roles</h1>

<ul>
    @foreach($roles['current'] as $role)
    <li>{{ $role['name'] }}</li>
    @endforeach
</ul>

@endsection