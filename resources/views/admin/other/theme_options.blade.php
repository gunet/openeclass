@extends('layouts.default')

@section('content')
    {!! isset($action_bar) ?  $action_bar : '' !!}
    @if (isset($preview_theme))
        <div class='alert alert-warning'>
            <div class='row'>
                <div class='col-sm-9'>
                    {{ trans('langPreviewState') }} &nbsp; {{ $themes_arr[getIndirectReference($preview_theme)] }}
                </div>
                <div class='col-sm-3'>
                    @if(!empty(showSecondFactorChallenge()))
                        <a href='#' class='theme_enable btn btn-success btn-xs' onclick="var totp=prompt('Type 2FA:',''); document.getElementById('theme_selection').elements['sfaanswer'].value=escape(totp);">{{ trans('langActivate') }}</a> 
                    @else                   
                        <a href='#' class='theme_enable btn btn-success btn-xs'>{{ trans('langActivate') }}</a>
                    @endif
                    &nbsp; 
                    <a href='theme_options.php?reset_theme_options=true' class='btn btn-default btn-xs'>{{ trans('langLeave') }}</a>
                </div>
            </div>
        </div>    
    @endif     
    <div class='form-wrapper'>
        <div class='row margin-bottom-fat'>
            <div class='col-sm-3 text-right'>
                <strong>{{ trans('langActiveTheme') }}:</strong>
            </div>
            <div class='col-sm-9'>
                {{ $themes_arr[getIndirectReference($active_theme)] }}
            </div>
        </div>
        <form class='form-horizontal' role='form' action='{{ $_SERVER['SCRIPT_NAME'] }}' method='post' id='theme_selection'>
            <div class='form-group'>
                <label for='bgColor' class='col-sm-3 control-label'>{{ trans('langAvailableThemes') }}:</label>
                <div class='col-sm-9'> 
                    {!! selection($themes_arr, 'active_theme_options', getIndirectReference($theme_id), 'class="form-control form-submit" id="theme_selection"') !!}
                    {!! showSecondFactorChallenge() !!}
                </div>
            </div>
            {!! generate_csrf_token_form_field() !!}
        </form>
        <div class='form-group margin-bottom-fat'>
            <div class='col-sm-9 col-sm-offset-3'>
                <a href='#' class='theme_enable btn btn-success btn-xs{{ isset($preview_theme) ? '' : ' hidden' }}' id='theme_enable'>{{ trans('langActivate') }}</a>
                <a href='#' class='btn btn-primary btn-xs hidden' id='theme_preview'>{{ trans('langSee') }}</a>  
                <form class='form-inline' style='display:inline;' method='post' action='{{ $_SERVER['SCRIPT_NAME'] }}?delThemeId={{ getIndirectReference($theme_id) }}'>
                    <a class='confirmAction btn btn-danger btn-xs{{ $theme_id != 0 ? "" : " hidden" }}' id='theme_delete' data-title='{{ trans('langConfirmDelete') }}' data-message='{{ trans('langThemeSettingsDelete') }}' data-cancel-txt='{{ trans('langCancel') }}' data-action-txt='{{ trans('langDelete') }}' data-action-class='btn-danger'>{{ trans('langDelete') }}</a>
                </form>
            </div>
        </div>
    </div>    
    <div role='tabpanel'>
      <!-- Nav tabs -->
      <ul class='nav nav-tabs' role='tablist'>
        <li role='presentation' class='active'>
            <a href='#generalsetting' aria-controls='generalsetting' role='tab' data-toggle='tab'>{{ trans('langGeneralSettings') }}</a>
        </li>
        <li role='presentation'>
            <a href='#navsettings' aria-controls='navsettings' role='tab' data-toggle='tab'>{{ trans('langNavSettings') }}</a>
        </li>
      </ul>

      <!-- Tab panes -->
      <form id='theme_options_form' class='form-horizontal' role='form' action='{{ $_SERVER['SCRIPT_NAME'] }}' enctype='multipart/form-data' method='post'>
      <div class='tab-content'>
        <div role='tabpanel' class='tab-pane in active fade' id='generalsetting'>
            <div class='form-wrapper'>
                <legend class='theme_options_legend'>{{ trans('langLayoutConfig') }}</legend>
                <div class='form-group'>
                    <label class='col-sm-3 control-label'>{{ trans('langLayout') }}:</label>
                    <div class='form-inline col-sm-9'>
                          <div class='radio'>
                            <label>
                              <input type='radio' name='containerType' value='boxed'{{ $theme_options_styles['containerType'] == 'boxed' ? ' checked' : '' }}>
                              {{ trans('langBoxed') }} &nbsp; 
                            </label>
                          </div>
                          <div class='radio'>
                            <label>
                              <input type='radio' name='containerType' value='fluid'{{ $theme_options_styles['containerType'] == 'fluid' ? ' checked' : '' }}>
                              {{ trans('langFluid') }} &nbsp;
                            </label>
                          </div>                                
                    </div>                
                </div>        
                <div class='form-group{{ $theme_options_styles['containerType'] == 'boxed' ? ' hidden' : '' }}'>
                    <label for='fluidContainerWidth' class='col-sm-3 control-label'>{{ trans('langFluidContainerWidth') }}:</label>
                    <div class='col-sm-9'>
                        <input id='fluidContainerWidth' name='fluidContainerWidth' data-slider-id='ex1Slider' type='text' data-slider-min='1340' data-slider-max='1920' data-slider-step='10' data-slider-value='{{ $theme_options_styles['fluidContainerWidth'] }}'{{ $theme_options_styles['containerType'] == 'boxed' ? ' disabled' : '' }}>
                        <span style='margin-left:10px;' id='pixelCounter'></span>
                    </div>
                </div>
                <legend class='theme_options_legend'>{{ trans('langLogoConfig') }}</legend>
                <div class='form-group'>
                    <label for='imageUpload' class='col-sm-3 control-label'>{{ trans('langLogo') }} <small>{{ trans('langLogoNormal') }}</small>:</label>
                    <div class='col-sm-9'>
                        @if (isset($theme_options_styles['imageUpload']))
                            <img src='{{ $urlThemeData . '/' . $theme_options_styles['imageUpload'] }}' style='max-height:100px;max-width:150px;'> 
                            &nbsp;&nbsp;
                            <a class='btn btn-xs btn-danger' href='{{ $_SERVER['SCRIPT_NAME'] }}?delete_image={{ getIndirectReference('imageUpload') }}'>{{ trans('langDelete') }}</a>
                            <input type='hidden' name='imageUpload' value='{{ $theme_options_styles['imageUpload'] }}'>
                        @else
                           <input type='file' name='imageUpload' id='imageUpload'>
                        @endif
                    </div>
                </div>
                <div class='form-group'>
                    <label for='imageUploadSmall' class='col-sm-3 control-label'>
                        {{ trans('langLogo') }} 
                        <small>{{ trans('langLogoSmall') }}</small>:
                    </label>
                    <div class='col-sm-9'>
                        @if (isset($theme_options_styles['imageUploadSmall']))
                            <img src='{{ $urlThemeData . '/'. $theme_options_styles['imageUploadSmall'] }}' style='max-height:100px;max-width:150px;'> 
                            &nbsp;&nbsp;
                            <a class='btn btn-xs btn-danger' href='{{ $_SERVER['SCRIPT_NAME'] }}?delete_image={{ getIndirectReference('imageUploadSmall') }}'>{{ trans('langDelete') }}</a>
                            <input type='hidden' name='imageUploadSmall' value='{{ $theme_options_styles['imageUploadSmall'] }}'>
                        @else
                           <input type='file' name='imageUploadSmall' id='imageUploadSmall'> 
                        @endif
                    </div>
                </div>
                <legend class='theme_options_legend'>{{ trans('langBgColorConfig') }}</legend>
                <div class='form-group'>
                  <label for='bgColor' class='col-sm-3 control-label'>{{ trans('langBgColor') }}:</label>
                  <div class='col-sm-9'>
                    <input name='bgColor' type='text' class='form-control colorpicker' id='bgColor' value='{{ $theme_options_styles['bgColor'] }}'>
                  </div>
                </div>
                <div class='form-group'>
                    <label for='imageBg' class='col-sm-3 control-label'>{{ trans('langBgImg') }}:</label>
                    <div class='col-sm-9'>
                        @if (isset($theme_options_styles['bgImage']))
                            <img src='{{ $urlThemeData . '/' . $theme_options_styles['bgImage'] }}' style='max-height:100px;max-width:150px;'> 
                            &nbsp;&nbsp;
                            <a class='btn btn-xs btn-danger' href='{{ $_SERVER['SCRIPT_NAME'] }}?delete_image={{ getIndirectReference('bgImage') }}'>{{ trans('langDelete') }}</a>
                            <input type='hidden' name='bgImage' value='{{ $theme_options_styles['bgImage'] }}'>
                        @else
                           <input type='file' name='bgImage' id='bgImage'> 
                        @endif
                    </div>
                    <div class='form-inline col-sm-9 col-sm-offset-3'>
                          <div class='radio'>
                            <label>
                              <input type='radio' name='bgType' value='repeat'{{ $theme_options_styles['bgType'] == 'repeat' ? ' checked' : '' }}>
                              {{ trans('langRepeatedImg') }} &nbsp; 
                            </label>
                          </div>
                          <div class='radio'>
                            <label>
                              <input type='radio' name='bgType' value='fix'{{ $theme_options_styles['bgType'] == 'fix' ? ' checked' : '' }}>
                              {{ trans('langFixedImg') }} &nbsp;
                            </label>
                          </div>                        
                          <div class='radio'>
                            <label>
                              <input type='radio' name='bgType' value='stretch'{{ $theme_options_styles['bgType'] == 'stretch' ? ' checked' : '' }}>
                              {{ trans('langStretchedImg') }} &nbsp;
                            </label>
                          </div>              
                    </div>                
                </div>
                <legend class='theme_options_legend'>{{ trans('langLinksCongiguration') }}</legend>
                <div class='form-group'>
                  <label for='linkColor' class='col-sm-3 control-label'>{{ trans('langLinkColor') }}:</label>
                  <div class='col-sm-9'>
                    <input name='linkColor' type='text' class='form-control colorpicker' id='linkColor' value='{{ $theme_options_styles['linkColor'] }}'>
                  </div>
                </div> 
                <div class='form-group'>
                  <label for='linkHoverColor' class='col-sm-3 control-label'>{{ trans('langLinkHoverColor') }}:</label>
                  <div class='col-sm-9'>
                    <input name='linkHoverColor' type='text' class='form-control colorpicker' id='linkHoverColor' value='{{ $theme_options_styles['linkHoverColor'] }}'>
                  </div>
                </div>
                <legend class='theme_options_legend'>{{ trans('langLoginConfiguration') }}</legend>
                <div class='form-group'>
                  <label for='loginJumbotronBgColor' class='col-sm-3 control-label'>{{ trans('langLoginBgGradient') }}:</label>
                  <div class='col-xs-4 col-sm-1'>
                    <input name='loginJumbotronBgColor' type='text' class='form-control colorpicker' id='loginJumbotronBgColor' value='{{ $theme_options_styles['loginJumbotronBgColor'] }}'>
                  </div>
                  <div class='col-xs-1 text-center' style='padding-top: 7px;'>
                    <i class='fa fa-arrow-right'></i>
                  </div>
                  <div class='col-xs-4 col-sm-1'>
                    <input name='loginJumbotronRadialBgColor' type='text' class='form-control colorpicker' id='loginJumbotronRadialBgColor' value='{{ $theme_options_styles['loginJumbotronRadialBgColor'] }}'>
                  </div>              
                </div>
                <div class='form-group'>
                    <label for='loginImg' class='col-sm-3 control-label'>{{ trans('langLoginImg') }}:</label>
                    <div class='col-sm-9'>
                        @if (isset($theme_options_styles['loginImg']))
                            <img src='{{ $urlThemeData . '/' . $theme_options_styles['loginImg'] }}' style='max-height:100px;max-width:150px;'> 
                            &nbsp;&nbsp;
                            <a class='btn btn-xs btn-danger' href='{{ $_SERVER['SCRIPT_NAME'] }}?delete_image={{ getIndirectReference('loginImg') }}'>{{ trans('langDelete') }}</a>
                            <input type='hidden' name='loginImg' value='{{ $theme_options_styles['loginImg'] }}'>
                        @else
                           <input type='file' name='loginImg' id='loginImg'>
                        @endif
                    </div>
                </div>
                <div class='form-group'>
                    <div class='form-inline col-sm-9 col-sm-offset-3'>
                          <div class='radio'>
                            <label>
                              <input type='radio' name='loginImgPlacement' value='small-right'{{ $theme_options_styles['loginImgPlacement'] == 'small-right' ? ' checked' : '' }}>
                              {{ trans('langLoginImgPlacementSmall') }} &nbsp; 
                            </label>
                          </div>
                          <div class='radio'>
                            <label>
                              <input type='radio' name='loginImgPlacement' value='full-width'{{ $theme_options_styles['loginImgPlacement'] == 'full-width' ? ' checked' : '' }}>
                              {{ trans('langLoginImgPlacementFull') }} &nbsp;
                            </label>
                          </div>                                    
                    </div> 
                </div>
                <div class='form-group'>
                    <label for='loginImg' class='col-sm-3 control-label'>{{ trans('langLoginBanner') }}:</label>
                    <div class='col-sm-9'>
                          <div class='checkbox'>
                            <label>
                              <input type='checkbox' name='openeclassBanner' value='1'{{ isset($theme_options_styles['openeclassBanner']) ? ' checked' : '' }}>
                              {{ trans('langDeactivate') }}
                            </label>
                          </div>                   
                    </div>
                </div>
            </div>
        </div>
        <div role='tabpanel' class='tab-pane fade' id='navsettings'>
            <div class='form-wrapper'>
                <legend class='theme_options_legend'>{{ trans('langBgColorConfig') }}</legend>
                <div class='form-group'>
                  <label for='leftNavBgColor' class='col-sm-3 control-label'>{{ trans('langBgColor') }}:</label>
                  <div class='col-sm-9'>
                    <input name='leftNavBgColor' type='text' class='form-control colorpicker' id='leftNavBgColor' value='{{ $theme_options_styles['leftNavBgColor'] }}'>
                  </div>
                </div>
                <legend class='theme_options_legend'>{{ trans('langMainMenuConfiguration') }}</legend>
                <div class='form-group'>
                  <label for='leftMenuBgColor' class='col-sm-3 control-label'>{{ trans('langMainMenuBgColor') }}:</label>
                  <div class='col-sm-9'>
                    <input name='leftMenuBgColor' type='text' class='form-control colorpicker' id='leftMenuBgColor' value='{{ $theme_options_styles['leftMenuBgColor'] }}'>
                  </div>
                </div>             
                <div class='form-group'>
                  <label for='leftMenuFontColor' class='col-sm-3 control-label'>{{ trans('langMainMenuLinkColor') }}:</label>
                  <div class='col-sm-9'>
                    <input name='leftMenuFontColor' type='text' class='form-control colorpicker' id='leftMenuFontColor' value='{{ $theme_options_styles['leftMenuFontColor'] }}'>
                  </div>
                </div>
                <div class='form-group'>
                  <label for='leftMenuHoverFontColor' class='col-sm-3 control-label'>{{ trans('langMainMenuLinkHoverColor') }}:</label>
                  <div class='col-sm-9'>
                    <input name='leftMenuHoverFontColor' type='text' class='form-control colorpicker' id='leftMenuHoverFontColor' value='{{ $theme_options_styles['leftMenuHoverFontColor'] }}'>
                  </div>
                </div>
                <div class='form-group'>
                  <label for='leftMenuSelectedFontColor' class='col-sm-3 control-label'>{{ trans('langMainMenuActiveLinkColor') }}:</label>
                  <div class='col-sm-9'>
                    <input name='leftMenuSelectedFontColor' type='text' class='form-control colorpicker' id='leftMenuSelectedFontColor' value='{{ $theme_options_styles['leftMenuSelectedFontColor'] }}'>
                  </div>
                </div>
                <legend class='theme_options_legend'>Ρυθμίσεις Επιλογών</legend>
                <div class='form-group'>
                  <label for='leftSubMenuFontColor' class='col-sm-3 control-label'>{{ trans('langSubMenuLinkColor') }}:</label>
                  <div class='col-sm-9'>
                    <input name='leftSubMenuFontColor' type='text' class='form-control colorpicker' id='leftSubMenuFontColor' value='{{ $theme_options_styles['leftSubMenuFontColor'] }}'>
                  </div>
                </div>
                <div class='form-group'>
                  <label for='leftSubMenuHoverFontColor' class='col-sm-3 control-label'>{{ trans('langSubMenuLinkHoverColor') }}:</label>
                  <div class='col-sm-9'>
                    <input name='leftSubMenuHoverFontColor' type='text' class='form-control colorpicker' id='leftSubMenuHoverFontColor' value='{{ $theme_options_styles['leftSubMenuHoverFontColor'] }}'>
                  </div>
                </div>                
                <div class='form-group'>
                  <label for='leftSubMenuHoverBgColor' class='col-sm-3 control-label'>{{ trans('langSubMenuLinkBgHoverColor') }}:</label>
                  <div class='col-sm-9'>
                    <input name='leftSubMenuHoverBgColor' type='text' class='form-control colorpicker' id='leftSubMenuHoverBgColor' value='{{ $theme_options_styles['leftSubMenuHoverBgColor'] }}'>
                  </div>
                </div>                                       
            </div>
        </div>
      </div>
        <div class='form-group'>
            <div class='col-sm-9 col-sm-offset-3'>
                {!! showSecondFactorChallenge() !!}
                @if ($theme_id)
                    <input class='btn btn-primary' name='optionsSave' type='submit' value='{{ trans('langSave') }}'>
                @endif
                <input class='btn btn-success' name='optionsSaveAs' id='optionsSaveAs' type='submit' value='{{ trans('langSaveAs') }}'>
                @if ($theme_id)
                    <a class='btn btn-info' href='theme_options.php?export=true'>{{ trans('langExport') }}</a>
                @endif
            </div>
        </div> 
        {!! generate_csrf_token_form_field() !!}    
    </form>
    </div>
@endsection