                            @if (!empty($breadcrumbs))
                                <!-- BEGIN breadCrumbs -->
                                <div class="row">                            
                                    <nav role="navigation" class="col-xs-12"> 
                                        <ol class="breadcrumb">
                                            @foreach ($breadcrumbs as $key => $item)
                                                @if (isset($item['bread_href']))
                                                    <li>
                                                        <a href='{{ $item['bread_href'] }}'>
                                                            {!! $session->status != USER_GUEST && isset($uid) && $key == 0 ? '<span class="fa fa-home"></span> ' : "" !!}
                                                            {!! $item['bread_text'] !!}
                                                        </a>
                                                    </li>
                                                @else
                                                    <li>{!! $item['bread_text'] !!}</li>
                                                @endif
                                            @endforeach
                                        </ol>
                                    </nav>
                                </div>
                                <!-- END breadCrumbs -->                            
                            @endif
