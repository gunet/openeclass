@extends('layouts.default')

@section('content')


<div class="col-12 basic-section p-xl-5 px-lg-3 py-lg-5">

        <div class="row rowMargin">

            <div class="col-12 col_maincontent_active_Homepage">
                    
                <div class="row">

                        @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                        @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

                        {!! $action_bar !!}

                        @if(Session::has('message'))
                        <div class='col-12 all-alerts'>
                            <div class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                                @php 
                                    $alert_type = '';
                                    if(Session::get('alert-class', 'alert-info') == 'alert-success'){
                                        $alert_type = "<i class='fa-solid fa-circle-check fa-lg'></i>";
                                    }elseif(Session::get('alert-class', 'alert-info') == 'alert-info'){
                                        $alert_type = "<i class='fa-solid fa-circle-info fa-lg'></i>";
                                    }elseif(Session::get('alert-class', 'alert-info') == 'alert-warning'){
                                        $alert_type = "<i class='fa-solid fa-triangle-exclamation fa-lg'></i>";
                                    }else{
                                        $alert_type = "<i class='fa-solid fa-circle-xmark fa-lg'></i>";
                                    }
                                @endphp
                                
                                @if(is_array(Session::get('message')))
                                    @php $messageArray = array(); $messageArray = Session::get('message'); @endphp
                                    {!! $alert_type !!}<span>
                                    @foreach($messageArray as $message)
                                        {!! $message !!}
                                    @endforeach</span>
                                @else
                                    {!! $alert_type !!}<span>{!! Session::get('message') !!}</span>
                                @endif
                                
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        </div>
                        @endif

                        @if ($user_registration)
                             @if ($registration_info)
                                <div class='col-12 mb-4'>
                                    <div class='alert alert-info'><i class='fa-solid fa-circle-info fa-lg'></i><span>{!! $registration_info !!}</span></div>
                                </div>
                            @endif
                        @endif

                        @if ($user_registration)
                            <div class='col-12'>
                                <div class="row row-cols-1 row-cols-md-2 g-4">
                                    @if (!$registration_info)
                                        <!-- student registration -->
                                        @if ($eclass_stud_reg != FALSE or $alt_auth_stud_reg != FALSE)  
                                            <div class='col'>
                                                <div class="card panelCard px-lg-4 py-lg-3 h-100">
                                                    <div class='card-header border-0 bg-white d-flex justify-content-between align-items-center'>
                                                        <div class='text-uppercase normalColorBlueText TextBold fs-6'>{{ trans('langOfStudent') }}</div>
                                                    </div>
                                                    <div class='card-body'>

                                                        <ul>
                                                            @if ($eclass_stud_reg == 2) <!--  allow student registration via eclass -->
                                                                <li><a href='newuser.php{{ $provider }}{{$provider_user_data}}'>{{ trans('langUserAccountInfo2') }}</a></li>
                                                            @else ($eclass_stud_reg == 1) <!-- allow student registration via request -->
                                                                <li><a href='formuser.php{{ $provider }}{{ $provider_user_data }}'>{{ trans('langUserAccountInfo1') }}</a></li>
                                                            @endif
                                                        </ul>

                                                        @if (count($auth) > 1 and $alt_auth_stud_reg != FALSE) <!-- allow user registration via alt auth methods -->
                                                            @if ($alt_auth_stud_reg == 2) <!-- registration -->
                                                                <p class='TextBold blackBlueText mb-1 mt-4'>{{ trans('langUserAccountInfo4') }}:</p>
                                                            @else
                                                                <p class='TextBold blackBlueText mb-1 mt-4'>{{ trans('langUserAccountInfo1') }}:</p>
                                                            @endif
                                                            <ul>
                                                                @foreach ($auth as $k => $v)
                                                                    @if ($v != 1)  <!--  bypass the eclass auth method -->
                                                                        <!-- hybridauth registration is performed in newuser.php of formuser.php rather than altnewuser.php -->
                                                                        @if ($v < 8) 
                                                                            <li><a href='altnewuser.php?auth={{ $v }}'>{{ get_auth_info($v) }}</a></li>
                                                                        @else
                                                                            @if($eclass_stud_reg == 1) 
                                                                                <li><a href='formuser.php?auth={{ $v }}'>{{ get_auth_info($v) }}</a></li>
                                                                            @else
                                                                                <li><a href='newuser.php?auth={{ $v }}'>{{ get_auth_info($v) }}</a></li>
                                                                            @endif
                                                                        @endif
                                                                    @endif
                                                                @endforeach
                                                            </ul>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <div class='col'>
                                                <div class="card panelCard px-lg-4 py-lg-3 h-100">
                                                    <div class='card-header border-0 bg-white d-flex justify-content-between align-items-center'>
                                                        <div class='text-uppercase normalColorBlueText TextBold fs-6'>{{ trans('langRegister') }}&nbsp{{ trans('langOfStudent') }}</div>
                                                    </div>
                                                    <div class='card-body'>
                                                        <p class='TextRegular blackBlueText'>{{ trans('langStudentCannotRegister') }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        <!--  teacher registration -->
                                        @if ($eclass_prof_reg or $alt_auth_prof_reg)  <!-- allow teacher registration -->
                                            <div class='col'>
                                                <div class="card panelCard px-lg-4 py-lg-3 h-100">
                                                    <div class='card-header border-0 bg-white d-flex justify-content-between align-items-center'>
                                                        <div class='text-uppercase normalColorBlueText TextBold fs-6'>{{ trans('langOfTeacher') }}</div>
                                                    </div>
                                                    <div class='card-body'>
                                                        @if ($eclass_prof_reg) 
                                                            <ul>
                                                                @if (empty($provider)) 
                                                                    <li><a href='formuser.php?p=1'>{{ trans('langUserAccountInfo1') }} </a></li>
                                                                @else 
                                                                    <li><a href='formuser.php{{ $provider }}{{ $provider_user_data}}&p=1'>{{ trans('langUserAccountInfo1') }}</a></li>
                                                                @endif    
                                                            </ul>
                                                        @endif
                                                        @if (count($auth) > 1 and $alt_auth_prof_reg)
                                                            <p class='TextBold blackBlueText mt-4 mb-1'>{{ trans('langUserAccountInfo1') }} {{ trans('langWith') }}:</p>
                                                            <ul>
                                                                @foreach ($auth as $k => $v)
                                                                    @if ($v != 1)   <!-- bypass the eclass auth method -->
                                                                        <!-- hybridauth registration is performed in newuser.php rather than altnewuser -->
                                                                        @if ($v < 8) 
                                                                            @if ($alt_auth_prof_reg) 
                                                                            <li><a href='altnewuser.php?auth={{ $v }}&p=1'>{{ get_auth_info($v) }}</a></li>
                                                                            @else 
                                                                            <li><a href='altnewuser.php?auth={{ $v }}'>{{ get_auth_info($v) }}</a></li>
                                                                            @endif
                                                                        @else 
                                                                            @if ($alt_auth_prof_reg) 
                                                                                <li><a href='formuser.php?auth={{ $v }}&p=1'>{{ get_auth_info($v) }}</a></li>
                                                                            @else 
                                                                                <li><a href='newuser.php?auth={{ $v }}&p=1'>{{ get_auth_info($v) }}</a></li>
                                                                            @endif    
                                                                        @endif
                                                                    @endif
                                                                @endforeach
                                                            </li>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @else 
                                            <div class='col'>
                                                <div class='alert alert-info'><i class='fa-solid fa-circle-info fa-lg'></i><span>{{ trans('langTeacherCannotRegister') }}</span></div>
                                                    <div class='card-header border-0 bg-white d-flex justify-content-between align-items-center'>
                                                        <div class='text-uppercase normalColorBlueText TextBold fs-6'>{{ trans('langRegister') }}&nbsp{{ trans('langOfTeacher') }}</div>
                                                    </div>
                                                    <div class='card-body'>
                                                        <p class='TextRegular blackBlueText'>{{ trans('langTeacherCannotRegister') }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        @else
                            <div class='col-12'>
                                <div class='alert alert-info'><i class='fa-solid fa-circle-info fa-lg'></i><span>{{ trans('langCannotRegister') }}</span></div>
                            </div>
                        @endif
                            
                    </div>
                
            </div>
            
        </div>

    
</div>



                    


@endsection