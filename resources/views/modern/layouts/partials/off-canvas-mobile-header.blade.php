<div class="offcanvas offcanvas-start d-lg-none bg-white" tabindex="-1" id="offcanvasScrollingTools" aria-labelledby="offcanvasScrollingLabel">
                <div class="offcanvas-header">
                    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                </div>
                <div class="offcanvas-body">
                    <div class='col-12 mt-3'>
                        <img class=" bg-transparent m-auto d-block" src="{{ $logo_img }}" style='width:150px;'>
                    </div>
                    @if(get_config('enable_search'))
                        <div class='col-12 mt-5 d-flex justify-content-center align-items-center px-4'>
                            @if(isset($course_code) and $course_code)
                                <form action="{{ $urlAppend }}modules/search/search_incourse.php?all=true" class='d-flex justify-content-center align-items-end w-100'>
                            @else
                                <form action="{{ $urlAppend }}modules/search/search.php" class='d-flex justify-content-center align-items-end w-100'>
                            @endif
                                    <input type="text" class="inputMobileSearch w-100 basic-value-cl" placeholder="{{ trans('langSearch')}}..." name="search_terms">
                                    <button class="btn submitMobileSearch rounded-0 d-flex justify-content-center align-items-center" type="submit" name="quickSearch">
                                        <i class='fa fa-search small-text'></i>
                                    </button>
                                </form>
                        </div>
                    @endif
                    <div class='col-12 mt-5 mb-3'>
                        <ul class="list-group px-4">
                            @if(!get_config('hide_login_link'))
                                <a id='homeId' type='button' class="list-group-item list-group-item-action toolHomePage rounded-0 d-flex justify-content-start align-items-start" href="{{ $urlServer }}">
                                    <i class="fa fa-home pt-1 pe-1"></i>{{ trans('langHome') }}
                                </a>
                            @endif
                            @if (!isset($_SESSION['uid']))
                                @if(get_config('registration_link')!='hide')
                                    <a id='registrationId' type="button" class="list-group-item list-group-item-action toolHomePage rounded-0 d-flex justify-content-start align-items-start" href="{{ $urlAppend }}modules/auth/registration.php">
                                        <i class="fa fa-pencil pt-1 pe-1"></i>{{ trans('langRegistration') }}
                                    </a>
                                @endif
                            @endif
                            <a id='coursesId' type='button' class="list-group-item list-group-item-action toolHomePage rounded-0 d-flex justify-content-start align-items-start" href="{{ $urlAppend }}modules/auth/listfaculte.php">
                                <i class="fa fa-book pt-1 pe-1"></i>{{ trans('langCourses') }}
                            </a>
                           
                            <a id='faqId' type='button' class="list-group-item list-group-item-action toolHomePage rounded-0 d-flex justify-content-start align-items-start" href="{{ $urlAppend }}info/faq.php">
                                <i class="fa fa-question-circle pt-1 pe-1"></i><span class='ms-0'>{{ trans('langFaq') }}</span>
                            </a>
                            
                        </ul>
                    </div>

                </div>
            </div>