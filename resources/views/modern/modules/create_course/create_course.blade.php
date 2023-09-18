@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
<div class='{{ $container }}'>
        <div class="row m-auto">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

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
                            
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                    @endif

                    @if (!$deps_valid)
                        <div class='col-12'>
                            <div class='alert alert-danger'><i class='fa-solid fa-circle-xmark fa-lg'></i><span>
                                {{ trans('langCreateCourseNotAllowedNode') }}</span>
                            </div>
                            <p class='float-end'>
                                <a class='btn btn-secondary' href='create_course.php'>{{ trans('langBack') }}</a>
                            </p>
                        </div>
                    @else
                    <div class='col-12'>
                        <div class='alert alert-success'><i class='fa-solid fa-circle-check fa-lg'></i><span>
                            <b>{{ trans('langJustCreated') }} :</b> {{ $title }}<br>
                            <span class='smaller'>{{ trans('langEnterMetadata') }}</span></span>
                        </div>
                    </div>
                    @endif
                
        </div>
  
</div>
</div>

@endsection
