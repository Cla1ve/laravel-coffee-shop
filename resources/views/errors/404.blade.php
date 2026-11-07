@extends('layouts.app')

@section('title', 'Страница не найдена')

@section('content')
<div class="text-center py-5">
    <h1 class="display-5">404</h1>
    <p class="lead">Такой страницы нет. Возможно, товар скрыт или удалён.</p>
    <a href="{{ route('catalog') }}" class="btn btn-primary">Перейти в каталог</a>
</div>
@endsection
