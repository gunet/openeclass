@extends('layouts.default')

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

