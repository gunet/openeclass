@extends('layouts.default')
@section('content')

    <div class="col-12 main-section">
        <div class='{{ $container }} module-container py-lg-0'>
            <div class="course-wrapper d-lg-flex align-items-lg-strech w-100">

                @include('layouts.partials.left_menu')

                <div class="col_maincontent_active">
                    <div class="row">
                        @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                        @include('layouts.partials.legend_view')

                        @include('layouts.partials.show_alert')
                        @if (count($res) == 0)
                            <div class='col-sm-12'>
                                <div class='alert alert-warning'>
                                    <i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>{{ trans('langNoAssign') }}</span>
                                </div>
                            </div>
                        @else
                            <div class='d-lg-flex gap-4 mt-4'>
                                <div class='flex-grow-1'>
                                    <div class='col-12 mt-3'>
                                        <div class='alert alert-info'>
                                            <i class='fa-solid fa-circle-info fa-lg'></i>
                                            {{ trans('langGroupWorkIntro') }}
                                        </div>
                                    </div>
                                    <div class='form-wrapper form-edit rounded'>
                                        <form class='form-horizontal' action='{{ $_SERVER['SCRIPT_NAME'] }}?course={{ $course_code }}' method='post'>
                                            <fieldset>
                                                <legend class='mb-0' aria-label='{{ trans('langForm') }}'></legend>
                                                <input type='hidden' name='file' value=' {{ $_GET['submit'] }})'>
                                                <input type='hidden' name='group_id' value='{{ $group_id }}'>
                                                <div class='form-group mt-4'>
                                                    <div class='col-sm-6 control-label-notes'>
                                                        {{ trans('langWorks') }} ({{ trans('langSelect') }})
                                                    </div>
                                                    <div class='col-12'>
                                                        <div class='table-responsive'>
                                                            <table class='table-default'>
                                                                <thead>
                                                                    <tr class='list-header'>
                                                                        <th colspan='2'>{{ trans('langTitle') }}</th>
                                                                        <th style='align:center;' width='30%'>{{ trans('langGroupWorkDeadline_of_Submission') }}</th>
                                                                        <th style='align:center;' width='10%'>{{ trans('langSubmitted') }}</th>
                                                                        <th style='align:center;' width='10%'>{{ trans('langSelect') }}</th>
                                                                    </tr>
                                                                </thead>
                                                                @foreach ($res as $row)
                                                                    <tr>
                                                                        <td width='1'><i class='fa-solid fa-caret-right'></i></td>
                                                                        <td style='align:left;'>
                                                                            <a href='index.php?course={{ $course_code }}&id={{ $row->id }}'>{{ $row->title }}</a>
                                                                        </td>
                                                                        <td style='align:center;'>
                                                                            @if (!is_null($row->deadline))
                                                                                {{ format_locale_date(strtotime($row->deadline), 'short') }}
                                                                            @endif
                                                                            @if ($row->time > 0)
                                                                                <br>(<small>{{ trans('langDaysLeft') }} {{format_time_duration($row->time) }}</small>)
                                                                            @elseif ($row->deadline)
                                                                                <br>(<small><span class='text-danger'>{{ trans('langHasExpiredS') }}</span></small>)
                                                                            @endif
                                                                        </td>
                                                                        <td style='align:center;'>
                                                                            @if (was_submitted($uid, $group_id, $row->id) == 'user')
                                                                                {{ trans('langYes') }}
                                                                            @elseif (was_submitted($uid, $group_id, $row->id) == 'group') {
                                                                                {{ trans('$langByGroupMate') }}
                                                                            @else
                                                                                {{ trans('langNo') }}
                                                                            @endif
                                                                        </td>
                                                                        <td style='align:center;'>
                                                                            @if ($row->time >= 0 and !was_graded($uid, $row->id) and is_group_assignment($row->id))
                                                                                <input type='radio' name='assign' value='{{ $row->id }}'>
                                                                            @else
                                                                                -
                                                                            @endif
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class='form-group mt-4'>
                                                    <label for='comments_id' class='col-sm-6 control-label-notes'>{{ trans('langComments') }}</label>
                                                    <div class='col-sm-12'>
                                                        <textarea id='comments_id' name='comments' rows='4' cols='60' class='form-control'></textarea>
                                                    </div>
                                                </div>
                                                <div class='form-group mt-5'>
                                                    <div class='col-12 d-flex justify-content-end align-items-center gap-2'>
                                                        <a class='btn cancelAdminBtn' href='{{ $urlServer }}/modules/group/document.php?course={{ $course_code }}&group_id={{ $group_id }}'>{{ trans('langCancel') }}</a>
                                                        <input class='btn submitAdminBtn' type='submit' name='submit' value='{{ trans('langSubmit') }}'>
                                                    </div>
                                                </div>
                                            </fieldset>
                                        </form>
                                    </div>
                                </div>
                                <div class='d-none d-lg-block'>
                                    <img class='form-image-modules' src='{{ get_form_image() }}' alt='{{ trans('langImgFormDes') }}'>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
