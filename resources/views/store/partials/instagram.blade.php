<!-- Instagram Begin -->
<div class="instagram">
    <div class="container-fluid">
        <div class="row">
            @for ($i = 1; $i <= 6; $i++)
                <div class="col-lg-2 col-md-4 col-sm-4 p-0">
                    <div class="instagram__item set-bg" data-setbg="{{ asset('theme/img/instagram/insta-' . $i . '.jpg') }}">
                        <div class="instagram__text">
                            <i class="fa fa-instagram"></i>
                            <a href="https://www.instagram.com">@ bstore_shop</a>
                        </div>
                    </div>
                </div>
            @endfor
        </div>
    </div>
</div>
<!-- Instagram End -->