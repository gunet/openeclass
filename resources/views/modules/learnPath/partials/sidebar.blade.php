<div class="contextual-menu contextual-menu-learningPath h-100 border-0">
    <ul class="list-group list-group-flush">
        <li class="border-0 list-group-item d-flex justify-content-center align-items-center gap-2 px-1 pe-none category" style="padding-top: 20px; padding-bottom: 20px;">{{ $lp_name }}</li>

        @foreach ($modules as $module)
            @if ($module['visible'] == 0 || $module['is_blocked'])
                @if (!$is_editor)
                    @continue
                @endif
                @php $style = " style='color:grey;'"; @endphp
            @else
                @php $style = ''; @endphp
            @endif

            @if ($module['contentType'] == CTLABEL_)
                <li class="d-flex justify-content-start gap-2" style="margin-left: {{ $module['indent'] }}px;">{{ $module['name'] }}</li>
            @else
                @php
                    $moduleImg = lp_module_icon($module['contentType'], $module['path']);

                    $imagePassed = '';
                    if ($module['credit'] == 'CREDIT' || $module['lesson_status'] == 'COMPLETED' || $module['lesson_status'] == 'PASSED') {
                        $imagePassed = '<i class="fas fa-check text-success"></i>';
                    }

                    if (($module['contentType'] == CTSCORM_ || $module['contentType'] == CTSCORMASSET_) && $module['lesson_status'] == 'FAILED') {
                        $moduleImg = 'fa-solid fa-file-code';
                        $imagePassed = '<i class="fa-solid fa-xmark text-danger"></i>';
                    }
                @endphp

                <li style="margin-left: {{ $module['indent'] }}px;">
                    @if ($currentModuleId == $module['module_id'])
                        <div class="list-group-item d-flex justify-content-start align-items-start gap-2 py-3 active">
                            <i class="{{ $moduleImg }}"></i>
                            {{ $module['name'] }}{!! $imagePassed !!}
                        </div>
                    @else
                        <a class="list-group-item d-flex justify-content-start align-items-start gap-2 py-3 lp-nav-link" href="{{ $urlAppend }}modules/learnPath/viewer_noframes.php?course={{ $course_code }}&amp;path_id={{ $module['learnPath_id'] }}&amp;module_id={{ $module['module_id'] }}{!! $unitParam !!}"{!! $style !!}>
                            <i class="{{ $moduleImg }}"></i>
                            {{ $module['name'] }}{!! $imagePassed !!}
                        </a>
                    @endif
                </li>
            @endif
        @endforeach
    </ul>
</div>
