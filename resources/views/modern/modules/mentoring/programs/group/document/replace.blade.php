
    <div class='col-12'>
        <div class='form-wrapper form-edit p-3 rounded-2 solidPanel'>
			<form class='form-horizontal' role='form' method='post' action='{{ $base_url }}' enctype='multipart/form-data'>
				<fieldset>
					<input type='hidden' name='replacePath' value='{{ $replacePath }}'>
                    {!! fileSizeHidenInput() !!}
					{!! $group_hidden_input !!}
                    <div class='form-group'>
                        <label class='col-sm-12 control-label-notes'>{!! $replaceMessage !!}</label>
                        <div class='col-sm-7'><input type='file' name='newFile' size='35'></div>
                    </div>

					<div class='form-group mt-4'>
                        @if($menuTypeID == 3 or $menuTypeID == 1)
                        <div class='col-12 d-flex justify-content-start align-items-center'>
                           
                               
                                    <button class='btn submitAdminBtn' type='submit'>{{ trans('langReplace') }}</button>
                            
                              
                                    <a class='btn cancelAdminBtn ms-1' href='{{ $backUrl }}'>{{ trans('langCancel') }}</a>
                            
                           
                        </div>
                        @else
						<div class='col-offset-2 col-10 d-flex justify-content-start align-items-center'>
                            <button class='btn submitAdminBtn' type='submit'>{{ trans('langReplace') }}</button>
                            <a class='btn cancelAdminBtn ms-1' href='{{ $backUrl }}'>{{ trans('langCancel') }}</a>
						</div>
                        @endif
					</div>
				</fieldset>
                {!! generate_csrf_token_form_field() !!}
			</form>
        </div>
    </div>

