
<div class='@if(isset($module_id) and $module_id) d-lg-flex gap-4 @else row m-auto @endif mt-4 dialog_box'>
    <div class='@if(isset($module_id) and $module_id) flex-grow-1 @else col-lg-6 col-12 px-0 @endif'>
        <div class='form-wrapper form-edit mt-2 border-0 px-0'>
			<form class='form-horizontal' role='form' method='post' action='{{ $base_url }}'>
				<fieldset>
					<legend class='mb-0' aria-label="{{ trans('langForm') }}"></legend>
					<input type='hidden' name='sourceFile' value='{{ $renamePath }}'>
					{!! $group_hidden_input !!}
					<div class='form-group'>
						<label for='renameTo' class='col-12 control-label-notes' >{{ $filenameLabel }}:</label>
						<div class='col-12'>
							<input id='renameTo' class='form-control' type='text' name='renameTo' value='{{ $filename }}'>
						</div>
					</div>

					<div class='form-group mt-4'>

						<div class='col-12 d-flex justify-content-end align-items-center gap-2 flex-wrap'>
                            <button class='btn submitAdminBtn' type='submit'>{{ trans('langRename') }}</button>
                            <a class='btn cancelAdminBtn' href='{{ $backUrl }}'>{{ trans('langCancel') }}</a>
						</div>

					</div>
				</fieldset>
                {!! generate_csrf_token_form_field() !!}
			</form>
        </div>
    </div>
	<div class='@if(isset($module_id) and $module_id) form-content-modules @else col-lg-6 col-12 @endif d-none d-lg-block'>
		<img class='form-image-modules' src='{!! get_form_image() !!}' alt="{{ trans('langImgFormsDes') }}">
	</div>
</div>

