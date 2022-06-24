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

                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12'>
                        <form class='shadow-lg p-3 mb-5 bg-body rounded bg-primary' action='{{ $_SERVER['SCRIPT_NAME'] }}' method='post'>
                            <fieldset>
                                <legend>{{ trans('langBasicCfgSetting') }}</legend>
                                <table class='table table-bordered' width='100%'>
                                    <tr>
                                        <th width='200' class='left'>
                                            <b>{{ trans('langWafConnector') }}</b>
                                        </th>
                                        <td>
                                            <select name='formconnector'>
                                                {!! implode('', $connectorOptions) !!}
                                            </select>
                                        </td>
                                    </tr>
                                    @foreach($connectorClasses as $curConnectorClass)
                                    <?php $rules = (new $curConnectorClass())->getRules();?>
                                        @foreach((new $curConnectorClass())->getConfigFields() as $curField => $curLabel)
                                            <tr class='connector-config connector-{{ $curConnectorClass }}' style='display: none;'>
                                                <th width='200' class='left'>
                                                    <b>Rule {{ $curLabel }}</b>
                                                    <br><br>
                                                    <var>Impact: {{ $rules[$curField]['impact'] }}</var>
                                                </th>
                                                <td>
                                                    <input class='FormData_InputText' type='text' name='form{{ $curField }}' size='80'  value='{{ $rules[$curField]['rule'] }}' disabled>
                                                    <input class='FormData_InputText' type='text' name='form{{ $curField }}' size='80' value='{{ $rules[$curField]['description'] }}' disabled>
                                                </td>
                                                <td>  
                                                    <label class='col-sm-3 control-label'>{{ trans('langActivate') }}:</label>
                                                    <br>
                                                    <div class='col-sm-9 radio'>
                                                        <label>
                                                            <input  type='radio' id='{{ $curField }}' name='{{ $curField }}' value='0'{!! !get_config($curField) || get_config($curField) == 0 ? ' checked' : '' !!}> {{ trans('langNo') }}
                                                        </label>
                                                    </div>
                                                    <div class='col-sm-offset-3 col-sm-9 radio'>
                                                        <label>
                                                            <input  type='radio' id='{{ $curField }}' name='{{ $curField }}' value='1'{!! !get_config($curField) || get_config($curField) == 0 ? '' : ' checked' !!}>{{ trans('langYes') }}
                                                        </label>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endforeach           
                                </table>
                            </fieldset>
                            <input class='btn btn-primary' type='submit' name='submit' value='{{ trans('langModify') }}'>
                            {!! generate_csrf_token_form_field() !!}
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection