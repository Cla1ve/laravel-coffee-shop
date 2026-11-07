@extends('layouts.app')

@section('title', 'Каталог')

@section('content')
<h1 class="h3 mb-3">Каталог</h1>

<div class="row">
    <div class="col-lg-3 mb-3">
        <form method="get" action="{{ route('catalog') }}" class="card card-body">
            <div class="mb-3">
                <label class="form-label" for="q">Поиск</label>
                <input type="text" class="form-control" id="q" name="q" value="{{ request('q') }}" maxlength="100">
            </div>
            <div class="mb-3">
                <label class="form-label" for="category">Категория</label>
                <select class="form-select" id="category" name="category">
                    <option value="">Все</option>
                    @foreach($categories as $c)
                        <option value="{{ $c->slug }}" @selected(request('category') === $c->slug)>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="row mb-3">
                <div class="col-6">
                    <label class="form-label" for="min">Цена от</label>
                    <input type="number" class="form-control" id="min" name="min" value="{{ request('min') }}" min="0">
                </div>
                <div class="col-6">
                    <label class="form-label" for="max">до</label>
                    <input type="number" class="form-control" id="max" name="max" value="{{ request('max') }}" min="0">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label" for="sort">Сортировка</label>
                <select class="form-select" id="sort" name="sort">
                    <option value="new" @selected(request('sort') === 'new')>Сначала новые</option>
                    <option value="price_asc" @selected(request('sort') === 'price_asc')>Сначала дешёвые</option>
                    <option value="price_desc" @selected(request('sort') === 'price_desc')>Сначала дорогие</option>
                    <option value="name" @selected(request('sort') === 'name')>По названию</option>
                </select>
            </div>
            <button class="btn btn-primary w-100" type="submit">Показать</button>
            <a href="{{ route('catalog') }}" class="btn btn-link w-100 mt-1">Сбросить</a>
        </form>
    </div>

    <div class="col-lg-9">
        @if($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif

        <div class="text-muted mb-2">Найдено товаров: {{ $products->total() }}</div>

        @if($products->count())
            <div class="row g-3">
                @foreach($products as $product)
                    <div class="col-6 col-md-4">
                        @include('partials.product-card')
                    </div>
                @endforeach
            </div>
            <div class="mt-4">{{ $products->links() }}</div>
        @else
            <div class="alert alert-secondary">По вашему запросу ничего не найдено.</div>
        @endif
    </div>
</div>
@endsection
