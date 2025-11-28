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
                        <div class='card-header border-0'>
                            <h3>{{ trans('langConnectedBackpack') }}</h3>
                        </div>
                        <div class='card-body'>
                            <div class='row'>
                                <div class='col-12'>
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
                            </div>
                            <div class='row mt-3'>
                                <div class='col-12'>
                                    <hr class='my-2'>
                                    <div class='text-end'>
                                        <form method='post' class='d-inline'>
                                            {!! generate_csrf_token_form_field() !!}
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
                </div>

                {{-- User's Badge Collections Section --}}
                <div class='col-12 mt-4'>
                    <div class='card panelCard px-lg-4 py-lg-3 p-3'>
                        <div class='card-header border-0 d-flex justify-content-between align-items-center gap-3 flex-wrap'>
                            <h3>{{ trans('langMyBadgeCollections') }}</h3>
                            <button type='button' class='btn btn-primary' id='fetch-collections-btn' style='display: none;'>
                                <i class='fa fa-sync me-1'></i>
                                {{ trans('langFetchCollections') }}
                            </button>
                        </div>
                        <div class='card-body'>
                            {{-- Collection Selector --}}
                            <div id='collection-selector' style='display: none;' class='mb-4'>
                                <div class='alert alert-info mb-3'>
                                    <i class='fa-solid fa-circle-info me-2'></i>
                                    {{ trans('langSyncCollectionInfo') }}
                                </div>
                                <div class='mb-2'>
                                    <label for='collection-select' class='form-label mb-1'>
                                        <strong>{{ trans('langSelectCollectionToSync') }}</strong>
                                    </label>
                                </div>
                                <div class='d-flex flex-column flex-lg-row gap-2 gap-lg-3'>
                                    <div class='flex-grow-1'>
                                        <select id='collection-select' class='form-select'>
                                            <option value=''>-- {{ trans('langChooseCollectionToSync') }} --</option>
                                        </select>
                                    </div>
                                    <div class='d-flex align-items-center'>
                                        <button type='button' class='btn btn-success' id='sync-collection-btn' disabled>
                                            <i class='fa fa-sync me-1'></i>
                                            {{ trans('langSyncCollection') }}
                                        </button>
                                    </div>
                                </div>
                                <div class='mt-1'>
                                    <small class='form-text text-muted'>{{ trans('langSelectCollectionHelpText') }}</small>
                                </div>
                                
                                {{-- Sync Progress --}}
                                <div id='sync-progress' style='display: none;' class='mt-3 p-3 border rounded'>
                                    <h6 class='mb-3'>
                                        <i class='fa fa-spinner fa-spin me-1'></i>
                                        {{ trans('langSyncingBadges') }}...
                                    </h6>
                                    <div class='progress'>
                                        <div id='sync-progress-bar' class='progress-bar progress-bar-striped progress-bar-animated' 
                                             role='progressbar' style='width: 0%'></div>
                                    </div>
                                    <p id='sync-status' class='mt-2 mb-0 text-muted'></p>
                                </div>
                                
                                {{-- Sync Results --}}
                                <div id='sync-results' style='display: none;' class='mt-3'></div>
                            </div>

                            {{-- Loading State --}}
                            <div id='collections-loading' style='display: none;' class='text-center py-4'>
                                <i class='fa fa-spinner fa-spin fa-2x text-primary'></i>
                                <p class='mt-2'>{{ trans('langLoadingCollections') }}...</p>
                            </div>

                            {{-- Error State --}}
                            <div id='collections-error' style='display: none;' class='alert alert-danger'>
                                <i class='fa-solid fa-triangle-exclamation me-2'></i>
                                <span id='collections-error-message'></span>
                            </div>

                            {{-- Empty State --}}
                            <div id='collections-empty' style='display: none;' class='alert alert-info'>
                                <i class='fa-solid fa-circle-info me-2'></i>
                                <span>{{ trans('langNoCollectionsFound') }}</span>
                            </div>

                            {{-- Collections List --}}
                            <div id='collections-list' style='display: none;'>
                                <div class='row' id='collections-container'>
                                    {{-- Collections will be dynamically inserted here --}}
                                </div>
                            </div>

                            {{-- Initial State --}}
                            <div id='collections-initial' class='text-center py-4 text-muted'>
                                <i class='fa-solid fa-folder-open fa-3x mb-3'></i>
                                <p>{{ trans('langClickToFetchCollections') }}</p>
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
                                    {!! generate_csrf_token_form_field() !!}
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
                                                            data-ob-version='{{ $provider->ob_version }}'
                                                            data-api-url='{{ $provider->api_url }}'>
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
    const backpackConnectionForm = document.getElementById('backpackConnectionForm');

    // Collections fetching functionality
    const fetchCollectionsBtn = document.getElementById('fetch-collections-btn');
    const collectionsLoading = document.getElementById('collections-loading');
    const collectionsError = document.getElementById('collections-error');
    const collectionsErrorMessage = document.getElementById('collections-error-message');
    const collectionsEmpty = document.getElementById('collections-empty');
    const collectionsList = document.getElementById('collections-list');
    const collectionsContainer = document.getElementById('collections-container');
    const collectionsInitial = document.getElementById('collections-initial');
    
    // Collection selector elements
    const collectionSelector = document.getElementById('collection-selector');
    const collectionSelect = document.getElementById('collection-select');
    const syncCollectionBtn = document.getElementById('sync-collection-btn');
    const syncProgress = document.getElementById('sync-progress');
    const syncProgressBar = document.getElementById('sync-progress-bar');
    const syncStatus = document.getElementById('sync-status');
    const syncResults = document.getElementById('sync-results');
    
    // Store fetched collections globally
    let fetchedCollections = [];

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
                        const hasCredentials = emailField.value.trim() && passwordField.value.trim();
                        connectBtn.disabled = !hasCredentials;
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

    // Handle Connect Backpack form submission via AJAX
    if (backpackConnectionForm) {
        backpackConnectionForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const providerId = providerSelect.value;
            const email = emailField ? emailField.value.trim() : '';
            const password = passwordField ? passwordField.value.trim() : '';
            
            // Validate form
            if (!providerId) {
                showErrorBanner('{{ js_escape(trans('langBackpackProviderRequired')) }}');
                return;
            }
            
            // Check if credentials are needed (OB 2.0/2.1)
            const selectedOption = providerSelect.options[providerSelect.selectedIndex];
            const obVersion = selectedOption.getAttribute('data-ob-version');
            
            if ((obVersion === 'OpenBadge v2.0' || obVersion === 'OpenBadge v2.1') && (!email || !password)) {
                showErrorBanner('{{ js_escape(trans('langBackpackCredentialsRequired')) }}');
                return;
            }
            
            // Show loading state
            connectBtn.disabled = true;
            const originalBtnText = connectBtn.innerHTML;
            connectBtn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i>{{ trans('langPleaseWait') }}...';
            
            // Prepare form data
            const formData = new FormData(backpackConnectionForm);
            formData.append('ajax_action', 'test_connection');
            
            // Make AJAX request
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.connection_saved) {
                    // Success: refresh page to show success banner
                    window.location.reload();
                } else {
                    // Error: show error banner
                    // Check if it's an authentication error (wrong username/password)
                    const isAuthError = data.status === 401 || 
                                       data.status === 400 || 
                                       (data.error && (
                                           data.error.toLowerCase().includes('authentication') ||
                                           data.error.toLowerCase().includes('failed') ||
                                           data.error.toLowerCase().includes('invalid')
                                       ));
                    
                    const errorMessage = isAuthError 
                        ? '{{ js_escape(trans('langWrongAuth')) }}'
                        : (data.error || '{{ js_escape(trans('langBackpackConnectionFailed')) }}');
                    
                    showErrorBanner(errorMessage);
                    
                    // Reset button state
                    connectBtn.disabled = false;
                    connectBtn.innerHTML = originalBtnText;
                }
            })
            .catch(error => {
                // Network or other error
                showErrorBanner('{{ js_escape(trans('langBackpackConnectionFailed')) }}');
                
                // Reset button state
                connectBtn.disabled = false;
                connectBtn.innerHTML = originalBtnText;
            });
        });
    }
    
    /**
     * Show error banner at the top of the page
     */
    function showErrorBanner(message) {
        // Remove any existing alert banners
        const existingAlerts = document.querySelectorAll('.all-alerts');
        existingAlerts.forEach(alert => alert.remove());
        
        // Create error banner
        const alertDiv = document.createElement('div');
        alertDiv.className = 'col-12 all-alerts';
        alertDiv.innerHTML = `
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class='fa-solid fa-circle-xmark fa-lg'></i>
                <span>${escapeHtml(message)}</span>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ trans('langClose') }}"></button>
            </div>
        `;
        
        // Insert at the top of the main section
        const mainSection = document.querySelector('.main-section .main-container .row');
        if (mainSection) {
            mainSection.insertBefore(alertDiv, mainSection.firstChild);
        }
    }

    // Fetch Collections functionality
    if (fetchCollectionsBtn) {
        fetchCollectionsBtn.addEventListener('click', function() {
            // Hide all states
            collectionsInitial.style.display = 'none';
            collectionsError.style.display = 'none';
            collectionsEmpty.style.display = 'none';
            collectionsList.style.display = 'none';
            
            // Show loading state
            collectionsLoading.style.display = 'block';
            fetchCollectionsBtn.disabled = true;

            // Make API call
            fetch('{{ $urlServer }}modules/backpack/api/collections.php', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                credentials: 'same-origin'
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(data => {
                        console.error('API Error Response:', data);
                        const errorMsg = data.errormessage || 'Failed to fetch collections';
                        const debugInfo = data.debug ? ` (HTTP ${data.debug.http_code})` : '';
                        throw new Error(errorMsg + debugInfo);
                    }).catch(jsonError => {
                        // If JSON parsing fails, throw generic error
                        throw new Error(`HTTP ${response.status}: Failed to fetch collections`);
                    });
                }
                return response.json();
            })
            .then(data => {
                collectionsLoading.style.display = 'none';
                
                if (data.success && data.data && data.data.length > 0) {
                    // Store collections globally
                    fetchedCollections = data.data;
                    
                    // Display collections
                    displayCollections(data.data);
                    collectionsList.style.display = 'block';
                    
                    // Populate and show selector
                    populateCollectionSelector(data.data);
                    collectionSelector.style.display = 'block';
                    
                    // Auto-select last used collection
                    autoSelectLastCollection();
                } else {
                    // No collections found
                    collectionsEmpty.style.display = 'block';
                    collectionSelector.style.display = 'none';
                }
            })
            .catch(error => {
                collectionsLoading.style.display = 'none';
                collectionsError.style.display = 'block';
                collectionsErrorMessage.textContent = error.message;
                console.error('Collections fetch error:', error);
            })
            .finally(() => {
                fetchCollectionsBtn.disabled = false;
            });
        });

        // Auto-fetch collections on page load if backpack is connected
        if (fetchCollectionsBtn) {
            // Automatically fetch collections when page loads
            setTimeout(() => {
                fetchCollectionsBtn.click();
            }, 500);
        }
    }

    /**
     * Display collections in the UI
     */
    function displayCollections(collections) {
        collectionsContainer.innerHTML = '';
        
        collections.forEach(collection => {
            const collectionCard = createCollectionCard(collection);
            collectionsContainer.appendChild(collectionCard);
        });
    }

    /**
     * Create a collection card element
     */
    function createCollectionCard(collection) {
        const col = document.createElement('div');
        col.className = 'col-md-6 col-lg-4 mb-3';
        
        // Extract collection data (handle different OpenBadges versions)
        const name = collection.name || collection.title || 'Untitled Collection';
        const description = collection.description || '';
        const badgeCount = collection.badges?.length || collection.assertions?.length || 0;
        const collectionId = collection.id || collection.entityId || '';
        const isEmpty = badgeCount === 0;
        
        col.innerHTML = `
            <div class='card h-100 shadow-sm collection-card ${isEmpty ? 'disabled' : ''}' 
                 data-collection-id='${escapeHtml(collectionId)}' 
                 style='cursor: ${isEmpty ? 'not-allowed' : 'pointer'}; transition: all 0.3s; ${isEmpty ? 'opacity: 0.6;' : ''}'>
                <div class='card-body'>
                    <h5 class='card-title'>
                        <i class='fa fa-folder ${isEmpty ? 'text-muted' : 'text-primary'} me-2'></i>
                        ${escapeHtml(name)}
                    </h5>
                    ${description ? `<p class='card-text text-muted'>${escapeHtml(description)}</p>` : ''}
                    <div class='mt-3'>
                        <span class='badge ${isEmpty ? 'bg-secondary' : 'bg-info'}'>
                            <i class='fa fa-certificate me-1'></i>
                            ${badgeCount} ${badgeCount === 1 ? 'Badge' : 'Badges'}
                        </span>
                        ${isEmpty ? '<span class="badge bg-warning ms-2"><i class="fa fa-ban me-1"></i>{{ trans('langEmpty') }}</span>' : ''}
                    </div>
                </div>
            </div>
        `;
        
        // Add click handler to card (only for non-empty collections)
        const card = col.querySelector('.collection-card');
        if (!isEmpty) {
            card.addEventListener('click', function() {
                selectCollectionById(collectionId);
            });
            
            // Add hover effect
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px)';
                this.style.boxShadow = '0 4px 8px rgba(0,0,0,0.2)';
            });
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = '';
            });
        }
        
        return col;
    }

    /**
     * Populate the collection selector dropdown
     */
    function populateCollectionSelector(collections) {
        // Clear existing options (keep the default one)
        collectionSelect.innerHTML = '<option value="">-- {{ trans('langChooseCollection') }} --</option>';
        
        collections.forEach((collection, index) => {
            const name = collection.name || collection.title || 'Untitled Collection';
            const badgeCount = collection.badges?.length || collection.assertions?.length || 0;
            const collectionId = collection.id || collection.entityId || '';
            const isEmpty = badgeCount === 0;
            
            const option = document.createElement('option');
            option.value = index;
            option.disabled = isEmpty;
            option.textContent = `${name} (${badgeCount} ${badgeCount === 1 ? 'badge' : 'badges'})`;
            option.setAttribute('data-collection-id', collectionId);
            
            collectionSelect.appendChild(option);
        });
    }

    /**
     * Handle collection selection from dropdown
     */
    if (collectionSelect) {
        collectionSelect.addEventListener('change', function() {
            const selectedIndex = this.value;
            
            if (selectedIndex === '') {
                // No collection selected
                syncCollectionBtn.disabled = true;
                syncResults.style.display = 'none';
                removeCollectionHighlights();
            } else {
                // Collection selected
                const collection = fetchedCollections[selectedIndex];
                highlightCollectionCard(collection);
                syncCollectionBtn.disabled = false;
                syncResults.style.display = 'none';
            }
        });
    }

    /**
     * Handle "Sync Collection" button click
     */
    if (syncCollectionBtn) {
        syncCollectionBtn.addEventListener('click', function() {
            const selectedIndex = collectionSelect.value;
            
            if (selectedIndex !== '') {
                const collection = fetchedCollections[selectedIndex];
                syncCollectionToPlatform(collection);
            }
        });
    }

    /**
     * Select collection by ID (when clicking on card)
     */
    function selectCollectionById(collectionId) {
        const index = fetchedCollections.findIndex(c => 
            (c.id || c.entityId) === collectionId
        );
        
        if (index !== -1) {
            collectionSelect.value = index;
            collectionSelect.dispatchEvent(new Event('change'));
        }
    }

    /**
     * Highlight the selected collection card
     */
    function highlightCollectionCard(collection) {
        removeCollectionHighlights();
        
        const collectionId = collection.id || collection.entityId || '';
        const cards = document.querySelectorAll('.collection-card');
        
        cards.forEach(card => {
            if (card.getAttribute('data-collection-id') === collectionId) {
                card.style.border = '3px solid #28a745';
            }
        });
    }

    /**
     * Remove all collection card highlights
     */
    function removeCollectionHighlights() {
        const cards = document.querySelectorAll('.collection-card');
        cards.forEach(card => {
            card.style.border = '';
            card.style.backgroundColor = '';
        });
    }

    /**
     * Sync collection badges from remote backpack to the platform
     */
    async function syncCollectionToPlatform(collection) {
        const name = collection.name || collection.title || 'Untitled Collection';
        const collectionId = collection.id || collection.entityId || '';
        const badges = collection.badges || collection.assertions || [];
        
        console.log('Syncing collection:', collection);
        
        // Hide previous results
        syncResults.style.display = 'none';
        syncResults.innerHTML = '';
        
        // Show progress
        syncProgress.style.display = 'block';
        syncProgressBar.style.width = '0%';
        syncStatus.textContent = '{{ trans('langPreparingSyncOperation') }}...';
        
        // Disable button during sync
        syncCollectionBtn.disabled = true;
        syncCollectionBtn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i>{{ trans('langSyncing') }}...';
        
        try {
            // Update progress
            syncProgressBar.style.width = '10%';
            syncStatus.textContent = '{{ js_escape(trans('langFetchingBadgesFromCollection')) }}' + `: ${escapeHtml(name)}...`;
            
            // Fetch the collection details with badges
            const collectionResponse = await fetch(`{{ $urlServer }}modules/backpack/api/collection_badges.php?collection_id=${encodeURIComponent(collectionId)}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                credentials: 'same-origin'
            });
            
            if (!collectionResponse.ok) {
                throw new Error(`HTTP ${collectionResponse.status}: Failed to fetch collection badges`);
            }
            
            const collectionData = await collectionResponse.json();
            
            if (!collectionData.success) {
                throw new Error(collectionData.errormessage || 'Failed to fetch collection badges');
            }
            
            const remoteBadges = collectionData.data || [];
            const totalBadges = remoteBadges.length;
            
            // Handle empty collections gracefully
            if (totalBadges === 0) {
                // Hide progress
                syncProgress.style.display = 'none';
                
                // Show info message instead of error
                syncResults.style.display = 'block';
                const emptyMessage = '{{ trans('langCollectionIsEmpty') }}'.replace('{name}', escapeHtml(name));
                syncResults.innerHTML = `
                    <div class='alert alert-info'>
                        <h6><i class='fa fa-info-circle me-2'></i>{{ trans('langNoSyncableBadges') }}</h6>
                        <p class='mb-0'>${emptyMessage}</p>
                    </div>
                `;
                
                // Re-enable button
                syncCollectionBtn.disabled = false;
                syncCollectionBtn.innerHTML = '<i class="fa fa-sync me-1"></i>{{ trans('langSyncCollection') }}';
                return;
            }
            
            syncProgressBar.style.width = '30%';
            const foundBadgesMsg = '{{ js_escape(trans('langFoundBadgesToSync')) }}';
            syncStatus.textContent = foundBadgesMsg.replace(':count', totalBadges);
            
            // Sync badges one by one
            let syncedCount = 0;
            let skippedCount = 0;
            let errorCount = 0;
            const syncResultsList = [];
            
            for (let i = 0; i < remoteBadges.length; i++) {
                const badge = remoteBadges[i];
                const badgeName = badge.badgeclass?.name || badge.badge?.name || `Badge ${i + 1}`;
                const assertionId = badge.id || badge.entityId || '';
                
                // Update progress
                const progress = 30 + Math.floor((i / totalBadges) * 60);
                syncProgressBar.style.width = `${progress}%`;
                syncStatus.textContent = '{{ js_escape(trans('langSyncingBadge')) }}' + ` ${i + 1}/${totalBadges}: ${escapeHtml(badgeName)}...`;
                
                try {
                    // Get CSRF token
                    const csrfToken = document.querySelector('input[name="token"]')?.value;
                    
                    // Call sync endpoint
                    const syncResponse = await fetch('{{ $urlServer }}modules/backpack/api/sync_badge.php', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-Token': csrfToken
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify({
                            assertion_id: assertionId,
                            collection_id: collectionId,
                            collection_name: name,
                            badge_data: badge,
                            token: csrfToken
                        })
                    });
                    
                    const syncResult = await syncResponse.json();
                    
                    if (syncResult.success) {
                        if (syncResult.action === 'created') {
                            syncedCount++;
                            syncResultsList.push({
                                name: badgeName,
                                status: 'success',
                                message: '{{ trans('langBadgeSyncedSuccessfully') }}'
                            });
                        } else if (syncResult.action === 'already_exists') {
                            skippedCount++;
                            syncResultsList.push({
                                name: badgeName,
                                status: 'info',
                                message: '{{ trans('langBadgeAlreadyExists') }}'
                            });
                        } else if (syncResult.action === 'skipped_local_badge') {
                            skippedCount++;
                            syncResultsList.push({
                                name: badgeName,
                                status: 'warning',
                                message: '{{ trans('langBadgeOriginatedLocally') }}'
                            });
                        }
                    } else {
                        errorCount++;
                        syncResultsList.push({
                            name: badgeName,
                            status: 'error',
                            message: syncResult.errormessage || '{{ trans('langBadgeSyncFailed') }}'
                        });
                    }
                } catch (error) {
                    errorCount++;
                    syncResultsList.push({
                        name: badgeName,
                        status: 'error',
                        message: error.message
                    });
                }
                
                // Small delay to avoid overwhelming the server
                await new Promise(resolve => setTimeout(resolve, 100));
            }
            
            // Sync complete
            syncProgressBar.style.width = '100%';
            syncStatus.textContent = '{{ trans('langSyncComplete') }}';
            
            // Hide progress after a moment
            setTimeout(() => {
                syncProgress.style.display = 'none';
            }, 1500);
            
            // Display results
            displaySyncResults(name, totalBadges, syncedCount, skippedCount, errorCount, syncResultsList);
            
        } catch (error) {
            console.error('Sync error:', error);
            
            // Hide progress
            syncProgress.style.display = 'none';
            
            // Show error
            syncResults.style.display = 'block';
            syncResults.innerHTML = `
                <div class='alert alert-danger'>
                    <h6><i class='fa fa-exclamation-triangle me-2'></i>{{ trans('langSyncFailed') }}</h6>
                    <p class='mb-0'>${escapeHtml(error.message)}</p>
                </div>
            `;
        } finally {
            // Re-enable button
            syncCollectionBtn.disabled = false;
            syncCollectionBtn.innerHTML = '<i class="fa fa-sync me-1"></i>{{ trans('langSyncCollection') }}';
        }
    }
    
    /**
     * Display sync results summary
     */
    function displaySyncResults(collectionName, total, synced, skipped, errors, resultsList) {
        let html = `
            <div class='card'>
                <div class='card-header bg-success text-white'>
                    <h6 class='mb-0'>
                        <i class='fa fa-check-circle me-2'></i>
                        {{ trans('langSyncCompletedSuccessfully') }}
                    </h6>
                </div>
                <div class='card-body'>
                    <h6 class='mb-3'>{{ trans('langCollection') }}: ${escapeHtml(collectionName)}</h6>
                    
                    <div class='row text-center mb-3'>
                        <div class='col-md-3'>
                            <div class='border rounded p-2'>
                                <h4 class='text-primary mb-0'>${total}</h4>
                                <small class='text-muted'>{{ trans('langTotalBadges') }}</small>
                            </div>
                        </div>
                        <div class='col-md-3'>
                            <div class='border rounded p-2'>
                                <h4 class='text-success mb-0'>${synced}</h4>
                                <small class='text-muted'>{{ trans('langSynced') }}</small>
                            </div>
                        </div>
                        <div class='col-md-3'>
                            <div class='border rounded p-2'>
                                <h4 class='text-info mb-0'>${skipped}</h4>
                                <small class='text-muted'>{{ trans('langSkipped') }}</small>
                            </div>
                        </div>
                        <div class='col-md-3'>
                            <div class='border rounded p-2'>
                                <h4 class='text-danger mb-0'>${errors}</h4>
                                <small class='text-muted'>{{ trans('langErrors') }}</small>
                            </div>
                        </div>
                    </div>
        `;
        
        // Show detailed results if there are any errors or if user wants to see details
        if (resultsList.length > 0) {
            html += `
                <details class='mt-3'>
                    <summary class='mb-2' style='cursor: pointer;'>
                        <strong>{{ trans('langViewDetailedResults') }}</strong>
                    </summary>
                    <div class='mt-2' style='max-height: 300px; overflow-y: auto;'>
            `;
            
            resultsList.forEach(result => {
                let iconClass = 'fa-check-circle text-success';
                let alertClass = 'alert-success';
                
                if (result.status === 'info') {
                    iconClass = 'fa-info-circle text-info';
                    alertClass = 'alert-info';
                } else if (result.status === 'error') {
                    iconClass = 'fa-exclamation-circle text-danger';
                    alertClass = 'alert-danger';
                }
                
                html += `
                    <div class='alert ${alertClass} py-2 px-3 mb-2'>
                        <i class='fa ${iconClass} me-2'></i>
                        <strong>${escapeHtml(result.name)}:</strong> ${escapeHtml(result.message)}
                    </div>
                `;
            });
            
            html += `
                    </div>
                </details>
            `;
        }
        
        html += `
                    <div class='mt-3'>
                        <a href='{{ $urlServer }}main/portfolio.php' class='btn btn-primary'>
                            <i class='fa fa-folder-open me-1'></i>
                            {{ trans('langViewMyPortfolio') }}
                        </a>
                    </div>
                </div>
            </div>
        `;
        
        syncResults.innerHTML = html;
        syncResults.style.display = 'block';
    }

    /**
     * Save the selected collection to the database
     */
    async function saveSelectedCollection(collectionId, collectionName) {
        try {
            const response = await fetch('{{ $urlServer }}modules/backpack/api/update_selected_collection.php', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    collection_id: collectionId,
                    collection_name: collectionName
                })
            });
            
            if (response.ok) {
                const result = await response.json();
                console.log('Selected collection saved:', result);
            } else {
                console.warn('Failed to save selected collection:', response.status);
            }
        } catch (error) {
            console.error('Error saving selected collection:', error);
        }
    }

    /**
     * Auto-select the last selected collection on page load
     */
    function autoSelectLastCollection() {
        @if (isset($selectedCollection) && $selectedCollection)
            const lastCollectionId = '{{ $selectedCollection['id'] ?? '' }}';
            const lastCollectionName = '{{ $selectedCollection['name'] ?? '' }}';
            
            if (lastCollectionId && fetchedCollections.length > 0) {
                // Find the collection in the fetched list
                const index = fetchedCollections.findIndex(col => {
                    const colId = col.id || col.entityId || '';
                    return colId === lastCollectionId;
                });
                
                if (index !== -1) {
                    console.log('Auto-selecting last used collection:', lastCollectionName);
                    collectionSelect.value = index;
                    collectionSelect.dispatchEvent(new Event('change'));
                }
            }
        @endif
    }

    /**
     * Escape HTML to prevent XSS
     */
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
});
</script>

@endsection 