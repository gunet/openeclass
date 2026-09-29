<div class='modal fade' id='tenantsCronInfoModal' tabindex='-1' role='dialog' aria-labelledby='tenantsCronInfoModalLabel'>
    <div class='modal-dialog' role='document'>
        <div class='modal-content'>
            <div class='modal-header'>
                <div class='modal-title' id='tenantsCronInfoModal'>{{ trans('langTenantsCronEnableTitle') }}</div>
                <button type='button' class='close' data-dismiss='modal' aria-label='{{ trans('langClose') }}'></button>
                
            </div>
            <div class='modal-body'>
                {!! trans('langTenantsCronEnableInstructions') !!}
            </div>
            <div class='modal-footer'>
                <button type='button' class='btn submitAdminBtn' data-bs-dismiss='modal'>{{ trans('langClose') }}</button>
            </div>
        </div>
    </div>
</div>

<div class='col-12'>
    <div class='alert {!! $cron_class !!}'>
        <i class='fa-solid fa-circle-info fa-lg'></i>
        <span>{!! $cron_message !!}</span>
    </div>
</div>
