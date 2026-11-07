@extends('layouts.app')

@section('title', 'Главная')

@section('content')
<div class="hero p-4 p-md-5 mb-4">
    <h1 class="display-6">Кофе и чай с доставкой по Москве</h1>
    <p class="mb-3">Свежая обжарка, листовой чай и посуда для заваривания. Заказ можно оформить за пару минут.</p>
    <a href="{{ route('catalog') }}" class="btn btn-light">Перейти в каталог</a>
</div>

<h2 class="h4 mb-3">Категории</h2>
<div class="row g-3 mb-4">
    @foreach($categories as $c)
        <div class="col-6 col-md-3">
            <a href="{{ route('catalog', ['category' => $c->slug]) }}" class="category-tile d-block p-3 text-center">
                <div class="fw-bold">{{ $c->name }}</div>
                <div class="small text-muted">{{ $c->products_count }} шт.</div>
            </a>
        </div>
    @endforeach
</div>

<h2 class="h4 mb-3">Новинки</h2>
<div class="row g-3">
    @foreach($products as $product)
        <div class="col-6 col-md-4 col-lg-3">
            @include('partials.product-card')
        </div>
    @endforeach
</div>
@endsection
