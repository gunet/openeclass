<div class='@if(isset($module_id) and $module_id) d-lg-flex gap-4 @else row m-auto @endif mt-4 dialog_box'>
    <div class='@if(isset($module_id) and $module_id) flex-grow-1 @else col-lg-6 col-12 px-0 @endif'>
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

                    <div class='col-12 d-flex justify-content-end align-items-center gap-2 flex-wrap mt-4'>
                        <button class='btn submitAdminBtn' type='submit'>{{ trans('langMove') }}</button>
                        <a class='btn cancelAdminBtn' href='{{ $backUrl }}'>{{ trans('langCancel') }}</a>
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
