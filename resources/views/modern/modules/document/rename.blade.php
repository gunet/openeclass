
    <div class='col-12'>
        <div class='form-wrapper form-edit p-3 mt-2 rounded'>
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
					    @if($menuTypeID == 3 or $menuTypeID == 1)
                        <div class='col-12 d-inline-flex'>
                            
                               
                                    <button class='btn btn-primary submitAdminBtn' type='submit'>{{ trans('langRename') }}</button>
                               
                                
                                    <a class='btn btn-outline-secondary cancelAdminBtn ms-2' href='{{ $backUrl }}'>{{ trans('langCancel') }}</a>
                              
                           
                        </div>
                        @else
						<div class='col-offset-2 col-10'>
                            <button class='btn btn-primary btn-sm' type='submit'>{{ trans('langRename') }}</button>
                            <a class='btn btn-outline-secondary btn-sm' href='{{ $backUrl }}'>{{ trans('langCancel') }}</a>
						</div>
						@endif
					</div>
				</fieldset>
                {!! generate_csrf_token_form_field() !!}
			</form>
        </div>
    </div>

