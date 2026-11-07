@extends('layouts.app')

@section('title', 'Мои заказы')

@section('content')
<h1 class="h3 mb-3">Мои заказы</h1>

@if($orders->count())
    <table class="table bg-white align-middle">
        <thead><tr><th>№</th><th>Дата</th><th>Позиций</th><th>Сумма</th><th>Статус</th><th></th></tr></thead>
        <tbody>
        @foreach($orders as $order)
            <tr>
                <td>{{ $order->id }}</td>
                <td>{{ $order->created_at->format('d.m.Y H:i') }}</td>
                <td>{{ $order->items_count }}</td>
                <td>{{ number_format($order->total, 0, ',', ' ') }} ₽</td>
                <td>{{ $order->status_name }}</td>
                <td><a href="{{ route('orders.show', $order) }}">Открыть</a></td>
            </tr>
        @endforeach
        </tbody>
    </table>
    {{ $orders->links() }}
@else
    <div class="alert alert-secondary">Заказов пока нет. <a href="{{ route('catalog') }}">Перейти в каталог</a></div>
@endif
@endsection
