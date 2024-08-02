@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
<div class='{{ $container }} main-container'>
        <div class="row m-auto">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])
                    

                    @include('layouts.partials.legend_view')

                    @if(isset($action_bar))
                        {!! $action_bar !!}
                    @else
                        <div class='mt-4'></div>
                    @endif

                    @include('layouts.partials.show_alert') 

                    @if ($app->getName() == 'turnitin') 
                        <div class='col-12'>
                           <div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>{{ trans('langNoInfoAvailable')}}</span></div>
                        </div>
                    @else
                        
                            
                            <div class='col-lg-6 col-12'>
                                <div class='form-wrapper form-edit border-0 px-0'>
                                    
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
                                                        <label class='label-container'>
                                                                <input type='checkbox' name='{{ $param->name() }}'{!! $param->value() == 1 ? " value='0' checked" : " value='1'" !!}> 
                                                                <span class='checkmark'></span>{{ $param->display() }}
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                        @endforeach
                                            <div class='form-group mt-5'>
                                                <div class='col-12 d-flex justify-content-end align-items-center'>
                                                
                                                       
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
                            <div class='col-lg-6 col-12 d-none d-md-none d-lg-block text-end'>
                            <img class='form-image-modules' src='{!! get_form_image() !!}' alt="{{ trans('langImgFormsDes') }}">
                            </div>
                        
                    @endif
                
        </div>
</div>   
</div>
@endsection