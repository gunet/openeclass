<div class='modal fade' id='consentModal' tabindex='-1' role='dialog' aria-labelledby='consentModalLabel'>
    <div class='modal-dialog' role='document'>
        <div class='modal-content'>
            <div class='modal-header'>
                <div class='modal-title' id='consentModalLabel'>{{ trans('langUserConsent') }}</div>
                <button type='button' class='close' data-bs-dismiss='modal' aria-label='Close'></button>
                
            </div>
            <div class='modal-body' style='margin-left:20px; margin-right:20px;'>
                {!! get_config('privacy_policy_text_' . $session->language) !!}
            </div>
            <form method='post' action='{{ $_SERVER['SCRIPT_NAME'] }}'>
                <div class='modal-footer'>
                    <button type='submit' class='btn submitAdminBtn' role='button' name='accept_policy'
                        value='yes'>{{ trans('langAccept') }}</button>
                    <button type='submit' class='btn deleteAdminBtn ms-1' role='button' name='accept_policy'
                        value='no'>{{ trans('langRejectRequest') }}</button>
                    <button type='submit' class='btn cancelAdminBtn' role='button' name='accept_policy'
                        value='later'>{{ trans('langLater') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
