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

                    

                    <div class='col-12'>
                        <form class='form-wrapper form-edit rounded' action='{{ $_SERVER['SCRIPT_NAME'] }}' method='post'>
                            <fieldset>
                                <legend class='text-center'>{{ trans('langBasicCfgSetting') }}</legend>
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
                            <input class='btn  submitAdminBtn' type='submit' name='submit' value='{{ trans('langModify') }}'>
                            {!! generate_csrf_token_form_field() !!}
                        </form>
                    </div>
                </div>
            </div>
        </div>
   
</div>
@endsection