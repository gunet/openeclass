@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
    <div class='{{ $container }} main-container'>
        <div class="row m-auto">

            <div class='col-12'>
                <h2>{{ trans('langRegistration')}}</h2>
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

                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
            @endif


            @if ($user_registration)
                <div class='col-12 mt-4'>
                    <div class='row row-cols-1 row-cols-lg-2 m-auto g-4'>
                        <div class='col-lg-6 col-12 ps-0'>
                            @if ($eclass_stud_reg != FALSE or $alt_auth_stud_reg != FALSE)
                                <div class="col-12">
                                    <ul class="list-group list-group-flush">
                                        @if ($eclass_stud_reg == 2) <!--  allow student registration via eclass -->
                                            <li class="list-group-item element"><a class='TextBold' href='newuser.php{{ $provider }}{{$provider_user_data}}'>{{ trans('langUserAccountInfo2') }}</a></li>
                                        @elseif ($eclass_stud_reg == 1) <!-- allow student registration via request -->
                                            <li class="list-group-item element"><a class='TextBold' href='newuser.php{{ $provider }}{{ $provider_user_data }}'>{{ trans('langUserAccountInfo1') }}</a></li>
                                        @endif
                                        @if (count($auth) > 1 and $alt_auth_stud_reg != FALSE) <!-- allow user registration via alt auth methods -->
                                            @foreach ($auth as $k => $v)
                                                @if ($v != 1)  <!--  bypass the eclass auth method -->
                                                    @if ($v < 8)
                                                        <li class="list-group-item element"><a class='TextBold' href='altnewuser.php?auth={{ $v }}'>{{ get_auth_info($v) }}</a></li>
                                                    @else
                                                        @if($eclass_stud_reg == 1)
                                                            <li class="list-group-item element"><a class='TextBold' href='newuser.php?auth={{ $v }}'>{{ get_auth_info($v) }}</a></li>
                                                        @else
                                                            <li class="list-group-item element"><a class='TextBold' href='newuser.php?auth={{ $v }}'>{{ get_auth_info($v) }}</a></li>
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
                            @if ($registration_info)
                                <div class='alert alert-info'><i class='fa-solid fa-circle-info fa-lg'></i><span>{!! $registration_info !!}</span></div>
                            @endif
                        </div>
                        <div class='col-lg-6 col-12 d-none d-lg-block'>
                            <img class='form-image-modules' src='{!! get_registration_form_image() !!}' alt='{{ trans('langRegistration') }}'>
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

@endsection
