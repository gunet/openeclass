@extends('layouts.default')

@section('content')

<div class="pb-lg-3 pt-lg-3 pb-0 pt-0">

    <div class="container-fluid main-container">

        <div class="row rowMedium">

            <div class="col-12 justify-content-center col_maincontent_active_Homepage">
                    
                <div class="row p-lg-5 p-md-5 ps-1 pe-1 pt-5 pb-5">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

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

                    {!! isset($action_bar) ?  $action_bar : '' !!}
                    
                    @if ($app->getName() == 'turnitin') 
                        <div class='col-12'>
                           <div class='text-center alert alert-warning'>Δεν έχει υλοποιηθεί ακόμα.</div>
                        </div>
                    @else
                        
                            <div class='col-lg-6 col-12 d-none d-md-none d-lg-block'>
                                <div class='col-12 h-100 left-form'></div>
                            </div>
                            <div class='col-lg-6 col-12'>
                                <div class='form-wrapper form-edit p-3 rounded'>
                                    
                                    <form class='form-horizontal' role='form' action='extapp.php?edit={{ $appName }}' method='post'>
                                        <fieldset>
                                        <?php $boolean_fields = [];?>
                                        @foreach ($app->getParams() as $param)
                                            @if ($param->getType() == ExtParam::TYPE_BOOLEAN)
                                                <?php $boolean_fields[] = $param; ?>
                                            @elseif ($param->getType() == ExtParam::TYPE_MULTILINE)
                                                
                                                <div class='form-group mt-3'>
                                                    <label for='{{ $param->name() }}' class='col-sm-12 control-label-notes'>{{ $param->display() }}</label>
                                                    <div class='col-sm-12'>
                                                        <textarea class='form-control' rows='3' cols='40' name='{{ $param->name() }}'>
                                                            {{ $param->value() }}
                                                        </textarea>
                                                    </div>
                                                </div>
                                            @else
                                                <div class='form-group mt-3'>
                                                    <label for='{{ $param->name() }}' class='col-sm-12 control-label-notes'>{{ $param->display() }}</label>
                                                    <div class='col-sm-12'>
                                                        <input class='form-control' type='text' name='{{ $param->name() }}' value='{{ $param->value() }}'>
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                        @foreach ($boolean_fields as $param)
                                                <div class='form-group mt-3'>
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
                                                <div class='col-12'>
                                                    <div class='row'>
                                                        <div class='col-md-6 col-4'>
                                                             <button class='btn btn-primary btn-sm submitAdminBtn w-100' type='submit' name='submit' value='{{ trans('langModify') }}'>
                                                                {{ trans('langModify') }}
                                                            </button> 
                                                        </div>
                                                        <div class='col-md-6 col-8'>
                                                            <button class='btn btn-danger btn-sm cancelAdminBtn w-100' type='submit' name='submit' value='clear'>
                                                                {{ trans('langClearSettings') }}
                                                            </button>
                                                        </div>
                                                    </div>
                                                   
                                                    
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
</div>
@endsection