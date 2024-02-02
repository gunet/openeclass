
<footer id="bgr-cheat-footer" class="site-footer mt-auto d-flex justify-content-start align-items-center">
<div class='{{ $container }} footer-container py-0 d-flex align-items-center flex-wrap gap-3'>

    @if($image_footer)
        <div class='col-12 d-flex justify-content-center align-items-center pt-4 gap-3'>
            <img style='max-width:350px; max-height:150px; ' src='{{ $image_footer }}?<?php echo time(); ?>'>
            <button class='footer-back-to-top' onclick="topFunction()"><i class='fa-solid fa-chevron-up'></i></button>
        </div>
        <div class='col-12 d-flex d-flex justify-content-center align-items-center gap-3 flex-wrap'>
            @if (!get_config('dont_display_about_menu'))
                <div>
                    <a class="a_tools_site_footer" href="{{$urlAppend}}info/about.php">
                        {{ trans('langPlatformIdentity') }}
                    </a>
                </div>
            @endif
            @if (!get_config('dont_display_contact_menu'))
                <div>
                    <a class="a_tools_site_footer" href="{{$urlAppend}}info/contact.php">
                        {{ trans('langContact') }}
                    </a>
                </div>
            @endif
        
            @if (!get_config('dont_display_manual_menu'))
                <div>
                    <a class="a_tools_site_footer" href="{{$urlAppend}}info/manual.php">
                        {{ trans('langManuals') }}
                    </a>
                </div>
            @endif
            <div>
                <a class="a_tools_site_footer" href="{{$urlAppend}}info/terms.php">
                    {{ trans('langUsageTerms') }}
                </a>
            </div>
            @if (get_config('activate_privacy_policy_text'))
                <div>
                    <a class="a_tools_site_footer" href="{{$urlAppend}}info/privacy_policy.php">
                        {{ trans('langPrivacyPolicy') }}
                    </a>
                </div>
            @endif
        </div>
        <div class="col-12 mt-auto d-flex justify-content-center align-items-center flex-wrap gap-5 pb-4">
            <a class="copyright" href='{{$urlAppend}}info/copyright.php'>Copyright © 2024 All rights reserved</a>
            @if(get_config('enable_social_sharing_links'))
                <div class='d-flex gap-3 justify-content-end'>
                    <a class='a_tools_site_footer' href="https://www.facebook.com/" target="_blank">
                        <i class="fab fa-facebook-f social-icon-tool"></i>
                    </a>
                    <a class='a_tools_site_footer' href="https://twitter.com/" target="_blank">
                        <i class="fab fa-twitter social-icon-tool"></i>
                    </a>
                    <a class='a_tools_site_footer' href="https://linkedin.com/" target="_blank">
                        <i class="fab fa-linkedin-in social-icon-tool"></i>
                    </a>
                </div>
            @endif
        </div>

    @else
        <div class='col-12 d-flex justify-content-between align-items-center'>
            <ul class="container-items-footer nav">
                @if (!get_config('dont_display_about_menu'))
                    <li class="nav-item"><a class="nav-link menu-item a_tools_site_footer ps-2 pe-3" href="{{ $urlAppend }}info/about.php">{{ trans('langPlatformIdentity') }}</a></li>
                @endif
                @if (!get_config('dont_display_contact_menu'))
                    <li class="nav-item"><a class="nav-link menu-item a_tools_site_footer px-3" href="{{ $urlAppend }}info/contact.php">{{ trans('langContact') }}</a></li>
                @endif
                @if (!get_config('dont_display_manual_menu'))
                    <li class="nav-item"><a class="nav-link menu-item a_tools_site_footer px-3" href="{{ $urlAppend }}info/manual.php">{{ trans('langManuals') }}</a></li>
                @endif
                <li class="nav-item"><a class="nav-link menu-item a_tools_site_footer px-3" href="{{ $urlAppend }}info/terms.php">{{ trans('langUsageTerms') }}</a></li>
                @if (get_config('activate_privacy_policy_text'))
                    <li class="nav-item"><a class="nav-link menu-item a_tools_site_footer px-3" href="{{ $urlAppend }}info/privacy_policy.php">{{ trans('langPrivacyPolicy') }}</a>
                @endif
            </ul>
            <div class='d-flex justify-content-start align-items-center'>
                <a class="copyright px-2" href='{{ $urlAppend }}info/copyright.php'>Copyright © 2024 All rights reserved</a>
                @if(get_config('enable_social_sharing_links'))
                    <a class="a_tools_site_footer px-2" href="https://www.facebook.com/" target="_blank" aria-label="Facebook"><i class="fab fa-facebook-f social-icon-tool"></i></a>
                    <a class="a_tools_site_footer px-2" href="https://twitter.com/" target="_blank" aria-label="Twitter"><i class="fab fa-twitter social-icon-tool"></i></a>
                    <a class="a_tools_site_footer px-2" href="https://linkedin.com/" target="_blank" aria-label="Linkedin"><i class="fab fa-linkedin-in social-icon-tool"></i></a>
                @endif
            </div>
        </div>
    @endif

</div>
</footer>

