@extends('admin.layout')

@section('title', 'Заказ №' . $order->id)

@section('admin')
<h1 class="h3 mb-3">Заказ №{{ $order->id }}</h1>

<div class="row g-3">
    <div class="col-lg-7">
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
    <div class="col-lg-5">
        <div class="card card-body">
            <p class="mb-1"><strong>Покупатель:</strong> {{ $order->user->name }} ({{ $order->user->email }})</p>
            <p class="mb-1"><strong>Получатель:</strong> {{ $order->customer_name }}</p>
            <p class="mb-1"><strong>Телефон:</strong> {{ $order->phone }}</p>
            <p class="mb-1"><strong>Адрес:</strong> {{ $order->address }}</p>
            @if($order->comment)<p class="mb-1"><strong>Комментарий:</strong> {{ $order->comment }}</p>@endif

            <form action="{{ route('admin.orders.update', $order) }}" method="post" class="mt-3">
                @csrf
                @method('PATCH')
                <label class="form-label" for="status">Статус</label>
                <div class="d-flex gap-2">
                    <select name="status" id="status" class="form-select">
                        @foreach(\App\Models\Order::STATUSES as $key => $name)
                            <option value="{{ $key }}" @selected($order->status === $key)>{{ $name }}</option>
                        @endforeach
                    </select>
                    <button class="btn btn-primary" type="submit">Сохранить</button>
                </div>
            </form>
        </div>
    </div>
</div>
<a href="{{ route('admin.orders.index') }}" class="d-inline-block mt-3">К списку заказов</a>
@endsection
