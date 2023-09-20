
<div class='d-lg-flex gap-4 mt-4'>
    <div class='flex-grow-1'>
        <div class='form-wrapper form-edit mt-2 border-0 px-0'>
			<form class='form-horizontal' role='form' method='post' action='{{ $base_url }}'>
				<fieldset>
					<input type='hidden' name='sourceFile' value='{{ $renamePath }}'>
					{!! $group_hidden_input !!}
					<div class='form-group'>
						<label for='renameTo' class='col-12 control-label-notes' >{{ $filenameLabel }}:</label>
						<div class='col-12'>
							<input class='form-control' type='text' name='renameTo' value='{{ $filename }}'>
						</div>
					</div>

					<div class='form-group mt-4'>

						<div class='col-12 d-flex justify-content-end align-items-center gap-2'>
                            <button class='btn submitAdminBtn' type='submit'>{{ trans('langRename') }}</button>
                            <a class='btn cancelAdminBtn' href='{{ $backUrl }}'>{{ trans('langCancel') }}</a>
						</div>

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

