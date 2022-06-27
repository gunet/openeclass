@if (!empty($breadcrumbs))
    <!-- BEGIN breadCrumbs -->
    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12'>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                @foreach ($breadcrumbs as $key => $item)
                    @if (isset($item['bread_href']))
                        <li class="breadcrumb-item">
                            <a href='{{ $item['bread_href'] }}'>
                                {!! $session->status != USER_GUEST && isset($uid) && $key == 0 ? '<span class="fa fa-home"></span> ' : "" !!}
                                {!! $item['bread_text'] !!}
                            </a>
                        </li>
                    @else
                        <li class="breadcrumb-item active" aria-current="page">{!! $item['bread_text'] !!}</li>
                    @endif
                @endforeach
            </ol>
        </nav>
        <hr>
    </div>
    <!-- END breadCrumbs -->
@endif
