@extends('layouts.default')

@section('content')
    {!! isset($action_bar) ?  $action_bar : '' !!}
    <div class='form-wrapper'>
        <form class='form-horizontal' action='{{ $_SERVER['SCRIPT_NAME'] }}' method='post'>
            <div class='form-group'>
                <label class='col-sm-3 control-label'>{{ trans('langAutoJudgeConnector') }}:</label>
                <div class='col-sm-8'>
                    <select class='form-control' name='formconnector'>{!! implode('', $connectorOptions) !!}</select>
                </div>
            </div>
            @foreach($connectorClasses as $curConnectorClass)
                <div class='form-group connector-config connector-{{ $curConnectorClass }}' style='display: none;'>
                    <label class='col-sm-3 control-label'>{{ trans('langAutoJudgeSupportedLanguages') }}:</label>
                    <div class='col-sm-8'>
                        {!! implode(', ', array_keys((new $curConnectorClass)->getSupportedLanguages())) !!}</div>
                </div>
                <div class='form-group connector-config connector-{{ $curConnectorClass }}' style='display: none;'>
                    <label class='col-sm-3 control-label'>{{ trans('langAutoJudgeSupportsInput') }}:</label>
                    <div class='col-sm-8'>
                        {{ (new $curConnectorClass)->supportsInput() ? trans("langCMeta['true']") : trans("langCMeta['false']") }}
                    </div>
                </div>
                @foreach((new $curConnectorClass())->getConfigFields() as $curField => $curLabel)
                      <div class='form-group connector-config connector-{{ $curConnectorClass }}' style='display: none;'>
                        <label class='col-sm-3 control-label'>{{ $curLabel }}:</label>
                        <div class='col-sm-8'><input class='FormData_InputText' type='text' name='form$curField' size='40' value='{{ get_config($curField) }}'></div>
                      </div>
                @endforeach
            @endforeach
            <div class='form-group'>
                <div class='col-sm-offset-3'>
                    {!! form_buttons(array(
                        array(
                            'text' => trans('langModify'),
                            'name' => 'submit',
                            'value'=> trans('langModify')
                        ),
                        array(
                            'href' => "extapp.php"
                        )
                    )) !!}
                </div>
            </div>
        </form>
    </div>
@endsection