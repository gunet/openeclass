
    <div class='col-12'>
        <div class='form-wrapper form-edit p-3 mt-2 rounded'>
			<form class='form-horizontal' role='form' method='post' action='{{ $base_url }}' enctype='multipart/form-data'>
				<fieldset>
					<input type='hidden' name='replacePath' value='{{ $replacePath }}'>
                    {!! fileSizeHidenInput() !!}
					{!! $group_hidden_input !!}
                    <div class='form-group'>
                        <label class='col-sm-12 control-label-notes' for='newFile'>{!! $replaceMessage !!}</label>
                        <div class='col-sm-7'><input type='file' name='newFile' size='35'></div>
                    </div>

					<div class='form-group mt-3'>
                        @if($menuTypeID == 3 or $menuTypeID == 1)
                        <div class='col-12'>
                            <div class='row'>
                                <div class='col-6'>
                                    <button class='btn btn-primary btn-sm submitAdminBtn w-100' type='submit'>{{ trans('langReplace') }}</button>
                                </div>
                                <div class='col-6'>
                                    <a class='btn btn-secondary btn-sm cancelAdminBtn w-100' href='{{ $backUrl }}'>{{ trans('langCancel') }}</a>
                                </div>
                            </div>
                        </div>
                        @else
						<div class='col-offset-2 col-10'>
                            <button class='btn btn-primary btn-sm' type='submit'>{{ trans('langReplace') }}</button>
                            <a class='btn btn-secondary btn-sm' href='{{ $backUrl }}'>{{ trans('langCancel') }}</a>
						</div>
                        @endif
					</div>
				</fieldset>
                {!! generate_csrf_token_form_field() !!}
			</form>
        </div>
    </div>

