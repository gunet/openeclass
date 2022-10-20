@extends('layouts.default')

@section('content')

<div class="pb-lg-3 pt-lg-3 pb-0 pt-0">

    <div class="container-fluid main-container">

        <div class="row rowMedium">

            <div class="col-12 justify-content-center col_maincontent_active_Homepage">

                <div class="row p-lg-5 p-md-5 ps-1 pe-1 pt-5 pb-5">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])


                    @if(Session::has('message'))
                    <div class='col-12 all-alerts'>
                        <div class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                            @if(is_array(Session::get('message')))
                                @php $messageArray = array(); $messageArray = Session::get('message'); @endphp
                                @foreach($messageArray as $message)
                                    {!! $message !!}
                                @endforeach
                            @else
                                {!! Session::get('message') !!}
                            @endif
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                    @endif

                    {!! isset($action_bar) ?  $action_bar : '' !!}

                        <div class='col-lg-6 col-12 d-none d-md-none d-lg-block'>
                            <div class='col-12 h-100 left-form'></div>
                        </div>

                        <div class='col-lg-6 col-12'>
                            <div class='form-wrapper form-edit p-3 rounded'>

                                <form class='form-horizontal' role='form' name='edituser' method='post' action='{{ $_SERVER['SCRIPT_NAME'] }}' onsubmit='return validateNodePickerForm();'>
                                    <fieldset>
                                        <div class='form-group'>
                                            <label class='col-sm-12 control-label-notes'>{{ trans('langSurname') }}</label>
                                            <div class='col-sm-12'>
                                                <input class='form-control' type='text' name='lname' size='50' value='{{ $info->surname }}'>
                                            </div>
                                        </div>
                                        <div class='form-group mt-3'>
                                            <label class='col-sm-12 control-label-notes'>{{ trans('langName') }}</label>
                                            <div class='col-sm-12'>
                                                <input  class='form-control' type='text' name='fname' size='50' value='{{ $info->givenname }}'>
                                            </div>
                                        </div>
                                        <div class='form-group mt-3'>
                                            <label class='col-sm-12 control-label-notes'>{{ trans('langUsername') }}</label>
                                            @if (!in_array($info->password, $auth_ids))
                                                <div class='col-sm-12'>
                                                    <input  class='form-control' type='text' name='username' size='50' value='{{ $info->username }}'>
                                                </div>
                                            @else
                                                <div class='col-sm-12'>
                                                    <p class='form-control-static'>
                                                        <b>{{ $info->username }}</b> [{{ $auth_info }}]
                                                        <input  class='form-control' type='hidden' name='username' value="{{ $info->username }}">
                                                    </p>
                                                </div>
                                            @endif
                                        </div>
                                        <div class='form-group mt-3'>
                                            <label class='col-sm-12 control-label-notes'>e-mail</label>
                                            <div class='col-sm-12'>
                                                <input  class='form-control' type='text' name='email' size='50' value='{{ mb_strtolower(trim($info->email)) }}'>
                                            </div>
                                        </div>
                                        <div class='form-group mt-3'>
                                            <label class='col-sm-12 control-label-notes'>{{ trans('langEmailVerified') }}: </label>
                                            <div class='col-sm-12'>
                                                {!! selection($verified_mail_data, "verified_mail", intval($info->verified_mail), "class='form-control'") !!}
                                            </div>
                                        </div>
                                        <div class='form-group mt-3'>
                                            <label class='col-sm-12 control-label-notes'>{{ trans('langAm') }}: </label>
                                            <div class='col-sm-12'>
                                                <input  class='form-control' type='text' name='am' value='{{ $info->am }}'>
                                            </div>
                                        </div>
                                        <div class='form-group mt-3'>
                                            <label class='col-sm-12 control-label-notes'>{{ trans('langTel') }}: </label>
                                            <div class='col-sm-12'>
                                                <input  class='form-control' type='text' name='phone' value='{{ $info->phone }}'>
                                            </div>
                                        </div>
                                        <div class='form-group mt-3'>
                                            <label class='col-sm-12 control-label-notes'>{{ trans('langFaculty') }}:</label>
                                            <div class='col-sm-12'>
                                                {!! $html !!}
                                            </div>
                                        </div>
                                        <div class='form-group mt-3'>
                                            <label class='col-sm-12 control-label-notes'>{{ trans('langProperty') }}:</label>
                                            <div class='col-sm-12'>
                                                @if ($info->status == USER_GUEST)
                                                    {!! selection(array(USER_GUEST => trans('langGuest')), 'newstatus', intval($info->status), "class='form-control'") !!}
                                                @else
                                                    {!! selection(array(USER_TEACHER => trans('langTeacher'),
                                                        USER_STUDENT => trans('langStudent')), 'newstatus', intval($info->status), "class='form-control'") !!}
                                                @endif
                                            </div>
                                        </div>
                                        <div class='form-group mt-3'>
                                            <label class='col-sm-12 control-label-notes'>{{ trans('langRegistrationDate') }}:</label>
                                            <div class='col-sm-12'>
                                                <p class='form-control-static'>{{ $reg_date->format("d-m-Y H:i") }}</p>
                                            </div>
                                        </div>
                                        <div class='input-append date form-group mt-3'>
                                            <label class='col-sm-12 control-label-notes'>{{ trans('langExpirationDate') }}:</label>
                                            <div class='col-sm-12'>
                                                <div class='input-group'>
                                                    <input class='form-control' id='user_date_expires_at' name='user_date_expires_at' type='text' value='{{ $exp_date->format("d-m-Y H:i") }}'>
                                                    <span class='input-group-addon'><i class='fa fa-calendar'></i></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class='form-group mt-3'>
                                            <label class='col-sm-12 control-label-notes'>{{ trans('langUserID') }}: </label>
                                            <div class='col-sm-12'>
                                                <p class='form-control-static'>{{ $u }}</p>
                                            </div>
                                        </div>
                                        <div class='form-group mt-3'>
                                            <label class='col-sm-12 control-label-notes'>{{ trans('langUserWhitelist') }}</label>
                                            <div class='col-sm-12'>
                                                <textarea class='w-100' rows='6' name='user_upload_whitelist'>{{ $info->whitelist }}</textarea>
                                            </div>
                                        </div>
                                        @if ($ext_uid)
                                            <div class='form-group mt-3'>
                                                <label class='col-sm-12 control-label-notes'>{{trans('langProviderConnectWith')}}:</label>
                                                <div class='col-sm-12'>
                                                    <div class='row'>
                                                    @foreach ($ext_uid as $ext_uid_item)
                                                        <div class='col-xs-2 text-center'>
                                                            <img src='{{ $themeimg }}/{{ $auth_ids[$ext_uid_item->auth_id] }}.png' alt='{{ trans('langLoginVia') }}'>
                                                            <br>
                                                            {{ $authFullName[$ext_uid_item->auth_id] }}
                                                            <br>
                                                            <button type='submit' name='delete_ext_uid' value='{{ $ext_uid_item->auth_id }}'>
                                                                {{ trans('langProviderDeleteConnection') }}
                                                            </button>
                                                        </div>
                                                    @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        <!--show custom profile fields input-->
                                        @if ($info->status != USER_GUEST)
                                            {!! render_profile_fields_form(array('origin' => 'admin_edit_profile', 'user_id' => $u)) !!}
                                        @endif
                                        <input type='hidden' name='u' value='{{ $u }}'>
                                        <input type='hidden' name='u_submitted' value='1'>
                                        <input type='hidden' name='registered_at' value='{{ $info->registered_at }}'>
                                        {!! showSecondFactorChallenge() !!}
                                        <div class='col-12 mt-5'>
                                            <input class='btn btn-sm btn-primary submitAdminBtn w-100' type='submit' name='submit_edituser' value='{{ trans('langModify') }}'>
                                        </div>
                                    </fieldset>
                                    {!! generate_csrf_token_form_field() !!}
                                </form>
                            </div>
                        </div>

                        <!--user is registered to courses-->
                        @if (count($sql) > 0)
                            <div class='col-12 mt-3'>
                                <div class='shadow-sm p-3 rounded'>
                                    <h6 class='text-center text-uppercase text-primary fw-bold w-auto p-2 bg-light'>{{ trans('langStudentParticipation') }}</h6>
                                    <div class='table-responsive'>
                                        <table class='table-default'>
                                            <tr class='list-header'>
                                                <th class='text-left text-white'>{{ trans('langCode') }}</th>
                                                <th class='text-left text-white'>{{ trans('langLessonName') }}</th>
                                                <th class='text-white'>{{ trans('langCourseRegistrationDate') }}</th>
                                                <th class='text-white'>{{ trans('langProperty') }}</th>
                                                <th class='text-white'>{{ trans('langActions') }}</th>
                                            </tr>
                                            @foreach ($sql as $logs)
                                                @if ($logs->visible == COURSE_INACTIVE)
                                                    <tr class='not_visible'>
                                                @else
                                                    <tr>
                                                @endif
                                                        <td>
                                                            <a href='{{ $urlServer }}courses/{{ $logs->code }}/'>{{ $logs->code }}</a>
                                                        </td>
                                                        <td>{{ $logs->title }}</td>
                                                        <td class='text-center'>
                                                            @if (!$logs->reg_date)
                                                                {{ trans('langUnknownDate') }}
                                                            @else
                                                                {{ format_locale_date(strtotime($logs->reg_date), 'short', false) }}
                                                            @endif
                                                        </td>
                                                        @if ($logs->status == USER_TEACHER)
                                                            <td class='text-center'>
                                                                {{ trans('langTeacher') }}
                                                            </td>
                                                            <td class='text-center'>---</td>
                                                        @else
                                                            <td class='text-center'>
                                                                @if ($logs->status == USER_STUDENT)
                                                                    {{ trans('langStudent') }}
                                                                @else
                                                                    {{ trans('langVisitor') }}
                                                                @endif
                                                            </td>
                                                            <td class='text-center'>
                                                                {!! icon('fa-times text-danger', trans('langUnregCourse'), "unreguser.php?u=$u&amp;c=$logs->id") !!}
                                                            </td>
                                                        @endif
                                                    </tr>
                                            @endforeach
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @else
                        <div class='col-12 mt-3'><div class='alert alert-warning'>{{ trans('langNoStudentParticipation') }}</div></div>
                        @endif

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
