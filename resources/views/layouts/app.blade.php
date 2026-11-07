<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Зерно') | Зерно</title>
    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body class="d-flex flex-column min-vh-100">

<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">
        <a class="navbar-brand" href="{{ route('home') }}">ЗЕРНО</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu" aria-label="Меню">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="menu">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="{{ route('catalog') }}">Каталог</a></li>
                @foreach($menuCategories as $c)
                    <li class="nav-item d-none d-xl-block">
                        <a class="nav-link" href="{{ route('catalog', ['category' => $c->slug]) }}">{{ $c->name }}</a>
                    </li>
                @endforeach
            </ul>

            <form class="d-flex me-lg-3 my-2 my-lg-0" action="{{ route('catalog') }}" method="get">
                <input class="form-control form-control-sm me-2" type="search" name="q" value="{{ request('q') }}" placeholder="Поиск товара" maxlength="100">
                <button class="btn btn-sm btn-outline-light" type="submit">Найти</button>
            </form>

            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('cart.index') }}">
                        Корзина
                        @if($cartCount > 0)<span class="badge text-bg-warning">{{ $cartCount }}</span>@endif
                    </a>
                </li>
                @auth
                    @if(auth()->user()->isAdmin())
                        <li class="nav-item"><a class="nav-link" href="{{ route('admin.dashboard') }}">Админка</a></li>
                    @endif
                    <li class="nav-item"><a class="nav-link" href="{{ route('orders.index') }}">Мои заказы</a></li>
                    <li class="nav-item">
                        <form action="{{ route('logout') }}" method="post">
                            @csrf
                            <button class="nav-link btn btn-link" type="submit">Выйти ({{ auth()->user()->name }})</button>
                        </form>
                    </li>
                @else
                    <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Войти</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('register') }}">Регистрация</a></li>
                @endauth
            </ul>
        </div>
    </div>
</nav>

<main class="container py-4 flex-grow-1">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @yield('content')
</main>

<footer class="py-4 mt-4">
    <div class="container d-flex flex-wrap justify-content-between">
        <div>Зерно, интернет-магазин кофе и чая</div>
        <div>Учебный проект, 2026</div>
    </div>
</footer>

<script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>
