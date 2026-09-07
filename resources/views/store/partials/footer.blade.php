<!-- Footer Section Begin -->
<footer class="footer">
    <div class="container">
        <div class="row">
            <div class="col-lg-4 col-md-6 col-sm-7">
                <div class="footer__about">
                    <div class="footer__logo">
                        <a href="{{ route('home') }}"><img src="{{ asset('theme/img/logo.png') }}" alt="B-Store"></a>
                    </div>
                    <p>Multi-brand fashion store. Quality products from {{ $navBrands->pluck('name')->implode(' & ') }} and more, at your door.</p>
                    <div class="footer__payment">
                        @for ($i = 1; $i <= 5; $i++)
                            <a href="#"><img src="{{ asset('theme/img/payment/payment-' . $i . '.png') }}" alt=""></a>
                        @endfor
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-md-3 col-sm-5">
                <div class="footer__widget">
                    <h6>Quick links</h6>
                    <ul>
                        <li><a href="{{ route('home') }}">Home</a></li>
                        <li><a href="{{ route('shop') }}">Shop</a></li>
                        <li><a href="{{ route('cart.index') }}">Cart</a></li>
                        <li><a href="{{ route('checkout.create') }}">Checkout</a></li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-2 col-md-3 col-sm-4">
                <div class="footer__widget">
                    <h6>Brands</h6>
                    <ul>
                        @foreach ($navBrands as $brand)
                            <li><a href="{{ route('shop.brand', $brand->slug) }}">{{ $brand->name }}</a></li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <div class="col-lg-4 col-md-8 col-sm-8">
                <div class="footer__newslatter">
                    <h6>NEWSLETTER</h6>
                    <form action="#" method="POST">
                        @csrf
                        <input type="email" name="email" placeholder="Email">
                        <button type="submit" class="site-btn">Subscribe</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="footer__copyright__text">
                    <p>&copy; {{ date('Y') }} All rights reserved | B-Store</p>
                </div>
            </div>
        </div>
    </div>
</footer>
<!-- Footer Section End -->