@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
    <div class='{{ $container }}'>
        <div class="row rowMargin">


                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])
                    
                    @if(isset($action_bar))
                        {!! $action_bar !!}
                    @else
                        <div class='mt-4'></div>
                    @endif                

                    @if(Session::has('message'))
                    <div class='col-12 all-alerts'>
                        <div class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                            @php 
                                $alert_type = '';
                                if(Session::get('alert-class', 'alert-info') == 'alert-success'){
                                    $alert_type = "<i class='fa-solid fa-circle-check fa-lg'></i>";
                                }elseif(Session::get('alert-class', 'alert-info') == 'alert-info'){
                                    $alert_type = "<i class='fa-solid fa-circle-info fa-lg'></i>";
                                }elseif(Session::get('alert-class', 'alert-info') == 'alert-warning'){
                                    $alert_type = "<i class='fa-solid fa-triangle-exclamation fa-lg'></i>";
                                }else{
                                    $alert_type = "<i class='fa-solid fa-circle-xmark fa-lg'></i>";
                                }
                            @endphp
                            
                            @if(is_array(Session::get('message')))
                                @php $messageArray = array(); $messageArray = Session::get('message'); @endphp
                                {!! $alert_type !!}<span>
                                @foreach($messageArray as $message)
                                    {!! $message !!}
                                @endforeach</span>
                            @else
                                {!! $alert_type !!}<span>{!! Session::get('message') !!}</span>
                            @endif
                            
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                    @endif


                    <div class='col-lg-6 col-12 d-none d-md-none d-lg-block'>
                        <div class='col-12 h-100 left-form'></div>
                    </div>

                    <div class='col-lg-6 col-12'>
                        <div class='form-wrapper form-edit rounded'>
                            
                            <form role='form' class='form-horizontal' action="{{ $_SERVER['SCRIPT_NAME'] }}?c={{ $course->code }}" method='post'>
                                <fieldset>                    
                                    <div class='alert alert-info mt-0'><i class='fa-solid fa-circle-info fa-lg'></i><span>
                                        {{ trans('langTheCourse') }} <b>{{ $course->title }}</b> {{ trans('langMaxQuota') }}</span>
                                    </div>
                                    <div class='form-group'>
                                        <label class='col-sm-12 control-label-notes'>{{ trans('langLegend') }} {{ trans('langDoc') }}</label>
                                            <div class='col-sm-12'><input type='text' name='dq' value='{{ $dq }}' size='4' maxlength='4'> MB</div>
                                    </div>
                                    <div class='form-group mt-4'>
                                        <label class='col-sm-12 control-label-notes'>{{ trans('langLegend') }} {{ trans('langVideo') }}</label>
                                            <div class='col-sm-12'><input type='text' name='vq' value='{{ $vq }}' size='4' maxlength='4'> MB</div>
                                    </div>
                                    <div class='form-group mt-4'>
                                        <label class='col-sm-12 control-label-notes'>{{ trans('langLegend') }} {{ trans('langGroups') }}</label>
                                        <div class='col-sm-12'>
                                            <input type='text' name='gq' value='{{ $gq }}' size='4' maxlength='4'> MB
                                        </div>
                                    </div>
                                    <div class='form-group mt-4'>
                                        <label class='col-sm-12 control-label-notes'>{{ trans('langLegend') }} {{ trans('langDropBox') }}</label>
                                        <div class='col-sm-12'>
                                            <input type='text' name='drq' value='{{ $drq }}' size='4' maxlength='4'> MB
                                        </div>
                                    </div>
                                    <div class='form-group mt-5'>
                                        <div class='col-12 d-flex justify-content-center align-items-center'>
                                            <input class='btn submitAdminBtn' type='submit' name='submit' value='{{ trans('langModify') }}'>
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
@endsection