@extends('layouts.default')

@section('content')

<div class="pb-3 pt-3">

    <div class="container-fluid main-container">

        <div class="row">

            <div class="col-xl-2 col-lg-2 col-md-0 col-sm-0 col-0 justify-content-center col_sidebar_active"> 
                <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block">
                    @include('layouts.partials.sidebarAdmin')
                </div>
            </div>

            <div class="col-xl-10 col-lg-10 col-md-12 col-sm-12 col-12 justify-content-center col_maincontent_active">
                    
                <div class="row p-5">

                    <nav class="navbar navbar-expand-lg navrbar_menu_btn">
                        <button type="button" id="menu-btn" class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block btn btn-primary menu_btn_button">
                            <i class="fas fa-align-left"></i>
                            <span></span>
                        </button>
                        
                    
                        <a class="btn btn-primary d-lg-none mr-auto" type="button" data-bs-toggle="offcanvas" href="#collapseTools" role="button" aria-controls="collapseTools" style="margin-top:-10px;">
                            <i class="fas fa-tools"></i>
                        </a>
                    </nav>

                    <nav class="navbar_breadcrumb" aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <?php $size_breadcrumb = count($breadcrumbs); $count=0; ?>
                            <?php for($i=0; $i<$size_breadcrumb; $i++){ ?>
                                <li class="breadcrumb-item"><a href="{!! $breadcrumbs[$i]['bread_href'] !!}">{!! $breadcrumbs[$i]['bread_text'] !!}</a></li>
                            <?php } ?> 
                        </ol>
                    </nav>

                    <div class="offcanvas offcanvas-start d-lg-none mr-auto" tabindex="-1" id="collapseTools" aria-labelledby="offcanvasExampleLabel">
                        <div class="offcanvas-header">
                            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                        </div>
                        <div class="offcanvas-body">
                        @include('layouts.partials.sidebarAdmin')
                        </div>
                    </div>

                    @if($breadcrumbs && count($breadcrumbs)>2)
                    <div class='row p-2'></div>
                    <div class="float-start">
                        <p class='control-label-notes'>{!! $breadcrumbs[1]['bread_text'] !!}</p>
                        <small class='text-secondary'>{!! $breadcrumbs[count($breadcrumbs)-1]['bread_text'] !!}</small>
                    </div>
                    <div class='row p-2'></div>
                    @endif

                    {!! isset($action_bar) ?  $action_bar : '' !!}

                    @if ($app->getName() == 'turnitin') 
                        <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12'>
                           <div class='text-center alert alert-warning'>Δεν έχει υλοποιηθεί ακόμα.</div>
                        </div>
                    @else
                        <div class='extapp'>
                            <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12'>
                                <div class='form-wrapper shadow-lg p-3 mb-5 bg-body rounded bg-primary'>
                                    <form class='form-horizontal' role='form' action='extapp.php?edit={{ $appName }}' method='post'>
                                        <fieldset>
                                        <?php $boolean_fields = [];?>
                                        @foreach ($app->getParams() as $param)
                                            @if ($param->getType() == ExtParam::TYPE_BOOLEAN)
                                                <?php $boolean_fields[] = $param; ?>
                                            @elseif ($param->getType() == ExtParam::TYPE_MULTILINE)
                                                
                                                <div class='form-group mt-3'>
                                                    <label for='{{ $param->name() }}' class='col-sm-6 control-label-notes'>{{ $param->display() }}</label>
                                                    <div class='col-sm-12'>
                                                        <textarea class='form-control' rows='3' cols='40' name='{{ $param->name() }}'>
                                                            {{ $param->value() }}
                                                        </textarea>
                                                    </div>
                                                </div>
                                            @else
                                                <div class='form-group mt-3'>
                                                    <label for='{{ $param->name() }}' class='col-sm-6 control-label-notes'>{{ $param->display() }}</label>
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
                                            <div class='form-group mt-3'>
                                                <div class='col-sm-offset-2 col-sm-10'>
                                                    <button class='btn btn-primary' type='submit' name='submit' value='{{ trans('langModify') }}'>
                                                        {{ trans('langModify') }}
                                                    </button> 
                                                    <button class='btn btn-danger' type='submit' name='submit' value='clear'>
                                                        {{ trans('langClearSettings') }}
                                                    </button>
                                                </div>
                                            </div>
                                        </fieldset>
                                        {!! generate_csrf_token_form_field() !!}
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection