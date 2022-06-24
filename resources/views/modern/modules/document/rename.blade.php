<div class='row'>
    <div class='col-md-12'>
        <div class='form-wrapper'>
			<form class='form-horizontal' role='form' method='post' action='/modules/document/index.php'>
				<fieldset>
					<input type='hidden' name='sourceFile' value='{{ $renamePath }}'>
					<input type='hidden' name='courseCodeAfterRename' value={{$course_code}}>
					{!! $group_hidden_input !!}
					<div class='form-group'>
						<label for='renameTo' class='col-xs-2 control-label' >{{ $filenameLabel }}:</label>
						<div class='col-xs-10'>
							<input class='form-control' type='text' name='renameTo' value='{{ $filename }}'>
						</div>
					</div>
					<div class="row p-2"></div>
					<div class='form-group'>
						<div class='col-xs-offset-2 col-xs-10'>
                            <button class='btn btn-primary' type='submit'>{{ trans('langRename') }}</button>
                            <a class='btn btn-secondary' href='{{ $backUrl }}'>{{ trans('langCancel') }}</a>
						</div>
					</div>
				</fieldset>
                {!! generate_csrf_token_form_field() !!}
			</form>
        </div>
    </div>
</div>
