<div class='modal fade' id='bbbCronInfoModal' tabindex='-1' role='dialog' aria-labelledby='bbbCronInfoModalLabel'>
    <div class='modal-dialog' role='document'>
        <div class='modal-content'>
            <div class='modal-header'>
                <button type='button' class='close' data-dismiss='modal' aria-label='{{ trans('langClose') }}'><span aria-hidden='true'></span></button>
                <h4 class='modal-title' id='bbbCronInfoModal'>{{ trans('langBBBCronEnableTitle') }}</h4>
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

<div class='alert {!! $tc_cron_class !!}' style='display: flex; align-items: center;'>
    <div style='margin-right: 15px'><i class='fas {{ $tc_cron_icon }} fa-2x'></i></div>
    <div style='width: 100%'>{!! $tc_cron_message !!}</div>
</div>
