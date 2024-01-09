
<footer id="bgr-cheat-footer" class="site-footer mt-auto rounded-0">
    <div class="{{ $container }} footer-container">

        <div class="d-flex align-items-start flex-column h-100">

            <div class='d-flex w-100 border-bottom-footer pb-3 gap-5'>

                <div>
                    @if (!get_config('dont_display_about_menu'))
                        <div class='col-12 px-0 pb-2'>
                            <a class="a_tools_site_footer" href="{{$urlAppend}}info/about.php">
                                {{ trans('langPlatformIdentity') }}
                            </a>
                        </div>
                    @endif
                    @if (!get_config('dont_display_contact_menu'))
                        <div class='col-12 px-0 pb-2'>
                            <a class="a_tools_site_footer" href="{{$urlAppend}}info/contact.php">
                                {{ trans('langContact') }}
                            </a>
                        </div>
                    @endif
                
                    @if (!get_config('dont_display_manual_menu'))
                        <div class='col-12 px-0 pb-2'>
                            <a class="a_tools_site_footer" href="{{$urlAppend}}info/manual.php">
                                {{ trans('langManuals') }}
                            </a>
                        </div>
                    @endif
                    <div class='col-12 px-0 pb-2'>
                        <a class="a_tools_site_footer" href="{{$urlAppend}}info/terms.php">
                            {{ trans('langUsageTerms') }}
                        </a>
                    </div>
                    @if (get_config('activate_privacy_policy_text'))
                        <div class='col-12 px-0 pb-2'>
                            <a class="a_tools_site_footer" href="{{$urlAppend}}info/privacy_policy.php">
                                {{ trans('langPrivacyPolicy') }}
                            </a>
                        </div>
                    @endif
                </div>
                <div>
                    @if(!get_config('show_only_loginScreen'))
                        <div class='col-12 px-0 pb-2'>
                            <a class="a_tools_site_footer" href="{{$urlAppend}}main/system_announcements.php">
                                {{ trans('langAnnouncements')}}
                            </a>
                        </div>
                    @endif
                    @if(!get_config('show_only_loginScreen'))
                        @if (!get_config('dont_display_courses_menu'))
                            <div class='col-12 px-0 pb-2'>
                                <a class="a_tools_site_footer" href="{{ $urlAppend }}modules/auth/listfaculte.php">
                                    {{ trans('langCourses') }}
                                </a>
                            </div>
                        @endif
                    @endif
                    @if(!get_config('show_only_loginScreen'))
                        <div class='col-12 px-0 pb-2'>
                            <a class="a_tools_site_footer" href="{{$urlAppend}}info/faq.php">
                                {{ trans('langFaq') }}
                            </a>
                        </div>
                    @endif
                </div>

                </div>
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
