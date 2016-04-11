@extends('layouts.default')

@section('content')
    {!! isset($action_bar) ?  $action_bar : '' !!}
    <div class='row extapp'>
        <div class='col-xs-12'>
            <form action='{{ $_SERVER['SCRIPT_NAME'] }}' method='post'>
                <fieldset>
                <table class='table table-bordered' width='100%'>
                    <tr>
                       <th width='200' class='left'>
                           <b>{{ trans('langAntivirusConnector') }}</b>
                       </th>
                       <td>
                           <select name='formconnector'>{!! implode('', $connectorOptions) !!}</select>
                       </td>
                    </tr>
                    @foreach($connectorClasses as $curConnectorClass)
                        @foreach((new $curConnectorClass())->getConfigFields() as $curField => $curLabel)
                            <tr class='connector-config connector-{{ $curConnectorClass }}' style='display: none;'>
                                <th width='200' class='left'>
                                    <b>{{ $curLabel }}</b>
                                </th>
                                <td>
                                    <input class='FormData_InputText' type='text' name='form{{ $curField }}' size='40' value='{{ get_config($curField) }}'>
                                </td>
                            </tr>
                        @endforeach
                    @endforeach
                </table>
                </fieldset>
                {!! generate_csrf_token_form_field() !!}
                <input class='btn btn-primary' type='submit' name='submit' value='{{ trans('langModify') }}'>
            </form>
        </div>
    </div>
@endsection