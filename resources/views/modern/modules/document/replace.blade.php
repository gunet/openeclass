<div class='row'>
    <div class='col-md-12'>
        <div class='form-wrapper'>
			<form class='form-horizontal' role='form' method='post' action='/modules/document/index.php' enctype='multipart/form-data'>
				<fieldset>
					<input type='hidden' name='replacePath' value='{{ $replacePath }}'>
                    <input type='hidden' name='courseCodeAfterReplace' value='{{ $course_code }}'>
                    <input type='hidden' name='curDirPathAfterReplace' value='{{ $curDirPath }}'>
                    {!! fileSizeHidenInput() !!}
					{!! $group_hidden_input !!}
                    <div class='form-group'>
                        <label class='col-sm-5 control-label' for='newFile'>{!! $replaceMessage !!}</label>
                        <div class='col-sm-7'><input type='file' name='newFile' size='35'></div>
                    </div>
                    <div class="row p-2"></div>
					<div class='form-group'>
						<div class='col-xs-offset-2 col-xs-10'>
                            <button class='btn btn-primary' type='submit'>{{ trans('langReplace') }}</button>
                            <a class='btn btn-secondary' href='{{ $backUrl }}'>{{ trans('langCancel') }}</a>
						</div>
					</div>
				</fieldset>
                {!! generate_csrf_token_form_field() !!}
			</form>
        </div>
    </div>
</div>
