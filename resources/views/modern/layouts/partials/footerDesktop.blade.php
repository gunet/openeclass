
<footer id="bgr-cheat-footer" class="site-footer mt-auto d-flex justify-content-start align-items-center px-3">
    <div class='col-12 d-flex justify-content-between align-items-center'>
        <ul class="container-items-footer nav">
            <li class="nav-item"><a class="nav-link menu-item a_tools_site_footer ps-2 pe-3" href="{{ $urlAppend }}info/about.php">{{ trans('langPlatformIdentity') }}</a></li>
            <li class="nav-item"><a class="nav-link menu-item a_tools_site_footer px-3" href="{{ $urlAppend }}info/contact.php">{{ trans('langContact') }}</a></li>
            <li class="nav-item"><a class="nav-link menu-item a_tools_site_footer px-3" href="{{ $urlAppend }}info/manual.php">{{ trans('langManuals') }}</a></li>
            <li class="nav-item"><a class="nav-link menu-item a_tools_site_footer px-3" href="{{ $urlAppend }}info/terms.php">{{ trans('langUsageTerms') }}</a></li>
            @if (get_config('activate_privacy_policy_text'))
                <li class="nav-item"><a class="nav-link menu-item a_tools_site_footer px-3" href="{{ $urlAppend }}info/privacy_policy.php">{{ trans('langPrivacyPolicy') }}</a>
            @endif
        </ul>
        <div class='d-flex justify-content-start align-items-center'>
            <a class="copyright px-2" href='{{ $urlAppend }}info/copyright.php'>Open eClass - 2023 All rights reserved</a>
            @if(get_config('enable_social_sharing_links'))
                <a class="a_tools_site_footer px-2" href="https://www.facebook.com/" target="_blank"><i class="fab fa-facebook-f social-icon-tool"></i></a>
                <a class="a_tools_site_footer px-2" href="https://twitter.com/" target="_blank"><i class="fab fa-twitter social-icon-tool"></i></a>
                <a class="a_tools_site_footer px-2" href="https://linkedin.com/" target="_blank"><i class="fab fa-linkedin-in social-icon-tool"></i></a>
            @endif
        </div>
    </div>
</footer>

