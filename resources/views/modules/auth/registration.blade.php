@extends('layouts.default')

@section('content')

{!! $action_bar !!}

@if ($user_registration)
    @if ($registration_info)
        <div class='alert alert-info'>{{ $registration_info }}</div>
    @else
        <!-- student registration -->
        @if ($eclass_stud_reg != FALSE or $alt_auth_stud_reg != FALSE)
            <table class='table-default table-responsive'>
                <tr class='list-header'>
                    <th>{{ trans('langOfStudent') }}</th>
                </tr>
            @if ($eclass_stud_reg == 2) <!--  allow student registration via eclass -->
                <tr>
                    <td>
                        <a href='newuser.php{{ $provider }}{{$provider_user_data}}'>{{ trans('langUserAccountInfo2') }}</a>
                    </td>
                </tr>
            @else ($eclass_stud_reg == 1) <!-- allow student registration via request -->
                <tr>
                    <td>
                        <a href='formuser.php{{ $provider }}{{ $provider_user_data }}'>{{ trans('langUserAccountInfo1') }}</a>
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
                            <br><a href='altnewuser.php?auth={{ $v }}'>{{ get_auth_info($v) }}</a>
                        @else
                            @if($eclass_stud_reg == 1) 
                                <br><a href='formuser.php?auth={{ $v }}'>{{ get_auth_info($v) }}</a>
                            @else
                                <br><a href='newuser.php?auth={{ $v }}'>{{ get_auth_info($v) }}</a>
                            @endif
                        @endif
                    @endif
                @endforeach
                        </td>
                    </tr>
            @endif
            </table>
        @else
            <div class='alert alert-info'>{{ trans('langStudentCannotRegister') }}</div>
        @endif

        <!--  teacher registration -->
        @if ($eclass_prof_reg or $alt_auth_prof_reg)  <!-- allow teacher registration -->
            <table class='table-default'>
            <tr class='list-header'>
                <th>{{ trans('langOfTeacher') }}</th>
            </tr>
            @if ($eclass_prof_reg) 
                @if (empty($provider)) 
                    <tr>
                        <td>
                            <a href='formuser.php?p=1'>{{ trans('langUserAccountInfo1') }} </a>
                        </td>
                    </tr>
                @else 
                    <tr>
                        <td>
                            <a href='formuser.php{{ $provider }}{{ $provider_user_data}}&p=1'>{{ trans('langUserAccountInfo1') }}</a>
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
                                   <br><a href='altnewuser.php?auth={{ $v }}&p=1'>{{ get_auth_info($v) }}</a>
                                @else 
                                   <br><a href='altnewuser.php?auth={{ $v }}'>{{ get_auth_info($v) }}</a>
                                @endif
                            @else 
                                @if ($alt_auth_prof_reg) 
                                    <br><a href='formuser.php?auth={{ $v }}&p=1'>{{ get_auth_info($v) }}</a>
                                @else 
                                    <br><a href='newuser.php?auth={{ $v }}&p=1'>{{ get_auth_info($v) }}</a>
                                @endif    
                            @endif
                        @endif
                    @endforeach
                    </td>
                </tr>
            @endif
            </table>
         @else 
            <div class='alert alert-info'>{{ trans('langTeacherCannotRegister') }}</div>
        @endif
    @endif
@else
    <div class='alert alert-info'>{{ trans('langCannotRegister') }}</div>
@endif
@endsection