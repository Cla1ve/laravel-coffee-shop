<div class="card h-100 product-card">
    <a href="{{ route('product.show', $product) }}">
        <img src="{{ $product->image_url }}" class="card-img-top" alt="{{ $product->name }}">
    </a>
    <div class="card-body d-flex flex-column">
        <div class="small text-muted">{{ $product->category->name }}</div>
        <h2 class="card-title h6 mt-1"><a href="{{ route('product.show', $product) }}">{{ $product->name }}</a></h2>
        <div class="mt-auto d-flex justify-content-between align-items-center">
            <span class="price">{{ number_format($product->price, 0, ',', ' ') }} ₽</span>
            @if($product->stock > 0)
                <form action="{{ route('cart.add', $product) }}" method="post">
                    @csrf
                    <button class="btn btn-sm btn-primary" type="submit">В корзину</button>
                </form>
            @else
                <span class="text-muted small">Нет в наличии</span>
            @endif
        </div>
    </div>
</div>
