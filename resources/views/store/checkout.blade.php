@extends('layouts.store', ['title' => 'Checkout | B-Store', 'nav' => 'shop'])

@section('breadcrumb')
    <!-- Breadcrumb Begin -->
    <div class="breadcrumb-option">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="breadcrumb__links">
                        <a href="{{ route('home') }}"><i class="fa fa-home"></i> Home</a>
                        <span>Checkout</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Breadcrumb End -->
@endsection

@section('content')
    @include('store.partials.flash')

    <!-- Checkout Section Begin -->
    <section class="checkout spad">
        <div class="container">
            <form action="{{ route('checkout.store') }}" method="POST" class="checkout__form">
                @csrf
                <div class="row">
                    <div class="col-lg-8">
                        <h5>Billing details</h5>
                        <div class="row">
                            <div class="col-lg-6 col-md-6 col-sm-6">
                                <div class="checkout__form__input">
                                    <p>Full Name <span>*</span></p>
                                    <input type="text" name="name" value="{{ old('name') }}" required>
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-6 col-sm-6">
                                <div class="checkout__form__input">
                                    <p>Phone <span>*</span></p>
                                    <input type="text" name="phone" value="{{ old('phone') }}" required>
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-6 col-sm-6">
                                <div class="checkout__form__input">
                                    <p>Email</p>
                                    <input type="email" name="email" value="{{ old('email') }}">
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-6 col-sm-6">
                                <div class="checkout__form__input">
                                    <p>City</p>
                                    <input type="text" name="city" value="{{ old('city') }}">
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div class="checkout__form__input">
                                    <p>Address</p>
                                    <input type="text" name="address" value="{{ old('address') }}" placeholder="Street address">
                                </div>
                                <div class="checkout__form__input">
                                    <p>Choose a store</p>
                                    <select name="store_id" class="form-control">
                                        <option value="">Collect from any store</option>
                                        @foreach ($stores as $store)
                                            <option value="{{ $store->id }}" {{ old('store_id') == $store->id ? 'selected' : '' }}>
                                                {{ $store->name }} &mdash; {{ $store->city }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="checkout__form__input">
                                    <p>Order notes</p>
                                    <input type="text" name="notes" value="{{ old('notes') }}" placeholder="Note about your order, e.g. special delivery requests">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="checkout__order">
                            <h5>Your order</h5>
                            <div class="checkout__order__product">
                                <ul>
                                    <li>
                                        <span class="top__text">Product</span>
                                        <span class="top__text__right">Total</span>
                                    </li>
                                    @foreach ($items as $line)
                                        <li>
                                            {{ Str::limit($line['variant']->product->name, 40) }}
                                            ({{ $line['quantity'] }}x)
                                            <span>EGP {{ number_format((float) $line['line_total'], 2) }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                            <div class="checkout__order__total">
                                <ul>
                                    <li>Subtotal <span>EGP {{ number_format((float) $subtotal, 2) }}</span></li>
                                    <li>Total <span>EGP {{ number_format((float) $subtotal, 2) }}</span></li>
                                </ul>
                            </div>
                            <div class="checkout__order__widget">
                                <p><i class="fa fa-money"></i> Cash on delivery</p>
                            </div>
                            <button type="submit" class="site-btn">Place order</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>
    <!-- Checkout Section End -->
@endsection