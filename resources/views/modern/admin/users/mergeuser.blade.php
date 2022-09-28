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

                    @if(!$merge_completed){!! isset($action_bar) ?  $action_bar : '' !!}@endif
                    
                    @if (isset($_REQUEST['u']) and !$merge_completed)
                        <div class='col-lg-6 col-12 d-none d-md-none d-lg-block'>
                            <div class='col-12 h-100 left-form'></div>
                        </div>
                        <div class='col-lg-6 col-12'>
                            <div class='form-wrapper shadow-sm p-3 rounded'>
                                
                                <form class='form-horizontal' role='form' method='post' action='{{ $_SERVER['SCRIPT_NAME'] }}'>
                                <fieldset>                                    
                                    <div class='form-group mt-3'>
                                        <label class='col-sm-12 control-label-notes'>{{ trans('langUser') }}</label>
                                        <div class='col-sm-12'>
                                            {!! display_user($info['id']) !!}
                                        </div>
                                    </div>
                                    <div class='form-group mt-3'>
                                        <label class='col-sm-12 control-label-notes'>{{ trans('langEditAuthMethod') }}</label>
                                        <div class='col-sm-12'>{!! get_auth_info($auth_id) !!}</div>
                                    </div>
                                    <div class='form-group mt-3'>
                                        <label class='col-sm-12 control-label-notes'>{{ trans('langProperty') }}</label>                     
                                        <div class='col-sm-12'>{!! $status_names[$info['status']] !!}</div>
                                    </div>                    
                                    {!! $target_field !!}
                                    <input type='hidden' name='u' value='{{ intval($u) }}'>
                                    {!! showSecondFactorChallenge() !!}
                                    <div class='col-12 mt-5'>                                                  
                                        <input class='btn btn-sm btn-primary submitAdminBtn w-100' type='submit' name='submit' value='{{ $submit_button }}'>
                                    </div>                                                  
                                </fieldset>
                                {!! $target_user_input !!}
                                {!! generate_csrf_token_form_field() !!}
                                </form>
                            </div>
                        </div>
                    @elseif($merge_completed)
                        <p class='mt-3'><a href='search_user.php'>{{trans('langBack')}}</p>
                    @else
                        <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12'>
                            <div class='alert alert-danger'>
                                {{ trans('langError') }}<br>
                                <a href='search_user.php'>{{ trans('langBack') }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection