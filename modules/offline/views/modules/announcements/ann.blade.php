@extends('layouts.default')

@section('content')

    <div class="row">
        <div class="col-xs-12">
            <div class="panel">
                <div class="panel-body">
                    <div class="single_announcement">
                        <div class="announcement-title">
                            {!! standard_text_escape($ann_title) !!}
                        </div>
                        <span class="announcement-date">
                            {{ claro_format_locale_date(trans('dateFormatLong'), strtotime($ann_date)) }}
                        </span>
                        <div class="announcement-main">
                            <p>{!! $ann_body !!}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection


