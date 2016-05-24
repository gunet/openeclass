@extends('layouts.default')

@section('content')
    {!! isset($action_bar) ?  $action_bar : '' !!}
    <form action='{{ $_SERVER['SCRIPT_NAME'] }}' method='post'>
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
@endsection