@extends('layouts.default')

@section('content')
    {!! $action_bar !!}

    <div class='row'>
        <div class='col-md-12'>
            <div class='form-wrapper'>
                <form class='form-horizontal' action='{{ $targetUrl }}' method='post'>
                    <div class='form-group'>
                        <label class='col-sm-2 control-label'>{{ trans('langcreator') }}:</label>
                        <div class='col-sm-10'>
                            <p class='form-control-static'>{{ $creatorName }}</p>
                        </div>
                    </div>

                    @if ($request_types)
                        <div class='form-group'>
                            <label for='requestType' class='col-sm-2 control-label'>{{ trans('langType') }}:</label>
                            <div class='col-sm-10'>
                                <select class='form-control' name='requestType' id='requestType'>
                                    <option value='0'>{{ trans('langRequestBasicType') }}</option>
                                    @foreach ($request_types as $type)
                                        <option value='{{ $type->id }}'>{{ getSerializedMessage($type->name) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    @endif

                    <div class='form-group'>
                        <label for='requestTitle' class='col-sm-2 control-label'>{{ trans('langTitle') }}:</label>
                        <div class='col-sm-10'>
                            <input type='text' class='form-control' id='requestTitle' name='requestTitle' required>
                        </div>
                    </div>

                    <div class='form-group'>
                        <label for='requestDescription' class='col-sm-2 control-label'>{{ trans('langDescription') }}:</label>
                        <div class='col-sm-10'>
                            {!! $descriptionEditor !!}
                        </div>
                    </div>

                    <div class='form-group'>
                        <label for='assignTo' class='col-sm-2 control-label'>{{ trans("m['WorkAssignTo']") }}:</label>
                        <div class='col-sm-10'>
                            <select class='form-control' name='assignTo[]' multiple id='assignTo'>
                                @foreach ($course_users as $cu)
                                    <option value='{{ $cu->user_id }}'>{{$cu->name}} ({{$cu->email}})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class='form-group'>
                        <label for='requestWatchers' class='col-sm-2 control-label'>{{ trans('langWatchers') }}:</label>
                        <div class='col-sm-10'>
                            <select class='form-control' name='requestWatchers[]' multiple id='requestWatchers'>
                                @foreach ($course_users as $cu)
                                    <option value='{{ $cu->user_id }}'>{{$cu->name}} ({{$cu->email}})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    @if ($request_types)
                        @foreach ($request_types as $type)
                            @include('modules.request.extra_fields',
                                ['type_name' => $type->name,
                                 'type_id' => $type->id,
                                 'fields_info' => $request_fields[$type->id]])
                        @endforeach
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
    <script>$(function () {
        $('#requestWatchers').select2();
        $('#assignTo').select2();
        @if ($request_types)
            $('#requestType').change(function () {
                var type_id = $(this).val();
                $('.extra-fields-set').hide();
                $('#fields_' + type_id).show();
            }).change();
        @endif
    })</script>
@endsection
