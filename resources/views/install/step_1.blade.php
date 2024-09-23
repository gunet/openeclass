@if ($configErrorExists)
    <div class='alert alert-danger'>
        <i class='fa-solid fa-circle-xmark fa-lg'></i>
        <span>
            {!! $errorContent !!}
        </span>
    </div>
    <div class='alert alert-warning'>
        <i class='fa-solid fa-triangle-exclamation fa-lg'></i>
        <span>{{ trans('langWarnInstallNotice1') }}
            <a href='{{ $install_info_file }}'>{{ trans('langHere') }}</a> {{ trans('langWarnInstallNotice2') }}
        </span>
    </div>
@else
    <div class='card panelCard card-default px-lg-4 py-lg-3'>
        <div class='card-body'>
            <form class='form-wrapper' action='{{ $_SERVER['SCRIPT_NAME'] }}' method='post'>
                <fieldset>
                    <legend class='mb-0' aria-label="{{ trans('langForm') }}"></legend>
                    <div class="text-heading-h4 mt-2">
                        {{ trans('langCheckReq') }}
                    </div>

                    <ul class='list-group list-group-flush'>
                        <li class='list-group-item element'><i class="fa-solid fa-check Success-200-cl"></i> <strong>Webserver</strong>
                            <em> {{ ($_SERVER['SERVER_SOFTWARE']) }} </em>
                        </li>
                    </ul>

                    <ul class='list-group list-group-flush'>
                        <div class="text-heading-h4 mt-2">
                            {{ trans('langPHPVersion') }}
                        </div>
                        {!! checkPHPVersion('8.0'); !!}
                    </ul>

                    <div class="text-heading-h4 mt-2">
                        {{ trans('langRequiredPHP') }}
                    </div>
                    <ul class='list-group list-group-flush'>
                        {!! warnIfExtNotLoaded('pdo_mysql') !!}
                        {!! warnIfExtNotLoaded('gd') !!}
                        {!! warnIfExtNotLoaded('mbstring') !!}
                        {!! warnIfExtNotLoaded('xml'); !!}
                        {!! warnIfExtNotLoaded('zlib') !!}
                        {!! warnIfExtNotLoaded('pcre') !!}
                        {!! warnIfExtNotLoaded('curl') !!}
                        {!! warnIfExtNotLoaded('zip') !!}
                        {!! warnIfExtNotLoaded('intl') !!}
                    </ul>

                    <div class="text-heading-h4 mt-2">
                        {{ trans('langOptionalPHP') }}
                    </div>
                    <ul class='list-group list-group-flush'>
                        {!! warnIfExtNotLoaded('soap'); !!}
                        {!! warnIfExtNotLoaded('ldap'); !!}
                    </ul>

                    @if (ini_get('register_globals'))
                        <div class='caution'>
                            {{ trans('langWarningInstall1') }}
                        </div>
                    @endif

                    @if (ini_get('short_open_tag'))
                        <div class='caution'>
                            {{ trans('langWarningInstall2') }}
                        </div>
                    @endif

                    <div class="text-heading-h3 mt-2">
                        {{ trans('langOtherReq') }}
                    </div>
                    <ul>
                        <li>
                            {{ trans('langInstallBullet1') }}
                        </li>
                        <li>
                            {{ trans('langInstallBullet3') }}
                        </li>
                    </ul>

                    <div class='info'>{{ trans('langBeforeInstall1') }}<a href='{{ $install_info_file }}' target=_blank>{{ trans('langInstallInstr') }}</a>.
                    <div class='smaller'>{{ trans('langBeforeInstall2') }}<a href='{{ $readme_file }}' target=_blank>{{ trans('langHere') }}</a>.</div></div><br>

                    <div class='col-12 d-flex justify-content-center mt-5'>
                        <input aria-label="{{ trans('langNextStep') }}" type='submit' class='btn w-100' name='install2' value='{{ trans('langNextStep') }} &raquo;' />
                    </div>
                    {!! hidden_vars($all_vars) !!}
                </fieldset>
            </form>
        </div>
    </div>
@endif
