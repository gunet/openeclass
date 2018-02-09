@extends('layouts.default')


@push('head_styles')
    <link href="{{ $urlAppend }}js/bootstrap-datepicker/css/bootstrap-datepicker3.min.css" type='text/css' rel='stylesheet'>
@endpush

@push('head_scripts')
    <script type='text/javascript' src='{{ $urlAppend }}js/tools.js'></script>
    <script type='text/javascript' src='{{ $urlAppend }}js/bootstrap-datepicker/js/bootstrap-datepicker.min.js'></script>
    <script type='text/javascript' src='{{ $urlAppend }}js/bootstrap-datepicker/locales/bootstrap-datepicker.{{ $language }}.min.js'></script>
    
    <script type='text/javascript'>
            $(function() {
                $('#unitdurationfrom, #unitdurationto').datepicker({
                    format: 'dd-mm-yyyy',
                    pickerPosition: 'bottom-right',
                    language: '".$language."',
                    autoclose: true    
                });
            });
    </script>            
@endpush

@section('content')
    {!! action_bar(array(
            array('title' => trans('langBack'),
                  'url' => q($postUrl),
                  'icon' => 'fa-reply',
                  'level' => 'primary-label')), false) !!}
    <div class='row'>
        <div class='col-md-12'>
            <div class='form-wrapper'>
                <form class='form-horizontal' action='{{ $postUrl }}' method='post' onsubmit="return checkrequired(this, 'unittitle')">
                    @if ($unitId)
                        <input type='hidden' name='unit_id' value='{{ $unitId }}'>
                    @endif

                    <div class='form-group'>
                        <label for='unitTitle' class='col-sm-2 control-label'>{{ trans('langTitle') }}</label>
                        <div class='col-sm-10'>
                            <input type='text' class='form-control' id='unitTitle' name='unittitle' value='{{ $unitTitle }}'>
                        </div>
                    </div>

                    <div class='form-group'>
                        <label for='unitdescr' class='col-sm-2 control-label'>{{ trans('langUnitDescr') }}</label>
                        <div class='col-sm-10'>
                            {!! $descriptionEditor !!}
                        </div>
                    </div>
                    
                    <div class='form-group'>
                        <label for='unitduration' class='col-sm-2 control-label'>{{ trans('langDuration') }}
                            <span class='help-block'>{{ trans('langOptional') }}</span>
                        </label>
                        <label for='unitduration' class='col-sm-1 control-label'>{{ trans('langFrom2') }}</label>
                        <div class='col-sm-4'>
                            <input type='text' class='form-control' id='unitdurationfrom' name='unitdurationfrom' value='{{ $start_week }}'>
                        </div>
                        <label for='unitduration' class='col-sm-1 control-label'>{{ trans('langUntil') }}</label>
                        <div class='col-sm-4'>
                            <input type='text' class='form-control' id='unitdurationto' name='unitdurationto' value='{{ $finish_week }}'>
                        </div>                        
                    </div>
                    
                    {!! $tagInput !!}

                    <div class='form-group'>
                        <div class='col-xs-offset-2 col-xs-10'>
                            <button class='btn btn-primary' type='submit' name='edit_submit'>{{ trans('langSubmit') }}</button>
                            <a class='btn btn-default' href='{{ $postUrl }}'>{{ trans('langCancel') }}</a>
                        </div>
                    </div>
                    {!! generate_csrf_token_form_field() !!}
                </form>
            </div>
        </div>
    </div>
@endsection

