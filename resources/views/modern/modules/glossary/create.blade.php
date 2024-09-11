@extends('layouts.default')

@section('content')

        <div class="col-12 main-section">
        <div class='{{ $container }} module-container py-lg-0'>
                <div class="course-wrapper d-lg-flex align-items-lg-strech w-100">

                    @include('layouts.partials.left_menu')

                    <div class="col_maincontent_active">

                            <div class="row">

                                @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])


                                <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="collapseTools">
                                    <div class="offcanvas-header">
                                        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="{{ trans('langClose') }}"></button>
                                    </div>
                                    <div class="offcanvas-body">
                                        @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                                    </div>
                                </div>


                                @include('layouts.partials.legend_view')

                                {!! $action_bar !!}

                                <div class='d-lg-flex gap-4 mt-4'>
                                        <div class='flex-grow-1'>
                                    <div class='form-wrapper form-edit rounded'>
                                        <form role='form' action='{{ $edit_url }}' method='post'>

                                                @if(isset($glossary_item))
                                                <input type='hidden' name='id' value='{{ getIndirectReference($glossary_item->id) }}'>
                                                @endif


                                                <div class='form-group{{ Session::getError('term') ? " has-error" : "" }}'>
                                                    <label for='term' class='col-sm-12 control-label-notes'>{{ trans('langGlossaryTerm') }} <span class='Accent-200-cl'>(*)</span></label>
                                                    <div class='col-sm-12'>
                                                        <input type='text' class='form-control' placeholder="{{ trans('langGlossaryTerm') }}" id='term' name='term' placeholder='{{ trans('langGlossaryTerm') }}' value='{{ $term }}'>
                                                        <span class='help-block Accent-200-cl'>{{ Session::getError('term') }}</span>
                                                    </div>
                                                </div>

                                                <div class='form-group{{ Session::getError('definition') ? " has-error" : "" }} mt-4'>
                                                    <label for='definition' class='col-sm-6 control-label-notes'>{{ trans('langGlossaryDefinition') }} <span class='Accent-200-cl'>(*)</span></label>
                                                    <div class='col-sm-12'>
                                                        <textarea id="definition" name="definition" placeholder="{{ trans('langGiveText') }}" rows="4" cols="60" class="form-control">{{ $definition }}</textarea>
                                                        <span class='help-block Accent-200-cl'>{{ Session::getError('definition') }}</span>
                                                    </div>
                                                </div>

                                                <div class='form-group{{ Session::getError('url') ? " has-error" : "" }} mt-4'>
                                                    <label for='url' class='col-sm-6 control-label-notes'>{{ trans('langGlossaryUrl') }} <span class='Accent-200-cl'>(*)</span></label>
                                                    <div class='col-sm-12'>
                                                        <input type='text' placeholder="{{ trans('langGlossaryUrl') }}" class='form-control' id='url' name='url' value='{{ $url }}'>
                                                        <span class='help-block Accent-200-cl'>{{ Session::getError('url') }}</span>
                                                    </div>
                                                </div>

                                                <div class='form-group mt-4'>
                                                    <label for='notes' class='col-sm-6 control-label-notes'>{{ trans('langCategoryNotes') }}</label>
                                                    <div class='col-sm-12'>
                                                        {!! $notes_rich !!}
                                                    </div>
                                                </div>

                                                <div class="mt-4">{!! isset($category_selection) ? $category_selection : "" !!}</div>


                                                <div class='form-group mt-5'>
                                                    <div class='col-12 d-flex justify-content-end align-items-center'>
                                                        {!! $form_buttons !!}
                                                        <a class='btn cancelAdminBtn ms-1' href="{{$base_url}}">{{trans('langCancel')}}</a>
                                                    </div>
                                                </div>


                                            {!! generate_csrf_token_form_field() !!}

                                        </form>
                                    </div>
                                </div><div class='d-none d-lg-block'>
                            <img class='form-image-modules' src='{!! get_form_image() !!}' alt="{{ trans('langImgFormsDes') }}">
                        </div>
                        </div>



                            </div>

                    </div>
                </div>

        </div>
        </div>
@endsection

