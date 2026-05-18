<nav id="background-cheat-leftnav" class="col_sidebar_active @if(isset($_COOKIE['CookieSlideSidebar'])) active-nav @endif @if(isset($_COOKIE['asideBarOn'])) sidebar-card @endif d-flex justify-content-start align-items-strech px-lg-0 h-100">
    <div class="d-none d-lg-block ContentLeftNav">
        @include('layouts.partials.sidebar',['is_editor' => $is_editor])
    </div>
</nav>