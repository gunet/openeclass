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
                            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                        </div>
                        <div class="offcanvas-body">
                            @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                        </div>
                    </div>

                    @include('layouts.partials.legend_view')

                    {!! $action_bar !!}

                    @include('layouts.partials.show_alert') 

                    <div class='col-12'>
                        <div class='card panelCard px-lg-4 py-lg-3'>

                            <div class='card-header border-0 d-flex justify-content-between align-items-center'>
                                <h3>
                                    {{ $request->title }}
                                    @if ($request->type_id)
                                        <small>&nbsp;->&nbsp;{{ $type->name }}</small>
                                    @endif
                                </h3>
                            </div>


                            <div class='card-body'>

                                <div class='row'>
                                    <div class='d-inline-flex align-items-center'>
                                        <span class='control-label-notes pe-2'>{{ trans('langNewBBBSessionStatus') }}:</span>
                                        {{ $state }}
                                    </div>
                                </div>
                                <div class='row mt-3'>
                                    <div class='d-inline-flex align-items-center'>
                                        <b class='control-label-notes pe-2'>{{ trans('langFrom') }}:</b>
                                        {!! display_user($request->creator_id) !!}
                                    </div>
                                </div>

                                @if ($watchers)
                                    <div class='row mt-3'>
                                        <div class='d-inline-flex align-items-center'>
                                            <b class='control-label-notes pe-2'>{{ trans('langWatchers') }}:</b>
                                            @foreach ($watchers as $user)
                                                {!! display_user($user) !!}
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                @if ($assigned)
                                    <div class='row mt-3'>
                                        <div class='d-inline-flex align-items-center'>
                                            <b class='control-label-notes pe-2'>{{ trans("m['WorkAssignTo']") }}:</b>
                                            @foreach ($assigned as $user)
                                                {!! display_user($user) !!}
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <hr>

                                @if ($field_data)
                                    @foreach ($field_data as $field)
                                        <div class='row'>
                                            <div class='col-12 col-2 text-end'>
                                                <b>{{ getSerializedMessage($field->name) }}:</b>
                                            </div>
                                            <div class='col-12 col-10'>
                                                @if (is_null($field->data) or $field->data === '')
                                                    <span class='not_visible'> - </span>
                                                @else
                                                    @if ($field->datatype == REQUEST_FIELD_DATE)
                                                        {{ format_locale_date(strtotime($field->data)) }}
                                                    @else
                                                        {{ $field->data }}
                                                    @endif
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                    <hr>
                                @endif

                                <div class='row'>
                                    <div class='col-12'>
                                        <div class='p-2'>{!! $request->description !!}</div>
                                    </div>
                                </div>

                            </div>

                            <div class='card-footer small-text border-0'>
                                {{ format_locale_date(strtotime($request->open_date)) }}
                            </div>
                        </div>
                    </div>

                    @if ($can_modify or $can_assign_to_self)
                        <div class='col-12 mt-3'>
                            <form role='form' method='post' action='{{ $targetUrl }}'>
                                <p>
                                {!! generate_csrf_token_form_field() !!}
                                @if ($can_assign_to_self)
                                    <button class='btn submitAdminBtn' type='submit' name='assignToSelf'>{{ trans('langTakeRequest') }}</button>
                                @endif
                                @if ($can_modify)
                                <div class='d-flex'>
                                    <button class='btn submitAdminBtn me-1' type='button' data-bs-toggle='modal' data-bs-target='#assigneesModal'>{{ trans("m['WorkAssignTo']") }}...</button>
                                    <button class='btn submitAdminBtn me-1' type='button' data-bs-toggle='modal' data-bs-target='#watchersModal'>{{ trans("langWatchers") }}...</button>
                                    <a class='btn submitAdminBtn' href='{{ $editUrl }}'>{{ trans("langElaboration") }}...</a>
                                </div>
                                @endif
                                </p>
                            </form>
                        </div>
                    @endif

                    @if ($can_comment)
                        <div class='col-12 mt-3'>
                            <div class='form-wrapper form-edit rounded'>
                                <form class='form-horizontal' role='form' method='post' action='{{ $targetUrl }}' enctype='multipart/form-data'>
                                    <fieldset>
                                        <legend class='mb-0' aria-label="{{ trans('langForm') }}"></legend>
                                        @if ($can_modify)
                                            <div class='form-group'>
                                                <label for='newState' class='col-sm-12 control-label-notes'>{{ trans('langChangeState') }}</label>
                                                <div class='col-sm-12'>
                                                    <select class='form-select' name='newState' id='newState'>
                                                        @foreach ($states as $stateId => $stateName)
                                                            <option value='{{ $stateId }}'@if ($stateId == $request->state) selected @endif>{{ $stateName }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        @endif



                                        <div class='form-group  @if($can_modify) mt-4 @endif'>
                                            <label for='requestComment' class='col-sm-12 control-label-notes'>{{ trans('langComment') }}</label>
                                            <div class='col-sm-12'>
                                                {!! $commentEditor !!}
                                            </div>
                                        </div>



                                        <div class='form-group mt-4'>
                                            <label for='requestFile' class='col-sm-12 control-label-notes'>{{ trans('langAttachedFile') }}</label>
                                            <div class='col-sm-12'>
                                                <input type='hidden' name='MAX_FILE_SIZE' value='{{ fileUploadMaxSize() }}'>
                                                <input type='file' name='requestFile' id='requestFile'>
                                            </div>
                                        </div>



                                        <div class='form-group mt-5'>
                                            <div class='col-12 d-flex justify-content-center align-items-center gap-2'>
                                                <button class='btn submitAdminBtn' type='submit'>{{ trans('langSubmit') }}</button>
                                                <a class='btn cancelAdminBtn' href='{{ $backUrl }}'>{{ trans('langCancel') }}</a>
                                            </div>
                                        </div>
                                        {!! generate_csrf_token_form_field() !!}
                                    </fieldset>
                                </form>
                            </div>
                        </div>
                    @endif

                    @if ($comments)
                        <div class='col-12 mt-5'>
                            @if(count($comments) == 1)
                                <div class="row row-cols-1 g-4">
                            @else
                                <div class="row row-cols-1 row-cols-md-2 g-4">
                            @endif
                                    @foreach ($comments as $comment)
                                        <div class="col">
                                            <div class='card panelCard px-lg-4 py-lg-3 h-100'>
                                                <div class='panel-body'>


                                                    <p class='card-title fw-bold mb-0 fs-6'>{{ trans('langFrom') }}</p>
                                                    <p class='card-text mb-3'>{!! display_user($comment->user_id) !!}</p>

                                                    <p class='card-title fw-bold mb-0 fs-6'>{{ trans('langDate') }}</p>
                                                    <p class='card-text mb-3'>{{ format_locale_date(strtotime($comment->ts)) }}</p>

                                                    @if ($comment->old_state != $comment->new_state)

                                                        <p class='card-title fw-bold mb-0 fs-6'>{{ trans('langChangeState') }}</p>
                                                        <p class='card-text mb-3'>
                                                            {{ $states[$comment->new_state] }}
                                                            <span>({{ trans('langFrom') }}:</span> {{ $states[$comment->old_state] }})
                                                        </p>

                                                    @endif
                                                    @if ($comment->real_filename)

                                                        <p class='card-title fw-bold mb-0 fs-6'>{{ trans('langAttachedFile') }}</p>
                                                        <p class='card-text mb-3'><a href='{{ commentFileLink($comment) }}'>{{ $comment->filename }}</a></p>

                                                    @endif
                                                    @if ($comment->comment)

                                                        <p class='card-title fw-bold mb-0 fs-6'>{{ trans('langComment') }}</p>
                                                        {!! standard_text_escape($comment->comment) !!}

                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                        </div>

                    @endif



                    @if ($can_modify)
                        @include('modules.request.modals')
                    @endif


                </div>
            </div>
        </div>

</div>
</div>
@endsection
