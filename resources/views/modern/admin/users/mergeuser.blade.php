@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
<div class='{{ $container }} main-container'>
        <div class="row m-auto">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])
                    

                    @include('layouts.partials.legend_view')
                   
                    @if(!$merge_completed){!! isset($action_bar) ?  $action_bar : '' !!}@endif
                    
                    @include('layouts.partials.show_alert') 

                    @if (isset($_REQUEST['u']) and !$merge_completed)
                       
                        <div class='col-lg-6 col-12'>
                            <div class='form-wrapper form-edit border-0 px-0'>
                                
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
                                    <div class='col-12 mt-5 d-flex justify-content-end align-items-center'>                                                  
                                        <input class='btn submitAdminBtn' type='submit' name='submit' value='{{ $submit_button }}'>
                                    </div>                                                  
                                </fieldset>
                                {!! $target_user_input !!}
                                {!! generate_csrf_token_form_field() !!}
                                </form>
                            </div>
                        </div>
                        <div class='col-lg-6 col-12 d-none d-md-none d-lg-block text-end'>
                        <img class='form-image-modules' src='{!! get_form_image() !!}' alt='form-image'>
                        </div>
                    @elseif($merge_completed)
                        <p class='mt-3'><a href='search_user.php'>{{trans('langBack')}}</p>
                    @else
                        <div class='col-12'>
                            <div class='alert alert-danger'><i class='fa-solid fa-circle-xmark fa-lg'></i><span>
                                {{ trans('langError') }}<br>
                                <a href='search_user.php'>{{ trans('langBack') }}</span>
                            </div>
                        </div>
                    @endif
               
        </div>
</div>
</div>
@endsection