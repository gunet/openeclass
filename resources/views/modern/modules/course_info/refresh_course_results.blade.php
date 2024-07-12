@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
<div class='{{ $container }} module-container py-lg-0'>
        <div class="course-wrapper d-lg-flex align-items-lg-strech w-100">

            <div id="background-cheat-leftnav" class="col_sidebar_active d-flex justify-content-start align-items-strech ps-lg-0 pe-lg-0">
                  <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block ContentLeftNav">
                      @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                  </div>
            </div>

            <div class="col_maincontent_active">
                    
                <div class="row">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])


                    <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="collapseTools">
                        <div class="offcanvas-header">
                            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                        </div>
                        <div class="offcanvas-body">
                            @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                        </div>
                    </div>


                    @include('layouts.partials.legend_view')


                    {!! $action_bar !!}

                    @include('layouts.partials.show_alert') 

                    <div class='col-sm-12'>
                        <div class='alert alert-success'><i class='fa-solid fa-circle-check fa-lg'></i><span>
                            @if (!isset($_GET['from_user']))
                                {{ trans('langRefreshSuccess') }}
                            @endif
                            <ol class='listBullet list-group list-group-numbered mt-3'>
                                @for ($i = 0; $i < $count_events; $i++) 
                                    <li class='list-group-item bg-light'>{!! $output[$i] !!}</li>
                                @endfor    
                            </ol></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    
</div>
</div>

@endsection