
<footer id="bgr-cheat-footer" class="site-footer mt-auto d-flex justify-content-start align-items-center">
    <div class='{{ $container }} footer-container d-flex align-items-center flex-wrap gap-3'>
        <div class='d-none d-lg-block w-100'>
            @if($image_footer)
                <div class='col-12 d-flex justify-content-center align-items-center gap-3 pt-3'>
                    @if(get_config('link_footer_image'))
                    <a href="{!! get_config('link_footer_image') !!}" target="_blank">
                        <img class='footer-image' src='{{ $image_footer }}?<?php echo time(); ?>' alt="{{ trans('langMetaImage') }}">
                    </a>
                    @else
                    <img class='footer-image' src='{{ $image_footer }}?<?php echo time(); ?>' alt="{{ trans('langMetaImage') }}">
                    @endif
                </div>
                @if(get_config('footer_intro'))
                    <div class='col-lg-8 col-12 d-flex justify-content-center align-items-center gap-3 p-3 footer-text m-auto'>
                        {!! get_config('footer_intro') !!}
                    </div>
                    <div class='col-lg-8 col-12 m-auto border-bottom-footer-text mb-3'></div>
                @endif
                <div class='col-12 d-flex d-flex justify-content-center align-items-center gap-3 flex-wrap mt-3'>
                    @if (!get_config('dont_display_about_menu'))
                        <div>
                            <a class="a_tools_site_footer" href="{{$urlAppend}}info/about.php" @if($_SESSION['provider'] == 'lti_publish') target="_blank" @endif>
                                {{ trans('langPlatformIdentity') }}
                            </a>
                        </div>
                    @endif
                    @if (!get_config('dont_display_contact_menu'))
                        <div>
                            <a class="a_tools_site_footer" href="{{$urlAppend}}info/contact.php" @if($_SESSION['provider'] == 'lti_publish') target="_blank" @endif>
                                {{ trans('langContact') }}
                            </a>
                        </div>
                    @endif

                    @if (!get_config('dont_display_manual_menu'))
                        <div>
                            <a class="a_tools_site_footer" href="{{$urlAppend}}info/manual.php" @if($_SESSION['provider'] == 'lti_publish') target="_blank" @endif>
                                {{ trans('langManuals') }}
                            </a>
                        </div>
                    @endif
                    <div>
                        <a class="a_tools_site_footer" href="{{$urlAppend}}info/terms.php" @if($_SESSION['provider'] == 'lti_publish') target="_blank" @endif>
                            {{ trans('langUsageTerms') }}
                        </a>
                    </div>
                    @if (get_config('activate_privacy_policy_text'))
                        <div>
                            <a class="a_tools_site_footer" href="{{$urlAppend}}info/privacy_policy.php" @if($_SESSION['provider'] == 'lti_publish') target="_blank" @endif>
                                {{ trans('langPrivacyPolicy') }}
                            </a>
                        </div>
                    @endif
                </div>
                <div class="col-12 d-flex justify-content-center align-items-center flex-wrap gap-5 mt-3 pb-3">
                    <a class="copyright" href='{{$urlAppend}}info/copyright.php' @if($_SESSION['provider'] == 'lti_publish') target="_blank" @endif>Copyright © {{ date('Y') }} All rights reserved</a>
                    @if(get_config('enable_social_sharing_links'))
                        <div class='d-flex gap-3 justify-content-end'>
                            @if (get_config('link_fb'))
                                <a class='a_tools_site_footer' href="{!! get_config('link_fb') !!}" target="_blank" aria-label="Facebook: {{ trans('langOpenNewTab') }}">
                                    <i class="fab fa-facebook-f social-icon-tool"></i>
                                </a>
                            @endif
                            @if (get_config('link_tw'))
                                <a class='a_tools_site_footer' href="{!! get_config('link_tw') !!}" target="_blank" aria-label="Twitter: {{ trans('langOpenNewTab') }}">
                                    <i class="fab fa-twitter social-icon-tool"></i>
                                </a>
                            @endif
                            @if (get_config('link_ln'))
                                <a class='a_tools_site_footer' href="{!! get_config('link_ln') !!}" target="_blank" aria-label="Linkedin: {{ trans('langOpenNewTab') }}">
                                    <i class="fab fa-linkedin-in social-icon-tool"></i>
                                </a>
                            @endif
                        </div>
                    @endif
                </div>

            @else
                @if(get_config('footer_intro'))
                    <div class='col-lg-8 col-12 d-flex justify-content-center align-items-center p-3 footer-text m-auto'>
                        {!! get_config('footer_intro') !!}
                    </div>
                    <div class='col-lg-8 col-12 m-auto border-bottom-footer-text mb-3'></div>
                @endif
                <nav class='col-12 d-flex justify-content-between align-items-center'>
                    <ul class="container-items-footer nav">
                        @if (!get_config('dont_display_about_menu'))
                            <li class="nav-item"><a class="nav-link menu-item a_tools_site_footer ps-2 pe-3" href="{{ $urlAppend }}info/about.php"  @if($_SESSION['provider'] == 'lti_publish') target="_blank" @endif>{{ trans('langPlatformIdentity') }}</a></li>
                        @endif
                        @if (!get_config('dont_display_contact_menu'))
                            <li class="nav-item"><a class="nav-link menu-item a_tools_site_footer px-3" href="{{ $urlAppend }}info/contact.php"  @if($_SESSION['provider'] == 'lti_publish') target="_blank" @endif>{{ trans('langContact') }}</a></li>
                        @endif
                        @if (!get_config('dont_display_manual_menu'))
                            <li class="nav-item"><a class="nav-link menu-item a_tools_site_footer px-3" href="{{ $urlAppend }}info/manual.php"  @if($_SESSION['provider'] == 'lti_publish') target="_blank" @endif>{{ trans('langManuals') }}</a></li>
                        @endif
                        <li class="nav-item"><a class="nav-link menu-item a_tools_site_footer px-3" href="{{ $urlAppend }}info/terms.php"  @if($_SESSION['provider'] == 'lti_publish') target="_blank" @endif>{{ trans('langUsageTerms') }}</a></li>
                        @if (get_config('activate_privacy_policy_text'))
                            <li class="nav-item"><a class="nav-link menu-item a_tools_site_footer px-3" href="{{ $urlAppend }}info/privacy_policy.php"  @if($_SESSION['provider'] == 'lti_publish') target="_blank" @endif>{{ trans('langPrivacyPolicy') }}</a>
                        @endif
                    </ul>
                    <div class='d-flex justify-content-start align-items-center'>
                        <a class="copyright px-2" href='{{ $urlAppend }}info/copyright.php' @if($_SESSION['provider'] == 'lti_publish') target="_blank" @endif>Copyright © {{ date('Y') }} All rights reserved</a>
                        @if(get_config('enable_social_sharing_links'))
                            @if (get_config('link_fb'))
                                <a class='a_tools_site_footer' href="{!! get_config('link_fb') !!}" target="_blank" aria-label="Facebook: {{ trans('langOpenNewTab') }}">
                                    <i class="fab fa-facebook-f social-icon-tool"></i>
                                </a>
                            @endif
                            @if (get_config('link_tw'))
                                <a class='a_tools_site_footer' href="{!! get_config('link_tw') !!}" target="_blank" aria-label="Twitter: {{ trans('langOpenNewTab') }}">
                                    <i class="fab fa-twitter social-icon-tool"></i>
                                </a>
                            @endif
                            @if (get_config('link_ln'))
                                <a class='a_tools_site_footer' href="{!! get_config('link_ln') !!}" target="_blank" aria-label="Linkedin: {{ trans('langOpenNewTab') }}">
                                    <i class="fab fa-linkedin-in social-icon-tool"></i>
                                </a>
                            @endif
                        @endif
                    </div>
                </nav>
            @endif
        </div>




        <div class='d-block d-lg-none w-100'>
            <div class="d-flex align-items-start flex-column h-100">
                @if($image_footer)
                    <div class='col-12 d-flex justify-content-center align-items-center pb-3 gap-3'>
                        @if(get_config('link_footer_image'))
                        <a href="{!! get_config('link_footer_image') !!}" target="_blank">
                            <img class='footer-image' src='{{ $image_footer }}?<?php echo time(); ?>' alt="{{ trans('langMetaImage') }}">
                        </a>
                        @else
                        <img class='footer-image' src='{{ $image_footer }}?<?php echo time(); ?>' alt="{{ trans('langMetaImage') }}">
                        @endif
                    </div>
                @endif
                @if(get_config('footer_intro'))
                    <div class='col-12 d-flex justify-content-center align-items-center gap-3 p-3 footer-text m-auto'>
                        {!! get_config('footer_intro') !!}
                    </div>
                    <div class='col-12 m-auto border-bottom-footer-text mb-3'></div>
                @endif
                <div class='col-12 d-flex d-flex justify-content-center align-items-center pb-3 gap-3 flex-wrap'>
                    @if (!get_config('dont_display_about_menu'))
                        <div>
                            <a class="a_tools_site_footer" href="{{$urlAppend}}info/about.php" @if($_SESSION['provider'] == 'lti_publish') target="_blank" @endif>
                                {{ trans('langPlatformIdentity') }}
                            </a>
                        </div>
                    @endif
                    @if (!get_config('dont_display_contact_menu'))
                        <div>
                            <a class="a_tools_site_footer" href="{{$urlAppend}}info/contact.php" @if($_SESSION['provider'] == 'lti_publish') target="_blank" @endif>
                                {{ trans('langContact') }}
                            </a>
                        </div>
                    @endif

                    @if (!get_config('dont_display_manual_menu'))
                        <div>
                            <a class="a_tools_site_footer" href="{{$urlAppend}}info/manual.php" @if($_SESSION['provider'] == 'lti_publish') target="_blank" @endif>
                                {{ trans('langManuals') }}
                            </a>
                        </div>
                    @endif
                    <div>
                        <a class="a_tools_site_footer" href="{{$urlAppend}}info/terms.php" @if($_SESSION['provider'] == 'lti_publish') target="_blank" @endif>
                            {{ trans('langUsageTerms') }}
                        </a>
                    </div>
                    @if (get_config('activate_privacy_policy_text'))
                        <div>
                            <a class="a_tools_site_footer" href="{{$urlAppend}}info/privacy_policy.php" @if($_SESSION['provider'] == 'lti_publish') target="_blank" @endif>
                                {{ trans('langPrivacyPolicy') }}
                            </a>
                        </div>
                    @endif
                </div>
                <div class='col-12 border-bottom-footer'></div>
                <div class="col-12 mt-auto d-flex justify-content-between align-items-center flex-wrap gap-3 pt-3">
                    <a class="copyright" href='{{$urlAppend}}info/copyright.php' @if($_SESSION['provider'] == 'lti_publish') target="_blank" @endif>Copyright © {{ date('Y') }} All rights reserved</a>
                    @if(get_config('enable_social_sharing_links'))
                        <div class='d-flex gap-3 justify-content-end'>
                            @if (get_config('link_fb'))
                                <a class='a_tools_site_footer' href="{!! get_config('link_fb') !!}" target="_blank" aria-label="Facebook: {{ trans('langOpenNewTab') }}">
                                    <i class="fab fa-facebook-f social-icon-tool"></i>
                                </a>
                            @endif
                            @if (get_config('link_tw'))
                                <a class='a_tools_site_footer' href="{!! get_config('link_tw') !!}" target="_blank" aria-label="Twitter: {{ trans('langOpenNewTab') }}">
                                    <i class="fab fa-twitter social-icon-tool"></i>
                                </a>
                            @endif
                            @if (get_config('link_ln'))
                                <a class='a_tools_site_footer' href="{!! get_config('link_ln') !!}" target="_blank" aria-label="Linkedin: {{ trans('langOpenNewTab') }}">
                                    <i class="fab fa-linkedin-in social-icon-tool"></i>
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</footer>
