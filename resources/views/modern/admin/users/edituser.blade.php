@extends('layouts.default_old')

@section('content')
    <div class="col-xl-10 col-lg-10 col-md-12 col-sm-12 col-12 justify-content-center col_maincontent_active">

        <div class="row p-5">

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

            {!! isset($action_bar) ?  $action_bar : '' !!}

            @if(Session::has('message'))
                <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mt-5'>
                    <p class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                        {{ Session::get('message') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </p>
            @endif

            <div class="offcanvas offcanvas-start d-lg-none mr-auto" tabindex="-1" id="collapseTools" aria-labelledby="offcanvasExampleLabel">
                <div class="offcanvas-header">
                    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                </div>
                <div class="offcanvas-body">
                    @include('layouts.partials.sidebarAdmin')
                </div>

            <div class="col-xl-10 col-lg-10 col-md-12 col-sm-12 col-12 justify-content-center col_maincontent_active">

                <div class="row p-lg-5 p-md-5 ps-1 pe-1 pt-5 pb-5">

            <div class='form-wrapper'>
                <form class='form-horizontal' role='form' name='edituser' method='post' action='{{ $_SERVER['SCRIPT_NAME'] }}' onsubmit='return validateNodePickerForm();'>
                <fieldset>
                    <div class='form-group'>
                        <label class='col-sm-2 control-label'>{{ trans('langSurname') }}</label>
                        <div class='col-sm-10'>
                            <input class='form-control' type='text' name='lname' size='50' value='{{ $info->surname }}'>
                        </div>
                    </div>
                    <div class='form-group'>
                        <label class='col-sm-2 control-label'>{{ trans('langName') }}</label>
                        <div class='col-sm-10'>
                            <input  class='form-control' type='text' name='fname' size='50' value='{{ $info->givenname }}'>
                        </div>
                    </div>
                    <div class='form-group'>
                        <label class='col-sm-2 control-label'>{{ trans('langUsername') }}</label>
                        @if (!in_array($info->password, $auth_ids))
                            <div class='col-sm-10'>
                                <input  class='form-control' type='text' name='username' size='50' value='{{ $info->username }}'>
                            </div>
                        @else
                            <div class='col-sm-10'>
                                <p class='form-control-static'>
                                    <b>{{ $info->username }}</b> [{{ $auth_info }}]
                                    <input  class='form-control' type='hidden' name='username' value="{{ $info->username }}">
                                </p>
                            </div>
                        @endif
                    </div>
                    <div class='form-group'>
                        <label class='col-sm-2 control-label'>e-mail</label>
                        <div class='col-sm-10'>
                            <input  class='form-control' type='text' name='email' size='50' value='{{ mb_strtolower(trim($info->email)) }}'>
                        </div>
                    </div>
                    <div class='form-group'>
                        <label class='col-sm-2 control-label'>{{ trans('langEmailVerified') }}: </label>
                        <div class='col-sm-10'>
                            {!! selection($verified_mail_data, "verified_mail", intval($info->verified_mail), "class='form-control'") !!}
                        </div>
                    </div>
                    <div class='form-group'>
                        <label class='col-sm-2 control-label'>{{ trans('langAm') }}: </label>
                        <div class='col-sm-10'>
                            <input  class='form-control' type='text' name='am' value='{{ $info->am }}'>
                        </div>
                    </div>
                    <div class='form-group'>
                        <label class='col-sm-2 control-label'>{{ trans('langTel') }}: </label>
                        <div class='col-sm-10'>
                            <input  class='form-control' type='text' name='phone' value='{{ $info->phone }}'>
                        </div>
                    </div>
                    <div class='form-group'>
                        <label class='col-sm-2 control-label'>{{ trans('langFaculty') }}:</label>
                        <div class='col-sm-10'>
                            {!! $html !!}
                        </div>
                    </div>
                    <div class='form-group'>
                        <label class='col-sm-2 control-label'>{{ trans('langProperty') }}:</label>
                        <div class='col-sm-10'>
                            @if ($info->status == USER_GUEST)
                                {!! selection(array(USER_GUEST => trans('langGuest')), 'newstatus', intval($info->status), "class='form-control'") !!}
                            @else
                                {!! selection(array(USER_TEACHER => trans('langTeacher'),
                                    USER_STUDENT => trans('langStudent')), 'newstatus', intval($info->status), "class='form-control'") !!}
                            @endif
                        </div>
                    </div>
                    <div class='form-group'>
                        <label class='col-sm-2 control-label'>{{ trans('langRegistrationDate') }}:</label>
                        <div class='col-sm-10'>
                            <p class='form-control-static'>{{ $reg_date->format("d-m-Y H:i") }}</p>
                        </div>
                    </div>
                    <div class='input-append date form-group'>
                        <label class='col-sm-2 control-label'>{{ trans('langExpirationDate') }}:</label>
                        <div class='col-sm-10'>
                            <div class='input-group'>
                                <input class='form-control' id='user_date_expires_at' name='user_date_expires_at' type='text' value='{{ $exp_date->format("d-m-Y H:i") }}'>
                                <span class='input-group-addon'><i class='fa fa-calendar'></i></span>
                            </div>
                        </div>
                    </div>
                    <div class='form-group'>
                        <label class='col-sm-2 control-label'>{{ trans('langUserID') }}: </label>
                        <div class='col-sm-10'>
                            <p class='form-control-static'>{{ $u }}</p>
                        </div>
                    </div>
                    <div class='form-group'>
                        <label class='col-sm-2 control-label'>{{ trans('langUserWhitelist') }}</label>
                        <div class='col-sm-10'>
                            <textarea rows='6' cols='60' name='user_upload_whitelist'>{{ $info->whitelist }}</textarea>
                        </div>
                    </div>
                    @if ($ext_uid)
                        <div class='form-group'>
                            <label class='col-sm-2 control-label'>$langProviderConnectWith:</label>
                            <div class='col-sm-10'>
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
                    <div class='col-sm-offset-2 col-sm-10'>
                        <input class='btn btn-primary' type='submit' name='submit_edituser' value='{{ trans('langModify') }}'>
                    </div>
                    </fieldset>
                    {!! generate_csrf_token_form_field() !!}
                </form>
            </div>
                <!--user is registered to courses-->
                @if (count($sql) > 0)
                    <h4>{{ trans('langStudentParticipation') }}</h4>
                    <div class='table-responsive'>
                        <table class='table-default'>
                            <tr>
                                <th class='text-left'>{{ trans('langCode') }}</th>
                                <th class='text-left'>{{ trans('langLessonName') }}</th>
                                <th>{{ trans('langCourseRegistrationDate') }}</th>
                                <th>{{ trans('langProperty') }}</th>
                                <th>{{ trans('langActions') }}</th>
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
                                                {{ nice_format($logs->reg_date) }};
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
                                                {!! icon('fa-ban', trans('langUnregCourse'), "unreguser.php?u=$u&amp;c=$logs->id") !!}
                                             </td>
                                        @endif
                                    </tr>
                            @endforeach
                        </table>
                    </div>
                @else
                    <div class='alert alert-warning'>{{ trans('langNoStudentParticipation') }}</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
