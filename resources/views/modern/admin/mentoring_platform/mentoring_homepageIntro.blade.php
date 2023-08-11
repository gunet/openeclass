@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
    <div class='{{ $container }}'>
        <div class="row m-auto">
                    @include('layouts.partials.legend_view')

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

                    <div class='col-8'>{!! $action_bar !!}</div>
                    @if (!$modify and !$new)
                    <div class='col-4'>
                        <div class="dropdown float-end">
                            <button class="btn dropdown-toggle" type="button" id="dropdownMenuLang" data-bs-display='static' data-bs-toggle="dropdown" aria-expanded="false">
                                {{ trans('langLanguage') }}
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuLang">
                                @foreach ($session->active_ui_languages as $code)
                                    <li>
                                        <a class="dropdown-item" href="{{ $_SERVER['SCRIPT_NAME'] }}?lang={{ $code }}">
                                            {!! $native_language_names_init[$code] !!}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    @endif
                    
                    @if ($modify || $new)
                        
                        

                        <div class='col-lg-6 col-12'>
                            <div class='form-wrapper form-edit rounded'>
                                <form role='form' class='form-horizontal' method='post' action='{{ $_SERVER['SCRIPT_NAME'] }}'>

                                    <input type='hidden' name='langText' value='{{ $langCode }}'>

                                    <div class='form-group'>
                                        <label for='answer' class='col-sm-12 control-label-notes'>{{ trans('langCont') }}</label>
                                        <div class='col-sm-12'>{!! $editor !!}</div>
                                    </div>
                                   
                                    <div class='form-group mt-5'>
                                        <div class='col-12 d-flex justify-content-center align-items-center'>

                                                <button type="submit" class="btn submitAdminBtn" name="submit">{{ trans('langSave') }}</button>
                                                <a href="{{ $_SERVER['SCRIPT_NAME'] }}" class="btn cancelAdminBtn ms-1">{{ trans('langCancel') }}</a>

                                        </div>
                                    </div>
                                </form>
                               
                            </div>
                        </div>

                        <div class='col-lg-6 col-12 d-none d-md-none d-lg-block'>
                            <div class='col-12 h-100 left-form'></div>
                        </div>
                        
                    @else
                            
                            @if(get_config($intro))
                                <div class='col-12'>
                                    <div class='panel panel-default mb-3 rounded-2'>
                                        <div class='panel-heading rounded-1 bg-light'>
                                            <div class='d-inline-flex justify-content-end align-items-center float-end'>
                                                <a class='me-2' href='{{ $urlAppend }}modules/admin/mentoring_homepageIntro.php?modify&lang={{ $langCode }}'>
                                                    <span class='fa fa-edit pe-1 text-primary' data-bs-toggle='tooltip' data-bs-placement='top' title='{{trans('langEdit')}}'></span>
                                                </a>

                                                <a href='{{ $urlAppend }}modules/admin/mentoring_homepageIntro.php?del&lang={{ $langCode }}'><span class='fa fa-times text-danger' data-bs-toggle='tooltip' data-bs-placement='top' title='{{ trans('langDelete') }}'></span></a>
                                            </div>
                                        </div>
                                        
                                        <div class='panel-body rounded-2'>
                                            {!! get_config($intro) !!}
                                        </div>
                                    </div>
                                </div>
                            @else
                               <div class='col-12'>
                                   <div class='alert alert-warning'>{{trans('langNoInfoAvailable')}}</div>
                               </div>
                            @endif
                        
                    @endif

               
        </div>
    </div>
</div>


@endsection
