@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
    <div class='{{ $container }} main-container'>
        <div class="row m-auto">

            @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

            @include('layouts.partials.legend_view')

            @if(isset($action_bar))
                {!! $action_bar !!}
            @else
                <div class='mt-4'></div>
            @endif

            @include('layouts.partials.show_alert') 

            @if (!empty($unparsed_lines))
                <p>
                    <b>{{ trans('langErrors') }}</b>
                </p>
                <pre>{{ $unparsed_lines }}</pre>
            @endif
            <div class='col-sm-12'>
                <div class='table-responsive'>
                    <table class='table-default'>
                        <thead>
                        <tr class='list-header'>
                            <th>{{ trans('langSurname') }}</th>
                            <th>{{ trans('langName') }}</th>
                            <th>e-mail</th>
                            <th>{{ trans('langPhone') }}</th>
                            <th>{{ trans('langAm') }}</th>
                            <th>username</th>
                            <th>password</th>
                        </tr></thead>
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
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
