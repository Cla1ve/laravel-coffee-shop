@extends('admin.layout')

@section('title', 'Товары')

@section('admin')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Товары</h1>
    <a href="{{ route('admin.products.create') }}" class="btn btn-primary">Добавить товар</a>
</div>

<form method="get" class="d-flex gap-2 mb-3">
    <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Название товара">
    <button class="btn btn-outline-primary" type="submit">Найти</button>
</form>

<table class="table bg-white align-middle">
    <thead><tr><th></th><th>Название</th><th>Категория</th><th>Цена</th><th>Остаток</th><th>Виден</th><th></th></tr></thead>
    <tbody>
    @foreach($products as $p)
        <tr>
            <td><img src="{{ $p->image_url }}" alt="" width="48" height="48" style="object-fit: cover;"></td>
            <td>{{ $p->name }}</td>
            <td>{{ $p->category->name }}</td>
            <td>{{ number_format($p->price, 0, ',', ' ') }} ₽</td>
            <td>{{ $p->stock }}</td>
            <td>{{ $p->is_active ? 'да' : 'нет' }}</td>
            <td class="text-nowrap">
                <a href="{{ route('admin.products.edit', $p) }}" class="btn btn-sm btn-outline-primary">Изменить</a>
                <form action="{{ route('admin.products.destroy', $p) }}" method="post" class="d-inline" onsubmit="return confirm('Удалить товар?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger" type="submit">Удалить</button>
                </form>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
{{ $products->links() }}
@endsection
