
<footer id="bgr-cheat-footer" class="site-footer mt-auto d-flex justify-content-start align-items-center px-3">
<div class='container-fluid footer-container py-0 d-flex align-items-center'>
    <div class='col-12 d-flex justify-content-between align-items-center'>
        <ul class="container-items-footer nav">
            <li class="nav-item"><a class="nav-link menu-item a_tools_site_footer ps-2 pe-3" href="#">{{ trans('langPlatformIdentity') }}</a></li>
            <li class="nav-item"><a class="nav-link menu-item a_tools_site_footer px-3" href="#">{{ trans('langContact') }}</a></li>
            <li class="nav-item"><a class="nav-link menu-item a_tools_site_footer px-3" href="#">{{ trans('langManuals') }}</a></li>
            <li class="nav-item"><a class="nav-link menu-item a_tools_site_footer px-3" href="#">{{ trans('langUsageTerms') }}</a></li>
            @if (get_config('activate_privacy_policy_text'))
                <li class="nav-item"><a class="nav-link menu-item a_tools_site_footer px-3" href="#">{{ trans('langPrivacyPolicy') }}</a>
            @endif
        </ul>
        <div class='d-flex justify-content-start align-items-center'>
            <a class="copyright px-2" href='#'>Open eClass - {{ date('Y') }} All rights reserved</a>
            @if(get_config('enable_social_sharing_links'))
                <a class="a_tools_site_footer px-2" href="#" target="_blank" aria-label="Facebook (opens new window)"><i class="fab fa-facebook-f social-icon-tool"></i></a>
                <a class="a_tools_site_footer px-2" href="#" target="_blank" aria-label="Twitter (opens new window)"><i class="fab fa-twitter social-icon-tool"></i></a>
                <a class="a_tools_site_footer px-2" href="#" target="_blank" aria-label="Linkedin (opens new window)"><i class="fab fa-linkedin-in social-icon-tool"></i></a>
            @endif
        </div>
    </div>
</div>
</footer>