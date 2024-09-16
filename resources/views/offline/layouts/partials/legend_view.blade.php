
<div class='d-none d-md-none d-lg-block mt-0'>
    <div class='col-12 px-0 py-3 rounded'>

            @if($course_code)
                @if($is_editor)
                    <div class='row'>
                        <div class='col-12'>
                            @if($toolName)
                                <div class='row'>
                                    <span class='title-default'>
                                        {{$toolName}}
                                    </span>
                                </div>
                                <div class='row'>
                                    <span class='title-default'>
                                        <a class='fs-2' href='{{ $urlAppend }}index.html'>{!! $section_title !!}</a>
                                    </span>
                                </div>
                                <div class='row'>
                                    <span class='title-default'>
                                        <span>{{course_id_to_prof($course_id)}}</span>&nbsp; - &nbsp;
                                        <span>{{course_id_to_public_code($course_id)}}</span>
                                    </span>
                                </div>
                            @else
                                <div class='row'>
                                    <span class='title-default'>
                                        <a class='fs-2' href='{{ $urlAppend }}index.html'>{!! $section_title !!}</a>
                                    </span>
                                </div>
                                <div class='row'>
                                    <span class='title-default'>
                                        <span>{{course_id_to_prof($course_id)}}</span>&nbsp; - &nbsp;
                                        <span>{{course_id_to_public_code($course_id)}}</span>
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>
                @else
                    <div class='row'>
                        <div class='col-12'>
                            @if($toolName)
                                <div class='row'>
                                    <span class='title-default'>
                                        {{$toolName}}
                                    </span>
                                </div>
                                <div class='row'>
                                    <span class='title-default'>
                                        <a class='fs-2' href='{{ $urlAppend }}index.html'>{!! $section_title !!}</a>
                                    </span>
                                </div>
                                <div class='row'>
                                    <span class='title-default'>
                                        <span>{{course_id_to_prof($course_id)}}</span>&nbsp; - &nbsp;
                                        <span>{{course_id_to_public_code($course_id)}}</span>
                                    </span>
                                </div>
                            @else
                                <div class='row'>
                                    <span class='title-default'>
                                        <a class='fs-2' href='{{ $urlAppend }}index.html'>{!! $section_title !!}</a>
                                    </span>
                                </div>
                                <div class='row'>
                                    <span class='title-default'>
                                        <span>{{course_id_to_prof($course_id)}}</span>&nbsp; - &nbsp;
                                        <span>{{course_id_to_public_code($course_id)}}</span>
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            @endif

    </div></br>
</div>

<div class='d-block d-md-block d-lg-none mt-3'>
    <div class='col-12 p-3'>

            @if($course_code)
                @if($is_editor)
                    <div class='row'>
                        <div class='col-12 border border-top-0 border-start-0 border-end-0 border-bottom-secondary px-0'>

                                <table class='table'>
                                    <thead>
                                        @if($toolName)
                                            <tr class='border-0'>
                                                <th class='border-0'>
                                                    <span class='title-default'>
                                                        {{$toolName}}
                                                    </span>
                                                </th>
                                            </tr>
                                        @endif

                                        <tr class='border-0'>
                                            <th class='border-0'>
                                                <a class='fs-3' href='{{ $urlAppend }}index.html'>{!! $section_title !!}</a>
                                            </th>
                                        </tr>


                                        <tr class='border-0'>
                                            <th class='border-0'>
                                                <span>
                                                    {{course_id_to_prof($course_id)}}
                                                </span>
                                            </th>
                                        </tr>

                                        <tr class='border-0'>
                                            <th class='border-0'>
                                                <span>
                                                    {{course_id_to_public_code($course_id)}}
                                                </span>
                                            </th>
                                        </tr>


                                        <tbody>
                                        </tbody>
                                    </thead>
                                </table>

                        </div>
                    </div>
                @else
                    <div class='row'>
                        <div class='col-12'>

                                <table class='table'>
                                    <thead>
                                        @if($toolName)
                                            <tr class='border-0'>
                                                <th class='border-0'>
                                                    <span>
                                                        {{$toolName}}
                                                    </span>
                                                </th>
                                            </tr>
                                        @endif

                                        <tr class='border-0'>
                                            <th class='border-0'>
                                                <a class='fs-3' href='{{ $urlAppend }}index.html'>{!! $section_title !!}</a>
                                            </th>
                                        </tr>


                                        <tr class='border-0'>
                                            <th class='border-0'>
                                                <span>
                                                    {{course_id_to_prof($course_id)}}
                                                </span>
                                            </th>
                                        </tr>

                                        <tr class='border-0'>
                                            <th class='border-0'>
                                                <span>
                                                    {{course_id_to_public_code($course_id)}}
                                                </span>
                                            </th>
                                        </tr>


                                        <tbody>
                                        </tbody>
                                    </thead>
                                </table>

                        </div>
                    </div>
                @endif
            @endif

    </div></br>
</div>
