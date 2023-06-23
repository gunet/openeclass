@extends('layouts.default')

@section('content')

<div class="col-12 basic-section p-xl-5 px-lg-3 py-lg-5">

        <div class="row rowMargin">

            <div class="col-12 col_maincontent_active_Homepage">
                    
                <div class="row">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])
                   
                    @if(!$merge_completed){!! isset($action_bar) ?  $action_bar : '' !!}@endif
                    
                    @if(Session::has('message'))
                    <div class='col-12 all-alerts'>
                        <div class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                            @if(is_array(Session::get('message')))
                                @php $messageArray = array(); $messageArray = Session::get('message'); @endphp
                                @foreach($messageArray as $message)
                                    {!! $message !!}
                                @endforeach
                            @else
                                {!! Session::get('message') !!}
                            @endif
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                    @endif

                   
                    
                    @if (isset($_REQUEST['u']) and !$merge_completed)
                        <div class='col-lg-6 col-12 d-none d-md-none d-lg-block'>
                            <div class='col-12 h-100 left-form'></div>
                        </div>
                        <div class='col-lg-6 col-12'>
                            <div class='form-wrapper form-edit rounded'>
                                
                                <form class='form-horizontal' role='form' method='post' action='{{ $_SERVER['SCRIPT_NAME'] }}'>
                                <fieldset>                                    
                                    <div class='form-group'>
                                        <label class='col-sm-12 control-label-notes'>{{ trans('langUser') }}</label>
                                        <div class='col-sm-12'>
                                            {!! display_user($info['id']) !!}
                                        </div>
                                    </div>
                                    <div class='form-group mt-4'>
                                        <label class='col-sm-12 control-label-notes'>{{ trans('langEditAuthMethod') }}</label>
                                        <div class='col-sm-12'>{!! get_auth_info($auth_id) !!}</div>
                                    </div>
                                    <div class='form-group mt-4'>
                                        <label class='col-sm-12 control-label-notes'>{{ trans('langProperty') }}</label>                     
                                        <div class='col-sm-12'>{!! $status_names[$info['status']] !!}</div>
                                    </div>                    
                                    {!! $target_field !!}
                                    <input type='hidden' name='u' value='{{ intval($u) }}'>
                                    {!! showSecondFactorChallenge() !!}
                                    <div class='col-12 mt-5 d-flex justify-content-center align-items-center'>                                                  
                                        <input class='btn submitAdminBtn' type='submit' name='submit' value='{{ $submit_button }}'>
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
                        <div class='col-12'>
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
@endsection