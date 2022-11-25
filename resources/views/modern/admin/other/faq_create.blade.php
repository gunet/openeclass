
@extends('layouts.default')

@section('content')

<div class="pb-lg-3 pt-lg-3 pb-0 pt-0">

    <div class="container-fluid main-container">

        <div class="row rowMedium">

            <div class="col-12 justify-content-center col_maincontent_active_Homepage">
                    
                <div class="row p-lg-5 p-md-5 ps-1 pe-1 pt-5 pb-5">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

                    @if(Session::has('message'))
                    <div class='col-12 all-alerts'>
                        <div class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                            @if(is_array(Session::get('message')))
                                @php $messageArray = array(); $messageArray = Session::get('message'); @endphp
                                @foreach($messageArray as $message)
                                    {!! $message !!}
                                @endforeach
                            @else
                                {!! Session::get('message') !!}
                            @endif
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                    @endif

                    {!! $action_bar !!}
                    
                    @if ($modify || $new)
                        
                        <div class='col-lg-6 col-12 d-none d-md-none d-lg-block'>
                            <div class='col-12 h-100 left-form'></div>
                        </div>
                        <div class='col-lg-6 col-12'>
                            <div class='form-wrapper form-edit rounded'>
                                
                                <form role='form' class='form-horizontal' method='post' action='{{ $_SERVER['SCRIPT_NAME'] }}'>
                                <input type='hidden' name='id' value='{{ $id }}'>
                                    <div class='form-group'>
                                        <label for='question' class='col-sm-12 control-label-notes'>{{ trans('langFaqQuestion') }} <sup><small>(<span class='text-danger'>*</span>)</small></sup>:</label>
                                        <div class='col-sm-12'>
                                            <input class='form-control' placeholder="{{ trans('langFaqQuestion') }}..." type='text' name='question' value="{{ $faq_mod->title }}" />
                                        </div>
                                    </div>
                                    <div class='form-group mt-4'>
                                        <label for='answer' class='col-sm-12 control-label-notes'>{{ trans('langFaqAnswer') }} <sup><small>(<span class='text-danger'>*</span>)</small></sup>:</label>
                                        <div class='col-sm-12'>{!! $editor !!}</div>
                                    </div>
                                    <div class='form-group mt-4'>
                                        <div class='col-sm-offset-2 col-sm-10'>
                                            <sup><small>(<span class='text-danger'>*</span>)</small></sup> <small class='text-muted'>{{ trans('langCPFFieldRequired') }}</small>
                                        </div>
                                    </div>
                                    <div class='form-group mt-5'>
                                        <div class='col-12 d-flex justify-content-center align-items-center'>
                                           
                                                
                                                <button type="submit" class="btn submitAdminBtn" name="{{ $new? "submitFaq" : "modifyFaq" }}" value="{{ trans('submitBtnValue') }}">{{ trans('langSave') }}</button>
                                                <a href="{{ $_SERVER['SCRIPT_NAME'] }}" class="btn btn-outline-secondary cancelAdminBtn ms-1">{{ trans('langCancel') }}</a>
                                               
                                           
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        
                    @else
                        
                            <div class='col-12'>
                                
                                <div class='accordion panel-group faq-section' id='accordion' role='tablist' aria-multiselectable='true'>
                                    @if (count($faqs) == 0)
                                        <div class='panel list-group-item'>
                                            <div class='text-center text-muted'><em>{{ trans('langFaqNoEntries') }}</em> <br><br> <em>{{ trans('langFaqAddNew') }}</em></div>
                                        </div>
                                    @else
                                        @foreach ($faqs as $key => $faq)
                                            <div class='accordion-item' data-id='{{ $faq->id }}'>
                                                <div class='accordion-header' role='tab' id='heading-{{ $faq->id }}'>
                                                    <div class='row'>
                                                        <div class='col-12'>
                                                            <button class="accordion-button btn btn-transparent" type='button' data-bs-toggle='collapse' data-bs-target='#faq-{{ $faq->id }}' aria-expanded='true' aria-controls='#{{ $faq->id }}'>
                                                                <span class="control-label-notes">{{ $key+1 }}. {!! $faq->title !!}</span>
                                                            </button>
                                                        </div>
                                                        <div class='col-12'>
                                                            <a class='forDelete' href='javascript:void(0);' data-id='{{ $faq->id }}' data-order='{{ $faq->order }}'><span class='fa fa-times text-danger float-end p-2' data-bs-toggle='tooltip' data-bs-placement='top' title='{{ trans('langDelete') }}'></span></a>
                                                            <a href='javascript:void(0);'><span class='fa fa-arrows text-dark float-end p-2' data-bs-toggle='tooltip' data-bs-placement='top' title='{{ trans('langReorder') }}'></span></a>
                                                            <a href='{{ $_SERVER['SCRIPT_NAME'] }}?faq=modify&id={{ $faq->id }}'><span class='fa fa-pencil-square text-primary float-end p-2' data-bs-toggle='tooltip' data-bs-placement='top' title='{{ trans('langEdit') }}'></span></a>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div id='faq-{{ $faq->id }}' class='panel-collapse accordion-collapse collapse' role='tabpanel' data-bs-parent='#accordion' aria-labelledby='heading{{ $faq->id }}'>
                                                    <div class='accordion-body'>
                                                        <p><strong><u>{{ trans('langFaqAnswer') }}:</u></strong></p>
                                                        {!! $faq->body !!}
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            
                            </div>
                        
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>


@endsection
