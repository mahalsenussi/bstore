<!-- Offcanvas Menu Begin -->
<div class="offcanvas-menu-overlay"></div>
<div class="offcanvas-menu-wrapper">
    <div class="offcanvas__close">+</div>
    <ul class="offcanvas__widget">
        <li><span class="icon_search search-switch"></span></li>
        <li><a href="{{ route('cart.index') }}"><span class="icon_bag_alt"></span>
            @if ($cartCount > 0)
                <div class="tip">{{ $cartCount }}</div>
            @endif
        </a></li>
    </ul>
    <div class="offcanvas__logo">
        <a href="{{ route('home') }}"><img src="{{ asset('theme/img/logo.png') }}" alt="B-Store"></a>
    </div>
    <div id="mobile-menu-wrap"></div>
</div>
<!-- Offcanvas Menu End -->