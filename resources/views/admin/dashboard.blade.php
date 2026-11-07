@extends('admin.layout')

@section('title', 'Админ-панель')

@section('admin')
<h1 class="h3 mb-3">Сводка</h1>

<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3"><div class="stat-card p-3"><div class="text-muted small">Заказов всего</div><div class="fs-3">{{ $stats['orders'] }}</div></div></div>
    <div class="col-6 col-lg-3"><div class="stat-card p-3"><div class="text-muted small">Новых заказов</div><div class="fs-3">{{ $stats['new_orders'] }}</div></div></div>
    <div class="col-6 col-lg-3"><div class="stat-card p-3"><div class="text-muted small">Выручка</div><div class="fs-3">{{ number_format($stats['revenue'], 0, ',', ' ') }} ₽</div></div></div>
    <div class="col-6 col-lg-3"><div class="stat-card p-3"><div class="text-muted small">Товаров</div><div class="fs-3">{{ $stats['products'] }}</div></div></div>
</div>

<h2 class="h5">Последние заказы</h2>
<table class="table bg-white mb-4">
    <thead><tr><th>№</th><th>Покупатель</th><th>Сумма</th><th>Статус</th></tr></thead>
    <tbody>
    @foreach($lastOrders as $o)
        <tr>
            <td><a href="{{ route('admin.orders.show', $o) }}">{{ $o->id }}</a></td>
            <td>{{ $o->user->name }}</td>
            <td>{{ number_format($o->total, 0, ',', ' ') }} ₽</td>
            <td>{{ $o->status_name }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<h2 class="h5">Заканчиваются на складе</h2>
<table class="table bg-white">
    <thead><tr><th>Товар</th><th>Остаток</th></tr></thead>
    <tbody>
    @forelse($lowStock as $p)
        <tr><td><a href="{{ route('admin.products.edit', $p) }}">{{ $p->name }}</a></td><td>{{ $p->stock }}</td></tr>
    @empty
        <tr><td colspan="2" class="text-muted">Всё в порядке</td></tr>
    @endforelse
    </tbody>
</table>
@endsection
