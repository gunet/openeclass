
<div class='col-12 breadcrumbs-container @if(!$course_code) d-flex justify-content-md-start justify-content-start @endif overflow-hidden'>

    <div class='d-inline-flex align-items-top overflow-hidden'>
        <!-- this is toggle-button in breadcrumb -->
        @if($course_code and !$is_in_tinymce and $currentCourseName and !isset($_GET['fromFlipped']))
            <nav class="me-lg-0 me-2" role="navigation" aria-label="{{ trans('langBreadcrumb') }}">
                <a class="btn d-lg-none p-0" type="button" data-bs-toggle="offcanvas" href="#collapseTools" role="button" aria-controls="collapseTools" aria-label="{{ trans('langOpenCloseTools') }}">
                    <svg id='collapse-left-menu-icon' width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" role="presentation">
                        <path d="M5 5C4.44772 5 4 5.44772 4 6V6.5C4 7.05228 4.44772 7.5 5 7.5H19.25C19.9404 7.5 20.5 6.94036 20.5 6.25C20.5 5.55964 19.9404 5 19.25 5H5Z" fill="#2B3944"/>
                        <path d="M5 10.5C4.44772 10.5 4 10.9477 4 11.5V12C4 12.5523 4.44772 13 5 13H14.75C15.4404 13 16 12.4404 16 11.75C16 11.0596 15.4404 10.5 14.75 10.5H5Z" fill="#2B3944"/>
                        <path d="M5 16C4.44772 16 4 16.4477 4 17V17.5C4 18.0523 4.44772 18.5 5 18.5H10.75C11.4404 18.5 12 17.9404 12 17.25C12 16.5596 11.4404 16 10.75 16H5Z" fill="#2B3944"/>
                    </svg>
                </a>
            </nav>
        @endif


        @if (!empty($breadcrumbs))
            @if($course_code)
                <nav style="--bs-breadcrumb-divider: '>';" class="d-flex justify-content-start breadcrumb-content" role="navigation" aria-label="{{ trans('langBreadcrumb') }}">
            @else
                <div class='col-12'>
                <nav style="--bs-breadcrumb-divider: '>';" class="w-auto h-auto" role="navigation" aria-label="{{ trans('langBreadcrumb') }}">
            @endif
                <ol class="breadcrumb mb-0 @if(!$course_code) py-1 @endif">
                    @foreach ($breadcrumbs as $key => $item)
                        @if (isset($item['bread_href']))
                            <li class="breadcrumb-item d-flex justify-content-center align-items-center">
                                <a class='text-wrap text-decoration-none vsmall-text' href='{{ $item['bread_href'] }}'>
                                    {!! $session->status != USER_GUEST && isset($uid) && $key == 0 ? '<i class="fa-solid fa-house pe-1"></i> ' : "" !!}
                                    {{ $item['bread_text'] }}
                                </a>
                            </li>
                        @else
                            <li class="breadcrumb-item active d-flex justify-content-center align-items-center TextMedium" aria-current="{{ $item['bread_text'] }}"><span class='pe-none Neutral-900-cl'>{{ $item['bread_text'] }}</span></li>
                        @endif
                    @endforeach
                </ol>
            </nav>
            @if(!$course_code)
            </div>
            @endif

        @endif

    </div>


</div>


@if(count($breadcrumbs) > 0)
    @if(isset($_GET['fromFlipped']))
        <div class='col-12'>
            <div class="w-100 mt-3 mb-3"></div>
        </div>
    @endif
@endif
