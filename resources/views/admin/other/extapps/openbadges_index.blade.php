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

                    @if(count($providers) > 0)
                        <div class="table-responsive">
                            <table class="table-default">
                                <thead>
                                    <tr class="list-header">
                                        <th>{{ trans('langBackpackProvider') }}</th>
                                        <th>{{ trans('langBackpackProviderUrl') }}</th>
                                        <th>{{ trans('langOpenBadgeVersion') }}</th>
                                        <th>{{ trans('langBackpackExternalProviderEnabled') }}</th>
                                        <th class="text-end" aria-label="{{ trans('langSettingSelect') }}">
                                            {!! icon('fa-gears') !!}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($providers as $provider)
                                        <tr>
                                            <td class="p-4">{{ $provider->name }}</td>
                                            <td class="p-4">{{ $provider->api_url }}</td>
                                            <td class="p-4">{{ $provider->ob_version }}</td>
                                            <td class="p-4">
                                                @if($provider->isEnabled())
                                                    {{ trans('langYes') }}
                                                @else
                                                    {{ trans('langNo') }}
                                                @endif
                                            </td>
                                            <td class="option-btn-cell text-end p-20">
                                                {!! action_button([
                                                    [
                                                        'title' => trans('langEditChange'),
                                                        'url' => $_SERVER['SCRIPT_NAME'] . '?action=edit&id=' . getIndirectReference($provider->id),
                                                        'icon' => 'fa-edit'
                                                    ],
                                                    [
                                                        'title' => trans('langDelete'),
                                                        'url' => $_SERVER['SCRIPT_NAME'] . '?action=delete&id=' . getIndirectReference($provider->id),
                                                        'icon' => 'fa-xmark',
                                                        'class' => 'delete',
                                                        'confirm' => trans('langConfirmDelete')
                                                    ]
                                                ]) !!}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="col-sm-12">
                            <div class="alert alert-warning">
                                <i class="fa-solid fa-triangle-exclamation fa-lg"></i>
                                <span>{{ trans('langNoAvailableBackpackProvider') }}</span>
                            </div>
                        </div>
                    @endif

        </div>
</div>
</div>
@endsection 