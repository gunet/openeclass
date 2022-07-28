<footer id="bgr-cheat-footer" class="site-footer mt-auto">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2 col-lg-2 col-xl-2 col-xxl-2">
                <ul class="ul_tools_site_footer">
                    <li><a class="a_tools_site_footer fs-6" href="{{$urlAppend}}info/about.php"><span class='fa fa-credit-card fa-fw text-warning'></span><span class='ps-2'>{{ trans('langPlatformIdentity') }}</span></a></li>
                    <li><a class="a_tools_site_footer fs-6" href="{{$urlAppend}}info/contact.php"><span class='fa fa-phone fa-fw fa-fw text-warning'></span><span class='ps-2'>{{ trans('langContact') }}</span></a></li>
                    <li><a class="a_tools_site_footer fs-6" href="{{$urlAppend}}info/privacy_policy.php"><span class='fas fa-shield-alt text-warning'></span><span class='ps-2'>{{ trans('langPrivacyPolicy') }}</span></a></li>
                </ul>
            </div>
            <div class="col-md-4 col-lg-4 col-xl-4 col-xxl-4">
                <ul class="ul_tools_site_footer">
                    <li><a class="a_tools_site_footer fs-6" href="{{$urlAppend}}info/manual.php"><span class='fa fa-file-video-o fa-fw text-warning'></span><span class='ps-2'>{{ trans('langManuals') }}</span></a></li>
                    <li><a class="a_tools_site_footer fs-6" href="{{$urlAppend}}info/terms.php"><span class='fa fa-gavel text-warning'></span><span class='ps-2'>{{ trans('langUsageTerms') }}</span></a></li>
                    <li><a class="a_tools_site_footer fs-6" href="{{$urlAppend}}info/faq.php"><span class='fa fa-question-circle fa-fw text-warning'></span><span class='ps-2'>{{ trans('langFaq') }}</span></a></li>
                </ul>
            </div>
            <div class="col-md-6 col-lg-6 col-xl-6 col-xxl-6">
                @if(get_config('enable_social_sharing_links'))
                <ul class="social_meadia_ul">
                    <li>
                        <div class="div_social"><a href="https://www.facebook.com/" target="_blank"><i class="fab fa-facebook-f"></i></a></div>
                    </li>
                    <li>
                        <div class="div_social"><a href="https://twitter.com/" target="_blank"><i class="fab fa-twitter"></i></a></div>
                    </li>
                    <li>
                        <div class="div_social"><a href="https://linkedin.com/" target="_blank"><i class="fab fa-linkedin-in"></i></a></div>
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
