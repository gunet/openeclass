@if ($db_error_connection)
    <div class='alert alert-danger'>
        <i class='fa-solid fa-circle-xmark fa-lg'></i>
            <span><p>{{ trans('langErrorConnectDatabase') }}</p>
                <p class="pt-2">
                    <strong>{{ $db_error_message }}</strong>
                </p>
                <p class="pt-2">
                    {{ trans('langCheckDatabaseSettings') }}
                </p>
            </span>
    </div>
@endif

@if ($db_error_db_engine)
    <div class='alert alert-warning'>
        <i class='fa-solid fa-triangle-exclamation fa-lg'></i>
        <span>{{ trans('langInnoDBMissing') }}</span>
    </div>
@endif

@if ($db_error_db_exists)
    <div class='alert alert-warning'>
        <i class='fa-solid fa-circle-info fa-lg'></i>
        <span> {!! (sprintf(trans('langDatabaseExists'), "<strong>$dbNameForm</strong>")) !!}</span>
    </div>
@endif

<div class='alert alert-info'>
    <i class='fa-solid fa-circle-info fa-lg'></i>
    <span>{{ trans('langWillWrite') }} <strong>config/config.php</strong>
        {{ trans('langDBSettingIntro') }}
    </span>
</div>
<form class='form-horizontal form-wrapper form-edit p-3 rounded' role='form' action='{{ $_SERVER['SCRIPT_NAME'] }}' method='post'>
    <legend class='mb-0' aria-label='{{ trans('langForm') }}'></legend>
    <div class='form-group'>
        <label for='dbHostForm' class='col-sm-12 control-label-notes'>{{ trans('langdbhost') }} (*)</label>
        <div class='row'>
            <div class='col-sm-12'>
                <input class='form-control' type='text' size='25' name='dbHostForm' value='{{ $GLOBALS['dbHostForm'] }}'>
            </div>
            <div class='col-sm-12 help-block'>{{ trans('langEG') }} localhost</div>
        </div>
    </div>

    <div class='form-group mt-3'>
        <label for='dbUsernameForm' class='col-sm-12 control-label-notes'>{{ trans('langDBLogin') }} (*)</label>
        <div class='row'>
            <div class='col-sm-12'>
                <input class='form-control' type='text' size='25' name='dbUsernameForm' value='{{ $GLOBALS['dbUsernameForm'] }}'>
            </div>
            <div class='col-sm-12 help-block'>{{ trans('langEG') }} root</div>
        </div>
    </div>

    <div class='form-group mt-3'>
        <label for='dbPassForm' class='col-sm-12 control-label-notes'>{{ trans('langDBPassword') }} (*)</label>
        <div class='col-sm-12'>
            <input class='form-control' type='text' size='25' name='dbPassForm' value='{{ $GLOBALS['dbPassForm'] }}'>
        </div>
    </div>

    <div class='form-group mt-3'>
        <label for='dbNameForm' class='col-sm-12 control-label-notes'>{{ trans('langMainDB') }} (*)</label>
        <div class='row'>
            <div class='col-sm-12'>
                <input class='form-control' type='text' size='25' name='dbNameForm' value='{{ $GLOBALS['dbNameForm'] }}'>
            </div>
            <div class='col-sm-12 help-block'>{{ trans('langNeedChangeDB') }}</div>
        </div>
    </div>

    <div class='form-group mt-3'>
        <label for='dbMyAdmin' class='col-sm-12 control-label-notes'>
            {{ trans('langphpMyAdminURL') }}<span class="help-block p-2">{{ trans('langOptional') }}</span>
        </label>
        <div class='row'>
            <div class='col-sm-12'>
                <input class='form-control' type='text' size='25' name='dbMyAdmin' value='{{ $GLOBALS['dbMyAdmin'] }}'>
            </div>
        </div>
    </div>

    <div class='form-group mt-3 help-block'>
        {{ trans('langRequiredFields') }}
    </div>

    <div class='form-group mt-4'>
        <div class='col-12'>
            <div class='row'>
                <div class='col-lg-6 col-12'>
                    <input type='submit' class='btn cancelAdminBtn w-100' name='install2' value='&laquo; {{ trans('langPreviousStep') }}'>
                </div>
                <div class='col-lg-6 col-12 mt-lg-0 mt-3'>
                    <input type='submit' class='btn w-100' name='install4' value='{{ trans('langNextStep') }} &raquo;'>
                </div>
            </div>
        </div>

    </div>
    {!! hidden_vars($all_vars, [ 'dbHostForm', 'dbUsernameForm', 'dbPassForm', 'dbNameForm', 'dbMyAdmin' ]) !!}
</form>

