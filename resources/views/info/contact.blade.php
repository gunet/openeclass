@extends('layouts.default')

@section('content')

    {!! $action_bar !!}
    <div class='row'>
        <div class='col-xs-12'>
            <div class='panel'>
                <div class='panel-body'>
                    <strong>{{ trans('langPostMail') }}</strong>&nbsp;{{ $Institution }}
                    <br> {!! $postaddress !!}

                    @if (empty($phone))
                        <strong>{{ trans('langPhone') }}:</strong>
                        <span class='not_visible'> - {{ trans('langProfileNotAvailable') }} - </span><br>
                    @else
                        <strong>{{ trans('langPhone') }}:&nbsp;</strong>
                        {{ $phone }}<br>
                    @endif

                    @if (empty($fax))
                        <strong>{{ trans('langFax') }}</strong>
                        <span class='not_visible'> - {{ trans('langProfileNotAvailable') }} - </span><br>
                    @else
                        <strong>{{ trans('langFax') }}&nbsp;</strong>
                        {{ $fax }}<br>
                    @endif

                    @if (empty($emailhelpdesk))
                        <strong>{{ trans('langEmail') }}:</strong>
                        <span class='not_visible'> - {{ trans('langProfileNotAvailable') }} - </span><br>
                    @else
                        <strong>{{ trans('langEmail') }}: </strong>
                        <a href='mailto:$emailhelpdesk'>{{ $emailhelpdesk }}</a>
                    @endif
                </div>
            </div>
        </div>
    </div>

@endsection
