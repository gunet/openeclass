@extends('layouts.default')

@section('content')

@if($course_code and $currentCourseName and !isset($_GET['fromFlipped']))
<div class="{{ $container }} module-container py-lg-0">
<div class="course-wrapper d-lg-flex align-items-lg-strech w-100">
<aside class='aside-sidebar'>@include('layouts.partials.left_menu')</aside>
<main id="main" class="col-12 main-maincontent col_maincontent_active">
@else
<main id="main" class="col-12 main-section">
<div class="{{ $container }} main-container">
<div class="row m-auto">
<div class="col-12">
@endif

                    <div class="row">
                        @if(isset($_SESSION['uid']))
                             @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])
                        @else
                            @if($course_code and !$is_in_tinymce and $currentCourseName and !isset($_GET['fromFlipped']))
                                <nav class="me-lg-0 me-2">
                                    <a class="btn d-lg-none p-0" type="button" data-bs-toggle="offcanvas" href="#collapseTools" role="button" aria-controls="collapseTools">
                                        <img src='{{ $urlAppend }}resources/img/Icons_menu-collapse.svg' alt="Open Course Tools"/>
                                    </a>
                                </nav>
                            @endif
                        @endif

                        @if($course_code and $currentCourseName)
                            <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="collapseTools">
                                <div class="offcanvas-header">
                                    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="{{ trans('langClose') }}"></button>
                                </div>
                                <div class="offcanvas-body">
                                    @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                                </div>
                            </div>
                        @endif

                        @include('layouts.partials.legend_view')

                        @include('layouts.partials.show_alert')

                        <div class='col-12'>
                            {!! $tool_content !!}
                        </div>
                    </div>

@if($course_code and $currentCourseName and !isset($_GET['fromFlipped']))
</main></div></div>
@else
</div></div></div></main>
@endif

@endsection
