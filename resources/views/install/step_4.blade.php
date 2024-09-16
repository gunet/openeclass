<form class='form-horizontal form-wrapper form-edit p-3 rounded' role='form' action='{{ $_SERVER['SCRIPT_NAME'] }}' method='post'>
    <legend class='mb-0' aria-label='{{ trans('langForm') }}'></legend>

        <div class='form-group mt-3'>
            <label for='urlForm' class='col-sm-12 control-label-notes'> {{ trans('langSiteUrl') }} (*)</label>
                <div class='col-sm-12'>
                    <input class='form-control' type='text' size='40' name='urlForm' value='{{ $GLOBALS['urlForm'] }}'>
                </div>
        </div>

        <div class='form-group mt-3'>
            <label for='campusForm' class='col-sm-12 control-label-notes'>{{ trans('langCampusName') }}</label>
            <div class='col-sm-12'>
                <input class='form-control' type='text' size='40' name='campusForm' value='{{ $GLOBALS['campusForm'] }}'>
            </div>
        </div>

        <div class='form-group mt-3'>
            <label for='nameForm' class='col-sm-12 control-label-notes'>{{ trans('langAdminName') }} (*)</label>
            <div class='col-sm-12'>
                <input class='form-control' type='text' size='40' name='nameForm' value='{{ $GLOBALS['nameForm'] }}'>
            </div>
        </div>

        <div class='form-group mt-3'>
            <label for='emailForm' class='col-sm-12 control-label-notes'>{{ trans('langAdminEmail') }} (*)</label>
            <div class='col-sm-12'>
                <input class='form-control' type='text' size='40' name='emailForm' value='{{ $GLOBALS['emailForm'] }}'>
            </div>
        </div>

        <div class='form-group mt-3'>
            <label for='loginForm' class='col-sm-12 control-label-notes'>{{ trans('langAdminLogin') }} (*)</label>
            <div class='col-sm-12'>
                <input class='form-control' type='text' size='40' name='loginForm' value='{{ $GLOBALS['loginForm'] }}'>
            </div>
        </div>

        <div class='form-group mt-3'>
            <label for='passForm' class='col-sm-12 control-label-notes'>{{ trans('langAdminPass') }} (*)</label>
            <div class='col-sm-12'>
                <input class='form-control' type='text' size='40' name='passForm' value='{{ $GLOBALS['passForm'] }}'>
            </div>
        </div>

        <div class='form-group mt-3'>
            <label for='helpdeskForm' class='col-sm-12 control-label-notes'>{{ trans('langHelpDeskPhone') }}</label>
            <div class='col-sm-12'>
                <input class='form-control' type='text' size='40' name='helpdeskForm' value='{{ $GLOBALS['helpdeskForm'] }}'>
            </div>
        </div>

        <div class='form-group mt-3'>
            <label for='helpdeskmail' class='col-sm-12 control-label-notes'>{{ trans('langHelpDeskEmail') }} </label>
            <div class='col-sm-12'>
                <input class='form-control' type='text' size='40' name='helpdeskmail' placeholder="{{ trans('langWarnHelpDesk') }}" value='{{ $GLOBALS['helpdeskmail'] }}'>
            </div>
        </div>

        <div class='form-group mt-3'>
            <label for='institutionForm' class='col-sm-12 control-label-notes'>{{ trans('langInstituteShortName') }}</label>
            <div class='col-sm-12'>
                <input class='form-control' type='text' size='40' name='institutionForm' value='{{ $GLOBALS['institutionForm'] }}'>
            </div>
        </div>

        <div class='form-group mt-3'>
            <label for='institutionUrlForm' class='col-sm-12 control-label-notes'>{{ trans('langInstituteName') }}</label>
            <div class='col-sm-12'>
                <input class='form-control' type='text' size='40' name='institutionUrlForm' value='{{ $GLOBALS['institutionUrlForm'] }}'>
            </div>
        </div>

        <div class='form-group mt-3'>
            <label for='postaddressForm' class='col-sm-12 control-label-notes'>{{ trans('langInstitutePostAddress') }}</label>
            <div class='col-sm-12'>
                <textarea class='form-control' rows='3' cols='40' name='postaddressForm'>{{ $GLOBALS['postaddressForm'] }}</textarea>
            </div>
        </div>

        <div class='form-group mt-3'>
            <label for='eclass_stud_reg' class='col-sm-12 control-label-notes'>{{ trans('langUsersAccount') }}</label>
            <div class='col-sm-12'>
                {!! $user_registration_selection !!}
            </div>
        </div>

        <div class='form-group mt-3 help-block'>
            {{ trans('langRequiredFields') }}
        </div>

        <div class='form-group mt-3'>
            <div class='col-12'>
                <div class='row'>
                    <div class='col-lg-6 col-12'>
                        <input type='submit' class='btn cancelAdminBtn w-100' name='install3' value='&laquo; {{ trans('langPreviousStep') }}'>
                    </div>
                    <div class='col-lg-6 col-12 mt-lg-0 mt-3'>
                        <input type='submit' class='btn w-100' name='install5' id='install5' value='{{ trans('langNextStep') }} &raquo;'>
                    </div>
                </div>
            </div>
        </div>

    {!! hidden_vars($all_vars, [ 'urlForm',
                                'nameForm',
                                'emailForm',
                                'loginForm',
                                'passForm',
                                'campusForm',
                                'helpdeskForm',
                                'helpdeskmail',
                                'institutionForm',
                                'institutionUrlForm',
                                'postaddressForm' ]) !!}
</form>
