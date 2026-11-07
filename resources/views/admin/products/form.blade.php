@extends('admin.layout')

@section('title', $product->exists ? 'Изменить товар' : 'Новый товар')

@section('admin')
<h1 class="h3 mb-3">{{ $product->exists ? 'Изменить товар' : 'Новый товар' }}</h1>

<form method="post" enctype="multipart/form-data" class="card card-body"
      action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}">
    @csrf
    @if($product->exists) @method('PUT') @endif

    <div class="mb-3">
        <label class="form-label" for="name">Название</label>
        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $product->name) }}" required>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="mb-3">
        <label class="form-label" for="category_id">Категория</label>
        <select class="form-select @error('category_id') is-invalid @enderror" id="category_id" name="category_id" required>
            @foreach($categories as $c)
                <option value="{{ $c->id }}" @selected(old('category_id', $product->category_id) == $c->id)>{{ $c->name }}</option>
            @endforeach
        </select>
        @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="mb-3">
        <label class="form-label" for="description">Описание</label>
        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="4" required>{{ old('description', $product->description) }}</textarea>
        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label" for="price">Цена, ₽</label>
            <input type="number" step="0.01" class="form-control @error('price') is-invalid @enderror" id="price" name="price" value="{{ old('price', $product->price) }}" required>
            @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label" for="stock">Остаток, шт.</label>
            <input type="number" class="form-control @error('stock') is-invalid @enderror" id="stock" name="stock" value="{{ old('stock', $product->stock ?? 0) }}" required>
            @error('stock')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
    <div class="mb-3">
        <label class="form-label" for="image">Фото (jpg, png, webp до 2 МБ)</label>
        <input type="file" class="form-control @error('image') is-invalid @enderror" id="image" name="image" accept="image/*">
        @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
        @if($product->image)<img src="{{ $product->image_url }}" alt="" width="100" class="mt-2 rounded">@endif
    </div>
    <div class="form-check mb-3">
        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" @checked(old('is_active', $product->is_active))>
        <label class="form-check-label" for="is_active">Показывать в каталоге</label>
    </div>
    <div>
        <button class="btn btn-primary" type="submit">Сохранить</button>
        <a href="{{ route('admin.products.index') }}" class="btn btn-link">Отмена</a>
    </div>
</form>
@endsection
