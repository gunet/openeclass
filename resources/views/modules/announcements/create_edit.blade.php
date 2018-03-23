@extends('layouts.default')

@section('content')

    {!! $action_bar !!}

    <div class="row">
        <div class="col-xs-12">
            <div class="form-wrapper">
                <form class="form-horizontal" role="form" method="post" action="{{ $_SERVER['SCRIPT_NAME'] }}?course={{ $course_code }}">
                    <fieldset>
                        <div class="form-group {{ $antitle_error }}">
                            <label for="AnnTitle" class="col-sm-2 control-label">{{ trans('langAnnTitle') }}:</label>
                            <div class="col-sm-10">
                                <input class="form-control" type="text" name="antitle" value="{{ $titleToModify }}"/>
                                <span class='help-block'>{{ $antitle_error }}</span>
                            </div>
                        </div>
                        <div class='form-group'>
                            <label for='AnnBody' class='col-sm-2 control-label'>{{ trans('langAnnBody') }}:</label>
                            <div class='col-sm-10'>{!! $contentToModify !!}</div>
                        </div>
                        <div class='form-group'><label for='Email' class='col-sm-offset-2 col-sm-10 control-panel'>{{ trans('langEmailOption') }}:</label></div>
                        <div class='form-group'>
                            <div class='col-sm-offset-2 col-sm-10'>
                                <select class='form-control' name='recipients[]' multiple='multiple' id='select-recipients'>
                                    <option value='-1' selected>{{ trans('langAllUsers') }}</option>
                                    @foreach ($course_users as $cu)
                                        <option value='{{ $cu->user_id }}'>{{$cu->name}} ({{$cu->email}})</option>
                                    @endforeach
                                </select>
                                <a href='#' id='selectAll'>{{ trans('langJQCheckAll') }}</a> | <a href='#' id='removeAll'>{{ trans('langJQUncheckAll') }}</a>
                            </div>
                        </div>
                        {!! $tags !!}
                        <div class='form-group'>
                            <label for='Email' class='col-sm-offset-2 col-sm-10 control-panel'>{{ trans('langAnnouncementActivePeriod') }}:</label>
                        </div>
                        <div class='form-group {{ $startdate_error }}'>
                            <label for='startdate' class='col-sm-2 control-label'>{{ trans('langStartDate') }} :</label>
                            <div class='col-sm-10'>
                                <div class='input-group'>
                                    <span class='input-group-addon'>
                                        <input type='checkbox' name='startdate_active' {{ $start_checkbox }}>
                                    </span>
                                    <input class='form-control' name='startdate' id='startdate' type='text' value = '{{ $showFrom }}'>
                                </div>
                                <span class='help-block'>{{ $startdate_error }}</span>
                            </div>
                        </div>
                        <div class='form-group {{ $enddate_error }}'>
                            <label for='enddate' class='col-sm-2 control-label'>{{ trans('langEndDate') }} :</label>
                            <div class='col-sm-10'>
                                <div class='input-group'>
                                    <span class='input-group-addon'>
                                        <input type='checkbox' name='enddate_active' {{ $end_checkbox}} {{ $end_disabled}}>
                                    </span>
                                    <input class='form-control' name='enddate' id='enddate' type='text' value = '{{ $showUntil }}'>
                                </div>
                                <span class='help-block'>{{ $enddate_error }}</span>
                            </div>
                        </div>
                        <div class='form-group'>
                            <div class='col-sm-10 col-sm-offset-2'>
                                <div class='checkbox'>
                                    <label>
                                        <input type='checkbox' name='show_public' {{  $checked_public }}> {{ trans('langViewShow') }}
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class='form-group'>
                            <div class='col-sm-offset-2 col-sm-10'>
                                <button type="submit" class="btn btn-primary" name="submitAnnouncement" value="{{ trans('langAdd') }}">{{ trans('langSubmit') }}</button>
                                <a href="{{ $_SERVER['SCRIPT_NAME'] }}?course={{ $course_code }}" class="btn btn-default">{{ trans('langCancel') }}</a>
                            </div>
                            <input type='hidden' name='id' value='{{ trans('AnnouncementToModify') }}'>
                        </div>
                    </fieldset>
                </form>
            </div>
        </div>
    </div>
@endsection
