<select name='moveTo' class='form-select' id='moveTo'>
    @if ($curDirPath and $curDirPath != '/')
        <option value=''>{{ trans('langParentDir') }}</option>
    @endif
    @foreach ($directories as $dir)
        <option value='{{ getIndirectReference($dir->path) }}'>{!!
            str_repeat('&nbsp;&nbsp;&nbsp;', $dir->depth) !!}{{ $dir->filename }}</option>
    @endforeach
</select>
