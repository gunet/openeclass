<footer id="bgr-cheat-footer" class="site-footer mt-auto d-flex justify-content-start align-items-center px-3">
    <div class='col-12 d-flex justify-content-between align-items-center'>
        <div class='d-flex justify-content-start align-items-center'>
            <a class="a_tools_site_footer px-2" href="{{ $urlAppend }}info/about.php">{{ trans('langPlatformIdentity') }}</a>
            <a class="a_tools_site_footer px-2" href="{{ $urlAppend }}info/contact.php">{{ trans('langContact') }}</a>
            <a class="a_tools_site_footer px-2" href="{{ $urlAppend }}info/manual.php">{{ trans('langManuals') }}</a>
            <a class="a_tools_site_footer px-2" href="{{ $urlAppend }}info/terms.php">{{ trans('langUsageTerms') }}</a>
            @if (get_config('activate_privacy_policy_text'))
                <a class="a_tools_site_footer px-2" href="{{ $urlAppend }}info/privacy_policy.php">{{ trans('langPrivacyPolicy') }}</a>
            @endif
        </div>
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