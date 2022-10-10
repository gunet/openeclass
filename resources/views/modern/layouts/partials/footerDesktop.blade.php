<footer id="bgr-cheat-footer" class="site-footer mt-auto">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-4 col-xl-3 col-xxl-3">
                <ul class="ul_tools_site_footer">
                    <li><a class="a_tools_site_footer fs-6 text-uppercase" href="{{$urlAppend}}info/about.php"><span class='fa fa-credit-card fa-fw colorPalette'></span><span class='ps-2'>{{ trans('langPlatformIdentity') }}</span></a></li>
                    <li><a class="a_tools_site_footer fs-6 text-uppercase" href="{{$urlAppend}}info/contact.php"><span class='fa fa-phone fa-fw fa-fw colorPalette'></span><span class='ps-2'>{{ trans('langContact') }}</span></a></li>
                    <li><a class="a_tools_site_footer fs-6 text-uppercase" href="{{$urlAppend}}info/faq.php"><span class='fa fa-question-circle fa-fw colorPalette'></span><span class='ps-2'>{{ trans('langFaq') }}</span></a></li>
                </ul>
            </div>
            <div class="col-lg-4 col-xl-4 col-xxl-4">
                <ul class="ul_tools_site_footer">
                    <li><a class="a_tools_site_footer fs-6 text-uppercase" href="{{$urlAppend}}info/manual.php"><span class='fa fa-file-video-o colorPalette'></span><span class='ps-2'>{{ trans('langManuals') }}</span></a></li>
                    <li><a class="a_tools_site_footer fs-6 text-uppercase" href="{{$urlAppend}}info/terms.php"><span class='fa fa-gavel colorPalette'></span><span class='ps-2'>{{ trans('langUsageTerms') }}</span></a></li>
                    @if (get_config('activate_privacy_policy_text'))
                    <li><a class="a_tools_site_footer fs-6 text-uppercase" href="{{$urlAppend}}info/privacy_policy.php"><span class='fas fa-shield-alt colorPalette'></span><span class='ps-2'>{{ trans('langPrivacyPolicy') }}</span></a></li>
                    @endif
                </ul>
            </div>
            <div class="col-lg-4 col-xl-5 col-xxl-5">
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
                <br><br><br><br>
                @endif
                <ul>
                    <li><a class="copyright" href='{{$urlAppend}}info/copyright.php'>Open eClass - 2022 All rights reserved</a></li>
                </ul>
            </div>
        </div>
    </div>
</footer>
