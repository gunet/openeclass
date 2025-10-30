/**
 * Badge Publication Module
 * 
 * Handles the UI and interactions for publishing badges to external backpack providers
 */

(function() {
    'use strict';

    const BadgePublication = {
        
        /**
         * Initialize the badge publication module
         */
        init: function() {
            this.attachEventListeners();
            this.loadBackpackConnections();
        },

        /**
         * Attach click event listeners to publish buttons
         */
        attachEventListeners: function() {
            $(document).on('click', '.badge-publish-btn', function(e) {
                e.preventDefault();
                const userBadgeId = $(this).data('user-badge-id');
                BadgePublication.showPublishModal(userBadgeId);
            });

            $(document).on('click', '#confirmPublishBadge', function() {
                BadgePublication.publishBadge();
            });

            $(document).on('change', '#publishBadgeProvider', function() {
                BadgePublication.updateProviderInfo();
            });
        },

        /**
         * Load backpack connections for the current user
         */
        loadBackpackConnections: function() {
            $.ajax({
                url: '/modules/backpack/api/collections.php',
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.data && response.data.length > 0) {
                        // User has at least one backpack connected
                        BadgePublication.enablePublishButtons();
                    }
                },
                error: function() {
                    // No connections or error - buttons remain disabled
                    console.log('No backpack connections found');
                }
            });
        },

        /**
         * Enable publish buttons if user has backpack connections
         */
        enablePublishButtons: function() {
            $('.badge-publish-btn').removeClass('disabled');
            $('.badge-publish-btn').prop('disabled', false);
            $('.badge-publish-btn').show();
            $('.badge-no-connection').hide();
        },

        /**
         * Show the publish modal dialog
         */
        showPublishModal: function(userBadgeId) {
            // Store the current badge ID
            window.currentUserBadgeId = userBadgeId;

            // Fetch available providers
            this.fetchAvailableProviders(function(providers) {
                // Clear and populate provider select
                const providerSelect = $('#publishBadgeProvider');
                providerSelect.empty();
                providerSelect.append('<option value="">-- ' + BADGE_SELECT_PROVIDER + ' --</option>');

                if (providers && providers.length > 0) {
                    $.each(providers, function(index, provider) {
                        providerSelect.append(
                            '<option value="' + provider.id + '" data-provider-name="' + provider.name + '">' +
                            provider.name +
                            '</option>'
                        );
                    });
                    providerSelect.prop('disabled', false);
                    $('#publishBadgeModal').modal('show');
                } else {
                    alert(BADGE_NO_PROVIDERS_CONNECTED);
                }
            });
        },

        /**
         * Fetch available providers
         */
        fetchAvailableProviders: function(callback) {
            $.ajax({
                url: '/modules/backpack/api/get_providers.php',
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.providers) {
                        callback(response.providers);
                    } else {
                        callback([]);
                    }
                },
                error: function() {
                    console.error('Failed to fetch providers');
                    callback([]);
                }
            });
        },

        /**
         * Update provider information display
         */
        updateProviderInfo: function() {
            const selectedProvider = $('#publishBadgeProvider').find(':selected');
            const providerName = selectedProvider.data('provider-name');
            
            if (providerName) {
                $('#selectedProviderInfo').text(providerName);
            }
        },

        /**
         * Publish badge to selected provider
         */
        publishBadge: function() {
            const providerId = $('#publishBadgeProvider').val();
            const userBadgeId = window.currentUserBadgeId;

            if (!providerId) {
                alert(BADGE_SELECT_PROVIDER_ALERT);
                return;
            }

            // Show loading state
            this.setPublishButtonState('loading');

            $.ajax({
                url: '/modules/backpack/api/publish_badge.php',
                method: 'POST',
                dataType: 'json',
                contentType: 'application/json',
                data: JSON.stringify({
                    user_badge_id: parseInt(userBadgeId),
                    provider_id: parseInt(providerId)
                }),
                success: function(response) {
                    if (response.success) {
                        BadgePublication.showSuccessMessage(response.message || BADGE_PUBLISH_SUCCESS);
                        $('#publishBadgeModal').modal('hide');
                        
                        // Mark badge as published
                        $('[data-user-badge-id="' + userBadgeId + '"]').addClass('badge-published');
                        $('[data-user-badge-id="' + userBadgeId + '"]').find('i').addClass('badge-published-icon');
                        
                        // Reset modal
                        BadgePublication.resetPublishModal();
                    } else {
                        BadgePublication.showErrorMessage(response.errormessage || BADGE_PUBLISH_ERROR);
                    }
                    BadgePublication.setPublishButtonState('ready');
                },
                error: function(xhr, status, error) {
                    let errorMessage = BADGE_PUBLISH_ERROR;
                    
                    if (xhr.responseJSON && xhr.responseJSON.errormessage) {
                        errorMessage = xhr.responseJSON.errormessage;
                    }
                    
                    BadgePublication.showErrorMessage(errorMessage);
                    BadgePublication.setPublishButtonState('ready');
                }
            });
        },

        /**
         * Set publish button state
         */
        setPublishButtonState: function(state) {
            const btn = $('#confirmPublishBadge');
            
            if (state === 'loading') {
                btn.prop('disabled', true);
                btn.html('<span class="spinner-border spinner-border-sm me-2"></span>' + BADGE_PUBLISHING);
            } else {
                btn.prop('disabled', false);
                btn.html('<i class="fa fa-cloud-upload"></i> ' + BADGE_PUBLISH);
            }
        },

        /**
         * Reset publish modal to initial state
         */
        resetPublishModal: function() {
            $('#publishBadgeProvider').val('');
            $('#selectedProviderInfo').text('--');
            this.setPublishButtonState('ready');
        },

        /**
         * Show success message
         */
        showSuccessMessage: function(message) {
            this.showAlert(message, 'success');
        },

        /**
         * Show error message
         */
        showErrorMessage: function(message) {
            this.showAlert(message, 'danger');
        },

        /**
         * Show alert message
         */
        showAlert: function(message, type) {
            const alertId = 'badge-alert-' + Date.now();
            const alertHtml = `
                <div id="${alertId}" class="alert alert-${type} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;

            // Insert alert after the publish modal or at the top of the page
            $('body').prepend(alertHtml);

            // Auto-remove after 5 seconds
            setTimeout(function() {
                $('#' + alertId).fadeOut('slow', function() {
                    $(this).remove();
                });
            }, 5000);
        }
    };

    // Initialize when document is ready
    $(document).ready(function() {
        BadgePublication.init();
    });

    // Export for external use
    window.BadgePublication = BadgePublication;

})();
