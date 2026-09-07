<!-- Search Begin -->
<div class="search-model">
    <div class="h-100 d-flex align-items-center justify-content-center">
        <div class="search-close-switch">+</div>
        <form class="search-model-form" action="{{ route('search') }}" method="GET">
            <input type="text" name="q" id="search-input" placeholder="Search here....." value="{{ request('q') }}">
        </form>
    </div>
</div>
<!-- Search End -->