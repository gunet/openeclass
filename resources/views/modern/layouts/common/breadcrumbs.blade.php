
<div class='col-12 breadcrumbs-container @if(!$course_code) d-flex justify-content-md-start justify-content-center @endif'>

    <div class='d-inline-flex align-items-top overflow-auto'>
        <!-- this is toggle-button in breadcrumb -->
        @if($course_code and !$is_in_tinymce and $currentCourseName and !isset($_GET['fromFlipped']))
            <nav class="me-lg-0 me-2">
                <a class="btn d-lg-none p-0" type="button" data-bs-toggle="offcanvas" href="#collapseTools" role="button" aria-controls="collapseTools">
                    <img class='min-img-size' src='{{ $urlAppend }}template/modern/img/Icons_menu-collapse.svg' />
                </a>
            </nav>
        @endif


        @if (!empty($breadcrumbs))
            @if($course_code)
                <nav style="--bs-breadcrumb-divider: '>';" class="d-flex justify-content-start breadcrumb-content" aria-label="breadcrumb">
            @else
                <div class='col-12'>
                <nav style="--bs-breadcrumb-divider: '>';" class="w-auto h-auto" aria-label="breadcrumb">
            @endif
                <ol class="breadcrumb mb-0 @if(!$course_code) py-1 @endif">
                    @foreach ($breadcrumbs as $key => $item)
                    @if (isset($item['bread_href']))
                            <li class="breadcrumb-item d-flex justify-content-center align-items-center">
                                <a class='text-wrap text-decoration-underline vsmall-text' href='{{ $item['bread_href'] }}'>
                                    {!! $session->status != USER_GUEST && isset($uid) && $key == 0 ? '<i class="fa-solid fa-house pe-1"></i> ' : "" !!}
                                    {!! $item['bread_text'] !!}
                                </a>
                            </li>
                        @else
                            <li class="breadcrumb-item active d-flex justify-content-center align-items-center TextMedium" aria-current="page"><a class='pe-none Neutral-900-cl vsmall-text'>{!! $item['bread_text'] !!}</a></li>
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
