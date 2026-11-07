@extends('layouts.app')

@section('title', 'Корзина')

@section('content')
<h1 class="h3 mb-3">Корзина</h1>

@if(count($items))
    <div class="table-responsive">
        <table class="table align-middle bg-white">
            <thead>
                <tr><th>Товар</th><th>Цена</th><th style="width: 160px;">Количество</th><th>Сумма</th><th></th></tr>
            </thead>
            <tbody>
            @foreach($items as $row)
                <tr>
                    <td>
                        <a href="{{ route('product.show', $row['product']) }}">{{ $row['product']->name }}</a>
                    </td>
                    <td>{{ number_format($row['product']->price, 0, ',', ' ') }} ₽</td>
                    <td>
                        <form action="{{ route('cart.update', $row['product']) }}" method="post" class="d-flex gap-1">
                            @csrf
                            @method('PATCH')
                            <input type="number" name="qty" value="{{ $row['qty'] }}" min="0" max="{{ $row['product']->stock }}" class="form-control form-control-sm">
                            <button class="btn btn-sm btn-outline-primary" type="submit">ОК</button>
                        </form>
                    </td>
                    <td>{{ number_format($row['sum'], 0, ',', ' ') }} ₽</td>
                    <td>
                        <form action="{{ route('cart.remove', $row['product']) }}" method="post">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" type="submit">Удалить</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-between align-items-center">
        <div class="fs-4">Итого: <strong>{{ number_format($total, 0, ',', ' ') }} ₽</strong></div>
        <a href="{{ route('checkout') }}" class="btn btn-primary">Оформить заказ</a>
    </div>
@else
    <div class="alert alert-secondary">Корзина пуста. <a href="{{ route('catalog') }}">Перейти в каталог</a></div>
@endif
@endsection
