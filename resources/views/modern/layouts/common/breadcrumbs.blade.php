@if (!empty($breadcrumbs))

    <!-- BEGIN breadCrumbs -->

    @if($course_code)
    <nav class="navbar_breadcrumb d-flex justify-content-md-start justify-content-center" aria-label="breadcrumb">
    @else
    <nav class="w-95 h-auto d-flex justify-content-md-start justify-content-center" aria-label="breadcrumb">
    @endif
        <ol class="breadcrumb">
            @foreach ($breadcrumbs as $key => $item)
               @if (isset($item['bread_href']))
                    <li class="breadcrumb-item">
                        <a class='text-wrap' href='{{ $item['bread_href'] }}'>
                            {!! $session->status != USER_GUEST && isset($uid) && $key == 0 ? '<span class="fa fa-home"></span> ' : "" !!}
                            {!! $item['bread_text'] !!}
                        </a>
                    </li>
                @else
                    <li class="breadcrumb-item active" aria-current="page"><span class='text-wrap'>{!! $item['bread_text'] !!}</span></li>
                @endif
            @endforeach
        </ol>
    </nav>

    <!-- END breadCrumbs -->

@endif
