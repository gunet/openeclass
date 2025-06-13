@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
    <div class='{{ $container }} main-container'>
        <div class='row m-auto'>

            @include('layouts.partials.show_alert')

            <div class='col-12'>
                <div class='alert alert-info'>
                    <i class='fa-solid fa-circle-info fa-lg'></i>
                    <span>{{ trans('langMyBackpacksInfo') }}</span>
                </div>
            </div>

            @if($userConnection && $userConnection->status === 'connected')
                {{-- User has a connected backpack --}}
                <div class='col-12'>
                    <div class='card panelCard px-lg-4 py-lg-3 p-3'>
                        <div class='card-header border-0 d-flex justify-content-between align-items-center gap-3 flex-wrap'>
                            <h3>{{ trans('langConnectedBackpack') }}</h3>
                        </div>
                        <div class='card-body'>
                            <div class='row'>
                                <div class='col-md-8'>
                                    <div class='mb-3'>
                                        <strong>{{ trans('langBackpackProvider') }}:</strong>
                                        <span class='ms-2'>{{ $userConnection->provider_name }}</span>
                                    </div>
                                    <div class='mb-3'>
                                        <strong>{{ trans('langProtocol') }}:</strong>
                                        <span class='ms-2'>OpenBadges {{ $userConnection->ob_version }}</span>
                                    </div>
                                    <div class='mb-3'>
                                        <strong>{{ trans('langStatus') }}:</strong>
                                        <span class='ms-2 badge bg-success'>
                                            <i class='fa fa-check-circle me-1'></i>
                                            {{ trans('langConnected') }}
                                        </span>
                                    </div>
                                    @if($userConnection->email)
                                        <div class='mb-3'>
                                            <strong>{{ trans('langEmail') }}:</strong>
                                            <span class='ms-2'>{{ $userConnection->email }}</span>
                                        </div>
                                    @endif
                                    <div class='mb-3'>
                                        <strong>{{ trans('langLastSync') }}:</strong>
                                        <span class='ms-2'>
                                            {{ $userConnection->last_sync ? format_locale_date(strtotime($userConnection->last_sync)) : trans('langNever') }}
                                        </span>
                                    </div>
                                </div>
                                <div class='col-md-4 text-end'>
                                    <form method='post' class='d-inline'>
                                        <input type='hidden' name='action' value='disconnect'>
                                        <button type='submit' class='btn deleteAdminBtn' 
                                                onclick="return confirm('{{ trans('langConfirmDisconnectBackpack') }}')">
                                            <i class='fa fa-unlink me-1'></i>
                                            {{ trans('langDisconnectBackpack') }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                {{-- User doesn't have a connected backpack --}}
                <div class='col-12'>
                    <div class='card panelCard px-lg-4 py-lg-3 p-3'>
                        <div class='card-header border-0'>
                            <h3>{{ trans('langConnectBackpack') }}</h3>
                        </div>
                        <div class='card-body'>
                            @if(count($availableProviders) > 0)
                                <form method='post' id='backpackConnectionForm'>
                                    <input type='hidden' name='action' value='connect'>
                                    
                                    <div class='form-group mb-4'>
                                        <label for='provider_id' class='col-sm-12 control-label-notes'>
                                            {{ trans('langSelectBackpackProvider') }}
                                        </label>
                                        <div class='col-sm-12'>
                                            <select name='provider_id' id='provider_id' class='form-select' required>
                                                <option value=''>{{ trans('langSelectProvider') }}</option>
                                                @foreach($availableProviders as $provider)
                                                    <option value='{{ $provider->id }}' 
                                                            data-ob-version='{{ $provider->ob_version }}'>
                                                        {{ $provider->name }} ({{ $provider->ob_version }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    {{-- Credentials form for OB 2.0/2.1 --}}
                                    <div id='credentials-form' style='display: none;'>
                                        <div class='form-group mb-3'>
                                            <label for='email' class='col-sm-12 control-label-notes'>
                                                {{ trans('langEmail') }}
                                            </label>
                                            <div class='col-sm-12'>
                                                <input type='email' name='email' id='email' class='form-control' 
                                                       placeholder='{{ trans('langEmailAddress') }}'>
                                            </div>
                                        </div>

                                        <div class='form-group mb-4'>
                                            <label for='password' class='col-sm-12 control-label-notes'>
                                                {{ trans('langPassword') }}
                                            </label>
                                            <div class='col-sm-12'>
                                                <input type='password' name='password' id='password' class='form-control' 
                                                       placeholder='{{ trans('langPassword') }}'>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- OB 3.0 message --}}
                                    <div id='ob3-message' style='display: none;'>
                                        <div class='alert alert-info mb-4'>
                                            <i class='fa-solid fa-circle-info fa-lg'></i>
                                            <span>{{ trans('langOB3Info') }}</span>
                                        </div>
                                    </div>

                                    <div class='form-group'>
                                        <div class='col-sm-12'>
                                            <button type='submit' class='btn submitAdminBtn' id='connect-btn' disabled>
                                                <i class='fa fa-link me-1'></i>
                                                {{ trans('langConnectBackpack') }}
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            @else
                                <div class='alert alert-warning'>
                                    <i class='fa-solid fa-triangle-exclamation fa-lg'></i>
                                    <span>{{ trans('langNoAvailableBackpackProvider') }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const providerSelect = document.getElementById('provider_id');
    const credentialsForm = document.getElementById('credentials-form');
    const ob3Message = document.getElementById('ob3-message');
    const connectBtn = document.getElementById('connect-btn');
    const emailField = document.getElementById('email');
    const passwordField = document.getElementById('password');

    if (providerSelect) {
        providerSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const obVersion = selectedOption.getAttribute('data-ob-version');
            
            // Hide all sections first
            credentialsForm.style.display = 'none';
            ob3Message.style.display = 'none';
            connectBtn.disabled = true;
            
            // Clear form fields
            if (emailField) emailField.value = '';
            if (passwordField) passwordField.value = '';
            
            if (this.value) {
                if (obVersion === 'OpenBadge v2.0' || obVersion === 'OpenBadge v2.1') {
                    credentialsForm.style.display = 'block';
                    // Enable button only when credentials are filled
                    const checkCredentials = () => {
                        connectBtn.disabled = !emailField.value.trim() || !passwordField.value.trim();
                    };
                    
                    emailField.addEventListener('input', checkCredentials);
                    passwordField.addEventListener('input', checkCredentials);
                    
                } else if (obVersion === 'OpenBadge v3') {
                    ob3Message.style.display = 'block';
                    connectBtn.disabled = false;
                }
            }
        });
    }
});
</script>

@endsection 