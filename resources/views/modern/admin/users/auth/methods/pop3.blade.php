                
             
                
                <div class='form-group'>
                    <label for='pop3host' class='col-sm-12 control-label-notes'>{{ trans('langpop3host') }}</label>
                    <div class='col-sm-12'>
                        <input class='form-control' placeholder="{{ trans('langpop3host') }}..." name='pop3host' id='pop3host' type='text' value='{{ isset($auth_data['pop3host']) ? $auth_data['pop3host'] : '' }}'>
                    </div>
                </div>


               


                <div class='form-group mt-3'>
                    <label for='pop3port' class='col-sm-12 control-label-notes'>{{ trans('langpop3port') }}</label>
                    <div class='col-sm-12'>
                        <input type='text' placeholder="{{ trans('langpop3port') }}..." class='form-control' value='110' name='pop3port' id='pop3port' disabled>
                    </div>
                </div>