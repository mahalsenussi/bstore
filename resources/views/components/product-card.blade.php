@php
    $img = $product->cover_url ?? asset('theme/img/shop/shop-' . ((($product->id - 1) % 9) + 1) . '.jpg');
    $hasSale = $product->price_from < $product->max_price && $product->max_price > 0;
    $isNew = $product->created_at?->isAfter(now()->subDays(30));
@endphp
<div class="product__item{{ $hasSale ? ' sale' : '' }}">
    <div class="product__item__pic set-bg" data-setbg="{{ $img }}">
        @if ($hasSale)
            <div class="label">Sale</div>
        @elseif ($isNew)
            <div class="label new">New</div>
        @endif
        <ul class="product__hover">
            <li><a href="{{ $img }}" class="image-popup"><span class="arrow_expand"></span></a></li>
            <li><a href="{{ route('product.show', $product->slug) }}"><span class="icon_bag_alt"></span></a></li>
        </ul>
    </div>
    <div class="product__item__text">
        <h6><a href="{{ route('product.show', $product->slug) }}">{{ $product->name }}</a></h6>
        <div class="product__price">
            EGP {{ number_format((float) $product->price_from, 2) }}
            @if ($hasSale)
                <span>EGP {{ number_format((float) $product->max_price, 2) }}</span>
            @endif
        </div>
    </div>
</div>