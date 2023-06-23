@extends('layouts.default')

@push('head_scripts')
    <script type='text/javascript'>
        $(document).on('change', '#typSel', function (e) {
            $('#titleSel').val( $(this).children(':selected').text());
        });
    </script>
@endpush

@section('content')

<div class="col-12 basic-section p-xl-5 px-lg-3 py-lg-5">

        <div class="row rowMargin">

            <div id="background-cheat-leftnav" class="col-xl-2 col-lg-3 col_sidebar_active d-flex justify-content-start align-items-strech ps-lg-0 pe-lg-3"> 
                <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block ContentLeftNav">
                    @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                </div>
            </div>

            <div class="col-xl-10 col-lg-9 col-12 col_maincontent_active p-lg-5">

                <div class="row">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="collapseTools" aria-labelledby="offcanvasExampleLabel">
                        <div class="offcanvas-header">
                            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                        </div>
                        <div class="offcanvas-body">
                            @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                        </div>
                    </div>


                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

                    {!! $action_bar !!}
                
                    <div class='col-12'>
                        <div class='form-wrapper form-edit rounded'>
                            <form class='form-horizontal' role='form' action='index.php?course={{ $course_code }}' method='post'>
                                @if ($editId)
                                    <input type='hidden' name='editId' value='{{ getIndirectReference($editId) }}'>
                                @endif
                                <div class='form-group'>
                                    <label for='editType' class='col-sm-6 control-label-notes'>{{ trans('langType') }}</label>
                                    <div class='col-sm-12'>
                                        {!! selection($types, 'editType', $defaultType, 'class="form-control" id="typSel"') !!}
                                    </div>
                                </div>
                                <div class='form-group{{ $titleError ? " form-error" : ""}} mt-4'>
                                    <label for='titleSel' class='col-sm-6 control-label-notes'>{{ trans('langTitle') }}</label>
                                    <div class='col-sm-12'>
                                        <input type='text' name='editTitle' class='form-control' value='{{ $cdtitle }}' size='40' id='titleSel'>
                                        {!! Session::getError('editTitle', "<span class='help-block'>:message</span>") !!}
                                    </div>
                                </div>
                                <div class='form-group mt-4'>
                                    <label for='editComments' class='col-sm-6 control-label-notes'>{{ trans('langContent') }}</label>
                                    <div class='col-sm-12'>
                                        {!! $text_area_comments !!}
                                    </div>
                                </div>
                                <div class='form-group mt-5'>
                                    <div class='col-12 d-flex justify-content-center align-items-center'>
                                        {!! $form_buttons !!}
                                    </div>
                                </div>
                            {!! generate_csrf_token_form_field() !!}
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    
</div>
    
@endsection

