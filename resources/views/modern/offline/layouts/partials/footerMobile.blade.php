
<footer id="bgr-cheat-footer" class="site-footer mt-auto rounded-0">
    <div class="container-fluid footer-container d-flex justify-content-center align-items-center">
        <div class="row m-auto w-100">

            <div class="col-12">
                <ul class="ul_tools_site_footer">
                    <li class='mb-1'><a class="a_tools_site_footer" href="#">{{ trans('langPlatformIdentity') }}</a></li>
                    <li class='mb-1'><a class="a_tools_site_footer" href="#">{{ trans('langContact') }}</a></li>
                    @if (get_config('activate_privacy_policy_text'))
                        <li  class='mb-1'><a class="a_tools_site_footer" href="#">{{ trans('langPrivacyPolicy') }}</a></li>
                    @endif
                    <li class='mb-1'><a class="a_tools_site_footer" href="#">{{ trans('langManuals') }}</a></li>
                    <li class='mb-3'><a class="a_tools_site_footer" href="#">{{ trans('langUsageTerms') }}</a></li>
                </ul>
            </div>

            <div class="col-12">
                @if(get_config('enable_social_sharing_links'))
                    <ul class="social_meadia_ul">
                        <li>
                            <div class="div_social"><a href="#" target="_blank"><i class="fab fa-facebook-f social-icon-tool"></i></a></div>
                        </li>
                        <li>
                            <div class="div_social"><a href="#" target="_blank"><i class="fab fa-twitter social-icon-tool"></i></a></div>
                        </li>
                        <li>
                            <div class="div_social"><a href="#" target="_blank"><i class="fab fa-linkedin-in social-icon-tool"></i></a></div>
                        </li>
                    </ul>
                    <br><br><br>
                @endif
                <ul>
                    <li><a class="copyright pt-2" href='#'>Open eClass - 2022 All rights reserved</a></li>
                </ul>
            </div>
        </div>
    </div>
</footer>
