@extends('layouts.default')

@section('content')


<div class="pb-3 pt-3">

    <div class="container-fluid main-container">

        <div class="row">

            <div id="background-cheat-leftnav" class="col-xl-2 col-lg-2 col-md-0 col-sm-0 col-0 justify-content-center col_sidebar_active"> 
                <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block">
                    @if($course_code)
                        @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                    @else
                        @include('layouts.partials.sidebarAdmin')
                    @endif 
                </div>
            </div>

            <div class="col-xl-10 col-lg-10 col-md-12 col-sm-12 col-12 justify-content-center col_maincontent_active">
                    
                <div class="row p-lg-5 p-md-5 ps-1 pe-2 pt-5 pb-5">

                        <nav class="navbar navbar-expand-lg navrbar_menu_btn">
                            <button type="button" id="menu-btn" class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block btn btn-primary menu_btn_button">
                                <i class="fas fa-align-left"></i>
                                <span></span>
                            </button>
                            <a class="btn btn-primary d-lg-none mr-auto" type="button" data-bs-toggle="offcanvas" href="#collapseTools" role="button" aria-controls="collapseTools" style="margin-top:-10px;">
                                <i class="fas fa-tools"></i>
                            </a>
                        </nav>

                        @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                        @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

                        <div class="offcanvas offcanvas-start d-lg-none mr-auto" tabindex="-1" id="collapseTools" aria-labelledby="offcanvasExampleLabel">
                            <div class="offcanvas-header">
                                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                            </div>
                            <div class="offcanvas-body">
                                @if($course_code)
                                    @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                                @else
                                    @include('layouts.partials.sidebarAdmin')
                                @endif
                            </div>
                        </div>

                        @if(Session::has('message'))
                        <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mt-5'>
                            <p class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                                {{ Session::get('message') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </p>
                        </div>
                        @endif

                    

                        @if ($user_registration)
                            @if ($registration_info)
                            <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mt-3'>
                                <div class='alert alert-info'>{{ $registration_info }}</div>
                            </div>
                            @else
                                <!-- student registration -->
                                @if ($eclass_stud_reg != FALSE or $alt_auth_stud_reg != FALSE)
                                    <div class='table-responsive mt-3'>
                                        <table class="announcements_table table">
                                            
                                            <tr class='notes_thead'>
                                                <th class='text-white'># {{ trans('langOfStudent') }}</th>
                                            </tr>
                            
                                            <tbody>
                                                @if ($eclass_stud_reg == 2) <!--  allow student registration via eclass -->
                                                    <tr>
                                                        <td>
                                                            <a class="text-primary fs-5" href='newuser.php{{ $provider }}{{$provider_user_data}}'>{{ trans('langUserAccountInfo2') }}</a>
                                                        </td>
                                                    </tr>
                                                @else ($eclass_stud_reg == 1) <!-- allow student registration via request -->
                                                    <tr>
                                                        <td>
                                                            <a class="text-primary fs-5"  href='formuser.php{{ $provider }}{{ $provider_user_data }}'>{{ trans('langUserAccountInfo1') }}</a>
                                                        </td>
                                                    </tr>
                                                @endif

                                                @if (count($auth) > 1 and $alt_auth_stud_reg != FALSE) <!-- allow user registration via alt auth methods -->
                                                    @if ($alt_auth_stud_reg == 2) <!-- registration -->
                                                        <tr>
                                                        <td>{{ trans('langUserAccountInfo4') }}:
                                                    @else
                                                        <tr>
                                                        <td>{{ trans('langUserAccountInfo1') }}:
                                                    @endif
                                                    @foreach ($auth as $k => $v)
                                                        @if ($v != 1)  <!--  bypass the eclass auth method -->
                                                            <!-- hybridauth registration is performed in newuser.php of formuser.php rather than altnewuser.php -->
                                                            @if ($v < 8) 
                                                                <a class="text-primary fs-5"  href='altnewuser.php?auth={{ $v }}'>{{ get_auth_info($v) }}</a>
                                                            @else
                                                                @if($eclass_stud_reg == 1) 
                                                                    <a class="text-primary fs-5" href='formuser.php?auth={{ $v }}'>{{ get_auth_info($v) }}</a>
                                                                @else
                                                                    <a class="text-primary fs-5" href='newuser.php?auth={{ $v }}'>{{ get_auth_info($v) }}</a>
                                                                @endif
                                                            @endif
                                                        @endif
                                                    @endforeach
                                                            </td>
                                                        </tr>
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mt-3'>
                                        <div class='alert alert-info'>{{ trans('langStudentCannotRegister') }}</div>
                                    </div>
                                @endif

                                <!--  teacher registration -->
                                @if ($eclass_prof_reg or $alt_auth_prof_reg)  <!-- allow teacher registration -->
                                    <div class='table-responsive mt-3'>
                                        <table class="announcements_table table">
                                            
                                            <tr class='notes_thead'>
                                                <th class='text-white'># {{ trans('langOfTeacher') }}</th>
                                            </tr>
                                            
                                            <tbody>
                                                @if ($eclass_prof_reg) 
                                                    @if (empty($provider)) 
                                                        <tr>
                                                            <td>
                                                                <a class="text-primary fs-5"  href='formuser.php?p=1'>{{ trans('langUserAccountInfo1') }} </a>
                                                            </td>
                                                        </tr>
                                                    @else 
                                                        <tr>
                                                            <td>
                                                                <a class="text-primary fs-5"  href='formuser.php{{ $provider }}{{ $provider_user_data}}&p=1'>{{ trans('langUserAccountInfo1') }}</a>
                                                            </td>
                                                        </tr>
                                                    @endif    
                                                @endif
                                                @if (count($auth) > 1 and $alt_auth_prof_reg)
                                                    <tr>
                                                        <td> {{ trans('langUserAccountInfo1') }} {{ trans('langWith') }}:
                                                        @foreach ($auth as $k => $v)
                                                            @if ($v != 1)   <!-- bypass the eclass auth method -->
                                                                <!-- hybridauth registration is performed in newuser.php rather than altnewuser -->
                                                                @if ($v < 8) 
                                                                    @if ($alt_auth_prof_reg) 
                                                                    <a class="text-primary fs-5"  href='altnewuser.php?auth={{ $v }}&p=1'>{{ get_auth_info($v) }}</a>
                                                                    @else 
                                                                    <a class="text-primary fs-5"  href='altnewuser.php?auth={{ $v }}'>{{ get_auth_info($v) }}</a>
                                                                    @endif
                                                                @else 
                                                                    @if ($alt_auth_prof_reg) 
                                                                        <a class="text-primary fs-5"  href='formuser.php?auth={{ $v }}&p=1'>{{ get_auth_info($v) }}</a>
                                                                    @else 
                                                                        <a class="text-primary fs-5"  href='newuser.php?auth={{ $v }}&p=1'>{{ get_auth_info($v) }}</a>
                                                                    @endif    
                                                                @endif
                                                            @endif
                                                        @endforeach
                                                        </td>
                                                    </tr>
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                @else 
                                <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mt-3'>
                                    <div class='alert alert-info'>{{ trans('langTeacherCannotRegister') }}</div>
                                </div>
                                @endif
                            @endif
                        @else
                        <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mt-3'>
                            <div class='alert alert-info'>{{ trans('langCannotRegister') }}</div>
                        </div>
                        @endif
                            
                    </div>
                
            </div>
            
        </div>

    </div>
</div>



                    


@endsection