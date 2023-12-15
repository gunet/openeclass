
<footer id="bgr-cheat-footer" class="site-footer mt-auto rounded-0">
    <div class="{{ $container }} footer-container d-flex justify-content-center align-items-center">
        <div class="row m-auto w-100">

            
                <div class="col-12 px-0 mb-3 pb-4 border-bottom-footer">
                    <div class='row m-auto'>
                        
                            @if (!get_config('dont_display_about_menu'))
                                <div class='col-12 px-0 pb-2'><a class="a_tools_site_footer" href="{{$urlAppend}}info/about.php">{{ trans('langPlatformIdentity') }}</a></div>
                            @endif
                            @if (!get_config('dont_display_contact_menu'))
                                <div class='col-12 px-0 pb-2'><a class="a_tools_site_footer" href="{{$urlAppend}}info/contact.php">{{ trans('langContact') }}</a></div>
                            @endif
                       
                            @if (!get_config('dont_display_manual_menu'))
                                <div class='col-12 px-0 pb-2'><a class="a_tools_site_footer" href="{{$urlAppend}}info/manual.php">{{ trans('langManuals') }}</a></div>
                            @endif
                                <div class='col-12 px-0 pb-2'><a class="a_tools_site_footer" href="{{$urlAppend}}info/terms.php">{{ trans('langUsageTerms') }}</a></div>
                        

                        @if (get_config('activate_privacy_policy_text'))
                            <div class='col-12 px-0'>
                                <a class="a_tools_site_footer" href="{{$urlAppend}}info/privacy_policy.php">{{ trans('langPrivacyPolicy') }}</a>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="col-12 d-flex justify-content-between align-items-start flex-wrap gap-4 px-0">
                    <a class="copyright pt-2" href='{{$urlAppend}}info/copyright.php'>2023 All rights reserved</a>
                    @if(get_config('enable_social_sharing_links'))
                        <div class='d-flex gap-4 justify-content-end'>
                            <a class='a_tools_site_footer' href="https://www.facebook.com/" target="_blank"><i class="fab fa-facebook-f social-icon-tool"></i></a>
                            <a class='a_tools_site_footer' href="https://twitter.com/" target="_blank"><i class="fab fa-twitter social-icon-tool"></i></a>
                            <a class='a_tools_site_footer' href="https://linkedin.com/" target="_blank"><i class="fab fa-linkedin-in social-icon-tool"></i></a>
                        </div>
                    @endif
                </div>
           
        </div>
    </div>
</footer>
