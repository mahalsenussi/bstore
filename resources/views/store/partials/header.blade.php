<!-- Header Section Begin -->
<header class="header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-xl-3 col-lg-2">
                <div class="header__logo">
                    <a href="{{ route('home') }}"><img src="{{ asset('theme/img/logo.png') }}" alt="B-Store"></a>
                </div>
            </div>
            <div class="col-xl-6 col-lg-7">
                <nav class="header__menu">
                    <ul>
                        <li class="{{ ($nav ?? '') === 'home' ? 'active' : '' }}"><a href="{{ route('home') }}">Home</a></li>
                        <li class="{{ ($nav ?? '') === 'shop' ? 'active' : '' }}"><a href="{{ route('shop') }}">Shop</a></li>
                        @if ($navCategories->isNotEmpty())
                            <li><a href="#">{{ $navCategories->first()->name }}</a>
                                <ul class="dropdown">
                                    @foreach ($navCategories as $category)
                                        <li><a href="{{ route('shop.category', $category->slug) }}">{{ $category->name }}</a></li>
                                        @foreach ($category->children as $child)
                                            <li><a href="{{ route('shop.category', $child->slug) }}">&nbsp;&nbsp;{{ $child->name }}</a></li>
                                        @endforeach
                                    @endforeach
                                </ul>
                            </li>
                        @endif
                        @if ($navBrands->isNotEmpty())
                            <li><a href="#">Brands</a>
                                <ul class="dropdown">
                                    @foreach ($navBrands as $brand)
                                        <li><a href="{{ route('shop.brand', $brand->slug) }}">{{ $brand->name }}</a></li>
                                    @endforeach
                                </ul>
                            </li>
                        @endif
                        <li><a href="#">Pages</a>
                            <ul class="dropdown">
                                <li><a href="{{ route('cart.index') }}">Shop Cart</a></li>
                                <li><a href="{{ route('checkout.create') }}">Checkout</a></li>
                            </ul>
                        </li>
                    </ul>
                </nav>
            </div>
            <div class="col-lg-3">
                <div class="header__right">
                    <ul class="header__right__widget">
                        <li><span class="icon_search search-switch"></span></li>
                        <li><a href="{{ route('cart.index') }}"><span class="icon_bag_alt"></span>
                            @if ($cartCount > 0)
                                <div class="tip">{{ $cartCount }}</div>
                            @endif
                        </a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="canvas__open">
            <i class="fa fa-bars"></i>
        </div>
    </div>
</header>
<!-- Header Section End -->