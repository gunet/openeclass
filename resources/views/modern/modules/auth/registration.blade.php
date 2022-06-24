@extends('layouts.default')

@section('content')


<div class="pb-3 pt-3">

    <div class="container-fluid main-container">

        <div class="row">

            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 justify-content-center col_maincontent_registration col_maincontent_active">
                    
                    <div class="row p-5">

                                <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                                    <legend class="float-none w-auto py-2 px-4 notes-legend"><span style="margin-left:-20px;"><i class="fas fa-user"></i> Δημιουργία λογαριασμού</span></legend>
                                    <div class="row p-2"></div><div class="row p-2"></div>
                                </div>

                            

                                @if ($user_registration)
                                    @if ($registration_info)
                                        <div class='alert alert-info'>{{ $registration_info }}</div>
                                    @else
                                        <!-- student registration -->
                                        @if ($eclass_stud_reg != FALSE or $alt_auth_stud_reg != FALSE)
                                            <table class="table">
                                                <thead class="text-light thead_register">
                                                    <tr class='list-header'>
                                                        <th># {{ trans('langOfStudent') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @if ($eclass_stud_reg == 2) <!--  allow student registration via eclass -->
                                                        <tr>
                                                            <td>
                                                                <a class="new_user_new_account" href='newuser.php{{ $provider }}{{$provider_user_data}}'>{{ trans('langUserAccountInfo2') }}</a>
                                                            </td>
                                                        </tr>
                                                    @else ($eclass_stud_reg == 1) <!-- allow student registration via request -->
                                                        <tr>
                                                            <td>
                                                                <a class="new_user_new_account"  href='formuser.php{{ $provider }}{{ $provider_user_data }}'>{{ trans('langUserAccountInfo1') }}</a>
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
                                                                    <br><a class="new_user_new_account"  href='altnewuser.php?auth={{ $v }}'>{{ get_auth_info($v) }}</a>
                                                                @else
                                                                    @if($eclass_stud_reg == 1) 
                                                                        <br><a class="new_user_new_account" href='formuser.php?auth={{ $v }}'>{{ get_auth_info($v) }}</a>
                                                                    @else
                                                                        <br><a class="new_user_new_account" href='newuser.php?auth={{ $v }}'>{{ get_auth_info($v) }}</a>
                                                                    @endif
                                                                @endif
                                                            @endif
                                                        @endforeach
                                                                </td>
                                                            </tr>
                                                    @endif
                                                </tbody>
                                            </table>
                                        @else
                                            <div class='alert alert-info'>{{ trans('langStudentCannotRegister') }}</div>
                                        @endif

                                        <!--  teacher registration -->
                                        @if ($eclass_prof_reg or $alt_auth_prof_reg)  <!-- allow teacher registration -->
                                            <table class="table">
                                                <thead class="text-light thead_register">
                                                    <tr class='list-header'>
                                                        <th># {{ trans('langOfTeacher') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @if ($eclass_prof_reg) 
                                                        @if (empty($provider)) 
                                                            <tr>
                                                                <td>
                                                                    <a class="new_user_new_account"  href='formuser.php?p=1'>{{ trans('langUserAccountInfo1') }} </a>
                                                                </td>
                                                            </tr>
                                                        @else 
                                                            <tr>
                                                                <td>
                                                                    <a class="new_user_new_account"  href='formuser.php{{ $provider }}{{ $provider_user_data}}&p=1'>{{ trans('langUserAccountInfo1') }}</a>
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
                                                                        <br><a class="new_user_new_account"  href='altnewuser.php?auth={{ $v }}&p=1'>{{ get_auth_info($v) }}</a>
                                                                        @else 
                                                                        <br><a class="new_user_new_account"  href='altnewuser.php?auth={{ $v }}'>{{ get_auth_info($v) }}</a>
                                                                        @endif
                                                                    @else 
                                                                        @if ($alt_auth_prof_reg) 
                                                                            <br><a class="new_user_new_account"  href='formuser.php?auth={{ $v }}&p=1'>{{ get_auth_info($v) }}</a>
                                                                        @else 
                                                                            <br><a class="new_user_new_account"  href='newuser.php?auth={{ $v }}&p=1'>{{ get_auth_info($v) }}</a>
                                                                        @endif    
                                                                    @endif
                                                                @endif
                                                            @endforeach
                                                            </td>
                                                        </tr>
                                                    @endif
                                                </tbody>
                                            </table>
                                        @else 
                                            <div class='alert alert-info'>{{ trans('langTeacherCannotRegister') }}</div>
                                        @endif
                                    @endif
                                @else
                                    <div class='alert alert-info'>{{ trans('langCannotRegister') }}</div>
                                @endif
                            
                    </div>
                
            </div>
            
        </div>

    </div>
</div>



                    


@endsection