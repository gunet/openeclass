@extends('layouts.default')

@section('content')
    {!! $backButton !!}
    <div class='row'>
        <div class='col-sm-12'>
            <div class='form-wrapper'>
                <form class='form-horizontal' role='form'>
                    <div class='form-group'>
                        <label class='col-sm-3 control-label'>{{ trans('langQuotaUsed') }}:</label>
                        <div class='col-sm-9'>
                            <p class='form-control-static'>{{ $used }}</p>
                        </div>
                    </div>
                    <div class='form-group'>
                        <label class='col-sm-3 control-label'>{{ trans('langQuotaPercentage') }}:</label>
                        <div class='col-sm-9'>
                            <div class='progress'>
                                <p class='progress-bar from-control-static' role='progressbar' aria-valuenow='{{ $diskUsedPercentage }}' aria-valuemin='0' aria-valuemax='100' style='min-width: 2em; width: {{ $diskUsedPercentage}}%;'>
                                    {{ $diskUsedPercentage }}%
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class='form-group'>
                        <label class='col-sm-3 control-label'>{{ trans('langQuotaTotal') }}:</label>
                        <div class='col-sm-9'>
                            <p class='form-control-static'>{{ $quota }}</p>
                        </div>
                    </div>  
                </form>
            </div>
        </div>
    </div>
@endsection

