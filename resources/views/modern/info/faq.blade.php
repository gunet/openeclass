@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
    <div class='{{ $container }} main-container'>
        <div class="row m-auto">

            @if(isset($_SESSION['uid']))
                @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])
            @endif
            <div class="col-12 @if(isset($_SESSION['uid'])) mt-4 @endif">
                <h1>{{ $toolName }}</h1>
                <div class='row row-cols-1 row-cols-lg-2 g-lg-5 g-4'>
                    <div class='col-lg-6 col-12'>
                        <div class='panel'>
                            <div class='panel-group group-section' id='accordion' role='tablist' aria-multiselectable='true'>

                                @if (count($faqs) == 0)
                                    <div class='alert alert-warning mt-5'>
                                    <i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>
                                        {{ trans('langFaqNoEntries') }}</span>
                                    </div>
                                @else
                                    <ul class="list-group list-group-flush mt-4">
                                        @foreach ($faqs as $key => $faq)

                                                <li class="list-group-item px-0 mb-4 bg-transparent">
                                                    <a class='accordion-btn d-flex justify-content-start align-items-start' role='button' data-bs-toggle='collapse' href='#faq-{{ $faq->id }}' aria-expanded='false' aria-controls='#{{ $faq->id }}'>
                                                        <span class='fa-solid fa-chevron-down'></span>
                                                        {!! $faq->title !!}

                                                    </a>

                                                    <div id='faq-{{ $faq->id }}' class='panel-collapse accordion-collapse collapse border-0 rounded-0' role='tabpanel' data-bs-parent='#accordion'>
                                                        <div class='panel-body bg-transparent Neutral-900-cl px-4'>
                                                            {!! $faq->body !!}
                                                        </div>
                                                    </div>
                                                </li>


                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class='col-lg-6 col-12 mt-lg-0 mt-4 d-none d-lg-block'>
                        <img class='form-image-modules' src='{!! get_FAQ_image() !!}' alt='Frequest questions' />
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
