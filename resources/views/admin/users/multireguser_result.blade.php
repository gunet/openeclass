@extends('layouts.default')

@section('content')
    {!! isset($action_bar) ?  $action_bar : '' !!}
    @if (!empty($unparsed_lines))
        <p>
            <b>{{ trans('langErrors') }}</b>
        </p>
        <pre>{{ $unparsed_lines }}</pre>
    @endif
    <table class='table-default'>
        <tr>
            <th>{{ trans('langSurname') }}</th>
            <th>{{ trans('langName') }}</th>
            <th>e-mail</th>
            <th>{{ trans('langPhone') }}</th>
            <th>{{ trans('langAm') }}</th>
            <th>username</th>
            <th>password</th>
        </tr>
        @foreach ($new_users_info as $n)
            <tr>
                <td>{{ $n[1] }}</td>
                <td>{{ $n[2] }}</td>
                <td>{{ $n[3] }}</td>
                <td>{{ $n[4] }}</td>
                <td>{{ $n[5] }}</td>
                <td>{{ $n[6] }}</td>
                <td>{{ $n[7] }}</td>
            </tr>
        @endforeach
    </table>              
@endsection