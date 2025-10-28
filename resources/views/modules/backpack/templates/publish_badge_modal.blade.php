<!-- Publish Badge to Backpack Modal -->
<div class="modal fade" id="publishBadgeModal" tabindex="-1" role="dialog" aria-labelledby="publishBadgeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="publishBadgeModalLabel">
                    <i class="fa fa-cloud-upload me-2"></i>
                    {{ trans('langPublishBadgeToBackpack') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="publishBadgeProvider" class="form-label">
                        {{ trans('langSelectBackpackProvider') }}
                    </label>
                    <select class="form-select" id="publishBadgeProvider" name="publishBadgeProvider" required>
                        <option value="">-- {{ trans('langSelectProvider') }} --</option>
                    </select>
                    <small class="form-text text-muted d-block mt-2">
                        {{ trans('langSelectProviderHelp') }}
                    </small>
                </div>

                <div class="form-group">
                    <label class="form-label">{{ trans('langSelectedProvider') }}</label>
                    <div id="selectedProviderInfo" class="py-2">
                        --
                    </div>
                </div>

                <div class="alert alert-info" role="alert">
                    <i class="fa fa-info-circle me-2"></i>
                    <small>
                        {{ trans('langPublishBadgeInfo') }}
                    </small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    {{ trans('langCancel') }}
                </button>
                <button type="button" class="btn btn-primary" id="confirmPublishBadge">
                    <i class="fa fa-cloud-upload"></i> {{ trans('langPublish') }}
                </button>
            </div>
        </div>
    </div>
</div>
