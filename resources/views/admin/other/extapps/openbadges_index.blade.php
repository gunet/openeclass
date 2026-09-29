@extends('layouts.default')

@push('head_scripts')
<script>
$(document).ready(function() {
    $('.delete-provider').on('click', function(e) {
        e.preventDefault();
        const deleteUrl = $(this).data('url');
        const providerName = $(this).data('name');
        const userCount = $(this).data('users');
        
        let message = '';
        if (userCount > 0) {
            const warningText = {!! json_encode(trans('langBackpackProviderHasConnectedUsers')) !!}.replace('%s', userCount);
            const confirmText = {!! json_encode(trans('langBackpackProviderDeleteConfirm')) !!};
            message = "<p class='text-center'><strong style='color:#F57600;'>{{ trans('langWarnUpgrade') }}</strong> " + warningText + "</p>" +
                      "<p class='text-center'>" + confirmText + "</p>";
        } else {
            message = "<p class='text-center'>{{ trans('langConfirmDelete') }}</p>";
        }
        
        bootbox.confirm({
            closeButton: false,
            title: "<div class='icon-modal-default'><i class='fa-regular fa-trash-can fa-xl Accent-200-cl'></i></div><div class='modal-title-default text-center mb-0'>{{ trans('langConfirmDelete') }}</div>",
            message: message,
            buttons: {
                cancel: {
                    label: "{{ js_escape(trans('langCancel')) }}",
                    className: "cancelAdminBtn position-center"
                },
                confirm: {
                    label: "{{ js_escape(trans('langDelete')) }}",
                    className: "deleteAdminBtn position-center"
                }
            },
            callback: function(result) {
                if (result) {
                    // Create and submit a POST form
                    const form = $('<form>', {
                        'method': 'POST',
                        'action': deleteUrl
                    });
                    
                    // Add CSRF token
                    form.append($('<input>', {
                        'type': 'hidden',
                        'name': 'token',
                        'value': '{{ generate_csrf_token() }}'
                    }));
                    
                    $('body').append(form);
                    form.submit();
                }
            }
        });
    });
});
</script>
@endpush

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
                                                <div class="d-inline-flex gap-2">
                                                    <a href="{{ $_SERVER['SCRIPT_NAME'] }}?action=edit&id={{ getIndirectReference($provider->id) }}" 
                                                       class="btn btn-sm btn-primary" 
                                                       title="{{ trans('langEditChange') }}"
                                                       data-bs-toggle="tooltip">
                                                        <i class="fa fa-edit"></i>
                                                    </a>
                                                    <a href="#" 
                                                       class="btn btn-sm btn-danger delete-provider" 
                                                       data-url="{{ $_SERVER['SCRIPT_NAME'] }}?action=delete&id={{ getIndirectReference($provider->id) }}"
                                                       data-name="{{ $provider->name }}"
                                                       data-users="{{ $userCounts[$provider->id] ?? 0 }}"
                                                       title="{{ trans('langDelete') }}"
                                                       data-bs-toggle="tooltip">
                                                        <i class="fa fa-xmark"></i>
                                                    </a>
                                                </div>
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