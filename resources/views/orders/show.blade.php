@extends('layouts.app')

@section('title', 'Заказ №' . $order->id)

@section('content')
<h1 class="h3 mb-3">Заказ №{{ $order->id }}</h1>

<div class="row g-4">
    <div class="col-md-7">
        <table class="table bg-white">
            <thead><tr><th>Товар</th><th>Цена</th><th>Кол-во</th><th>Сумма</th></tr></thead>
            <tbody>
            @foreach($order->items as $item)
                <tr>
                    <td>{{ $item->product->name }}</td>
                    <td>{{ number_format($item->price, 0, ',', ' ') }} ₽</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ number_format($item->price * $item->quantity, 0, ',', ' ') }} ₽</td>
                </tr>
            @endforeach
            </tbody>
            <tfoot><tr><th colspan="3" class="text-end">Итого</th><th>{{ number_format($order->total, 0, ',', ' ') }} ₽</th></tr></tfoot>
        </table>
    </div>
    <div class="col-md-5">
        <div class="card card-body">
            <p class="mb-1"><strong>Статус:</strong> {{ $order->status_name }}</p>
            <p class="mb-1"><strong>Дата:</strong> {{ $order->created_at->format('d.m.Y H:i') }}</p>
            <p class="mb-1"><strong>Получатель:</strong> {{ $order->customer_name }}</p>
            <p class="mb-1"><strong>Телефон:</strong> {{ $order->phone }}</p>
            <p class="mb-1"><strong>Адрес:</strong> {{ $order->address }}</p>
            @if($order->comment)<p class="mb-1"><strong>Комментарий:</strong> {{ $order->comment }}</p>@endif

            @if($order->status === 'new')
                <form action="{{ route('orders.cancel', $order) }}" method="post" class="mt-3">
                    @csrf
                    <button class="btn btn-outline-danger" type="submit">Отменить заказ</button>
                </form>
            @endif
        </div>
    </div>
</div>
<a href="{{ route('orders.index') }}" class="d-inline-block mt-3">К списку заказов</a>
@endsection
