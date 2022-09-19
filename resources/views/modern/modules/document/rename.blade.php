
    <div class='col-12'>
        <div class='form-wrapper shadow-sm p-3 mt-2 rounded'>
			<form class='form-horizontal' role='form' method='post' action='{{ $base_url }}'>
				<fieldset>
					<input type='hidden' name='sourceFile' value='{{ $renamePath }}'>
					{!! $group_hidden_input !!}
					<div class='form-group'>
						<label for='renameTo' class='col-6 control-label-notes' >{{ $filenameLabel }}:</label>
						<div class='col-12'>
							<input class='form-control' type='text' name='renameTo' value='{{ $filename }}'>
						</div>
					</div>
					<div class="row p-2"></div>
					<div class='form-group'>
						<div class='col-offset-2 col-10'>
                            <button class='btn btn-primary' type='submit'>{{ trans('langRename') }}</button>
                            <a class='btn btn-secondary' href='{{ $backUrl }}'>{{ trans('langCancel') }}</a>
						</div>
					</div>
				</fieldset>
                {!! generate_csrf_token_form_field() !!}
			</form>
        </div>
    </div>

