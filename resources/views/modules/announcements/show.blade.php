@extends('layouts.default')

@section('content')

    {!! $action_bar !!}

    <div class='row'>
        <div class='col-xs-12'>
            <div class='panel'>
                <div class='panel-body'>
                    <div class='single_announcement'>
                        <div class='announcement-title'>
                            {!! $title !!}
                        </div>
                        <span class='announcement-date'>
                            {!! $date !!}
                        </span>
                        <div class='announcement-main'>
                            {!! $content !!}
                        </div>
                    </div>
                    @if ($tags_list)
                        <hr>
                        <div>{{ trans('langTags') }}: {!! $tags_list !!}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
