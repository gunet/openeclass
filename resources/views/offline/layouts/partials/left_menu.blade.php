<nav id="background-cheat-leftnav" class="col_sidebar_active @if(isset($_COOKIE['CookieSlideSidebar'])) active-nav @endif d-flex justify-content-start align-items-strech px-lg-0">
    <div class="d-none d-lg-block ContentLeftNav">
        @include('layouts.partials.sidebar',['is_editor' => $is_editor])
    </div>
</nav>