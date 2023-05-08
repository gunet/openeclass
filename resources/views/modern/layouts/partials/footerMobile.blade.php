{{--
<footer id="bgr-cheat-footer" class="w-100 ms-0 mt-auto site-footerMobile"> 

    <div class='col-12 d-flex justify-content-center p-2'>

        <div class="btn-group w-100" role="group" aria-label="Basic example">
            <a class="btn btn-transparent text-white" href="{{$urlAppend}}info/about.php"><span class='fa fa-credit-card fa-fw text-white'></span></a>
            <a class="btn btn-transparent text-white" href="{{$urlAppend}}info/contact.php"><span class='fa fa-phone fa-fw fa-fw text-white'></span></a>
            
            <a class="btn btn-transparent text-white" href="{{$urlAppend}}info/manual.php"><span class='fa fa-file-video-o fa-fw text-white'></span></a>
            <a class="btn btn-transparent text-white" href="{{$urlAppend}}info/terms.php"><span class='fa fa-gavel text-white'></span></a>
            @if (get_config('activate_privacy_policy_text'))
            <a class="btn btn-transparent text-white" href="{{$urlAppend}}info/privacy_policy.php"><span class='fas fa-shield-alt text-white'></span></a>
            @endif
        </div>
        
    </div>
</footer>
--}}


<footer id="bgr-cheat-footer" class="site-footer mt-auto rounded-0">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-4 col-xl-3 col-xxl-2">
                <ul class="ul_tools_site_footer">
                    <li class='mb-1'><a class="a_tools_site_footer" href="{{$urlAppend}}info/about.php">{{ trans('langPlatformIdentity') }}</a></li>
                    <li class='mb-1'><a class="a_tools_site_footer" href="{{$urlAppend}}info/contact.php">{{ trans('langContact') }}</a></li>
                   
                   <li><a class="a_tools_site_footer" href="{{$urlAppend}}info/privacy_policy.php">{{ trans('langPrivacyPolicy') }}</a></li>
                </ul>
            </div>
            <div class="col-lg-4 col-xl-4 col-xxl-4">
                <ul class="ul_tools_site_footer">
                    <li class='mb-1'><a class="a_tools_site_footer" href="{{$urlAppend}}info/manual.php">{{ trans('langManuals') }}</a></li>
                    <li class='mb-1'><a class="a_tools_site_footer" href="{{$urlAppend}}info/terms.php">{{ trans('langUsageTerms') }}</a></li>
                    @if (get_config('activate_privacy_policy_text'))
                   
                    @endif
                </ul>
            </div><hr>
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
