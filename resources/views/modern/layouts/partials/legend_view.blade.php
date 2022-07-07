<div class='d-none d-md-none d-lg-block mt-4'>
    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12'>
        <div class='shadow-lg p-3 bg-body rounded bg-primary LegendShadow'>
            @if($course_code)
                @if($is_editor)
                    <div class='float-start ps-1 pt-1'>
                        <span class="control-label-notes">
                            @if($toolName)<i class="fas fa-tools text-warning" aria-hidden="true"></i>
                                {{$toolName}}
                            @endif
                            <strong class='ps-1'>
                                <span class='fas fa-folder-open text-warning'></span><span class='ps-1'>{{$currentCourseName}}</span>
                                <span class="fas fa-code text-warning"></span> <span>({{$course_code}})</span>
                            </strong>
                        </span>
                    </div>
                    <div class='float-end pe-1'>
                        @include('layouts.partials.manageCourse',[$urlAppend => $urlAppend,'coursePrivateCode' => $course_code])
                    </div>
                @else
                    <div class='float-start ps-1 pt-1'>
                        <span class="control-label-notes">
                            @if($toolName)<i class="fas fa-tools text-warning" aria-hidden="true"></i>
                                {{$toolName}}
                            @endif
                            <strong class='ps-1'>
                                <span class='fas fa-folder-open text-warning'></span><span class='ps-1'>{{$currentCourseName}}</span>
                                <span class="fas fa-code text-warning"></span> <span>({{$course_code}})</span>
                            </strong>
                        </span>
                    </div>
                @endif
            @else
                <div class='d-flex justify-content-center ps-1 pt-1 pb-2'>
                    <span class="control-label-notes">
                        <i class="fas fa-tools text-warning" aria-hidden="true"></i>
                        {{$toolName}}
                    </span>
                </div>
            @endif
        </div>
    </div>
</div>

<div class='d-block d-md-block d-lg-none mt-4'>
    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12'>
        <div class='shadow-lg p-3 bg-body rounded bg-primary LegendShadow'>
            @if($course_code)
                <div class='float-start ps-1 pt-1 pb-3'>
                    <span class="control-label-notes">
                        @if($toolName)<i class="fas fa-tools text-warning" aria-hidden="true"></i>
                        {{$toolName}} <br>@endif
                        @if($course_code)
                            <strong class='ps-1'>
                                <span class='fas fa-folder-open text-warning'></span><span class='ps-1'>{{$currentCourseName}}</span> <br>
                                <span class="fas fa-code text-warning"></span> <span>({{$course_code}})</span>
                            </strong>
                        @endif
                    </span>
                </div>
                @if($is_editor)
                    <div class='float-end pe-1'>
                        @include('layouts.partials.manageCourse',[$urlAppend => $urlAppend,'coursePrivateCode' => $course_code])
                    </div>
                @endif
            @else
            @endif
        </div>
    </div>
</div>
