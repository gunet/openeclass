@if (!$tenant)
@push('head_scripts')
<script>
    $(function() {

        $('#admin_id').select2({
            placeholder: 'Αναζήτηση...',
            tags: true,
            multiple: true,
            maximumSelectionLength: 1,
            ajax: {
                delay: 300,
                url: 'listusers.php',
                type: 'POST',
                dataType: 'json',
                data: function(params) {
                    return {
                        search: {
                            value: params.term
                        },
                        length: 10,
                        start: 0
                    };
                },
                processResults: function(data) {
                    return {
                        results: $.map(data.aaData, function(item) {
                            const username = $(item[2]).text();
                            const surname = $(item[0]).text();
                            const name = $(item[1]).text();
                            return {
                                id: username,
                                text: `${surname} ${name} (${username})`
                            };
                        })
                    };
                }
            }
        });


    });
</script>
@endpush
@endif

@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
    <div class='{{ $container }} main-container'>
        <div class="row m-auto">

            @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

            @include('layouts.partials.legend_view')

            {!! action_bar([
            [ 'title' => trans('langBack'),
            'url' => "tenants.php",
            'icon' => 'fa-reply',
            'level' => 'primary-label' ],
            ]) !!}

            @include('layouts.partials.show_alert')

            <div class='col-lg-6 col-12'>

                <div class='form-wrapper form-edit border-0 px-0'>

                    <form class='form-horizontal' role='form' name='edit-tenant' method='post' action='tenant_edit.php' onsubmit='return validateNodePickerForm();'>
                        @if ($tenant)
                        <input type="hidden" name="id" value="{{ $tenant->id }}">
                        @endif
                        <fieldset>
                            <legend class='mb-0' aria-label="{{ trans('langForm') }}"></legend>
                            <div class='form-group'>
                                <label for='name' class='col-sm-12 control-label-notes'>{{ trans('langName') }} <span class="asterisk Accent-200-cl">(*)</span></label>
                                <div class='col-sm-12'>
                                    <input id='name' class='form-control' type='text' name='name' required
                                        @if ($tenant)
                                        value='{{ $tenant->name }}'
                                        @endif>
                                </div>
                            </div>
                            <div class='form-group mt-4'>
                                <label for='description' class='col-sm-12 control-label-notes'>{{ trans('langDescription') }}</label>
                                <div class='col-sm-12'>
                                    {!! $description_editor !!}
                                </div>
                            </div>
                            <div class='form-group mt-4'>
                                <label class='col-sm-12 control-label-notes mb-2'>Συνδεδεμένη κατηγορία</label>
                                <div class="col-sm-12">
                                    @if ($tenant)
                                    <p class="form-control-static">{{ $department_name }}</p>
                                    @else
                                    <div class="radio mb-2">
                                        <label>
                                            <input type="radio" value="0" name="tenant_category" checked>
                                            Αυτόματη δημιουργία νέας κατηγορίας
                                        </label>
                                    </div>
                                    <div class="radio">
                                        <label>
                                            <input type="radio" value="1" name="tenant_category">
                                            Επιλογή υπάρχουσας κατηγορίας
                                        </label>
                                        <div class="help-block">Όλα τα μαθήματα και οι χρήστες του ενοίκου βρίσκονται στην κατηγορία αυτή και τις υποκατηγορίες της</div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            <div class='form-group mt-4 collapse' id="category-select">
                                <label for='category' class='col-sm-12 control-label-notes'>{{ trans('langCategory') }} <span class='asterisk Accent-200-cl'>(*)</span></label>
                                <div class="col-sm-12">
                                    @if (count($categories) > 0)
                                    <select class='form-select' name='category' id='category'>
                                        @foreach ($categories as $category)
                                        <option value='{{ $category->id }}'>{{ getSerializedMessage($category->name) }}</option>
                                        @endforeach
                                    </select>
                                    @else
                                    <input type="text" class="form-control" value="{{ trans('langTenantCategoryNotExist') }}" disabled>
                                    @endif
                                </div>
                            </div>

                            @if (!$tenant)
                            <div class="form-group mt-4" id="user-select-wrapper">
                                <label for="admin_id" class="col-sm-12 control-label-notes">
                                    Διαχειριστής
                                </label>
                                <div class="col-sm-12">
                                    <select
                                        id="admin_id"
                                        name="admin_id[]"
                                        class="form-control"
                                        style="width: 100%;">
                                    </select>
                                </div>
                            </div>
                            @endif
                            <div class='form-group mt-2'>
                                <div class='col-sm-12 mt-5 d-flex justify-content-end align-items-center gap-2'>
                                    <a class='btn btn-default' href='tenants.php'>{{ trans('langCancel') }}</a>
                                    <button class='btn btn-primary' type='submit'>{{ $tenant? trans('langSubmit'): trans('langAdd') }}</button>
                                </div>
                            </div>
                        </fieldset>
                        {!! generate_csrf_token_form_field() !!}
                    </form>
                </div>
            </div>
            <div class='col-lg-6 col-12 d-none d-md-none d-lg-block text-end'>
                <img class='form-image-modules' src='{!! get_form_image() !!}' alt="{{ trans('langImgFormsDes') }}">
            </div>
        </div>
    </div>
</div>
@endsection

@push('bottom_scripts')
<script>
    $(function() {
        var categories = $('#category-select');
        $('input[type=radio][name=tenant_category]').change(function() {
            if (this.value == '1') {
                categories.addClass('show');
            } else if (this.value == '0') {
                categories.removeClass('show');
            }
        });
    });
</script>
@endpush