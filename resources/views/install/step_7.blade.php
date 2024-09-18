<div class='alert alert-info'>
    <i class='fa-solid fa-circle-info fa-lg'></i>
    {{ trans('langReviewSettings') }}
</div>

<form class='form-horizontal form-wrapper form-edit p-3 rounded' role='form' action='{{ $_SERVER['SCRIPT_NAME'] }}' method='post'>
    <fieldset>
        <legend class='mb-0' aria-label='{{ trans('langForm') }}'></legend>

        <div class='form-group mt-3'>
            <div class='col-sm-12 control-label-notes'>
                {{ trans('langdbhost') }}
            </div>
            <div class='col-sm-12'>
                <p class='form-control-static'>
                    {{ $GLOBALS['dbHostForm'] }}
                </p>
            </div>
        </div>

        <div class='form-group mt-3'>
            <div class='col-sm-12 control-label-notes'>
                {{ trans('langDBLogin') }}
            </div>
            <div class='col-sm-12'>
                <p class='form-control-static'>
                    {{ $GLOBALS['dbUsernameForm'] }}
                </p>
            </div>
        </div>

        <div class='form-group mt-3'>
            <div class='col-sm-12 control-label-notes'>
                {{ trans('langMainDB') }}
            </div>
            <div class='col-sm-12'>
                <p class='form-control-static'>
                    {{ $GLOBALS['dbNameForm'] }}
                </p>
            </div>
        </div>

        <div class='form-group mt-3'>
            <div class='col-sm-12 control-label-notes'>
                {{ trans('langSiteUrl') }}
            </div>
            <div class='col-sm-12'>
                <p class='form-control-static'>
                    {{ $GLOBALS['urlForm'] }}
                </p>
            </div>
        </div>

        <div class='form-group mt-3'>
            <div class='col-sm-12 control-label-notes'>
                {{ trans('langAdminEmail') }}
            </div>
            <div class='col-sm-12'>
                <p class='form-control-static'>
                    {{ $GLOBALS['emailForm'] }}
                </p>
            </div>
        </div>

        <div class='form-group mt-3'>
            <div class='col-sm-12 control-label-notes'>
                {{ trans('langAdminName') }}
            </div>
            <div class='col-sm-12'>
                <p class='form-control-static'>
                    {{ $GLOBALS['nameForm'] }}
                </p>
            </div>
        </div>

        <div class='form-group mt-3'>
            <div class='col-sm-12 control-label-notes'>
                {{ trans('langAdminLogin') }}
            </div>
            <div class='col-sm-12'>
                <p class='form-control-static'>
                    {{ $GLOBALS['loginForm'] }}
                </p>
            </div>
        </div>

        <div class='form-group mt-3'>
            <div class='col-sm-12 control-label-notes'>
                {{ trans('langAdminPass') }}
            </div>
            <div class='col-sm-12'>
                <p class='form-control-static'>
                    {{ $GLOBALS['passForm'] }}
                </p>
            </div>
        </div>

        <div class='form-group mt-3'>
            <div class='col-sm-12 control-label-notes'>
                {{ trans('langCampusName') }}
            </div>
            <div class='col-sm-12'>
                <p class='form-control-static'>
                    {{ $GLOBALS['campusForm'] }}
                </p>
            </div>
        </div>

        <div class='form-group mt-3'>
            <div class='col-sm-12 control-label-notes'>
                {{ trans('langHelpDeskPhone') }}
            </div>
            <div class='col-sm-12'>
                <p class='form-control-static'>
                    {{ $GLOBALS['helpdeskForm'] }}
                </p>
            </div>
        </div>

        <div class='form-group mt-3'>
            <div class='col-sm-12 control-label-notes'>
                {{ trans('langHelpDeskEmail') }}
            </div>
            <div class='col-sm-12'>
                <p class='form-control-static'>
                    {{ $GLOBALS['helpdeskmail'] }}
                </p>
            </div>
        </div>

        <div class='form-group mt-3'>
            <div class='col-sm-12 control-label-notes'>
                {{ trans('langInstituteShortName') }}
            </div>
            <div class='col-sm-12'>
                <p class='form-control-static'>
                    {{ $GLOBALS['institutionForm'] }}
                </p>
            </div>
        </div>

        <div class='form-group mt-3'>
            <div class='col-sm-12 control-label-notes'>
                {{ trans('langInstituteName') }}
            </div>
            <div class='col-sm-12'>
                <p class='form-control-static'>
                    {{ $GLOBALS['institutionUrlForm'] }}
                </p>
            </div>
        </div>

        <div class='form-group mt-3'>
            <div class='col-sm-12 control-label-notes'>
                {{ trans('langDisableEclassStudRegType') }}
            </div>
            <div class='col-sm-12'>
                <p class='form-control-static'>
                    {{ $GLOBALS['disable_eclass_stud_reg_info'] }}
                </p>
            </div>
        </div>

        <div class='form-group mt-3'>
            <div class='col-sm-12 control-label-notes'>
                {{ trans('langInstitutePostAddress') }}
            </div>
            <div class='col-sm-12'>
                <p class='form-control-static'>
                    {{ $GLOBALS['postaddressForm'] }}
                </p>
            </div>
        </div>

        <div class='form-group mt-3'>
            <div class='col-sm-12 control-label-notes'>
                {{ trans('langHomePageIntroTextHelp') }}
            </div>
            <div class='col-sm-12'>
                <p class='form-control-static'>
                    {{ $GLOBALS['homepage_intro'] }}
                </p>
            </div>
        </div>

        <div class='form-group mt-3'>
            <div class='col-sm-12 control-label-notes'>
                {{ trans('langActiveTheme') }}
            </div>
            <div class='col-sm-12'>
                <p class='form-control-static'>
                    {{ $available_theme }}
                </p>
            </div>
        </div>

        <div class='form-group mt-5'>
            <div class='col-12'>
                <div class='row'>
                    <div class='col-lg-5 col-12'>
                        <input aria-label="{{ trans('langPreviousStep') }}" type='submit' class='btn cancelAdminBtn w-100' name='install6' value='&laquo; {{ trans('langPreviousStep') }}'>
                    </div>
                    <div class='col-lg-7 col-12 mt-lg-0 mt-3'>
                        <input aria-label="{{ trans('langInstall') }}" type='submit' class='btn w-100' name='install8' id='install8' value='{{ trans('langInstall') }} &raquo;'>
                    </div>
                </div>
            </div>
        </div>

        {!! hidden_vars($all_vars) !!}
    </fieldset>

</form>

<script type='text/javascript'>
    $(function() {
        $('#install6').on( 'click', function() {
            bootbox.dialog({
                closeButton: false,
                message:  '<div><p>{{ js_escape(trans('langInstallMsg')) }}</p></div>'+
                    '<div class=\"progress\">'+
                    '<div class=\"progress-bar progress-bar-striped active\" role=\"progressbar\" aria-valuenow=\"100\" aria-valuemin=\"0\" aria-valuemax=\"100\" style=\"width: 100%\">'+
                    '<span class=\"sr-only\">{{ js_escape(trans('langCheckNotOk1')) }}</span>'+
                    '</div>'+
                    '</div>',
                title: '{{ js_escape(trans('langCheckNotOk1')) }}'
            });
        });
    });
</script>
