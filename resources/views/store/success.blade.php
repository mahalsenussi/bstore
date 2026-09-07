@extends('layouts.store', ['title' => 'Order Placed | B-Store', 'nav' => 'shop'])

@section('breadcrumb')
    <!-- Breadcrumb Begin -->
    <div class="breadcrumb-option">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="breadcrumb__links">
                        <a href="{{ route('home') }}"><i class="fa fa-home"></i> Home</a>
                        <span>Order Confirmed</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Breadcrumb End -->
@endsection

@section('content')
    <section class="checkout spad">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center">
                    <div class="checkout__order">
                        <h3>Thank you for your order!</h3>
                        <p>Your order <strong>{{ $order->order_number }}</strong> has been placed successfully and is pending confirmation.</p>
                        <p class="text-muted">Order total: <strong>EGP {{ number_format((float) $order->total, 2) }}</strong></p>
                        <p class="text-muted">Payment: Cash on delivery</p>
                        <br>
                        <a href="{{ route('shop') }}" class="primary-btn">Continue Shopping</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection