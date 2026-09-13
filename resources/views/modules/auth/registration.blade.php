@extends('layouts.default')

@section('content')

<main id="main" class="col-12 main-section">
    <div class='{{ $container }} main-container'>
        <div class="row m-auto">

            <div class='col-12'>
                <h1>{{ trans('langRegistration')}}</h1>
            </div>

            @include('layouts.partials.show_alert') 

            @if ($user_registration)
                <div class='col-12 mt-4'>
                    <div class='row row-cols-1 row-cols-lg-2 m-auto g-4'>
                        <div class='col-lg-6 col-12 ps-0'>
                            @if ($alt_auth_stud_reg != FALSE or $eclass_stud_reg != FALSE)
                                <div class="col-12">
                                    <ul class="list-group list-group-flush">
                                        @if (count($auth) > 1 and $alt_auth_stud_reg != FALSE) <!-- allow user registration via alt auth methods -->
                                            @foreach ($auth as $k => $v)
                                                @if ($v != 1)  <!--  bypass the eclass auth method -->
                                                    @if ($v < 8)
                                                        <li class="list-group-item element"><a class='TextBold' href='altnewuser.php?auth={{ $v }}'>{{ get_auth_info($v) }}</a><br>{{ trans('langUserAccountHelp1') }}</li>
                                                    @else
                                                        @if($eclass_stud_reg == 1)
                                                            <li class="list-group-item element"><a class='TextBold' href='newuser.php?auth={{ $v }}'>{{ get_auth_info($v) }}</a><br>{{ trans('langUserAccountHelp2') }}</li>
                                                        @else
                                                            <li class="list-group-item element"><a class='TextBold' href='newuser.php?auth={{ $v }}'>{{ get_auth_info($v) }}</a><br>{{ trans('langUserAccountHelp3') }}</li>
                                                        @endif
                                                    @endif
                                                @endif
                                            @endforeach
                                        @endif
                                        @if ($eclass_stud_reg == 2) <!--  allow student registration via eclass -->
                                            <li class="list-group-item element"><a class='TextBold' href='newuser.php{{ $provider }}{{$provider_user_data}}'>{{ trans('langUserAccountInfo2') }}</a><br>{{ trans('langUserAccountHelp4') }}</li>
                                        @elseif ($eclass_stud_reg == 1) <!-- allow student registration via request -->
                                            <li class="list-group-item element"><a class='TextBold' href='newuser.php{{ $provider }}{{ $provider_user_data }}'>{{ trans('langUserAccountInfo1') }}</a><br>{{ trans('langUserAccountHelp5') }}</li>
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
                            <img class='form-image-modules form-image-registration' src='{!! get_registration_form_image() !!}' alt='{{ trans('langRegistration') }}'>
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
</main>

@endsection
