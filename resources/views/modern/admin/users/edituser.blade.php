@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
<div class='{{ $container }} main-container'>
        <div class="row m-auto">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])


                    @include('layouts.partials.legend_view')

                    @if(isset($action_bar))
                        {!! $action_bar !!}
                    @else
                        <div class='mt-4'></div>
                    @endif


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




                        <div class='col-lg-6 col-12'>
                            <div class='form-wrapper form-edit border-0 px-0'>

                                <form class='form-horizontal' role='form' name='edituser' method='post' action='{{ $_SERVER['SCRIPT_NAME'] }}' onsubmit='return validateNodePickerForm();'>
                                    <fieldset>
                                        <div class='form-group'>
                                            <label class='col-sm-12 control-label-notes'>{{ trans('langSurname') }}</label>
                                            <div class='col-sm-12'>
                                                <input class='form-control' type='text' name='lname' size='50' value='{{ $info->surname }}'>
                                            </div>
                                        </div>
                                        <div class='form-group mt-4'>
                                            <label class='col-sm-12 control-label-notes'>{{ trans('langName') }}</label>
                                            <div class='col-sm-12'>
                                                <input  class='form-control' type='text' name='fname' size='50' value='{{ $info->givenname }}'>
                                            </div>
                                        </div>
                                        <div class='form-group mt-4'>
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
                                        <div class='form-group mt-4'>
                                            <label class='col-sm-12 control-label-notes'>e-mail</label>
                                            <div class='col-sm-12'>
                                                <input  class='form-control' type='text' name='email' size='50' value='{{ mb_strtolower(trim($info->email)) }}'>
                                            </div>
                                        </div>
                                        <div class='form-group mt-4'>
                                            <label class='col-sm-12 control-label-notes'>{{ trans('langEmailVerified') }}: </label>
                                            <div class='col-sm-12'>
                                                {!! selection($verified_mail_data, "verified_mail", intval($info->verified_mail), "class='form-control'") !!}
                                            </div>
                                        </div>

                                        <div class='form-group mt-4'>
                                            <label class='col-sm-12 control-label-notes'>{{ trans('langAm') }}</label>
                                            <div class='col-sm-12'>
                                                <input  class='form-control' type='text' name='am' value='{{ $info->am }}'>
                                            </div>
                                        </div>

                                        <div class='form-group mt-4'>
                                            <label class='col-sm-12 control-label-notes'>{{ trans('langTel') }}</label>
                                            <div class='col-sm-12'>
                                                <input  class='form-control' type='text' name='phone' value='{{ $info->phone }}'>
                                            </div>
                                        </div>

                                        <div class='form-group mt-4'>
                                            <label class='col-sm-12 control-label-notes'>{{ trans('langFaculty') }}</label>
                                            <div class='col-sm-12'>
                                                {!! $html !!}
                                            </div>
                                        </div>

                                        <div class="form-group mt-4">
                                            @if ($info->status == USER_GUEST)
                                                <label class='col-sm-12 control-label-notes'>{{ trans('langProperty') }}</label>{{ trans('langGuest') }}
                                            @else
                                                <label class='col-sm-12 control-label-notes mb-2'> {{ trans('langUserPermissions') }}</label>
                                                <div class="radio mb-2 d-flex justify-content-start align-items-center">
                                                    <label>
                                                        <input type='radio' name='newstatus' value='" . USER_STUDENT . "' {!! ($info->status == USER_STUDENT) ? 'checked' : '';  !!} >
                                                        {{ trans('langWithNoCourseCreationRights') }}
                                                    </label>
                                                </div>
                                                <div class="radio mb-2 d-flex justify-content-start align-items-center">
                                                    <label>
                                                        <input type='radio' name='newstatus' value='" . USER_TEACHER . "'{!! ($info->status == USER_TEACHER) ? 'checked' : ''; !!} >
                                                        {{ trans('langWithCourseCreationRights') }}
                                                    </label>
                                                </div>
                                                <div class='checkbox mb-2'>
                                                    <label class='label-container'>
                                                        <input type='checkbox' name='enable_course_registration' value='1' {!! (!$info->disable_course_registration) ? 'checked' : ''; !!}>
                                                        <span class='checkmark'></span>{{ trans('langInfoEnableCourseRegistration') }}
                                                    </label>
                                                </div>
                                            @endif
                                        </div>

                                        <div class='form-group mt-4'>
                                            <label class='col-sm-12 control-label-notes'>{{ trans('langRegistrationDate') }}</label>
                                            <div class='col-sm-12'>
                                                <p class='form-control-static'>{{ $reg_date->format("d-m-Y H:i") }}</p>
                                            </div>
                                        </div>
                                        <div class='input-append date form-group mt-4'>
                                            <label class='col-sm-12 control-label-notes'>{{ trans('langExpirationDate') }}</label>
                                            <div class='col-sm-12'>
                                                <div class='input-group'>
                                                    <span class='add-on input-group-text h-40px bg-input-default input-border-color border-end-0'><i class='fa-regular fa-calendar'></i></span>
                                                    <input class='form-control mt-0 border-start-0' id='user_date_expires_at' name='user_date_expires_at' type='text' value='{{ $exp_date->format("d-m-Y H:i") }}'>
                                                </div>
                                            </div>
                                        </div>

                                        <div class='form-group mt-4'>
                                            <label class='col-sm-12 control-label-notes'>{{ trans('langLanguage') }}:</label>
                                            <div class='col-sm-12'>
                                                {!! lang_select_options('user_language', "class='form-control'", $info->lang)  !!}
                                            </div>
                                        </div>

                                        <div class='form-group mt-4'>
                                            <label class='col-sm-12 control-label-notes'>{{ trans('langLastLogin') }}</label>
                                            <div class='col-sm-10'><p class='form-control-static'>{{ $last_login_date }}&nbsp;&mdash;&nbsp; <small><a href='user_last_logins.php?u={{ $u }}'>{{ trans('langUserLastLogins') }}</a></small></p></div>
                                        </div>

                                        <div class='form-group mt-4'>
                                            <label class='col-sm-12 control-label-notes'>{{ trans('langUserID') }}</label>
                                            <div class='col-sm-12'>
                                                <p class='form-control-static'>{{ $u }}</p>
                                            </div>
                                        </div>

                                        <div class='form-group mt-4'>
                                            <label class='col-sm-12 control-label-notes'>{{ trans('langUserWhitelist') }}</label>
                                            <div class='col-sm-12'>
                                                <textarea class='w-100' rows='6' name='user_upload_whitelist'>{{ $info->whitelist }}</textarea>
                                            </div>
                                        </div>

                                        @if ($ext_uid)
                                            <div class='form-group mt-4'>
                                                <label class='col-sm-12 control-label-notes'>{{ trans('langProviderConnectWith') }}</label>
                                                <div class='col-sm-12'>
                                                    <div class='row'>
                                                    @foreach ($ext_uid as $ext_uid_item)
                                                        <div class='col-2 text-center'>
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
                                            <div class='row'>{!! render_profile_fields_form(array('origin' => 'admin_edit_profile', 'user_id' => $u)) !!}</div>
                                        @endif
                                        <input type='hidden' name='u' value='{{ $u }}'>
                                        <input type='hidden' name='u_submitted' value='1'>
                                        <input type='hidden' name='registered_at' value='{{ $info->registered_at }}'>
                                        {!! showSecondFactorChallenge() !!}
                                        <div class='col-12 mt-5 d-flex justify-content-end align-items-center'>
                                            <input class='btn submitAdminBtn' type='submit' name='submit_edituser' value='{{ trans('langModify') }}'>
                                        </div>
                                    </fieldset>
                                    {!! generate_csrf_token_form_field() !!}
                                </form>
                            </div>
                        </div>

                        <div class='col-lg-6 col-12 d-none d-md-none d-lg-block text-end'>
                        <img class='form-image-modules' src='{!! get_form_image() !!}' alt='form-image'>
                        </div>

                        <!--user is registered to courses-->

                            @if (count($sql) > 0)
                                <div class='col-12 mt-3'>
                                    <div class='card panelCard px-lg-4 py-lg-3 h-100'>
                                        <div class="card-header border-0 d-flex justify-content-start">
                                            <h3>{{ trans('langStudentParticipation') }}</h3>
                                        </div>
                                        <div class='card-body'>
                                            <div class='table-responsive mt-0'>
                                                <table class='table-default'>
                                                    <thead><tr class='list-header'>
                                                        <th>{{ trans('langCode') }}</th>
                                                        <th>{{ trans('langLessonName') }}</th>
                                                        <th>{{ trans('langCourseRegistrationDate') }}</th>
                                                        <th>{{ trans('langProperty') }}</th>
                                                        <th></th>
                                                    </tr></thead>
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
                                                                <td>
                                                                    @if (!$logs->reg_date)
                                                                        {{ trans('langUnknownDate') }}
                                                                    @else
                                                                        {{ format_locale_date(strtotime($logs->reg_date), 'short', false) }}
                                                                    @endif
                                                                </td>
                                                                @if ($logs->status == USER_TEACHER)
                                                                    <td>
                                                                        {{ trans('langTeacher') }}
                                                                    </td>
                                                                    <td>---</td>
                                                                @else
                                                                    <td>
                                                                        @if ($logs->status == USER_STUDENT)
                                                                            {{ trans('langStudent') }}
                                                                        @else
                                                                            {{ trans('langVisitor') }}
                                                                        @endif
                                                                    </td>
                                                                    <td class='text-end'>
                                                                        {!! icon('fa-xmark text-danger', trans('langUnregCourse'), "unreguser.php?u=$u&amp;c=$logs->id") !!}
                                                                    </td>
                                                                @endif
                                                            </tr>
                                                    @endforeach
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif



        </div>
</div>
</div>
@endsection
