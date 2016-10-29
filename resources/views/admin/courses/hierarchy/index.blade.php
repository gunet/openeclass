@extends('layouts.default')

@section('content')
    {!! isset($action_bar) ?  $action_bar : '' !!}
    <table class='table-default'>
        <tr>
            <td colspan='{{ $maxdepth + 4 }}' class='right'>
                    {{ trans('langThereAre') }}: <b>{{ $nodesCount }}</b> {{ trans('langFaculties') }}
            </td>
        </tr>
        <tr>
            <td colspan='{{ $maxdepth + 4 }}'>
                <div id='js-tree'></div>
            </td>
        </tr>
    </table>    
@endsection