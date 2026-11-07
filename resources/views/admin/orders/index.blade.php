@extends('admin.layout')

@section('title', 'Заказы')

@section('admin')
<h1 class="h3 mb-3">Заказы</h1>

<form method="get" class="d-flex gap-2 mb-3">
    <select name="status" class="form-select">
        <option value="">Все статусы</option>
        @foreach(\App\Models\Order::STATUSES as $key => $name)
            <option value="{{ $key }}" @selected(request('status') === $key)>{{ $name }}</option>
        @endforeach
    </select>
    <button class="btn btn-outline-primary" type="submit">Показать</button>
</form>

<table class="table bg-white align-middle">
    <thead><tr><th>№</th><th>Дата</th><th>Покупатель</th><th>Сумма</th><th>Статус</th><th></th></tr></thead>
    <tbody>
    @foreach($orders as $o)
        <tr>
            <td>{{ $o->id }}</td>
            <td>{{ $o->created_at->format('d.m.Y H:i') }}</td>
            <td>{{ $o->user->name }}</td>
            <td>{{ number_format($o->total, 0, ',', ' ') }} ₽</td>
            <td>{{ $o->status_name }}</td>
            <td><a href="{{ route('admin.orders.show', $o) }}">Открыть</a></td>
        </tr>
    @endforeach
    </tbody>
</table>
{{ $orders->links() }}
@endsection
