@extends('layouts.store', ['title' => ($brand->name ?? $category->name ?? 'Shop') . ' | B-Store', 'nav' => 'shop'])

@section('breadcrumb')
    <!-- Breadcrumb Begin -->
    <div class="breadcrumb-option">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="breadcrumb__links">
                        <a href="{{ route('home') }}"><i class="fa fa-home"></i> Home</a>
                        @if (isset($brand))
                            <a href="{{ route('shop') }}">Shop</a>
                            <span>{{ $brand->name }}</span>
                        @elseif (isset($category))
                            <a href="{{ route('shop') }}">Shop</a>
                            <span>{{ $category->name }}</span>
                        @elseif (isset($query))
                            <a href="{{ route('shop') }}">Shop</a>
                            <span>Search results</span>
                        @else
                            <span>Shop</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Breadcrumb End -->
@endsection

@section('content')
    @include('store.partials.flash')

    <!-- Shop Section Begin -->
    <section class="shop spad">
        <div class="container">
            <div class="row">
                <div class="col-lg-3 col-md-3">
                    <div class="shop__sidebar">
                        <div class="sidebar__categories">
                            <div class="section-title">
                                <h4>Categories</h4>
                            </div>
                            <div class="categories__accordion">
                                @foreach ($categories as $parent)
                                    <div class="card">
                                        <div class="card-heading{{ request('category') === $parent->slug ? ' active' : '' }}">
                                            <a href="{{ route('shop.category', $parent->slug) }}">{{ $parent->name }}</a>
                                        </div>
                                        @if ($parent->children->isNotEmpty())
                                            <div class="card-body">
                                                <ul>
                                                    @foreach ($parent->children as $child)
                                                        <li><a href="{{ route('shop.category', $child->slug) }}" class="{{ request('category') === $child->slug ? 'active' : '' }}">{{ $child->name }}</a></li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                            <div class="card mb-3">
                                <div class="card-heading"><a href="{{ route('shop') }}">All products</a></div>
                            </div>
                        </div>
                        <div class="sidebar__filter">
                            <div class="section-title">
                                <h4>Shop by brand</h4>
                            </div>
                            <div class="size__list">
                                @foreach ($brands as $b)
                                    <label for="brand-{{ $b->id }}">
                                        <a href="{{ route('shop.brand', $b->slug) }}" class="{{ request('brand') === $b->slug ? 'text-danger' : '' }}">{{ $b->name }} ({{ $b->products_count }})</a>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        <div class="sidebar__filter">
                            <div class="section-title">
                                <h4>Shop by price</h4>
                            </div>
                            <form action="{{ url()->current() }}" method="GET" class="filter-range-wrap">
                                <div class="price-input mb-3">
                                    <p>Price:</p>
                                    <input type="number" name="min" id="minamount" placeholder="Min" value="{{ request('min') }}" style="width:48%;display:inline-block">
                                    <input type="number" name="max" id="maxamount" placeholder="Max" value="{{ request('max') }}" style="width:48%;display:inline-block">
                                </div>
                                <a href="#" class="filter-submit">Filter</a>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-lg-9 col-md-9">
                    <div class="row">
                        <div class="col-lg-12 mb-3">
                            <h6><a href="{{ url()->current() }}"><i class="fa fa-times-circle"></i> Clear filters</a></h6>
                        </div>
                        @forelse ($products as $product)
                            <div class="col-lg-4 col-md-6">
                                <x-product-card :product="$product" />
                            </div>
                        @empty
                            <div class="col-lg-12">
                                <p class="text-center">No products found.</p>
                            </div>
                        @endforelse
                        <div class="col-lg-12 text-center">
                            {{ $products->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Shop Section End -->
@endsection

@push('scripts')
<script>
    $('.filter-submit').on('click', function (e) {
        e.preventDefault();
        $(this).closest('form').submit();
    });
</script>
@endpush