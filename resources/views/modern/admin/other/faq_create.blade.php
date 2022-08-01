
@extends('layouts.default')

@section('content')

<div class="pb-lg-3 pt-lg-3 pb-0 pt-0">

    <div class="container-fluid main-container">

        <div class="row rowMedium">

            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 justify-content-center col_maincontent_active_Homepage">
                    
                <div class="row p-lg-5 p-md-5 ps-1 pe-1 pt-5 pb-5">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

                    @if(Session::has('message'))
                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mt-5'>
                        <p class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                            {{ Session::get('message') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </p>
                    </div>
                    @endif

                    {!! $action_bar !!}
                    
                    @if ($modify || $new)
                        
                        <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12'>
                            <div class='form-wrapper shadow-sm p-3 mt-5 rounded'>
                                
                                <form role='form' class='form-horizontal' method='post' action='{{ $_SERVER['SCRIPT_NAME'] }}'>
                                <input type='hidden' name='id' value='{{ $id }}'>
                                    <div class='form-group mt-3'>
                                        <label for='question' class='col-sm-6 control-label-notes'>{{ trans('langFaqQuestion') }} <sup><small>(<span class='text-danger'>*</span>)</small></sup>:</label>
                                        <div class='col-sm-12'>
                                            <input class='form-control' type='text' name='question' value="{{ $faq_mod->title }}" />
                                        </div>
                                    </div>
                                    <div class='form-group mt-3'>
                                        <label for='answer' class='col-sm-6 control-label-notes'>{{ trans('langFaqAnswer') }} <sup><small>(<span class='text-danger'>*</span>)</small></sup>:</label>
                                        <div class='col-sm-12'>{!! $editor !!}</div>
                                    </div>
                                    <div class='form-group mt-3'>
                                        <div class='col-sm-offset-2 col-sm-10'>
                                            <sup><small>(<span class='text-danger'>*</span>)</small></sup> <small class='text-muted'>{{ trans('langCPFFieldRequired') }}</small>
                                        </div>
                                    </div>
                                    <div class='form-group mt-3'>
                                        <div class='col-sm-offset-2 col-sm-10'>
                                            <button type="submit" class="btn btn-primary" name="{{ $new? "submitFaq" : "modifyFaq" }}" value="{{ trans('submitBtnValue') }}">{{ trans('langSave') }}</button>
                                            <a href="{{ $_SERVER['SCRIPT_NAME'] }}" class="btn btn-secondary">{{ trans('langCancel') }}</a>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        
                    @else
                        
                            <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12'>
                                <div class='panel shadow-lg p-3 mb-5 bg-body rounded bg-primary'>
                                    <div class='panel-group faq-section' id='accordion' role='tablist' aria-multiselectable='true'>
                                        @if (count($faqs) == 0)
                                            <div class='panel list-group-item'>
                                                <div class='text-center text-muted'><em>{{ trans('langFaqNoEntries') }}</em> <br><br> <em>{{ trans('langFaqAddNew') }}</em></div>
                                            </div>
                                        @else
                                            @foreach ($faqs as $key => $faq)
                                                <div class='panel panel-default list-group-item' data-id='{{ $faq->id }}'>
                                                    <div class='panel-heading' role='tab' id='heading-{{ $faq->id }}'>
                                                        <h4 class='panel-title pt-1 pe-3'>
                                                            <a class='control-label-notes' role='button' data-bs-toggle='collapse' data-bs-parent='#accordion' href='#faq-{{ $faq->id }}' aria-expanded='true' aria-controls='#{{ $faq->id }}'>
                                                                <span class="indexing">{{ $key+1 }}.</span>{!! $faq->title !!} <span class='caret'></span>
                                                                <span class='fa fa-arrow-down text-warning fs-6 ps-2'></span>
                                                            </a>
                                                            <a class='forDelete' href='javascript:void(0);' data-id='{{ $faq->id }}' data-order='{{ $faq->order }}'><span class='fa fa-times text-danger pull-right' data-bs-toggle='tooltip' data-bs-placement='top' title='{{ trans('langDelete') }}'></span></a>
                                                            <a href='javascript:void(0);'><span class='fa fa-arrows text-dark pull-right' data-bs-toggle='tooltip' data-bs-placement='top' title='{{ trans('langReorder') }}'></span></a>
                                                            <a href='{{ $_SERVER['SCRIPT_NAME'] }}?faq=modify&id={{ $faq->id }}'><span class='fa fa-pencil-square text-primary pull-right' data-bs-toggle='tooltip' data-bs-placement='top' title='{{ trans('langEdit') }}'></span></a>
                                                        </h4>
                                                    </div>
                                                    <div id='faq-{{ $faq->id }}' class='panel-collapse collapse' role='tabpanel' aria-labelledby='heading{{ $faq->id }}'>
                                                        <div class='panel-body'>
                                                            <p><strong><u>{{ trans('langFaqAnswer') }}:</u></strong></p>
                                                            {!! $faq->body !!}
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                </div>
                            </div>
                        
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>


@endsection
