@extends('layouts.default')

@push('head_scripts')
    <script type="text/javascript">
        var langEmptyGroupName = '{{ trans('langNoPgTitle') }}'
    </script>
@endpush

@section('content')

{!! $action_bar !!}
    <div class='form-wrapper'>
        <form class='form-horizontal' role='form' method='post' action='{{ $_SERVER['SCRIPT_NAME'] }}?course={{ $course_code}}&action=true'>
            <div class='form-group'>
                <label for='link' class='col-sm-2 control-label'>{{ trans('langLink') }}:</label>
                <div class='col-sm-10'>
                    <input id='link' class='form-control' type='text' name='link' size='50' value='http://'>
                </div>
            </div>
            <div class='form-group'>
                <label for-'name_link' class='col-sm-2 control-label'>{{ trans('langLinkName') }}:</label>
                <div class='col-sm-10'>
                    <input class='form-control' type='text' name='name_link' size='50'>
                </div>
            </div>
            <div class='form-group'>
                <div class='col-sm-offset-2 col-sm-10'>
                  <input class='btn btn-primary' type='submit' name='submit' value='{{ trans('langAdd') }}'>
                </div>
            </div>
            {!! $csrf !!}
        </form>
    </div>

@endsection