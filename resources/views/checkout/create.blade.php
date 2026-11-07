@extends('layouts.app')

@section('title', 'Оформление заказа')

@section('content')
<h1 class="h3 mb-3">Оформление заказа</h1>

@error('items')
    <div class="alert alert-danger">{{ $message }}</div>
@enderror

<div class="row g-4">
    <div class="col-md-7">
        <form method="post" action="{{ route('checkout.store') }}" class="card card-body">
            @csrf
            <div class="mb-3">
                <label class="form-label" for="customer_name">Имя получателя</label>
                <input type="text" class="form-control @error('customer_name') is-invalid @enderror" id="customer_name" name="customer_name" value="{{ old('customer_name', auth()->user()->name) }}" required>
                @error('customer_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label" for="phone">Телефон</label>
                <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone', auth()->user()->phone) }}" placeholder="+79991234567" required>
                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label" for="address">Адрес доставки</label>
                <input type="text" class="form-control @error('address') is-invalid @enderror" id="address" name="address" value="{{ old('address') }}" required>
                @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label" for="comment">Комментарий</label>
                <textarea class="form-control @error('comment') is-invalid @enderror" id="comment" name="comment" rows="2" maxlength="500">{{ old('comment') }}</textarea>
                @error('comment')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <button class="btn btn-primary" type="submit">Подтвердить заказ</button>
        </form>
    </div>
    <div class="col-md-5">
        <div class="card card-body">
            <h2 class="h5">Ваш заказ</h2>
            <ul class="list-unstyled mb-2">
                @foreach($items as $row)
                    <li class="d-flex justify-content-between">
                        <span>{{ $row['product']->name }} × {{ $row['qty'] }}</span>
                        <span>{{ number_format($row['sum'], 0, ',', ' ') }} ₽</span>
                    </li>
                @endforeach
            </ul>
            <hr>
            <div class="fs-5">Итого: <strong>{{ number_format($total, 0, ',', ' ') }} ₽</strong></div>
        </div>
    </div>
</div>
@endsection
