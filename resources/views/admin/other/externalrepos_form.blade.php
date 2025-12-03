@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
    <div class='{{ $container }} main-container'>
        <div class="row m-auto">

            @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

            @include('layouts.partials.legend_view')

            @include('layouts.partials.show_alert')

            <div class='col-12 mt-4'>
                <div class='card panelCard px-lg-4 py-lg-3 p-3'>
                    <div class='card-header border-0'>
                        <h3>
                            @if ($action === 'edit')
                                {{ trans('langEditExternalRepo') }}
                            @else
                                {{ trans('langAddExternalRepo') }}
                            @endif
                        </h3>
                    </div>
                    <div class='card-body'>
                        <form method='post' action='{{ $urlAppend }}modules/admin/externalreposconf.php' class='form-horizontal'>
                            {!! generate_csrf_token_form_field() !!}
                            
                            @if ($repository)
                                <input type='hidden' name='repo_id' value='{{ $repository->id }}'>
                            @endif

                            {{-- Repository Name --}}
                            <div class='form-group mb-4'>
                                <label for='repo_name' class='form-label'>
                                    {{ trans('langName') }} <span class='text-danger'>*</span>
                                </label>
                                <input type='text' 
                                       class='form-control' 
                                       id='repo_name' 
                                       name='repo_name' 
                                       value='{{ $repository->name ?? '' }}'
                                       placeholder='{{ trans('langRepoNamePlaceholder') }}'
                                       required>
                                <small class='form-text text-muted'>{{ trans('langRepoNameHelp') }}</small>
                            </div>

                            {{-- Repository Type --}}
                            <div class='form-group mb-4'>
                                <label for='repo_type' class='form-label'>
                                    {{ trans('langType') }} <span class='text-danger'>*</span>
                                </label>
                                <select class='form-select' id='repo_type' name='repo_type' required>
                                    <option value=''>{{ trans('langSelectRepoType') }}</option>
                                    @foreach ($repositoryTypes as $type => $info)
                                        <option value='{{ $type }}' 
                                                data-auth-types='{{ json_encode($info['auth_types']) }}'
                                                data-hardcoded-url='{{ isset($info['hardcoded_url']) && $info['hardcoded_url'] ? 'true' : 'false' }}'
                                                data-api-endpoint='{{ $info['api_endpoint'] ?? '' }}'
                                                {{ ($repository->type ?? '') === $type ? 'selected' : '' }}>
                                            {{ $info['name'] }} - {{ $info['description'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Base URL --}}
                            <div class='form-group mb-4' id='base_url_group'>
                                <label for='base_url' class='form-label'>
                                    {{ trans('langBaseUrl') }} <span class='text-danger' id='base_url_required'>*</span>
                                </label>
                                <input type='url' 
                                       class='form-control' 
                                       id='base_url' 
                                       value='{{ $repository->base_url ?? '' }}'
                                       placeholder='https://example.com/api'>
                                <input type='hidden' id='base_url_hidden' name='base_url' value='{{ $repository->base_url ?? '' }}'>
                                <small class='form-text text-muted' id='base_url_help'>{{ trans('langBaseUrlHelp') }}</small>
                                <div class='alert alert-info mt-2' id='hardcoded_url_notice' style='display: none;'>
                                    <i class='fa fa-info-circle me-2'></i>
                                    <span>{{ trans('langHardcodedUrlNotice') }}: <strong id='hardcoded_url_value'></strong></span>
                                </div>
                            </div>

                            {{-- Authentication Type --}}
                            <div class='form-group mb-4'>
                                <label for='auth_type' class='form-label'>
                                    {{ trans('langAuthType') }}
                                </label>
                                <select class='form-select' id='auth_type' name='auth_type'>
                                    <option value='none' {{ ($repository->auth_type ?? 'none') === 'none' ? 'selected' : '' }}>
                                        {{ trans('langAuthType_none') }}
                                    </option>
                                    <option value='api_key' {{ ($repository->auth_type ?? '') === 'api_key' ? 'selected' : '' }}>
                                        {{ trans('langAuthType_api_key') }}
                                    </option>
                                    <option value='oauth' {{ ($repository->auth_type ?? '') === 'oauth' ? 'selected' : '' }}>
                                        {{ trans('langAuthType_oauth') }}
                                    </option>
                                </select>
                            </div>

                            {{-- API Key --}}
                            <div class='form-group mb-4' id='api_key_group' style='display: none;'>
                                <label for='api_key' class='form-label'>
                                    {{ trans('langApiKey') }}
                                </label>
                                <input type='text' 
                                       class='form-control' 
                                       id='api_key' 
                                       name='api_key' 
                                       value='{{ $repository->api_key ?? '' }}'
                                       placeholder='{{ trans('langApiKeyPlaceholder') }}'>
                                <small class='form-text text-muted'>{{ trans('langApiKeyHelp') }}</small>
                            </div>

                            {{-- Additional Configuration (JSON) --}}
                            <div class='form-group mb-4'>
                                <label for='additional_config' class='form-label'>
                                    {{ trans('langAdditionalConfig') }}
                                </label>
                                <textarea class='form-control' 
                                          id='additional_config' 
                                          name='additional_config' 
                                          rows='3'
                                          placeholder='{"key": "value"}'>{{ $repository->config ?? '' }}</textarea>
                                <small class='form-text text-muted'>{{ trans('langAdditionalConfigHelp') }}</small>
                            </div>

                            {{-- Enabled --}}
                            <div class='form-group mb-4'>
                                <div class='form-check'>
                                    <input type='checkbox' 
                                           class='form-check-input' 
                                           id='enabled' 
                                           name='enabled' 
                                           value='1'
                                           {{ ($repository->enabled ?? 1) ? 'checked' : '' }}>
                                    <label class='form-check-label' for='enabled'>
                                        {{ trans('langEnableRepository') }}
                                    </label>
                                </div>
                            </div>

                            {{-- Submit Buttons --}}
                            <div class='form-group mt-4'>
                                <div class='d-flex gap-2'>
                                    <button type='submit' name='save_repo' class='btn submitAdminBtn'>
                                        <i class='fa fa-save me-2'></i>{{ trans('langSave') }}
                                    </button>
                                    <a href='{{ $urlAppend }}modules/admin/externalreposconf.php' class='btn cancelAdminBtn'>
                                        {{ trans('langCancel') }}
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Help Section --}}
            <div class='col-12 mt-4'>
                <div class='card panelCard px-lg-4 py-lg-3 p-3'>
                    <div class='card-header border-0'>
                        <h3>{{ trans('langHelp') }}</h3>
                    </div>
                    <div class='card-body'>
                        <div class='accordion' id='helpAccordion'>
                            @foreach ($repositoryTypes as $type => $info)
                                <div class='accordion-item'>
                                    <h2 class='accordion-header'>
                                        <button class='accordion-button collapsed' type='button' 
                                                data-bs-toggle='collapse' 
                                                data-bs-target='#help_{{ $type }}'>
                                            <i class='fa-brands {{ $info['icon'] }} me-2'></i>
                                            {{ $info['name'] }}
                                        </button>
                                    </h2>
                                    <div id='help_{{ $type }}' class='accordion-collapse collapse' data-bs-parent='#helpAccordion'>
                                        <div class='accordion-body'>
                                            <p>{{ $info['description'] }}</p>
                                            <p><strong>{{ trans('langSupportedAuthTypes') }}:</strong></p>
                                            <ul>
                                                @foreach ($info['auth_types'] as $authType)
                                                    <li>{{ trans('langAuthType_' . $authType) }}</li>
                                                @endforeach
                                            </ul>
                                            @if ($type === 'youtube')
                                                <div class='alert alert-info'>
                                                    <i class='fa fa-info-circle me-2'></i>
                                                    {{ trans('langYouTubeApiHelp') }}
                                                </div>
                                            @elseif ($type === 'pixabay')
                                                <div class='alert alert-info'>
                                                    <i class='fa fa-info-circle me-2'></i>
                                                    {{ trans('langPixabayApiHelp') }}
                                                </div>
                                            @elseif ($type === 'dspace')
                                                <div class='alert alert-info'>
                                                    <i class='fa fa-info-circle me-2'></i>
                                                    {{ trans('langDSpaceApiHelp') }}
                                                </div>
                                            @endif
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

<script>
$(document).ready(function() {
    // Show/hide API key field based on auth type
    function toggleApiKeyField() {
        var authType = $('#auth_type').val();
        if (authType === 'api_key' || authType === 'oauth') {
            $('#api_key_group').slideDown();
        } else {
            $('#api_key_group').slideUp();
        }
    }
    
    // Handle base URL field for hardcoded endpoints
    function toggleBaseUrlField() {
        var selectedOption = $('#repo_type option:selected');
        var repoType = selectedOption.val();
        
        // Don't do anything if no type is selected
        if (!repoType) {
            $('#base_url').prop('disabled', false);
            $('#base_url_required').show();
            $('#base_url_help').show();
            $('#hardcoded_url_notice').hide();
            return;
        }
        
        var hardcodedUrl = selectedOption.data('hardcoded-url') === 'true' || selectedOption.data('hardcoded-url') === true;
        var apiEndpoint = selectedOption.data('api-endpoint');
        
        if (hardcodedUrl && apiEndpoint) {
            // Populate with hardcoded endpoint and make it disabled (non-editable)
            $('#base_url').val(apiEndpoint).prop('disabled', true);
            $('#base_url_hidden').val(apiEndpoint);
            $('#base_url_required').hide();
            $('#base_url_help').hide();
            $('#hardcoded_url_value').text(apiEndpoint);
            $('#hardcoded_url_notice').show();
        } else {
            // Allow custom URL input
            $('#base_url').prop('disabled', false);
            $('#base_url_required').show();
            $('#base_url_help').show();
            $('#hardcoded_url_notice').hide();
            // Clear the value if it was previously disabled
            if ($('#base_url').prop('disabled')) {
                $('#base_url').val('');
                $('#base_url_hidden').val('');
            }
        }
    }
    
    // Sync visible input with hidden field for custom URLs
    $('#base_url').on('input', function() {
        if (!$(this).prop('disabled')) {
            $('#base_url_hidden').val($(this).val());
        }
    });
    
    // Track if this is the initial load
    var isInitialLoad = true;
    
    // Reset form fields when repository type changes (except repo name)
    function resetFieldsOnTypeChange() {
        // Skip reset on initial page load to preserve edit values
        if (isInitialLoad) {
            isInitialLoad = false;
            return;
        }
        
        var repoType = $('#repo_type').val();
        if (!repoType) {
            return;
        }
        
        // Reset all fields except repository name
        $('#base_url').val('');
        $('#base_url_hidden').val('');
        $('#api_key').val('');
        $('#additional_config').val('');
        $('#enabled').prop('checked', true);
        
        // Auth type will be reset by updateAuthTypeOptions to first available
    }
    
    // Update auth type options based on repository type
    function updateAuthTypeOptions() {
        var selectedOption = $('#repo_type option:selected');
        var authTypes = selectedOption.data('auth-types');
        var shouldResetAuthType = !isInitialLoad;
        
        if (authTypes && authTypes.length > 0) {
            $('#auth_type option').each(function() {
                var optionValue = $(this).val();
                if (authTypes.includes(optionValue)) {
                    $(this).prop('disabled', false);
                } else {
                    $(this).prop('disabled', true);
                }
            });
            
            // If current selection is disabled or we're resetting, select first available
            if ($('#auth_type option:selected').prop('disabled') || shouldResetAuthType) {
                $('#auth_type option:not(:disabled):first').prop('selected', true);
            }
        }
        
        toggleApiKeyField();
        toggleBaseUrlField();
    }
    
    $('#auth_type').on('change', toggleApiKeyField);
    $('#repo_type').on('change', function() {
        resetFieldsOnTypeChange();
        updateAuthTypeOptions();
    });
    
    // Initialize on page load
    toggleApiKeyField();
    updateAuthTypeOptions();
    
    // Ensure URL field is set correctly on initial load
    setTimeout(function() {
        toggleBaseUrlField();
    }, 100);
});
</script>

@endsection

