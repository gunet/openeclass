@extends('layouts.default')

@section('content')
    {!! $backButton !!}
    @if ($can_upload)
        <div class='form-wrapper'>
            <form class='form-horizontal' role='form' action='{{ $upload_target_url }}' method='post'>
                <input type='hidden' name='{{ $pathName }}' value='{{ $pathValue }}'>
                {!! $group_hidden_input !!}
                    
                @if ($sections)
                    <div class='form-group'>
                        <label for='section' class='col-sm-2 control-label'>{{ trans('langSection') }}:</label>
                        <div class='col-sm-10'>
                            {!! selection($sections, 'section_id', $section_id) !!}
                        </div>
                    </div>
                @endif

                @if ($filename)
                    <div class='form-group'>
                        <label for='file_name' class='col-sm-2 control-label'>{{ trans('langFileName') }}:</label>
                        <div class='col-sm-10'>
                            <p class='form-control-static'>{{ $filename }}</p>
                        </div>
                    </div>
                @endif

                <div class='form-group{{ Session::getError('file_title') ? ' has-error' : '' }}'>
                    <label for='file_title' class='col-sm-2 control-label'>{{ trans('langTitle') }}:</label>
                    <div class='col-sm-10'>
                        <input type='text' class='form-control' id='file_title' name='file_title' value='{{ $title }}'>
                        <span class='help-block'>{{ Session::getError('file_title') }}</span>    
                    </div>
                </div>
                <div class='form-group'>
                    <label for='file_title' class='col-sm-2 control-label'>{{ trans('langContent') }}:</label>
                    <div class='col-sm-10'>
                        {!! $rich_text_editor !!}
                    </div>
                </div>

                <div class='form-group'>
                    <div class='col-xs-offset-2 col-xs-10'>
                        <div class='form-group'>
                            <div class='col-xs-offset-2 col-xs-10'>
                                <button class='btn btn-primary' type='submit'>{{ trans('langSave') }}</button>
                                <a class='btn btn-default' href='{{ $backUrl }}'>{{ trans('langCancel') }}</a>
                            </div>
                        </div>
                        {!! generate_csrf_token_form_field() !!}
                    </div>
                </div>
            </form>
        </div>
    @else
        <div class='alert alert-warning'>{{ trans('langNotAllowed') }}</div>
    @endif
@endsection
