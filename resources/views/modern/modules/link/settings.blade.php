@extends('layouts.default')


@section('content')


<div class="col-12 basic-section p-xl-5 px-lg-3 py-lg-5">

        <div class="row rowMargin">

            <div id="background-cheat-leftnav" class="col-xl-2 col-lg-3 col_sidebar_active d-flex justify-content-start align-items-strech ps-lg-0 pe-lg-3"> 
                <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block ContentLeftNav">
                    @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                </div>
            </div>

            <div class="col-xl-10 col-lg-9 col-12 col_maincontent_active p-lg-5">
                    
                <div class="row">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])


                    <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="collapseTools" aria-labelledby="offcanvasExampleLabel">
                        <div class="offcanvas-header">
                            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                        </div>
                        <div class="offcanvas-body">
                            @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                        </div>
                    </div>


                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])
               


                        {!! isset($action_bar) ?  $action_bar : '' !!}<div class="row p-2"></div>

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

                        <div class='col-sm-12'>
                            <div class='form-wrapper form-edit rounded'>
                                <form class='form-horizontal' role='form' method='post' action='index.php?course={{ $course_code }}'>
                                <fieldset>                 
                                             
                                    <div class='form-group'>
                                        <label class='col-sm-6 control-label-notes mb-2'>{{ trans('langSocialBookmarksFunct') }}</label>
                                        <div class='col-sm-12'> 
                                            <div class='radio mb-2'>
                                                <label>
                                                    <input type='radio' value='1' name='settings_radio'{{ $social_enabled ? " checked" : "" }}> {{ trans('langActivate') }}
                                                </label>
                                            </div>
                                            <div class='radio'>
                                                <label>
                                                    <input type='radio' value='0' name='settings_radio' {{ $social_enabled ? "" : " checked" }}> {{ trans('langDeactivate') }}
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                            
                                    
                                    <div class='form-group mt-5'>
                                        <div class='col-12 d-flex justify-content-start align-items-center'>
                                           
                                                
                                                    <input type='submit' class='btn submitAdminBtn' name='submitSettings' value='{{ trans('langSubmit') }}' />
                                               
                                              
                                                    <a href='index.php?course={{ $course_code }}' class='btn cancelAdminBtn ms-1'>{{ trans('langCancel') }}</a>
                                               
                                            
                                            
                                            
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
@endsection