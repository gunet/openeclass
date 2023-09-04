@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
<div class='{{ $container }}'>
        <div class="row m-auto">

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
                    
                    @if (get_admin_rights($user) > 0)
                        <div class='col-12'>
                            <div class='alert alert-warning'>
                            <i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>
                                {{ trans('langCantDeleteAdmin', ["$u_realname ($u_account)"]) }}
                                {{ trans('langIfDeleteAdmin') }}
                            </span>
                            </div>
                        </div>
                    @else
                        <div class='col-12'>
                            <div class='alert alert-warning'>
                            <i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>{{ trans('langConfirmDeleteQuestion1') }} <em>{{ $u_realname }} ({{ $u_account }})</em><br>
                                {{ trans('langConfirmDeleteQuestion3') }}</span>
                            </div>
                        </div>

                        <div class='col-12'>
                            <form method='post' action='{{ $_SERVER['SCRIPT_NAME'] }}?u={{ $user }}'>
                                {!! showSecondFactorChallenge() !!}
                                <input class='btn deleteAdminBtn' type='submit' name='doit' value='{{ trans('langDelete') }}'>
                                {!! generate_csrf_token_form_field() !!}
                            </form>
                        </div>
                    @endif

               
        </div>
</div>
</div>
@endsection