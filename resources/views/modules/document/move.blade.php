<div class='row'>
    <div class='col-md-12'>
        <div class='form-wrapper'>
            <form class='form-horizontal' role='form' method='post' action='{{ $base_url }}'>
                <input type='hidden' name='movePath' value='{{ getIndirectReference($movePath) }}'>
                <fieldset>
                    {!! $group_hidden_input !!}
                    <div class='form-group'>
                        <label for='moveTo' class='col-sm-2 control-label'>{{ trans('langMove') }} {{ trans('langTo') }}:</label>
                        <div class='col-sm-10'>
                            <select name='moveTo' class='form-control'>
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

                    <div class='form-group'>
                        <div class='col-xs-offset-2 col-xs-10'>
                            <button class='btn btn-primary' type='submit'>{{ trans('langMove') }}</button>
                            <a class='btn btn-default' href='{{ $backUrl }}'>{{ trans('langCancel') }}</a>
                        </div>
                    </div>
                </fieldset>
                {!! generate_csrf_token_form_field() !!}
            </form>
        </div>
    </div>
</div>
