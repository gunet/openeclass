@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
    
            <div class='about-content about-content-1 d-flex flex-column align-items-center'>
                <div class='{{ $container }} padding-default p-lg-5'>
                    <div class='about-organization-name text-center'>
                        <div class="about-title">{{ trans('langInstituteShortNameSecondary') }}</div>
                        <a href='{{ $institution_url }}' target='_blank' class='mainpage about-value' aria-label='{{ $institution }}'>{{ $institution }}</a>
                    </div>
                    <div class='about-site-name text-center mt-5'>
                        <div class="about-title">{{ trans('langCampusName') }}</div>
                        <p class='form-label about-value'>{{ $siteName }}</p>
                    </div>
                    <div class='about-version text-center mt-5'>
                        <div class="about-title">{{ trans('langVersion') }}</div>
                        <a class='about-value' href='http://www.openeclass.org/' title='Open eClass Portal' target='_blank' aria-label='{{ $eclass_version }}'>{{ $eclass_version }}</a>
                    </div>
                    <div class='about-support-user text-center mt-5'>
                        <div class="about-title">{{ trans('langSupportUser') }}</div>
                        <p class='form-label about-value'>{{ $admin_name }}</p>
                    </div>
                </div>
            </div>

            <div class='about-content about-content-2'>
                <div class='{{ $container }} padding-default'>
                    <div class='row row-cols-1 g-4 w-100 row-about-courses mb-5'>
                        <div class='col'>
                            <div class='card panelCard card-transparent card-about-courses h-100 border-0'>
                                <div class='card-body d-flex align-items-center'>
                                    <div class='row m-auto w-100'>
                                        <div class='col-lg-3 col-md-6 col-12 text-center'>
                                            <div class='about-badge text-heading-h2 badge Primary-600-bg p-4 rounded-circle' style='font-size:24px;'>{{ $course_inactive }}</div>
                                            <p class='TextBold text-dark'>{{ trans('langCourses') }}</p>
                                        </div>
                                        <div class='col-lg-3 col-md-6 col-12 col-12 text-center mt-md-0 mt-4'>
                                            <div class='about-badge text-heading-h2 badge Success-200-bg p-4 rounded-circle' style='font-size:24px;'>{{ $course_open }}</div>
                                            <p class='TextBold text-dark'>{{ trans('langOpenCoursesShort') }}</p>
                                        </div>
                                        <div class='col-lg-3 col-md-6 col-12 col-12 text-center mt-lg-0 mt-4'>
                                            <div class='about-badge text-heading-h2 badge Warning-200-bg p-4 rounded-circle' style='font-size:24px;'>{{ $course_registration }}</div>
                                            <p class='TextBold text-dark'>{{ trans('langOpenCourseWithRegistration') }}</p>
                                        </div>
                                        <div class='col-lg-3 col-md-6 col-12 col-12 text-center mt-lg-0 mt-4'>
                                            <div class='about-badge text-heading-h2 badge Accent-200-bg p-4 rounded-circle' style='font-size:24px;'>{{ $course_closed }}</div>
                                            <p class='TextBold text-dark'>{{ trans('langClosedCourses') }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class='row row-cols-1 g-4 w-100 row-about-students'>
                        <div class='col'>
                            <div class='card panelCard card-transparent card-about-students h-100 border-0'>
                                <div class='card-body d-flex align-items-center'>
                                    <div class='row m-auto w-100'>
                                        <div class='col-lg-3 col-md-6 col-12 text-center'>
                                            <div class='about-badge text-heading-h2 badge Primary-600-bg p-4 rounded-circle' style='font-size:24px;'>{{ $count_total }}</div>
                                            <p class='TextBold text-dark'>{{ trans('langUsers') }}</p>
                                        </div>
                                        <div class='col-lg-3 col-md-6 col-12 text-center mt-md-0 mt-4'>
                                            <div class='about-badge text-heading-h2 badge Success-200-bg p-4 rounded-circle' style='font-size:24px;'>{{ $count_status[USER_TEACHER] }}</div>
                                            <p class='TextBold text-dark'>{{ trans('langTeachers') }}</p>
                                        </div>
                                        <div class='col-lg-3 col-md-6 col-12 text-center mt-lg-0 mt-4'>
                                            <div class='about-badge text-heading-h2 badge Warning-200-bg p-4 rounded-circle' style='font-size:24px;'>{{ $count_status[USER_STUDENT] }}</div>
                                            <p class='TextBold text-dark'>{{ trans('langStudents') }}</p>
                                        </div>
                                        <div class='col-lg-3 col-md-6 col-12 text-center mt-lg-0 mt-4'>
                                            <div class='about-badge text-heading-h2 badge Accent-200-bg p-4 rounded-circle' style='font-size:24px;'>{{ $count_status[USER_GUEST] }}</div>
                                            <p class='TextBold text-dark'>{{ trans('langGuest') }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        

       
 
</div>


<script type="text/javascript">
    setTimeout(function(){
        $('.about-content-1').addClass('show-animate');
        window.scrollTo({ top: 1, behavior: 'smooth' });
    }, 100);
</script>

@endsection
