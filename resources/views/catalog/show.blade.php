@extends('layouts.app')

@section('title', $product->name)

@section('content')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('catalog') }}">Каталог</a></li>
        <li class="breadcrumb-item"><a href="{{ route('catalog', ['category' => $product->category->slug]) }}">{{ $product->category->name }}</a></li>
        <li class="breadcrumb-item active">{{ $product->name }}</li>
    </ol>
</nav>

<div class="row g-4">
    <div class="col-md-5">
        <img src="{{ $product->image_url }}" class="img-fluid rounded" alt="{{ $product->name }}">
    </div>
    <div class="col-md-7">
        <h1 class="h3">{{ $product->name }}</h1>
        <div class="mb-2">
            @if($rating)
                <span class="rating">{{ str_repeat('★', (int) round($rating)) }}{{ str_repeat('☆', 5 - (int) round($rating)) }}</span>
                <span class="text-muted small">{{ number_format($rating, 1) }} ({{ $product->reviews->count() }})</span>
            @else
                <span class="text-muted small">Отзывов пока нет</span>
            @endif
        </div>
        <p>{{ $product->description }}</p>
        <div class="price fs-3 mb-3">{{ number_format($product->price, 0, ',', ' ') }} ₽</div>

        @if($product->stock > 0)
            <p class="text-muted">В наличии: {{ $product->stock }} шт.</p>
            <form action="{{ route('cart.add', $product) }}" method="post" class="d-flex gap-2" style="max-width: 260px;">
                @csrf
                <input type="number" name="qty" value="1" min="1" max="{{ $product->stock }}" class="form-control" aria-label="Количество">
                <button class="btn btn-primary text-nowrap" type="submit">В корзину</button>
            </form>
        @else
            <p class="text-danger">Нет в наличии</p>
        @endif
    </div>
</div>

<hr class="my-4">

<h2 class="h4">Отзывы</h2>

@forelse($product->reviews as $review)
    <div class="card mb-2">
        <div class="card-body">
            <div class="d-flex justify-content-between">
                <strong>{{ $review->user->name }}</strong>
                <span class="rating">{{ str_repeat('★', $review->rating) }}</span>
            </div>
            {{-- текст выводится через {{ }}, поэтому теги экранируются --}}
            <p class="mb-1">{{ $review->text }}</p>
            <div class="small text-muted">{{ $review->created_at->format('d.m.Y') }}</div>
            @auth
                @if($review->user_id === auth()->id() || auth()->user()->isAdmin())
                    <form action="{{ route('reviews.destroy', $review) }}" method="post" class="mt-1">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger" type="submit">Удалить</button>
                    </form>
                @endif
            @endauth
        </div>
    </div>
@empty
    <p class="text-muted">Пока никто не оставил отзыв.</p>
@endforelse

@auth
    <h3 class="h5 mt-4">{{ $myReview ? 'Изменить свой отзыв' : 'Оставить отзыв' }}</h3>
    <form action="{{ route('reviews.store', $product) }}" method="post" style="max-width: 600px;">
        @csrf
        <div class="mb-2">
            <label class="form-label" for="rating">Оценка</label>
            <select class="form-select @error('rating') is-invalid @enderror" id="rating" name="rating">
                @for($i = 5; $i >= 1; $i--)
                    <option value="{{ $i }}" @selected(old('rating', $myReview->rating ?? 5) == $i)>{{ $i }}</option>
                @endfor
            </select>
            @error('rating')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-2">
            <label class="form-label" for="text">Текст</label>
            <textarea class="form-control @error('text') is-invalid @enderror" id="text" name="text" rows="3" maxlength="1000">{{ old('text', $myReview->text ?? '') }}</textarea>
            @error('text')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <button class="btn btn-primary" type="submit">Сохранить отзыв</button>
    </form>
@else
    <p class="mt-3"><a href="{{ route('login') }}">Войдите</a>, чтобы оставить отзыв.</p>
@endauth
@endsection
