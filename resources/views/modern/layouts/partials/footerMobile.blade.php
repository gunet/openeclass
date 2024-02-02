
<footer id="bgr-cheat-footer" class="site-footer mt-auto rounded-0">
    <div class="{{ $container }} footer-container">

        <div class="d-flex align-items-start flex-column h-100">

            @if($image_footer)
                <div class='col-12 d-flex justify-content-center align-items-center pb-3 gap-3'>
                    <img style='max-width:350px; max-height:150px; ' src='{{ $image_footer }}?<?php echo time(); ?>' alt="Available footer image">
                    <button class='footer-back-to-top hidden-xs' onclick="topFunction()" aria-label='Back to the top'><i class='fa-solid fa-chevron-up'></i></button>
                </div>
            @endif

            <div class='col-12 d-flex d-flex justify-content-center align-items-center pb-3 gap-3 flex-wrap'>

                
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

            <div class='col-12 border-bottom-footer'></div>

            <div class="col-12 mt-auto d-flex justify-content-between align-items-center flex-wrap gap-3 pt-3">
                <a class="copyright" href='{{$urlAppend}}info/copyright.php'>Copyright © {{ date('Y') }} All rights reserved</a>
                @if(get_config('enable_social_sharing_links'))
                    <div class='d-flex gap-3 justify-content-end'>
                        <a class='a_tools_site_footer' href="https://www.facebook.com/" target="_blank" aria-label="Facebook (opens new window)">
                            <i class="fab fa-facebook-f social-icon-tool"></i>
                        </a>
                        <a class='a_tools_site_footer' href="https://twitter.com/" target="_blank" aria-label="Twitter (opens new window)">
                            <i class="fab fa-twitter social-icon-tool"></i>
                        </a>
                        <a class='a_tools_site_footer' href="https://linkedin.com/" target="_blank" aria-label="Linkedin (opens new window)">
                            <i class="fab fa-linkedin-in social-icon-tool"></i>
                        </a>
                    </div>
                @endif
            </div>
            
        </div>

        
    </div>
</footer>
