@extends('admin.layout')

@section('title', 'Категории')

@section('admin')
<h1 class="h3 mb-3">Категории</h1>

@if($errors->any())
    <div class="alert alert-danger">{{ $errors->first() }}</div>
@endif

<form method="post" action="{{ route('admin.categories.store') }}" class="d-flex gap-2 mb-3">
    @csrf
    <input type="text" name="name" class="form-control" placeholder="Название новой категории" required>
    <button class="btn btn-primary text-nowrap" type="submit">Добавить</button>
</form>

<table class="table bg-white align-middle">
    <thead><tr><th>Название</th><th>Товаров</th><th></th></tr></thead>
    <tbody>
    @foreach($categories as $c)
        <tr>
            <td>
                <form action="{{ route('admin.categories.update', $c) }}" method="post" class="d-flex gap-2">
                    @csrf
                    @method('PUT')
                    <input type="text" name="name" value="{{ $c->name }}" class="form-control form-control-sm" required>
                    <button class="btn btn-sm btn-outline-primary" type="submit">Сохранить</button>
                </form>
            </td>
            <td>{{ $c->products_count }}</td>
            <td>
                <form action="{{ route('admin.categories.destroy', $c) }}" method="post" onsubmit="return confirm('Удалить категорию?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger" type="submit">Удалить</button>
                </form>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
@endsection
