@extends('layouts.default')

@section('content')
    {!! $action_bar !!}

    <div class='row'>
        <div class='col-md-12'>
            <div class='panel panel-default'>
                <div class='panel-heading'>
                    <h4>{{ $request->title }}</h4>
                    <div class='announcement-date'>{{
                        claro_format_locale_date(trans('dateFormatLong') . ' ' . trans('timeNoSecFormat'),
                                                 strtotime($request->open_date)) }}</div>
                </div>
                <div class='panel-body'>
                    <div class='row'>
                        <div class='col-xs-12 col-sm-2'>
                            <b>{{ trans('langNewBBBSessionStatus') }}:</b>
                        </div>
                        <div class='col-xs-12 col-sm-4'>
                            {{ $state }}
                        </div>
                    </div>
                    <div class='row'>
                        <div class='col-xs-12 col-sm-2'>
                            <b>{{ trans('langFrom') }}:</b>
                        </div>
                        <div class='col-xs-12 col-sm-4'>
                            {!! display_user($request->creator_id) !!}
                        </div>
                        @if ($watchers)
                            <div class='col-xs-12 col-sm-2'>
                                <b>{{ trans('langWatchers') }}:</b>
                            </div>
                            <div class='col-xs-12 col-sm-4'>
                                @foreach ($watchers as $user)
                                    {!! display_user($user) !!}
                                @endforeach
                            </div>
                        @endif
                        @if ($assigned)
                            <div class='col-xs-12 col-sm-2'>
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
                    <div class='row'>
                        <div class='col-xs-12'>
                            {!! $request->description !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>

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
                                    {{ claro_format_locale_date(trans('dateFormatLong') . ' ' . trans('timeNoSecFormat'),
                                                                strtotime($comment->ts)) }}
                                </div>
                            </div>
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
                            <div class='row'>
                                <div class='col-xs-12 col-sm-2'>
                                    <b>{{ trans('langComment') }}:</b>
                                </div>
                                <div class='col-xs-12 col-sm-10'>
                                    {!! $comment->comment !!}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif

        @if ($can_comment)
            <div class='col-md-12'>
                <form class='form-horizontal' role='form' method='post' action='{{ $targetUrl }}' enctype='multipart/form-data'>
                    <fieldset>
                        <div class='form-group'>
                            <label class='col-sm-2 control-label'>{{ trans('langFrom') }}:</label>
                            <div class='col-sm-10'>
                                <p class='form-control-static'>{{ $commenterName }}<p>
                            </div>
                        </div>

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
    </div>
@endsection
