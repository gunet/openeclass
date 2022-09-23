@extends('layouts.default')

@section('content')

<div class="pb-lg-3 pt-lg-3 pb-0 pt-0">

    <div class="container-fluid main-container">

        <div class="row rowMedium">

            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 justify-content-center col_maincontent_active_Homepage">
                    
                <div class="row p-lg-5 p-md-5 ps-1 pe-1 pt-5 pb-5">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])                   

                    @if(Session::has('message'))
                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mt-5'>
                        <p class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                            @if(is_array(Session::get('message')))
                                @php $messageArray = array(); $messageArray = Session::get('message'); @endphp
                                @foreach($messageArray as $message)
                                    {!! $message !!}
                                @endforeach
                            @else
                                {!! Session::get('message') !!}
                            @endif
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </p>
                    </div>
                    @endif

                    {!! isset($action_bar) ?  $action_bar : '' !!}

                    <div class='col-lg-6 col-12 d-none d-md-none d-lg-block'>
                        <div class='col-12 h-100 left-form'></div>
                    </div>

                    <div class='col-lg-6 col-12'>
                        <div class='form-wrapper shadow-sm p-3 rounded'>
                            
                            <form role='form' class='form-horizontal' action="{{ $_SERVER['SCRIPT_NAME'] }}?c={{ $course->code }}" method='post'>
                                <fieldset>                    
                                    <div class='alert alert-info'>
                                        {{ trans('langTheCourse') }} <b>{{ $course->title }}</b> {{ trans('langMaxQuota') }}
                                    </div>
                                    <div class='form-group mt-3'>
                                        <label class='col-sm-12 control-label-notes'>{{ trans('langLegend') }} {{ trans('langDoc') }}</label>
                                            <div class='col-sm-12'><input type='text' name='dq' value='{{ $dq }}' size='4' maxlength='4'> MB</div>
                                    </div>
                                    <div class='form-group mt-3'>
                                        <label class='col-sm-12 control-label-notes'>{{ trans('langLegend') }} {{ trans('langVideo') }}</label>
                                            <div class='col-sm-12'><input type='text' name='vq' value='{{ $vq }}' size='4' maxlength='4'> MB</div>
                                    </div>
                                    <div class='form-group mt-3'>
                                        <label class='col-sm-12 control-label-notes'>{{ trans('langLegend') }} {{ trans('langGroups') }}</label>
                                        <div class='col-sm-12'>
                                            <input type='text' name='gq' value='{{ $gq }}' size='4' maxlength='4'> MB
                                        </div>
                                    </div>
                                    <div class='form-group mt-3'>
                                        <label class='col-sm-12 control-label-notes'>{{ trans('langLegend') }} {{ trans('langDropBox') }}</label>
                                        <div class='col-sm-12'>
                                            <input type='text' name='drq' value='{{ $drq }}' size='4' maxlength='4'> MB
                                        </div>
                                    </div>
                                    <div class='form-group mt-5'>
                                        <div class='col-12'>
                                            <input class='btn btn-primary submitAdminBtn w-100' type='submit' name='submit' value='{{ trans('langModify') }}'>
                                        </div>
                                    </div>
                                </fieldset>
                                {!! generate_csrf_token_form_field() !!}
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection