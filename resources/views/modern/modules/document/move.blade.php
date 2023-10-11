<div class='d-lg-flex gap-4 mt-4'>
    <div class='flex-grow-1'>
        <div class='form-wrapper form-edit mt-2 border-0 px-0'>
            <form class='form-horizontal' role='form' method='post' action='{{ $base_url }}'>
                <input type='hidden' name='movePath' value='{{ $file }}'>
                <fieldset>
                    {!! $group_hidden_input !!}
                    <div class='form-group'>
                        <label for='moveTo' class='col-sm-12 control-label-notes'>{{ trans('langMove') }} {{ trans('langTo') }}:</label>
                        <div class='col-12'>
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

                    <div class='col-12 d-flex justify-content-end align-items-center gap-2 mt-4'>
                        <button class='btn submitAdminBtn' type='submit'>{{ trans('langMove') }}</button>
                        <a class='btn cancelAdminBtn' href='{{ $backUrl }}'>{{ trans('langCancel') }}</a>
                    </div>

                    </div>
                </fieldset>
                {!! generate_csrf_token_form_field() !!}
            </form>
        </div>
        <div class='form-content-modules d-none d-lg-block'>
            <img class='form-image-modules' src='{{ $urlAppend }}template/modern/img/form-image.png' alt='form-image'>
        </div>
</div>

