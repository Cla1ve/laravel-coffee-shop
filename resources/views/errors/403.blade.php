@extends('layouts.app')

@section('title', 'Доступ закрыт')

@section('content')
<div class="text-center py-5">
    <h1 class="display-5">403</h1>
    <p class="lead">У вас нет доступа к этой странице.</p>
    <a href="{{ route('home') }}" class="btn btn-primary">На главную</a>
</div>
@endsection
