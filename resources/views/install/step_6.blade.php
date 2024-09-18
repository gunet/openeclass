<div class='col-12'>
    <div class='form-wrapper form-edit p-3 rounded'>
        <form class='form-horizontal form-wrapper form-edit p-3 rounded' role='form' action='{{ $_SERVER['SCRIPT_NAME'] }}' method='post'>
            <fieldset>
                <legend class='mb-0' aria-label='{{ trans('langForm') }}'></legend>
                {!! mail_settings_form() !!}

                <div class='form-group mt-5'>
                    <div class='col-12'>
                        <div class='row'>
                            <div class='col-lg-6 col-12'>
                                <input aria-label="{{ trans('langPreviousStep') }}" type='submit' class='btn cancelAdminBtn w-100' name='install5' value='&laquo; {{ trans('langPreviousStep') }}'>
                            </div>
                            <div class='col-lg-6 col-12 mt-lg-0 mt-3'>
                                <input aria-label="{{ trans('langNextStep') }}" type='submit' class='btn w-100' name='install7' value='{{ trans('langNextStep') }} &raquo;'>
                            </div>
                        </div>
                    </div>
                </div>

                {!! hidden_vars($all_vars, [ 'dont_mail_unverified_mails',
                                            'email_from',
                                            'email_announce',
                                            'email_bounces',
                                            'email_transport',
                                            'smtp_server',
                                            'smtp_port',
                                            'smtp_encryption',
                                            'smtp_username',
                                            'smtp_password',
                                            'sendmail_command'
                                            ]) !!}
            </fieldset>
        </form>
    </div>
</div>

<script>
    $(function () {
        $('#formEmailTransport').change(function() {
            var type = $(this).val();
            if (type == 1) {
                $('.SMTP-settings').show();
                $('.Sendmail-settings').hide();
            } else if (type == 2) {
                $('.SMTP-settings').hide();
                $('.Sendmail-settings').show();
            } else {
                $('.SMTP-settings, .Sendmail-settings').hide();
            }
            if (type == 0 && $('#formEmailAnnounce').val() == '') {
                $('#emailSendWarn').show();
                $('#formEmailAnnounceGroup').addClass('has-error');
            } else {
                $('#emailSendWarn').hide();
                $('#formEmailAnnounceGroup').removeClass('has-error');
            }
        }).change();
        $('#revealPass').mousedown(function () {
            $('#formSMTPPassword').attr('type', 'text');
        }).mouseup(function () {
            $('#formSMTPPassword').attr('type', 'password');
        });
    });
</script>
