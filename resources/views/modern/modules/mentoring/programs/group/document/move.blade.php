
        <div class='form-wrapper form-edit p-3 rounded-2 solidPanel'>
            <form class='form-horizontal' role='form' method='post' action='{{ $base_url }}'>
                <input type='hidden' name='movePath' value='{{ $file }}'>
                <fieldset>
                    {!! $group_hidden_input !!}
                    <div class='form-group'>
                        <label class='col-12 control-label-notes'>{{ trans('langMove') }} {{ trans('langTo') }}:</label>
                        <div class='col-md-6 col-12'>
                            <select name='moveTo' class='form-select'>
                                @if ($curDirPath and $curDirPath != '/')
                                    <option value=''>{{ trans('langParentDir') }}</option>
                                @endif
                                @foreach ($directories as $dir)
                                    <option{{ $dir->disabled? ' disabled': '' }} value='{{ getIndirectReference($dir->path) }}'>{!!
                                        str_repeat('&nbsp;&nbsp;&nbsp;', $dir->depth) !!}{{ $dir->filename }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class='form-group mt-4'>
                        
                        <div class='col-12 d-flex justify-content-start align-items-center'>
                          
                               
                                    <button class='btn submitAdminBtn' type='submit'>{{ trans('langMove') }}</button>
                               
                               
                                    <a class='btn cancelAdminBtn ms-1' href='{{ $backUrl }}'>{{ trans('langCancel') }}</a>
                             
                           
                        </div>
                       
                    </div>
                </fieldset>
                {!! generate_csrf_token_form_field() !!}
            </form>
        </div>

