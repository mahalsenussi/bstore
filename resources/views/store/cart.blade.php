@extends('layouts.store', ['title' => 'Shopping Cart | B-Store', 'nav' => 'shop'])

@section('breadcrumb')
    <!-- Breadcrumb Begin -->
    <div class="breadcrumb-option">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="breadcrumb__links">
                        <a href="{{ route('home') }}"><i class="fa fa-home"></i> Home</a>
                        <span>Shopping cart</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Breadcrumb End -->
@endsection

@section('content')
    @include('store.partials.flash')

    <!-- Shop Cart Section Begin -->
    <section class="shop-cart spad">
        <div class="container">
            @if ($items->isEmpty())
                <div class="row justify-content-center">
                    <div class="col-lg-6 text-center">
                        <h4>Your cart is empty</h4>
                        <p>You have no items in your shopping cart yet.</p>
                        <a href="{{ route('shop') }}" class="primary-btn">Continue Shopping</a>
                    </div>
                </div>
            @else
                <form action="{{ route('cart.update') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="shop__cart__table">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Price</th>
                                            <th>Quantity</th>
                                            <th>Total</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($items as $line)
                                            @php $variant = $line['variant']; @endphp
                                            <tr>
                                                <td class="cart__product__item">
                                                    <img src="{{ $variant->product->cover_url ?? asset('theme/img/shop-cart/cp-' . ((($variant->product->id - 1) % 4) + 1) . '.jpg') }}" alt="">
                                                    <div class="cart__product__item__title">
                                                        <h6><a href="{{ route('product.show', $variant->product->slug) }}">{{ $variant->product->name }}</a></h6>
                                                        <small>{{ $variant->product->brand->name ?? '' }}</small>
                                                        <small>{{ $variant->color }} / {{ $variant->size }}</small>
                                                    </div>
                                                </td>
                                                <td class="cart__price">EGP {{ number_format((float) $variant->price, 2) }}</td>
                                                <td class="cart__quantity">
                                                    <div class="pro-qty">
                                                        <input type="text" name="quantity[{{ $variant->id }}]" value="{{ $line['quantity'] }}">
                                                    </div>
                                                </td>
                                                <td class="cart__total">EGP {{ number_format((float) $line['line_total'], 2) }}</td>
                                                <td class="cart__close">
                                                    <button type="submit" form="remove-{{ $variant->id }}" class="btn btn-link p-0" style="border:none;background:none;" onclick="return confirm('Remove this item?')"><span class="icon_close"></span></button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-6 col-md-6 col-sm-6">
                            <div class="cart__btn">
                                <a href="{{ route('shop') }}">Continue Shopping</a>
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-6 col-sm-6">
                            <div class="cart__btn update__btn">
                                <button type="submit" class="btn btn-link"><span class="icon_loading"></span> Update cart</button>
                            </div>
                        </div>
                    </div>
                </form>
                @foreach ($items as $line)
                    <form id="remove-{{ $line['variant']->id }}" action="{{ route('cart.remove') }}" method="POST" class="d-none">
                        @csrf
                        <input type="hidden" name="variant_id" value="{{ $line['variant']->id }}">
                    </form>
                @endforeach
                <div class="row">
                    <div class="col-lg-4 offset-lg-8">
                        <div class="cart__total__procced">
                            <h6>Cart total</h6>
                            <ul>
                                <li>Subtotal <span>EGP {{ number_format((float) $subtotal, 2) }}</span></li>
                                <li>Total <span>EGP {{ number_format((float) $subtotal, 2) }}</span></li>
                            </ul>
                            <a href="{{ route('checkout.create') }}" class="primary-btn">Proceed to checkout</a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </section>
    <!-- Shop Cart Section End -->
@endsection