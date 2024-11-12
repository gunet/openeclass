@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
    <div class='{{ $container }} main-container hierarchy'>
        <div class="row m-auto">

            @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

            @include('layouts.partials.legend_view')

            @if(isset($action_bar) and !empty($action_bar))
                {!! $action_bar !!}
            @else
                <div class='mt-4'></div>
            @endif

            @include('layouts.partials.show_alert')

            <div class='col-12'>
                <div class='table-responsive'>
                    <table class='table-default'>
                        <thead><tr class='list-header'>
                            <th colspan='{{ $maxdepth + 4 }}' class='right'>
                                    {{ trans('langThereAre') }}: <b>{{ $nodesCount }}</b> {{ trans('langFaculties') }}
                            </th>
                        </tr></thead>
                        <tr>
                            <td colspan='{{ $maxdepth + 4 }}'>
                                <div id='js-tree'></div>
                            </td>
                        </tr>
                    </table>
                </div>

            </div>

        </div>
    </div>
</div>
@endsection
