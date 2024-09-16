<div class='modal fade' id='bbbCronInfoModal' tabindex='-1' role='dialog' aria-labelledby='bbbCronInfoModalLabel'>
    <div class='modal-dialog' role='document'>
        <div class='modal-content'>
            <div class='modal-header'>
                <div class='modal-title' id='bbbCronInfoModal'>{{ trans('langBBBCronEnableTitle') }}</div>
                <button type='button' class='close' data-dismiss='modal' aria-label='{{ trans('langClose') }}'></button>
                
            </div>
            <div class='modal-body'>
                {!! trans('langBBBCronEnableInstructions') !!}
            </div>
            <div class='modal-footer'>
                <button type='button' class='btn submitAdminBtn' data-bs-dismiss='modal'>{{ trans('langClose') }}</button>
            </div>
        </div>
    </div>
</div>

<div class='col-12'>
    <div class='alert {!! $tc_cron_class !!}'>
        <i class='fa-solid fa-circle-info fa-lg'></i>
        <span>{!! $tc_cron_message !!}</span>
    </div>
</div>
