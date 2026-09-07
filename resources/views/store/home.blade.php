@extends('layouts.store', ['title' => 'B-Store | Home', 'nav' => 'home'])

@section('content')
    @include('store.partials.flash')

    <!-- Categories Section Begin -->
    <section class="categories">
        <div class="container-fluid">
            <div class="row">
                @if ($categories->isNotEmpty())
                    <div class="col-lg-6 p-0">
                        <div class="categories__item categories__large__item set-bg"
                        data-setbg="{{ asset('theme/img/categories/category-1.jpg') }}">
                        <div class="categories__text">
                            <h1>{{ $categories->first()->name }}</h1>
                            <p>{{ $categories->first()->description }}</p>
                            <a href="{{ route('shop.category', $categories->first()->slug) }}">Shop now</a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="row">
                        @foreach ($categories->skip(1)->take(4) as $index => $category)
                            <div class="col-lg-6 col-md-6 col-sm-6 p-0">
                                <div class="categories__item set-bg" data-setbg="{{ asset('theme/img/categories/category-' . ($index + 2) . '.jpg') }}">
                                    <div class="categories__text">
                                        <h4>{{ $category->name }}</h4>
                                        <p>{{ $category->products_count }} items</p>
                                        <a href="{{ route('shop.category', $category->slug) }}">Shop now</a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</section>
<!-- Categories Section End -->

<!-- Product Section Begin -->
<section class="product spad">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="section-title text-center">
                    <h4>New product</h4>
                </div>
            </div>
        </div>
        <div class="row">
            @foreach ($newProducts as $product)
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <x-product-card :product="$product" />
                </div>
            @endforeach
        </div>
    </div>
</section>
<!-- Product Section End -->

<!-- Banner Section Begin -->
<section class="banner set-bg" data-setbg="{{ asset('theme/img/banner/banner-1.jpg') }}">
    <div class="container">
        <div class="row">
            <div class="col-xl-7 col-lg-8 m-auto">
                <div class="banner__slider owl-carousel">
                    <div class="banner__item">
                        <div class="banner__text">
                            <span>The B-Store Collection</span>
                            <h1>Shop The Latest</h1>
                            <a href="{{ route('shop') }}">Shop now</a>
                        </div>
                    </div>
                    <div class="banner__item">
                        <div class="banner__text">
                            <span>Multi-brand fashion</span>
                            <h1>Explore Our Brands</h1>
                            <a href="{{ route('shop') }}">Shop now</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- Banner Section End -->

<!-- Trend Section Begin -->
<section class="trend spad">
    <div class="container">
        <div class="row">
            @foreach ($brands as $brand)
                <div class="col-lg-4 col-md-4 col-sm-6">
                    <div class="trend__content">
                        <div class="section-title">
                            <h4>{{ $brand->name }}</h4>
                        </div>
                        @foreach ($brand->products->take(3) as $item)
                            <div class="trend__item">
                                <div class="trend__item__pic">
                                    <img src="{{ $item->cover_url ?? asset('theme/img/shop/shop-' . ((($item->id - 1) % 9) + 1) . '.jpg') }}" alt="">
                                </div>
                                <div class="trend__item__text">
                                    <h6><a href="{{ route('product.show', $item->slug) }}">{{ $item->name }}</a></h6>
                                    <div class="product__price">EGP {{ number_format((float) $item->price_from, 2) }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
<!-- Trend Section End -->

<!-- Discount Section Begin -->
<section class="discount">
    <div class="container">
        <div class="row">
            <div class="col-lg-6 p-0">
                <div class="discount__pic">
                    <img src="{{ asset('theme/img/discount.jpg') }}" alt="">
                </div>
            </div>
            <div class="col-lg-6 p-0">
                <div class="discount__text">
                    <div class="discount__text__title">
                        <span>Discount</span>
                        <h2>Seasonal Sale</h2>
                        <h5><span>Sale</span> 50%</h5>
                    </div>
                    <div class="discount__countdown" id="countdown-time">
                        <div class="countdown__item">
                            <span>22</span>
                            <p>Days</p>
                        </div>
                        <div class="countdown__item">
                            <span>18</span>
                            <p>Hour</p>
                        </div>
                        <div class="countdown__item">
                            <span>46</span>
                            <p>Min</p>
                        </div>
                        <div class="countdown__item">
                            <span>05</span>
                            <p>Sec</p>
                        </div>
                    </div>
                    <a href="{{ route('shop') }}">Shop now</a>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- Discount Section End -->

<!-- Services Section Begin -->
<section class="services spad">
    <div class="container">
        <div class="row">
            <div class="col-lg-3 col-md-4 col-sm-6">
                <div class="services__item">
                    <i class="fa fa-car"></i>
                    <h6>Free Shipping</h6>
                    <p>For all orders</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-4 col-sm-6">
                <div class="services__item">
                    <i class="fa fa-money"></i>
                    <h6>Money Back Guarantee</h6>
                    <p>If goods have problems</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-4 col-sm-6">
                <div class="services__item">
                    <i class="fa fa-support"></i>
                    <h6>Online Support 24/7</h6>
                    <p>Dedicated support</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-4 col-sm-6">
                <div class="services__item">
                    <i class="fa fa-headphones"></i>
                    <h6>Payment Secure</h6>
                    <p>100% secure payment</p>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- Services Section End -->
@endsection