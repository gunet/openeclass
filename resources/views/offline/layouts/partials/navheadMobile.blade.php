<header>
<nav id="bgr-cheat-header" class="navbar h-auto navbar-eclass fixed-top py-0">
    <div class='container-fluid header-container py-0'>
        <div class='col-12 d-flex justify-content-center h-100'>
            <div class="btn-group w-100" role="group" aria-label="Basic example">
                @if(!get_config('hide_login_link'))
                    <a type="button"><img class="eclass-nav-icon ps-1 pe-2" src={{$logo_img_small}}></a>
                @endif
            </div>
        </div>
    </div>
</nav>
</header>