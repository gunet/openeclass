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

            {{-- C3 Plot --}}
            <div class='row plotscontainer ms-0'>
                <div id='userlogins_container' class='col-lg-12 p-0'>
                    {!! plot_placeholder("old_stats") !!}
                </div>
            </div>

            {{-- table data --}}
            <div class='col-4 text-center'>
                <div class='table-responsive'>
                    <table class='table-default'>
                        {{--@foreach ($recent_logins as $data)
                            <tr>
                                <td><strong>{{ $data[0] }}</strong></td>
                                <td class='text-right col-sm-1'>{{ $data[1] }}</td>
                            </tr>
                        @endforeach --}}
                        <th>
                            {{ trans('langMonth') }}
                        </th>
                        <th>
                            {{ trans('langNbLogin') }}
                        </th>
                        @foreach ($user_logins_data as $data)
                            @php
                                $formatted_data = date_format(date_create($data[0]), "n / Y")
                            @endphp
                            <tr>
                                <td>{{ $formatted_data }}</td>
                                <td class='text-right'>{{ $data[1] }}</td>
                            </tr>
                        @endforeach
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
