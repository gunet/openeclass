@if (!empty($breadcrumbs))

    <!-- BEGIN breadCrumbs -->

        <nav class="navbar_breadcrumb" aria-label="breadcrumb">
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
        </nav>

    <!-- END breadCrumbs -->

@endif
