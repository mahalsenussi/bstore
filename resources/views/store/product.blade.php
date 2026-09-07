@extends('layouts.store', ['title' => $product->name . ' | B-Store', 'nav' => 'shop'])

@section('breadcrumb')
    <!-- Breadcrumb Begin -->
    <div class="breadcrumb-option">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="breadcrumb__links">
                        <a href="{{ route('home') }}"><i class="fa fa-home"></i> Home</a>
                        <a href="{{ route('shop') }}">Shop</a>
                        @if ($product->category)
                            <a href="{{ route('shop.category', $product->category->slug) }}">{{ $product->category->name }}</a>
                        @endif
                        <span>{{ $product->name }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Breadcrumb End -->
@endsection

@php
    $price = (float) $product->price_from;
    $compare = (float) $product->max_price;
    $hasSale = $compare > $price;
    $sizes = $product->variants->pluck('size')->filter()->unique()->values();
    $colors = $product->variants->pluck('color')->filter()->unique()->values();
    $isStocked = $product->is_stocked;

    $gallery = $product->images;
    $baseGallery = $gallery->isEmpty();
    $fallback = (($product->id - 1) % 4) + 1;
@endphp

@section('content')
    <!-- Product Details Section Begin -->
    <section class="product-details spad">
        <div class="container">
            <div class="row">
                <div class="col-lg-6">
                    <div class="product__details__pic">
                        @if ($baseGallery)
                            <div class="product__details__slider__content">
                                <div class="product__details__pic__slider owl-carousel">
                                    @for ($i = 1; $i <= 4; $i++)
                                        <img data-hash="product-{{ $i }}" class="product__big__img"
                                        src="{{ asset('theme/img/product/details/product-' . $i . '.jpg') }}" alt="">
                                    @endfor
                                </div>
                            </div>
                        @else
                            <div class="product__details__pic__left product__thumb nice-scroll">
                                @foreach ($gallery as $key => $image)
                                    <a class="pt{{ $key === 0 ? ' active' : '' }}" href="#product-{{ $key + 1 }}">
                                        <img src="{{ Storage::disk('public')->url($image->path) }}" alt="">
                                    </a>
                                @endforeach
                            </div>
                            <div class="product__details__slider__content">
                                <div class="product__details__pic__slider owl-carousel">
                                    @foreach ($gallery as $key => $image)
                                        <img data-hash="product-{{ $key + 1 }}" class="product__big__img"
                                        src="{{ Storage::disk('public')->url($image->path) }}" alt="">
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="col-lg-6">
                    <form action="{{ route('cart.add') }}" method="POST" class="product__details__text">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <h3>{{ $product->name }}
                            @if ($product->brand)
                                <span>Brand: {{ $product->brand->name }}</span>
                            @endif
                        </h3>
                        <div class="product__details__price">
                            EGP {{ number_format($price, 2) }}
                            @if ($hasSale)
                                <span>EGP {{ number_format($compare, 2) }}</span>
                            @endif
                        </div>
                        <p>{{ $product->description }}</p>
                        <div class="product__details__button">
                            <div class="quantity">
                                <span>Quantity:</span>
                                <div class="pro-qty">
                                    <input type="text" name="quantity" value="1">
                                </div>
                            </div>
                            <button type="submit" class="cart-btn"><span class="icon_bag_alt"></span> Add to cart</button>
                        </div>
                        <div class="product__details__widget">
                            <ul>
                                <li>
                                    <span>Availability:</span>
                                    <div class="stock__checkbox">
                                        <p class="{{ $isStocked ? 'text-success' : 'text-danger' }}">{{ $isStocked ? 'In Stock' : 'Out of Stock' }}</p>
                                    </div>
                                </li>
                                @if ($colors->isNotEmpty())
                                    <li>
                                        <span>Available color:</span>
                                        <div class="color__checkbox">
                                            @foreach ($colors as $color)
                                                <label for="color-{{ Str::slug($color) }}">
                                                    <input type="radio" name="color" id="color-{{ Str::slug($color) }}" value="{{ $color }}" {{ $loop->first ? 'checked' : '' }}>
                                                    <span class="checkmark" style="background: {{ $color === 'red' ? '#ff0000' : ($color === 'black' ? '#111' : ($color === 'grey' ? '#999' : '#e3e3e3')) }}"></span>
                                                    <small>{{ $color }}</small>
                                                </label>
                                            @endforeach
                                        </div>
                                    </li>
                                @endif
                                @if ($sizes->isNotEmpty())
                                    <li>
                                        <span>Available size:</span>
                                        <div class="size__btn">
                                            @foreach ($sizes as $size)
                                                <label for="size-{{ Str::slug($size) }}" class="{{ $loop->first ? 'active' : '' }}">
                                                    <input type="radio" name="size" id="size-{{ Str::slug($size) }}" value="{{ $size }}" {{ $loop->first ? 'checked' : '' }}>
                                                    {{ $size }}
                                                </label>
                                            @endforeach
                                        </div>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </form>
                </div>
                <div class="col-lg-12">
                    <div class="product__details__tab">
                        <ul class="nav nav-tabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-toggle="tab" href="#tabs-1" role="tab">Description</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#tabs-2" role="tab">Specification</a>
                            </li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane active" id="tabs-1" role="tabpanel">
                                <h6>Description</h6>
                                <p>{{ $product->description ?: 'No description available.' }}</p>
                            </div>
                            <div class="tab-pane" id="tabs-2" role="tabpanel">
                                <h6>Specification</h6>
                                <ul>
                                    <li><strong>Brand:</strong> {{ $product->brand->name ?? '—' }}</li>
                                    <li><strong>Category:</strong> {{ $product->category->name ?? '—' }}</li>
                                    @foreach ($product->variants as $variant)
                                        <li><strong>SKU {{ $variant->sku }}:</strong> {{ implode(' / ', array_filter([$variant->color, $variant->size])) }} &mdash; EGP {{ number_format((float) $variant->price, 2) }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if ($related->isNotEmpty())
                <div class="row">
                    <div class="col-lg-12 text-center">
                        <div class="related__title">
                            <h5>RELATED PRODUCTS</h5>
                        </div>
                    </div>
                    @foreach ($related as $item)
                        <div class="col-lg-3 col-md-4 col-sm-6">
                            <x-product-card :product="$item" />
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
    <!-- Product Details Section End -->
@endsection