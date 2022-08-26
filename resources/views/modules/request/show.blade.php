@extends('layouts.default')

@section('content')
    {!! $action_bar !!}

    <div class='row'>
        <div class='col-md-12'>
            <div class='panel panel-default'>
                <div class='panel-heading'>
                    <h4>{{ $request->title }}
                        @if ($request->type_id)
                            <small><br>{{ $type->name }}</small>
                        @endif
                    </h4>
                    <div class='announcement-date'>{{
                        format_locale_date(strtotime($request->open_date)) }}
                    </div>
                </div>
                <div class='panel-body'>
                    <div class='row'>
                        <div class='col-xs-12 col-sm-2 text-right'>
                            <b>{{ trans('langNewBBBSessionStatus') }}:</b>
                        </div>
                        <div class='col-xs-12 col-sm-4'>
                            {{ $state }}
                        </div>
                    </div>
                    <div class='row'>
                        <div class='col-xs-12 col-sm-2 text-right'>
                            <b>{{ trans('langFrom') }}:</b>
                        </div>
                        <div class='col-xs-12 col-sm-4'>
                            {!! display_user($request->creator_id) !!}
                        </div>
                        @if ($watchers)
                            <div class='col-xs-12 col-sm-2 text-right'>
                                <b>{{ trans('langWatchers') }}:</b>
                            </div>
                            <div class='col-xs-12 col-sm-4'>
                                @foreach ($watchers as $user)
                                    {!! display_user($user) !!}
                                @endforeach
                            </div>
                        @endif
                        @if ($assigned)
                            <div class='col-xs-12 col-sm-2 text-right'>
                                <b>{{ trans("m['WorkAssignTo']") }}:</b>
                            </div>
                            <div class='col-xs-12 col-sm-4'>
                                @foreach ($assigned as $user)
                                    {!! display_user($user) !!}
                                @endforeach
                            </div>
                        @endif
                        </div>
                    <hr>
                    @if ($field_data)
                        @foreach ($field_data as $field)
                            <div class='row'>
                                <div class='col-xs-12 col-sm-2 text-right'>
                                    <b>{{ getSerializedMessage($field->name) }}:</b>
                                </div>
                                <div class='col-xs-12 col-sm-10'>
                                    @if (is_null($field->data) or $field->data === '')
                                        <span class='not_visible'> - </span>
                                    @else
                                        @if ($field->datatype == REQUEST_FIELD_DATE)
                                            {{ format_locale_date(strtotime($field->data)) }}
                                        @else
                                            {{ $field->data }}
                                        @endif
                                    @endif
                                </div>
                            </div>
                        @endforeach
                        <hr>
                    @endif
                    <div class='row'>
                        <div class='col-xs-12'>
                            {!! $request->description !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if ($can_modify or $can_assign_to_self)
            <div class='col-md-12'>
                <form role='form' method='post' action='{{ $targetUrl }}'>
                    <p>
                    {!! generate_csrf_token_form_field() !!}
                    @if ($can_assign_to_self)
                        <button class='btn btn-default' type='submit' name='assignToSelf'>{{ trans('langTakeRequest') }}</button>
                    @endif
                    @if ($can_modify)
                        <button class='btn btn-default' type='button' data-toggle='modal' data-target='#assigneesModal'>{{ trans("m['WorkAssignTo']") }}...</button>
                        <button class='btn btn-default' type='button' data-toggle='modal' data-target='#watchersModal'>{{ trans("langWatchers") }}...</button>
                        <a class='btn btn-default' href='{{ $editUrl }}'>{{ trans("langElaboration") }}...</a>
                    @endif
                    </p>
                </form>
            </div>
        @endif

        @if ($can_comment)
            <div class='col-md-12'>
                <form class='form-horizontal' role='form' method='post' action='{{ $targetUrl }}' enctype='multipart/form-data'>
                    <fieldset>
                        @if ($can_modify)
                            <div class='form-group'>
                                <label for='newState' class='col-sm-2 control-label'>{{ trans('langChangeState') }}:</label>
                                <div class='col-sm-10'>
                                    <select class='form-control' name='newState' id='newState'>
                                        @foreach ($states as $stateId => $stateName)
                                            <option value='{{ $stateId }}'@if ($stateId == $request->state) selected @endif>{{ $stateName }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        @endif

                        <div class='form-group'>
                            <label for='requestComment' class='col-sm-2 control-label'>{{ trans('langComment') }}:</label>
                            <div class='col-sm-10'>
                                {!! $commentEditor !!}
                            </div>
                        </div>

                        <div class='form-group'>
                            <label for='requestFile' class='col-sm-2 control-label'>{{ trans('langAttachedFile') }}:</label>
                            <div class='col-sm-10'>
                                <input type='hidden' name='MAX_FILE_SIZE' value='{{ fileUploadMaxSize() }}'>
                                <input type='file' name='requestFile'>
                            </div>
                        </div>

                        <div class='form-group'>
                            <div class='col-xs-offset-2 col-xs-10'>
                                <button class='btn btn-primary' type='submit'>{{ trans('langSubmit') }}</button>
                                <a class='btn btn-default' href='{{ $backUrl }}'>{{ trans('langCancel') }}</a>
                            </div>
                        </div>
                        {!! generate_csrf_token_form_field() !!}
                    </fieldset>
                </form>
            </div>
        @endif

        @if ($comments)
            @foreach ($comments as $comment)
                <div class='col-md-12'>
                    <div class='panel panel-default'>
                        <div class='panel-body'>
                            <div class='row'>
                                <div class='col-xs-12 col-sm-2'>
                                    <b>{{ trans('langFrom') }}:</b>
                                </div>
                                <div class='col-xs-12 col-sm-10'>
                                    {!! display_user($comment->user_id) !!}
                                </div>
                            </div>
                            <div class='row'>
                                <div class='col-xs-12 col-sm-2'>
                                    <b>{{ trans('langDate') }}:</b>
                                </div>
                                <div class='col-xs-12 col-sm-10'>
                                    {{ format_locale_date(strtotime($comment->ts)) }}
                                </div>
                            </div>
                            @if ($comment->old_state != $comment->new_state)
                                <div class='row'>
                                    <div class='col-xs-12 col-sm-2'>
                                        <b>{{ trans('langChangeState') }}:</b>
                                    </div>
                                    <div class='col-xs-12 col-sm-10'>
                                        <b>{{ $states[$comment->new_state] }}</b> ({{ trans('langFrom') }}: {{ $states[$comment->old_state] }})
                                    </div>
                                </div>
                            @endif
                            @if ($comment->real_filename)
                                <div class='row'>
                                    <div class='col-xs-12 col-sm-2'>
                                        <b>{{ trans('langAttachedFile') }}:</b>
                                    </div>
                                    <div class='col-xs-12 col-sm-10'>
                                        <a href='{{ commentFileLink($comment) }}'>{{ $comment->filename }}</a>
                                    </div>
                                </div>
                            @endif
                            @if ($comment->comment)
                                <div class='row'>
                                    <div class='col-xs-12 col-sm-2'>
                                        <b>{{ trans('langComment') }}:</b>
                                    </div>
                                    <div class='col-xs-12 col-sm-10'>
                                        {!! standard_text_escape($comment->comment) !!}
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        @endif

    </div>

    @if ($can_modify)
        @include('modules.request.modals')
    @endif
@endsection
