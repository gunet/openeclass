
        <div class='form-wrapper form-edit p-3 mt-2 rounded'>
            <form class='form-horizontal' role='form' method='post' action='{{ $base_url }}'>
                <input type='hidden' name='movePath' value='{{ $file }}'>
                <fieldset>
                    {!! $group_hidden_input !!}
                    <div class='form-group'>
                        <label for='moveTo' class='col-sm-12 control-label-notes'>{{ trans('langMove') }} {{ trans('langTo') }}:</label>
                        <div class='col-lg-6 col-md-12 col-sm-12 col-12'>
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

                    <div class='form-group mt-3'>
                        @if($menuTypeID == 3 or $menuTypeID == 1)
                        <div class='col-12 d-inline-flex'>
                          
                               
                                    <button class='btn btn-primary submitAdminBtn' type='submit'>{{ trans('langMove') }}</button>
                               
                               
                                    <a class='btn btn-outline-secondary cancelAdminBtn ms-2' href='{{ $backUrl }}'>{{ trans('langCancel') }}</a>
                             
                           
                        </div>
                        @else
                        <div class='col-offset-2 col-10'>
                            <button class='btn btn-primary btn-sm' type='submit'>{{ trans('langMove') }}</button>
                            <a class='btn btn-outline-secondary btn-sm' href='{{ $backUrl }}'>{{ trans('langCancel') }}</a>
                        </div>
                        @endif
                    </div>
                </fieldset>
                {!! generate_csrf_token_form_field() !!}
            </form>
        </div>

