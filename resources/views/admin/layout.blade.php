@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-3 mb-3">
        <div class="list-group admin-menu">
            <a href="{{ route('admin.dashboard') }}" class="list-group-item list-group-item-action {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">Сводка</a>
            <a href="{{ route('admin.products.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('admin.products.*') ? 'active' : '' }}">Товары</a>
            <a href="{{ route('admin.categories.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">Категории</a>
            <a href="{{ route('admin.orders.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">Заказы</a>
            <a href="{{ route('admin.users.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">Пользователи</a>
        </div>
    </div>
    <div class="col-md-9">
        @yield('admin')
    </div>
</div>
@endsection
