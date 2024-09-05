<div class='col-12'>
    <div class='form-wrapper form-edit p-3 rounded'>
        <form class='form-horizontal form-wrapper form-edit p-3 rounded' role='form' action='{{ $_SERVER['SCRIPT_NAME'] }}' method='post'>
            <div class='card panelCard px-lg-4 py-lg-3'>
                <div class='card-header border-0 d-flex justify-content-between align-items-center'>
                    <h3>{{ trans('langThemeSettings') }}</h3>
                </div>
                <div class='card-body'>
                    <legend class='mb-0' aria-label='{{ trans('langForm') }}'></legend>
                    <div class='form-group'>

                        <div class='form-group mt-3'>
                            <label for='homepage_intro' class='col-sm-12 control-label-notes'>{{ trans('langHomePageIntroTextHelp') }}</label>
                            <div class='col-sm-12'>
                                <textarea class='form-control' rows='3' cols='40' name='homepage_intro'>{{ $GLOBALS['homepage_intro'] }}</textarea>
                            </div>
                        </div>

                        <div class='form-group mt-4'>
                            <div class='col-sm-12'>
                                <a class='link-color TextBold' type='button' href='#view_themes_screens' data-bs-toggle='modal'>{{ trans('langViewScreensThemes') }}</a></br></br>
                                <label for='themeSelection' class='control-label-notes'>{{ trans('langAvailableThemes') }}:</label>
                                {!! $theme_selection !!}
                            </div>
                        </div>

                        <div class='form-group mt-4'>
                            <div class='col-12 d-flex justify-content-between'>
                                <input class='btn btn-primary' name='install4' value='&laquo; {{ trans('langBack') }}' type='submit'>
                                <input class='btn btn-primary' name='install6' value='{{ trans('langContinue') }} &raquo;' type='submit'>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            {!! hidden_vars($all_vars, [ 'homepage_intro', 'theme_selection' ]) !!}
        </form>
    </div>
</div>

<div class='modal fade' id='view_themes_screens' tabindex='-1' aria-labelledby='view_themes_screensLabel' aria-hidden='true'>
    <div class='modal-dialog modal-fullscreen' style='margin-top:0px;'>
        <div class='modal-content'>
            <div class='modal-header'>
                <div class='modal-title' id='view_themes_screensLabel'>{{ trans('langAvailableThemes') }}</div>
                <button type='button' class='close' data-bs-dismiss='modal' aria-label='Close'></button>
            </div>
            <div class='modal-body'>
                <div class='row row-cols-1 g-4'>
                    {!! $theme_images !!}
                </div>
            </div>
        </div>
    </div>
</div>

