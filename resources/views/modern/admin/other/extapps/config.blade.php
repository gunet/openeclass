@extends('layouts.default')

@section('content')

<div class="col-12 basic-section p-xl-5 px-lg-3 py-lg-5">

        <div class="row rowMargin">

            <div class="col-12 col_maincontent_active_Homepage">
                    
                <div class="row">

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

                    
                    
                    @if ($app->getName() == 'turnitin') 
                        <div class='col-12'>
                           <div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>{{ trans('langNoInfoAvailable')}}</span></div>
                        </div>
                    @else
                        
                            <div class='col-lg-6 col-12 d-none d-md-none d-lg-block'>
                                <div class='col-12 h-100 left-form'></div>
                            </div>
                            <div class='col-lg-6 col-12'>
                                <div class='form-wrapper form-edit rounded'>
                                    
                                    <form class='form-horizontal' role='form' action='extapp.php?edit={{ $appName }}' method='post'>
                                        <fieldset>
                                        <?php $boolean_fields = [];?>
                                        @foreach ($app->getParams() as $param)
                                            @if ($param->getType() == ExtParam::TYPE_BOOLEAN)
                                                <?php $boolean_fields[] = $param; ?>
                                            @elseif ($param->getType() == ExtParam::TYPE_MULTILINE)
                                                
                                                <div class='form-group mt-4'>
                                                    <label for='{{ $param->name() }}' class='col-sm-12 control-label-notes'>{{ $param->display() }}</label>
                                                    <div class='col-sm-12'>
                                                        <textarea class='form-control' rows='3' cols='40' name='{{ $param->name() }}'>
                                                            {{ $param->value() }}
                                                        </textarea>
                                                    </div>
                                                </div>
                                            @else
                                                <div class='form-group mt-4'>
                                                    <label for='{{ $param->name() }}' class='col-sm-12 control-label-notes'>{{ $param->display() }}</label>
                                                    <div class='col-sm-12'>
                                                        <input class='form-control' type='text' name='{{ $param->name() }}' value='{{ $param->value() }}'>
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                        @foreach ($boolean_fields as $param)
                                                <div class='form-group mt-4'>
                                                    <div class='col-sm-offset-2 col-sm-10'>
                                                        <div class='checkbox'>
                                                            <label>
                                                                <input type='checkbox' name='{{ $param->name() }}'{!! $param->value() == 1 ? " value='0' checked" : " value='1'" !!}> 
                                                                {{ $param->display() }}
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                        @endforeach
                                            <div class='form-group mt-5'>
                                                <div class='col-12 d-flex justify-content-center align-items-center'>
                                                
                                                       
                                                             <button class='btn submitAdminBtn' type='submit' name='submit' value='{{ trans('langModify') }}'>
                                                                {{ trans('langModify') }}
                                                            </button> 
                                                     
                                                      
                                                            <button class='btn deleteAdminBtn ms-1' type='submit' name='submit' value='clear'>
                                                                {{ trans('langClearSettings') }}
                                                            </button>
                                                     
                                                   
                                                   
                                                    
                                                </div>
                                            </div>
                                        </fieldset>
                                        {!! generate_csrf_token_form_field() !!}
                                    </form>
                                </div>
                            </div>
                        
                    @endif
                </div>
            </div>
        </div>
</div>
@endsection