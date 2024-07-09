@extends('layouts.default')

@section('content')

<main id="main" class="col-12 main-section">
    <div class='{{ $container }} module-container py-lg-0'>
        <div class="course-wrapper d-lg-flex align-items-lg-strech w-100">

            <nav id="background-cheat-leftnav" class="col_sidebar_active d-flex justify-content-start align-items-strech ps-lg-0 pe-lg-0"> 
                <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block ContentLeftNav">
                    @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                </div>
            </nav>

            <div class="col_maincontent_active">
                    
                <div class="row">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="collapseTools">
                        <div class="offcanvas-header">
                            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                        </div>
                        <div class="offcanvas-body">
                            @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                        </div>
                    </div>

                    @include('layouts.partials.legend_view')
                    
                    {!! $action_bar !!}

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
                                
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        </div>
                    @endif

                    <div class='d-lg-flex gap-4'>
                        <div class='flex-grow-1'>
                            @if(count($all_sessions) > 0)
                                <div class='alert alert-info'><i class='fa-solid fa-circle-info fa-lg'></i><span>{!! trans('langCompletedConsultingInfo') !!}</span></div>
                                <div class='form-wrapper form-edit'>
                                    <form role='form' class='form-horizontal' action="{{ $_SERVER['SCRIPT_NAME'] }}?course={{ $course_code }}" method='post'>
                                        <fieldset>
                                            <div class='form-group'>
                                                <div class='table-responsive mt-0'>
                                                    <table class='table-default'>
                                                        <thead>
                                                            <tr>
                                                                <th>{{ trans('langSession') }}</th>
                                                                <th></th>
                                                            </tr>
                                                        </thead>
                                                        @foreach($all_sessions as $s)
                                                            <tr>
                                                                <td>
                                                                    <a class='link-color' href='{{ $urlAppend }}modules/session/session_space.php?course={{ $course_code }}&session={{ $s->id }}'>
                                                                        {{ $s->title }}
                                                                    </a>
                                                                </td>
                                                                <td>
                                                                    <div class='d-flex justify-content-end'>
                                                                        <div class='checkbox'>
                                                                            <label class='label-container'>
                                                                                <input type='checkbox' name='sessions_completed[]' value='{{ $s->id }}' {!! in_array($s->id,$session_ids) ? 'checked' : '' !!}>
                                                                                <span class='checkmark'></span>
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </table>
                                                </div>
                                            </div>
                                            {!! generate_csrf_token_form_field() !!}    
                                            <div class='form-group mt-5'>
                                                <div class='col-12 d-flex justify-content-end aling-items-center'>
                                                    <input class='btn submitAdminBtn' type='submit' name='submit' value='{{ trans('langSubmit') }}'>
                                                </div>
                                            </div>
                                        </fieldset>
                                    </form>
                                </div>
                            @else
                                <div class='alert alert-warning'>
                                    <i class='fa-solid fa-triangle-exclamation fa-lg'></i>
                                    <span>{{ trans('langNoInfoAvailable')}}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</main>






@endsection
