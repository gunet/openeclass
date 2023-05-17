<footer id="bgr-cheat-footer" class="site-footer mt-auto">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-4 col-xl-3 col-xxl-2">
                <ul class="ul_tools_site_footer">
                    <li class='mb-1'><a class="a_tools_site_footer" href="{{$urlAppend}}info/about.php">{{ trans('langPlatformIdentity') }}</a></li>
                    <li class='mb-1'><a class="a_tools_site_footer" href="{{$urlAppend}}info/contact.php">{{ trans('langContact') }}</a></li>
                   {{-- <li><a class="a_tools_site_footer" href="{{$urlAppend}}info/faq.php">{{ trans('langFaq') }}</a></li> --}} 
                   @if (get_config('activate_privacy_policy_text'))
                        <li><a class="a_tools_site_footer" href="{{$urlAppend}}info/privacy_policy.php">{{ trans('langPrivacyPolicy') }}</a></li>
                    @endif
                   
                </ul>
            </div>
            <div class="col-lg-4 col-xl-4 col-xxl-4">
                <ul class="ul_tools_site_footer">
                    <li class='mb-1'><a class="a_tools_site_footer" href="{{$urlAppend}}info/manual.php">{{ trans('langManuals') }}</a></li>
                    <li class='mb-1'><a class="a_tools_site_footer" href="{{$urlAppend}}info/terms.php">{{ trans('langUsageTerms') }}</a></li>
                    
                </ul>
            </div>
            <div class="col-lg-4 col-xl-5 col-xxl-6">
                @if(get_config('enable_social_sharing_links'))
                <ul class="social_meadia_ul">
                    <li>
                        <div class="div_social"><a href="https://www.facebook.com/" target="_blank"><i class="fab fa-facebook-f social-icon-tool"></i></a></div>
                    </li>
                    <li>
                        <div class="div_social"><a href="https://twitter.com/" target="_blank"><i class="fab fa-twitter social-icon-tool"></i></a></div>
                    </li>
                    <li>
                        <div class="div_social"><a href="https://linkedin.com/" target="_blank"><i class="fab fa-linkedin-in social-icon-tool"></i></a></div>
                    </li>
                </ul>
                <br><br><br>
                @endif
                <ul>
                    <li><a class="copyright pt-2 me-2" href='{{$urlAppend}}info/copyright.php'>Open eClass - 2022 All rights reserved</a></li>
                </ul>
            </div>
        </div>
    </div>
</footer>
