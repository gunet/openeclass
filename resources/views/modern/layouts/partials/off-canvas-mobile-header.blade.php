<div class="offcanvas offcanvas-start d-lg-none offCanvas-Tools" tabindex="-1" id="offcanvasScrollingTools">
                <div class="offcanvas-header">
                    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                </div>
                <div class="offcanvas-body px-3">
                    <div class='col-12 mt-3 d-flex justify-content-center align-items-center'>
                        <img src="{{ $logo_img_small }}">
                    </div>
                    @if(get_config('enable_search'))
                        <div class='col-12 mt-5 d-flex justify-content-center align-items-center'>
                            @if(isset($course_code) and $course_code)
                                <form action="{{ $urlAppend }}modules/search/search_incourse.php?all=true" class='d-flex justify-content-center align-items-end w-100'>
                            @else
                                <form action="{{ $urlAppend }}modules/search/search.php" class='d-flex justify-content-center align-items-end w-100'>
                            @endif
                                    <input type="text" class="inputMobileSearch rounded-0 w-100 basic-value-cl" placeholder="{{ trans('langSearch')}}..." name="search_terms">
                                    <button class="btn d-flex justify-content-center align-items-center rounded-0" type="submit" name="quickSearch">
                                        <i class='fa fa-search small-text'></i>
                                    </button>
                                </form>
                        </div>
                    @endif
                    <div class='col-12 mt-5 mb-3'>
                        <ul class="list-group list-group-flush">
                            @if(!get_config('hide_login_link'))
                                <li class="list-group-item element">
                                    <a id='homeId' class='d-flex justify-content-start align-items-start gap-2 flex-wrap' type='button' href="{{ $urlServer }}">
                                        <i class="fa-solid fa-home"></i>{{ trans('langHome') }}
                                    </a>
                                </li>
                            @endif
                            @if (!isset($_SESSION['uid']))
                                @if(get_config('registration_link')!='hide')
                                    <li class="list-group-item element">
                                        <a id='registrationId' type="button" class='d-flex justify-content-start align-items-start gap-2 flex-wrap' href="{{ $urlAppend }}modules/auth/registration.php">
                                            <i class="fa-solid fa-pencil"></i>{{ trans('langRegistration') }}
                                        </a>
                                    </li>
                                @endif
                            @endif

                            @if (!get_config('dont_display_courses_menu'))
                                <li class="list-group-item element">
                                    <a id='coursesId' type='button' class='d-flex justify-content-start align-items-start gap-2 flex-wrap' href="{{ $urlAppend }}modules/auth/listfaculte.php">
                                        <i class="fa-solid fa-book"></i>{{ trans('langCourses') }}
                                    </a>
                                </li>
                            @endif
                           
                            <li class="list-group-item element">
                                <a id='faqId' type='button' class='d-flex justify-content-start align-items-start gap-2 flex-wrap' href="{{ $urlAppend }}info/faq.php">
                                    <i class="fa-solid fa-question-circle"></i>{{ trans('langFaq') }}
                                </a>
                            </li>
                            
                        </ul>
                    </div>

                </div>
            </div>