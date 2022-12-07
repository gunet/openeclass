
<div class='col-12 breadcrumbs-container @if(!$course_code) d-flex justify-content-md-start justify-content-center @endif'>
   
    <div class='d-inline-flex align-items-top overflow-auto'>
        <!-- this is toggle-button in breadcrumb -->
        @if($course_code and !$is_in_tinymce and $currentCourseName)
            <nav class="me-2">
                <button type="button" id="menu-btn" class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block btn btn-outline-primary btn-sm menu_btn_button">
                    <i class="fas fa-align-left"></i>
                    <span></span>
                </button>
                <a class="btn btn-sm btn-outline-primary d-lg-none" type="button" data-bs-toggle="offcanvas" href="#collapseTools" role="button" aria-controls="collapseTools">
                    <i class="fa fa-bars"></i>
                </a>
            </nav>
        @endif


        @if (!empty($breadcrumbs))
            @if($course_code)
                <nav style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='currentColor'/%3E%3C/svg%3E&#34;);" class="d-flex justify-content-start breadcrumb-content" aria-label="breadcrumb">
            @else
                <div class='col-12'>
                <nav style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='currentColor'/%3E%3C/svg%3E&#34;);" class="w-auto h-auto" aria-label="breadcrumb">
            @endif
                <ol class="breadcrumb mb-0">
                    @foreach ($breadcrumbs as $key => $item)
                    @if (isset($item['bread_href']))
                            <li class="breadcrumb-item d-flex justify-content-center align-items-center">
                                <a class='text-wrap text-capitalize TextMedium' href='{{ $item['bread_href'] }}'>
                                    {!! $session->status != USER_GUEST && isset($uid) && $key == 0 ? '<span class="fa fa-home"></span> ' : "" !!}
                                    {!! $item['bread_text'] !!}
                                </a>
                            </li>
                        @else
                            <li class="breadcrumb-item active d-flex justify-content-center align-items-center TextMedium" aria-current="page"><a class='pe-none text-secondary text-lowercase'>{!! $item['bread_text'] !!}</a></li>
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