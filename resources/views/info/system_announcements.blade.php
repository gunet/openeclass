@extends('layouts.default')

@section('content')

    {!! $action_bar !!}
    <table id='ann_table_admin_logout' class='table-default'>
        <thead>
        <tr class='list-header'>
            <th>{{ trans('langAnnouncement') }}</th>
            <th>{{ trans('langDate') }}</th>
        </tr>
        </thead>
        <tbody></tbody>
    </table>

@endsection
