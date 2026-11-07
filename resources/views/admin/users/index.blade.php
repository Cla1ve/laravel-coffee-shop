@extends('admin.layout')

@section('title', 'Пользователи')

@section('admin')
<h1 class="h3 mb-3">Пользователи</h1>

<table class="table bg-white">
    <thead><tr><th>№</th><th>Имя</th><th>Почта</th><th>Роль</th><th>Заказов</th><th>Дата регистрации</th></tr></thead>
    <tbody>
    @foreach($users as $u)
        <tr>
            <td>{{ $u->id }}</td>
            <td>{{ $u->name }}</td>
            <td>{{ $u->email }}</td>
            <td>{{ $u->role === 'admin' ? 'админ' : 'покупатель' }}</td>
            <td>{{ $u->orders_count }}</td>
            <td>{{ $u->created_at->format('d.m.Y') }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
{{ $users->links() }}
@endsection
