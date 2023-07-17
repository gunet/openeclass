@extends('layouts.default')

@section('content')


<div class="col-12 basic-section p-xl-5 px-lg-3 py-lg-5">

        <div class="row rowMargin">

            <div class="col-12 col_maincontent_active_Homepage">
                    
                <div class="row">

                        <div class='col-12'>
                            <h1>{{ trans('langRegistration')}}</h1>
                        </div>

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
                            <div class='col-12 mt-4'>
                                <div class='row rowMargin row-cols-1 row-cols-lg-2 g-lg-5'>
                                    <div class='col-lg-6 col-12'>
                                        <ul class="nav nav-tabs" id="myRegistration">
                                            <li class="nav-item">
                                                <button class="nav-link active" id="reg-student" data-bs-toggle="tab" data-bs-target="#regStudent" type="button" role="tab" aria-controls="regStudent" aria-selected="true" aria-current="page">{{ trans('langOfStudent') }}</button>
                                            </li>
                                            <li class="nav-item">
                                                <button class="nav-link" id="reg-teacher" data-bs-toggle="tab" data-bs-target="#regTeacher" type="button" role="tab" aria-controls="regTeacher" aria-selected="true" aria-current="page">{{ trans('langOfTeacher') }}</button>
                                            </li>
                                        </ul>

                                        <div class="tab-content mt-5" id="myContentRegistration">
                                            @if (!$registration_info)
                                                <div class="tab-pane fade show active" id="regStudent" role="tabpanel" aria-labelledby="reg-student">
                                                    @if ($eclass_stud_reg != FALSE or $alt_auth_stud_reg != FALSE)  
                                                        <div class="col-12">
                                                            <ul class="list-group list-group-flush">
                                                                @if ($eclass_stud_reg == 2) <!--  allow student registration via eclass -->
                                                                    <li class="list-group-item border-bottom-list-group"><a class='TextBold text-decoration-underline' href='newuser.php{{ $provider }}{{$provider_user_data}}'>{{ trans('langUserAccountInfo2') }}</a></li>
                                                                @else ($eclass_stud_reg == 1) <!-- allow student registration via request -->
                                                                    <li class="list-group-item border-bottom-list-group"><a class='TextBold text-decoration-underline' href='formuser.php{{ $provider }}{{ $provider_user_data }}'>{{ trans('langUserAccountInfo1') }}</a></li>
                                                                @endif
                                                                @if (count($auth) > 1 and $alt_auth_stud_reg != FALSE) <!-- allow user registration via alt auth methods -->
                                                                    @foreach ($auth as $k => $v)
                                                                        @if ($v != 1)  <!--  bypass the eclass auth method -->
                                                                            <!-- hybridauth registration is performed in newuser.php of formuser.php rather than altnewuser.php -->
                                                                            @if ($v < 8) 
                                                                                <li class="list-group-item border-bottom-list-group"><a class='TextBold text-decoration-underline' href='altnewuser.php?auth={{ $v }}'>{{ get_auth_info($v) }}</a></li>
                                                                            @else
                                                                                @if($eclass_stud_reg == 1) 
                                                                                    <li class="list-group-item border-bottom-list-group"><a class='TextBold text-decoration-underline' href='formuser.php?auth={{ $v }}'>{{ get_auth_info($v) }}</a></li>
                                                                                @else
                                                                                    <li class="list-group-item border-bottom-list-group"><a class='TextBold text-decoration-underline' href='newuser.php?auth={{ $v }}'>{{ get_auth_info($v) }}</a></li>
                                                                                @endif
                                                                            @endif
                                                                        @endif
                                                                    @endforeach
                                                                @endif
                                                            </ul>
                                                        </div>
                                                    @else
                                                        <div class='col-12'>
                                                            <p class='TextRegular'>{{ trans('langStudentCannotRegister') }}</p>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="tab-pane fade" id="regTeacher" role="tabpanel" aria-labelledby="reg-teacher">
                                                    @if ($eclass_prof_reg or $alt_auth_prof_reg)  <!-- allow teacher registration -->
                                                        <div class='col-12'>
                                                            <ul class="list-group list-group-flush">
                                                                @if ($eclass_prof_reg) 
                                                                    @if (empty($provider)) 
                                                                        <li class="list-group-item border-bottom-list-group"><a class='TextBold text-decoration-underline' href='formuser.php?p=1'>{{ trans('langUserAccountInfo1') }} </a></li>
                                                                    @else 
                                                                        <li class="list-group-item border-bottom-list-group"><a class='TextBold text-decoration-underline' href='formuser.php{{ $provider }}{{ $provider_user_data}}&p=1'>{{ trans('langUserAccountInfo1') }}</a></li>
                                                                    @endif    
                                                                @endif
                                                                @if (count($auth) > 1 and $alt_auth_prof_reg)
                                                                    @foreach ($auth as $k => $v)
                                                                        @if ($v != 1)   <!-- bypass the eclass auth method -->
                                                                            <!-- hybridauth registration is performed in newuser.php rather than altnewuser -->
                                                                            @if ($v < 8) 
                                                                                @if ($alt_auth_prof_reg) 
                                                                                <li class="list-group-item border-bottom-list-group"><a class='TextBold text-decoration-underline' href='altnewuser.php?auth={{ $v }}&p=1'>{{ get_auth_info($v) }}</a></li>
                                                                                @else 
                                                                                <li class="list-group-item border-bottom-list-group"><a class='TextBold text-decoration-underline' href='altnewuser.php?auth={{ $v }}'>{{ get_auth_info($v) }}</a></li>
                                                                                @endif
                                                                            @else 
                                                                                @if ($alt_auth_prof_reg) 
                                                                                    <li class="list-group-item border-bottom-list-group"><a class='TextBold text-decoration-underline' href='formuser.php?auth={{ $v }}&p=1'>{{ get_auth_info($v) }}</a></li>
                                                                                @else 
                                                                                    <li class="list-group-item border-bottom-list-group"><a class='TextBold text-decoration-underline' href='newuser.php?auth={{ $v }}&p=1'>{{ get_auth_info($v) }}</a></li>
                                                                                @endif    
                                                                            @endif
                                                                        @endif
                                                                    @endforeach
                                                                @endif
                                                            </ul>
                                                        </div>
                                                    @else 
                                                        <div class='col-12'>
                                                            <p class='TextRegular'>{{ trans('langTeacherCannotRegister') }}</p>
                                                        </div>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class='col-lg-6 col-12 mt-lg-0 mt-4'>
                                        <img src='{{ $urlAppend }}template/modern/img/RegImg.png' />
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class='col-12 mt-4'>
                                <div class='alert alert-info'><i class='fa-solid fa-circle-info fa-lg'></i><span>{{ trans('langCannotRegister') }}</span></div>
                            </div>
                        @endif
                            
                    </div>
                
            </div>
            
        </div>

    
</div>



                    


@endsection