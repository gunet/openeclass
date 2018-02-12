@extends('layouts.default')

@section('content')
    {!! $action_bar !!}

    <div class='row'>
        <div class='col-md-12'>
            <div class='form-wrapper'>
                <form class='form-horizontal' action='{{ $targetUrl }}' method='post'>
                    <div class='form-group'>
                        <label for='requestTitle' class='col-sm-2 control-label'>{{ trans('langTitle') }}:</label>
                        <div class='col-sm-10'>
                            <input type='text' class='form-control' id='requestTitle' name='requestTitle' value='{{ $request->title }}' required>
                        </div>
                    </div>

                    <div class='form-group'>
                        <label for='requestDescription' class='col-sm-2 control-label'>{{ trans('langDescription') }}:</label>
                        <div class='col-sm-10'>
                            {!! $descriptionEditor !!}
                        </div>
                    </div>

                    @if ($request->type_id)
                        @include('modules.request.extra_fields',
                            ['type_name' => $type->name,
                             'type_id' => $type->id,
                             'fields_info' => $field_data])
                    @endif

                    <div class='form-group'>
                        <div class='col-xs-offset-2 col-xs-10'>
                            <button class='btn btn-primary' type='submit'>{{ trans('langSubmit') }}</button>
                            <a class='btn btn-default' href='{{ $backUrl }}'>{{ trans('langCancel') }}</a>
                        </div>
                    </div>

                    {!! generate_csrf_token_form_field() !!}
                </form>
            </div>
        </div>
    </div>
@endsection
