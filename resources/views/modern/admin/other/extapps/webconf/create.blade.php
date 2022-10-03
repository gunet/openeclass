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
                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 all-alerts'>
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
                            
                            <form class='form-horizontal' role='form' name='serverForm' action='{{ $_SERVER['SCRIPT_NAME'] }}' method='post'>
                            <fieldset>
                                <div class='form-group mt-3'>
                                    <label for='host' class='col-sm-12 control-label-notes'>{{ trans('langWebConfServer') }}</label>
                                    <div class='col-sm-12'>
                                        <input class='form-control' placeholder="{{ trans('langWebConfServer') }}..." id='host' type='text' name='hostname_form' value="{{ isset($server) ? $server->hostname : ''}}">
                                    </div>
                                </div>            
                                <div class='form-group mt-3'>
                                    <label class='col-sm-12 control-label-notes'>{{ trans('langActivate') }}</label>
                                    <div class='col-sm-12 d-inline-flex'>
                                        <div class='radio'>
                                            <label>
                                                <input  type='radio' id='enabled_true' name='enabled' value='true'{{ $enabled ? ' checked' : '' }}>
                                                {{ trans('langYes') }}
                                            </label>                    
                                        </div>                     
                                        <div class='radio ms-2'>
                                            <label>
                                                <input  type='radio' id='enabled_false' name='enabled' value='false'{{ $enabled ? '' : ' checked' }}>
                                                {{ trans('langNo') }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class='form-group mt-3'>
                                    <label class='col-sm-12 control-label-notes'>{{ trans('langUseOfTc') }}</label>                
                                    <div class="col-sm-12">
                                        <select class='form-select' name='tc_courses[]' multiple class='form-control' id='select-courses'>
                                            {!! $listcourses !!}
                                        </select>            
                                        <a href='#' id='selectAll'>{{ trans('langJQCheckAll') }}</a> | <a href='#' id='removeAll'>{{ trans('langJQUncheckAll') }}</a>                
                                    </div>
                                </div>
                                @if (isset($server))
                                    <input class='form-control' type = 'hidden' name = 'id_form' value='{{ $wc_server }}'>
                                @endif
                                <div class='form-group mt-5'>
                                    <div class='col-12'>
                                        <input class='btn btn-sm btn-primary submitAdminBtn w-100' type='submit' name='submit' value='{{ trans('langAddModify') }}'>
                                    </div>
                                </div>
                            </fieldset>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<script language="javaScript" type="text/javascript">
    var chkValidator  = new Validator("serverForm");
    chkValidator.addValidation("hostname_form","req", "{{ trans('langWebConfServerAlertHostname') }}");        
</script>    
@endsection