@extends('layouts.default')
@section('content')

    <div class="col-12 main-section">
        <div class='{{ $container }} main-container'>
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
                                } elseif(Session::get('alert-class', 'alert-info') == 'alert-info'){
                                    $alert_type = "<i class='fa-solid fa-circle-info fa-lg'></i>";
                                } elseif(Session::get('alert-class', 'alert-info') == 'alert-warning'){
                                    $alert_type = "<i class='fa-solid fa-triangle-exclamation fa-lg'></i>";
                                } else {
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
                @if ($display_form)
                    <div class='col-12'>
                        <div class='form-wrapper form-edit rounded mt-4'>
                            <form class='form-horizontal' method='post' action='{{ $_SERVER['SCRIPT_NAME'] }}'>

                                <div class='form-group'>
                                    <div class='col-12 control-label-notes mb-4'>
                                        {{ trans('langConfirm') }}
                                    </div>
                                </div>

                                <div class='col-12 d-flex justify-content-center align-items-center flex-wrap gap-2'>

                                    <button class='btn deleteAdminBtn' name='doit'>
                                        {{ trans('langUnregUser') }}
                                    </button>

                                    <a class='btn cancelAdminBtn' href='{{ $urlAppend }}main/profile/display_profile.php'>
                                        {{ trans('langCancel') }}
                                    </a>

                                </div>

                            </form>
                        </div>
                    </div>
                @endif

                @if ($user_deleted)
                    <div class='col-sm-12'>
                        <div class='alert alert-success'>
                            <i class='fa-solid fa-circle-check fa-lg'></i>
                            <span>{{ trans('langDelSuccess') }}
                                <p class="mt-2">{{ trans('langThanks') }}</p>
                            </span>
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>

@endsection
