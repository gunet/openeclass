
<div class='d-lg-flex gap-4 mt-4'>
    <div class='flex-grow-1'>
        <div class='form-wrapper form-edit mt-2 rounded'>
			<form class='form-horizontal' role='form' method='post' action='{{ $base_url }}' enctype='multipart/form-data'>
				<fieldset>
					<input type='hidden' name='replacePath' value='{{ $replacePath }}'>
                    {!! fileSizeHidenInput() !!}
					{!! $group_hidden_input !!}
                    <div class='form-group'>
                        <label class='col-sm-12 control-label-notes' for='newFile'>{!! $replaceMessage !!}</label>
                        <div class='col-sm-7'><input type='file' name='newFile' size='35'></div>
                    </div>

					<div class='form-group mt-4'>
                        @if($menuTypeID == 3 or $menuTypeID == 1)
                        <div class='col-12 d-flex justify-content-end align-items-center gap-2'>
                           
                               
                                    <button class='btn submitAdminBtn' type='submit'>{{ trans('langReplace') }}</button>
                            
                              
                                    <a class='btn cancelAdminBtn' href='{{ $backUrl }}'>{{ trans('langCancel') }}</a>
                            
                           
                        </div>
                        @else
						<div class='col-12 d-flex justify-content-end align-items-center gap-2'>
                            <button class='btn submitAdminBtn' type='submit'>{{ trans('langReplace') }}</button>
                            <a class='btn cancelAdminBtn' href='{{ $backUrl }}'>{{ trans('langCancel') }}</a>
						</div>
                        @endif
					</div>
				</fieldset>
                {!! generate_csrf_token_form_field() !!}
			</form>
        </div>
    </div>
    <div class='d-none d-lg-block'>
		<img class='form-image-modules' src='{{ $urlAppend }}template/modern/img/form-image.png' alt='form-image'>
	</div>
</div>

