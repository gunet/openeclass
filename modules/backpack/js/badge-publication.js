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
                // Check if button is disabled (e.g., export not allowed)
                if ($(this).prop('disabled')) {
                    return false;
                }
                const userBadgeId = $(this).data('user-badge-id');
                // Only show modal if we have a valid badge ID
                if (userBadgeId) {
                    BadgePublication.showPublishModal(userBadgeId);
                }
            });

            $(document).on('click', '#confirmPublishBadge', function() {
                BadgePublication.publishBadge();
            });

            $(document).on('change', '#publishBadgeProvider', function() {
                // Clear any previous feedback messages when provider changes
                $('#publishBadgeFeedback').hide().empty();
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
         * Note: Does not enable buttons that are disabled due to export restrictions
         */
        enablePublishButtons: function() {
            // Only enable buttons that have a user-badge-id attribute
            // Buttons without this attribute are disabled for other reasons (e.g., allow_export=0)
            $('.badge-publish-btn[data-user-badge-id]').removeClass('disabled');
            $('.badge-publish-btn[data-user-badge-id]').prop('disabled', false);
            $('.badge-publish-btn[data-user-badge-id]').show();
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
                        // Use DOM API to prevent XSS
                        const option = document.createElement('option');
                        option.value = provider.id;
                        option.setAttribute('data-provider-name', provider.name);
                        option.textContent = provider.name; // Safely sets text content
                        providerSelect.append(option);
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

            // Get CSRF token from the page
            const csrfToken = document.querySelector('input[name="token"]')?.value || 
                              document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            $.ajax({
                url: '/modules/backpack/api/publish_badge.php',
                method: 'POST',
                dataType: 'json',
                contentType: 'application/json',
                headers: {
                    'X-CSRF-Token': csrfToken
                },
                data: JSON.stringify({
                    user_badge_id: parseInt(userBadgeId),
                    provider_id: parseInt(providerId),
                    token: csrfToken
                }),
                success: function(response) {
                    if (response.success) {
                        // Show success message in modal
                        BadgePublication.showModalMessage(response.message || BADGE_PUBLISH_SUCCESS, 'success');
                        
                        // Update badge card to show published status
                        const badgeCard = $('[data-user-badge-id="' + userBadgeId + '"]').closest('.badge-card-wrapper');
                        const badgeFooter = badgeCard.find('.badge-card-footer');
                        
                        // Replace the publish button with published status
                        if (typeof BADGE_PUBLISHED_TO_BACKPACK !== 'undefined') {
                            badgeFooter.html(`
                                <div class='badge-published-status'>
                                    <i class='fa fa-check-circle text-success'></i>
                                    <span class='text-success'>${BADGE_PUBLISHED_TO_BACKPACK}</span>
                                </div>
                            `);
                        }
                        
                        // Close modal after showing success message
                        setTimeout(function() {
                            $('#publishBadgeModal').modal('hide');
                            BadgePublication.resetPublishModal();
                        }, 1500);
                    } else {
                        // Show generic error message in modal (keep modal open)
                        BadgePublication.showModalMessage(BADGE_PUBLISH_ERROR, 'danger');
                        BadgePublication.setPublishButtonState('ready');
                    }
                },
                error: function(xhr, status, error) {
                    // Show generic error message in modal (keep modal open)
                    BadgePublication.showModalMessage(BADGE_PUBLISH_ERROR, 'danger');
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
            $('#publishBadgeFeedback').hide().empty();
            this.setPublishButtonState('ready');
        },

        /**
         * Show message inside the modal
         */
        showModalMessage: function(message, type) {
            const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
            const alertHtml = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    <i class="fa ${icon} me-2"></i>
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;

            $('#publishBadgeFeedback').html(alertHtml).show();
            
            // Scroll to top of modal to show message
            $('.modal-body').animate({ scrollTop: 0 }, 300);
        }
    };

    // Initialize when document is ready
    $(document).ready(function() {
        BadgePublication.init();
    });

    // Export for external use
    window.BadgePublication = BadgePublication;

})();
