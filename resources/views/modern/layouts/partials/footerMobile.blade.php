
<footer id="bgr-cheat-footer" class="site-footer mt-auto rounded-0">
    <div class="{{ $container }} footer-container">

        <div class="d-flex align-items-start flex-column h-100">

            @if($image_footer)
                <div class='col-12 d-flex justify-content-md-between justify-content-center align-items-center py-3'>
                    <img style='max-width:350px; max-height:200px; ' src='{{ $image_footer }}?<?php echo time(); ?>'>
                    <a class='footer-back-to-top hidden-xs' href='#bgr-cheat-header'><i class='fa-solid fa-chevron-up back-to-top-icon'></i></a>
                </div>
            @endif

            <div class='d-flex w-100 border-bottom-footer pb-3 gap-3 flex-wrap'>

                
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

            <div class="mt-auto w-100 d-flex justify-content-between align-items-center flex-wrap gap-3 pt-3">
                <a class="copyright" href='{{$urlAppend}}info/copyright.php'>2003 - 2024 -- All rights reserved</a>
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
            
        </div>

        
    </div>
</footer>
