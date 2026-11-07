@extends('layouts.app')

@section('title', 'Вход')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-5">
        <h1 class="h3 mb-3">Вход</h1>
        <form method="post" action="{{ route('login') }}" class="card card-body">
            @csrf
            <div class="mb-3">
                <label class="form-label" for="email">Почта</label>
                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required autofocus>
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label" for="password">Пароль</label>
                <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required>
                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="remember" name="remember" value="1">
                <label class="form-check-label" for="remember">Запомнить меня</label>
            </div>
            <button class="btn btn-primary" type="submit">Войти</button>
        </form>
        <p class="mt-3">Нет аккаунта? <a href="{{ route('register') }}">Зарегистрироваться</a></p>
    </div>
</div>
@endsection
