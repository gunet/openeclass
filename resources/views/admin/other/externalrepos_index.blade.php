@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
    <div class='{{ $container }} main-container'>
        <div class="row m-auto">

            @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

            @include('layouts.partials.legend_view')

            <div class='col-12 mt-4'>
                <div class='d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4'>
                    <h3 class='mb-0'>{{ trans('langExternalRepos') }}</h3>
                    <a href='{{ $urlAppend }}modules/admin/externalreposconf.php?add=1' class='btn submitAdminBtn'>
                        <i class='fa fa-plus me-2'></i>{{ trans('langAddExternalRepo') }}
                    </a>
                </div>
            </div>

            @include('layouts.partials.show_alert')

            <div class='col-12'>
                <div class='alert alert-info'>
                    <i class='fa-solid fa-circle-info fa-lg me-2'></i>
                    <span>{{ trans('langExternalReposInfo') }}</span>
                </div>
            </div>

            <div class='col-12'>
                <div class='card panelCard px-lg-4 py-lg-3 p-3'>
                    <div class='card-header border-0'>
                        <h3>{{ trans('langConfiguredRepositories') }}</h3>
                    </div>
                    <div class='card-body'>
                        @if (count($repositories) > 0)
                            <div class='table-responsive'>
                                <table class='table table-striped table-hover'>
                                    <thead>
                                        <tr>
                                            <th class='align-middle'>{{ trans('langName') }}</th>
                                            <th class='align-middle'>{{ trans('langType') }}</th>
                                            <th class='align-middle'>{{ trans('langBaseUrl') }}</th>
                                            <th class='align-middle'>{{ trans('langStatus') }}</th>
                                            <th class='text-end align-middle'>{{ trans('langActions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($repositories as $repo)
                                            @php
                                                $typeInfo = $repositoryTypes[$repo->type] ?? ['name' => $repo->type, 'icon' => 'fa-database'];
                                            @endphp
                                            <tr data-repo-id='{{ $repo->id }}' class='align-middle'>
                                                <td class='align-middle'>
                                                    <i class='fa-brands {{ $typeInfo['icon'] }} me-2'></i>
                                                    <strong>{{ $repo->name }}</strong>
                                                </td>
                                                <td class='align-middle'>
                                                    <span class='badge bg-secondary'>{{ $typeInfo['name'] }}</span>
                                                </td>
                                                <td class='align-middle'>
                                                    @if ($repo->base_url)
                                                        <small class='text-muted'>{{ strlen($repo->base_url) > 40 ? substr($repo->base_url, 0, 40) . '...' : $repo->base_url }}</small>
                                                    @else
                                                        <small class='text-muted'>-</small>
                                                    @endif
                                                </td>
                                                <td class='align-middle'>
                                                    @if ($repo->enabled)
                                                        <span class='badge bg-success'>
                                                            <i class='fa fa-check-circle me-1'></i>{{ trans('langActive') }}
                                                        </span>
                                                    @else
                                                        <span class='badge bg-secondary'>
                                                            <i class='fa fa-times-circle me-1'></i>{{ trans('langInactive') }}
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class='text-end align-middle'>
                                                    <div class='btn-group btn-group-sm' style='gap: 0.25rem;'>
                                                        <button type='button' class='btn btn-info test-repo-btn px-3 py-2 text-white' 
                                                                data-repo-id='{{ $repo->id }}'
                                                                title='{{ trans('langTestConnection') }}'>
                                                            <i class='fa fa-plug text-white'></i>
                                                        </button>
                                                        <button type='button' class='btn btn-{{ $repo->enabled ? 'warning' : 'success' }} toggle-repo-btn px-3 py-2 text-white' 
                                                                data-repo-id='{{ $repo->id }}'
                                                                data-enabled='{{ $repo->enabled ? 0 : 1 }}'
                                                                title='{{ $repo->enabled ? trans('langDeactivate') : trans('langActivate') }}'>
                                                            <i class='fa fa-toggle-{{ $repo->enabled ? 'on' : 'off' }} text-white'></i>
                                                        </button>
                                                        <a href='{{ $urlAppend }}modules/admin/externalreposconf.php?edit={{ $repo->id }}' 
                                                           class='btn btn-primary px-3 py-2 text-white'
                                                           title='{{ trans('langEdit') }}'>
                                                            <i class='fa fa-edit text-white'></i>
                                                        </a>
                                                        <button type='button' class='btn btn-danger delete-repo-btn px-3 py-2 text-white' 
                                                                data-repo-id='{{ $repo->id }}'
                                                                data-repo-name='{{ $repo->name }}'
                                                                title='{{ trans('langDelete') }}'>
                                                            <i class='fa fa-trash text-white'></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class='alert alert-warning'>
                                <i class='fa fa-info-circle me-2'></i>
                                {{ trans('langNoExternalRepos') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Supported Repository Types --}}
            <div class='col-12 mt-4'>
                <div class='card panelCard px-lg-4 py-lg-3 p-3'>
                    <div class='card-header border-0'>
                        <h3>{{ trans('langSupportedRepositoryTypes') }}</h3>
                    </div>
                    <div class='card-body'>
                        <div class='row'>
                            @foreach ($repositoryTypes as $type => $info)
                                <div class='col-md-6 col-lg-4 mb-3'>
                                    <div class='card h-100'>
                                        <div class='card-body'>
                                            <h5 class='card-title'>
                                                <i class='fa-brands {{ $info['icon'] }} me-2'></i>
                                                {{ $info['name'] }}
                                            </h5>
                                            <p class='card-text text-muted small'>{{ $info['description'] }}</p>
                                            <small class='text-muted'>
                                                <strong>{{ trans('langAuthTypes') }}:</strong>
                                                @foreach ($info['auth_types'] as $authType)
                                                    <span class='badge bg-light text-dark'>{{ trans('langAuthType_' . $authType) }}</span>
                                                @endforeach
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- Delete Confirmation Modal --}}
<div class='modal fade' id='deleteRepoModal' tabindex='-1' aria-labelledby='deleteRepoModalLabel' aria-hidden='true'>
    <div class='modal-dialog'>
        <div class='modal-content'>
            <div class='modal-header'>
                <h5 class='modal-title' id='deleteRepoModalLabel'>{{ trans('langConfirmDelete') }}</h5>
                <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='{{ trans('langClose') }}'></button>
            </div>
            <div class='modal-body'>
                <p>{{ trans('langConfirmDeleteRepo') }} <strong id='deleteRepoName'></strong>?</p>
                <p class='text-danger small'>{{ trans('langDeleteRepoWarning') }}</p>
            </div>
            <div class='modal-footer'>
                <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>{{ trans('langCancel') }}</button>
                <button type='button' class='btn btn-danger' id='confirmDeleteRepo'>{{ trans('langDelete') }}</button>
            </div>
        </div>
    </div>
</div>

{{-- Test Connection Result Modal --}}
<div class='modal fade' id='testResultModal' tabindex='-1' aria-labelledby='testResultModalLabel' aria-hidden='true'>
    <div class='modal-dialog'>
        <div class='modal-content'>
            <div class='modal-header'>
                <h5 class='modal-title' id='testResultModalLabel'>{{ trans('langTestConnection') }}</h5>
                <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='{{ trans('langClose') }}'></button>
            </div>
            <div class='modal-body'>
                <div id='testResultContent'></div>
            </div>
            <div class='modal-footer'>
                <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>{{ trans('langClose') }}</button>
            </div>
        </div>
    </div>
</div>

<style>
.delete-repo-btn {
    background-color: #dc3545 !important;
    border-color: #dc3545 !important;
}
.delete-repo-btn:hover {
    background-color: #c82333 !important;
    border-color: #bd2130 !important;
}
.delete-repo-btn:focus {
    background-color: #c82333 !important;
    border-color: #bd2130 !important;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.5) !important;
}
.table td, .table th {
    vertical-align: middle !important;
}
</style>

<script>
$(document).ready(function() {
    var csrfToken = '{{ $_SESSION['csrf_token'] }}';
    var deleteRepoId = null;
    
    // Toggle repository enabled status
    $('.toggle-repo-btn').on('click', function() {
        var btn = $(this);
        var repoId = btn.data('repo-id');
        var enabled = btn.data('enabled');
        var icon = btn.find('i');
        
        icon.removeClass('fa-toggle-on fa-toggle-off').addClass('fa-spinner fa-spin');
        
        $.post('{{ $urlAppend }}modules/admin/externalreposconf.php', {
            action: 'toggle',
            id: repoId,
            enabled: enabled,
            token: csrfToken
        }, function(response) {
            if (response.success) {
                if (enabled == 1) {
                    icon.removeClass('fa-spinner fa-spin').addClass('fa-toggle-on text-white');
                    btn.removeClass('btn-success').addClass('btn-warning');
                    btn.data('enabled', 0);
                    btn.closest('tr').find('.badge.bg-secondary').removeClass('bg-secondary').addClass('bg-success')
                        .html('<i class="fa fa-check-circle me-1"></i>{{ trans('langActive') }}');
                } else {
                    icon.removeClass('fa-spinner fa-spin').addClass('fa-toggle-off text-white');
                    btn.removeClass('btn-warning').addClass('btn-success');
                    btn.data('enabled', 1);
                    btn.closest('tr').find('.badge.bg-success').removeClass('bg-success').addClass('bg-secondary')
                        .html('<i class="fa fa-times-circle me-1"></i>{{ trans('langInactive') }}');
                }
            } else {
                icon.removeClass('fa-spinner fa-spin').addClass(enabled == 1 ? 'fa-toggle-off text-white' : 'fa-toggle-on text-white');
                alert('{{ trans('langError') }}');
            }
        }, 'json').fail(function() {
            icon.removeClass('fa-spinner fa-spin').addClass(enabled == 1 ? 'fa-toggle-off text-white' : 'fa-toggle-on text-white');
            alert('{{ trans('langError') }}');
        });
    });
    
    // Delete repository
    $('.delete-repo-btn').on('click', function() {
        deleteRepoId = $(this).data('repo-id');
        $('#deleteRepoName').text($(this).data('repo-name'));
        $('#deleteRepoModal').modal('show');
    });
    
    $('#confirmDeleteRepo').on('click', function() {
        if (deleteRepoId) {
            $.post('{{ $urlAppend }}modules/admin/externalreposconf.php', {
                action: 'delete',
                id: deleteRepoId,
                token: csrfToken
            }, function(response) {
                if (response.success) {
                    $('tr[data-repo-id="' + deleteRepoId + '"]').fadeOut(function() {
                        $(this).remove();
                    });
                } else {
                    alert('{{ trans('langError') }}');
                }
                $('#deleteRepoModal').modal('hide');
            }, 'json').fail(function() {
                alert('{{ trans('langError') }}');
                $('#deleteRepoModal').modal('hide');
            });
        }
    });
    
    // Test connection
    $('.test-repo-btn').on('click', function() {
        var btn = $(this);
        var repoId = btn.data('repo-id');
        var icon = btn.find('i');
        
        icon.removeClass('fa-plug').addClass('fa-spinner fa-spin');
        
        $.post('{{ $urlAppend }}modules/admin/externalreposconf.php', {
            action: 'test',
            id: repoId,
            token: csrfToken
        }, function(response) {
            icon.removeClass('fa-spinner fa-spin').addClass('fa-plug');
            
            var content = '';
            if (response.success) {
                content = '<div class="alert alert-success"><i class="fa fa-check-circle me-2"></i>{{ trans('langConnectionSuccess') }}</div>';
            } else {
                content = '<div class="alert alert-danger"><i class="fa fa-times-circle me-2"></i>' + 
                          (response.message || '{{ trans('langConnectionFailed') }}') + '</div>';
            }
            
            $('#testResultContent').html(content);
            $('#testResultModal').modal('show');
        }, 'json').fail(function() {
            icon.removeClass('fa-spinner fa-spin').addClass('fa-plug');
            $('#testResultContent').html('<div class="alert alert-danger"><i class="fa fa-times-circle me-2"></i>{{ trans('langConnectionFailed') }}</div>');
            $('#testResultModal').modal('show');
        });
    });
});
</script>

@endsection

