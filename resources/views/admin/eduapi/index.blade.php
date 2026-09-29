@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
    <div class='{{ $container }} main-container'>
        <div class="row m-auto">

            @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

            @include('layouts.partials.legend_view')

            @include('layouts.partials.show_alert')

            <div class='col-12'>
                <div class='row'>
                    <div class='col-12'>

                        @if (isset($error))
                            <div class='alert alert-danger'>
                                <strong>{{ trans('langError') }}:</strong> {{ $error }}
                            </div>
                            <p>{{ trans('langEduApiConfigureFirst') }}
                            <a href='{{ $urlAppend }}modules/admin/eduapiconf.php'>{{ trans('langEduApiAdminPath') }}</a>.</p>
                        @else
                            {{-- Session picker: always populated from a live API query --}}
                            <form method='get' action='{{ $urlAppend }}modules/eduapi/index.php' class='mb-4'>
                                <div class='form-group'>
                                    <label for='session' class='control-label-notes'>{{ trans('langEduApiSelectSession') }}</label>
                                    <select id='session' name='session' class='form-select' onchange='this.form.submit()'>
                                        @foreach ($session_options as $option)
                                            <option value='{{ $option['value'] }}' {{ $option['selected'] ? 'selected' : '' }}>{{ $option['label'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </form>

                            @if (empty($offerings_rows))
                                <div class='alert alert-warning'>{{ trans('langEduApiNoOfferings') }}</div>
                            @else
                                <div class='alert alert-info'>
                                    <strong>{{ trans('langEduApiOfferings') }}:</strong> {{ $offerings_total }} |
                                    <strong>{{ trans('langEduApiSelectedCount') }}</strong> <span id='eduapi-selected-count'>{{ $preselected_count }}</span>
                                </div>

                                <form method='post' action='{{ $urlAppend }}modules/eduapi/index.php' id='importForm'>
                                    <input type='hidden' name='session' value='{{ $selected_session }}'>
                                    <input type='hidden' name='sync_type' id='syncType' value='full'>
                                    {!! generate_csrf_token_form_field() !!}
                                    <div class='mb-3 d-flex gap-2 flex-wrap'>
                                        <button type='button' id='eduapi-select-all' class='btn btn-sm cancelAdminBtn'>{{ trans('langEduApiSelectAll') }}</button>
                                        <button type='button' id='eduapi-deselect-all' class='btn btn-sm cancelAdminBtn'>{{ trans('langEduApiDeselectAll') }}</button>
                                        <button type='button' id='eduapi-select-open-active' class='btn btn-sm cancelAdminBtn'>{{ trans('langEduApiSelectOpenActive') }}</button>
                                    </div>
                                    <div class='table-responsive'>
                                        <table class='table table-striped'>
                                            <thead>
                                                <tr>
                                                    <th></th>
                                                    <th>{{ trans('langEduApiCourseTitle') }}</th>
                                                    <th>{{ trans('langEduApiOrganization') }}</th>
                                                    <th>{{ trans('langEduApiDates') }}</th>
                                                    <th>{{ trans('langEduApiEnrolled') }}</th>
                                                    <th>{{ trans('langEduApiStatus') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($offerings_rows as $row)
                                                    <tr {!! $row['is_active'] ? '' : "class='text-muted'" !!}>
                                                        <td>
                                                            <input type='checkbox' class='eduapi-offering-cb' name='offerings[]' value='{{ $row['sourced_id'] }}'
                                                                data-open='{{ $row['is_open'] ? 1 : 0 }}' data-active='{{ $row['is_active'] ? 1 : 0 }}'
                                                                {{ $row['preselected'] ? 'checked' : '' }}>
                                                        </td>
                                                        <td>
                                                            {{ $row['title'] }}
                                                            @if ($row['description'] !== '')
                                                                <br><small class='text-muted'>{{ $row['description'] }}</small>
                                                            @endif
                                                        </td>
                                                        <td>{{ $row['org_name'] }}</td>
                                                        <td>{{ $row['dates'] }}</td>
                                                        <td>{{ $row['enrolled'] }}</td>
                                                        <td>{{ $row['status'] }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class='mt-4 d-flex justify-content-end gap-2'>
                                        <button type='submit' name='preview_codes' value='1' class='btn submitAdminBtn' onclick="document.getElementById('syncType').value='full'">
                                            <i class='fa fa-download'></i>&nbsp;&nbsp;{{ trans('langEduApiFullSyncBtn') }}
                                        </button>
                                        <button type='submit' name='preview_codes' value='1' class='btn submitAdminBtn' onclick="document.getElementById('syncType').value='partial'">
                                            <i class='fa fa-download'></i>&nbsp;&nbsp;{{ trans('langEduApiPartialSyncBtn') }}
                                        </button>
                                        <a href='{{ $urlAppend }}modules/admin/hierarchy.php' class='btn cancelAdminBtn'>
                                            <i class='fa fa-times'></i>&nbsp;&nbsp;{{ trans('langCancel') }}
                                        </a>
                                    </div>
                                </form>
                                <div id='loading-overlay' style='display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999;'>
                                    <div style='position:absolute; top:50%; left:50%; transform:translate(-50%, -50%); text-align:center; background: white; padding: 30px; border-radius: .3rem; box-shadow: 0 0 20px rgba(0,0,0,0.2);'>
                                        <i class='fa fa-spinner fa-spin fa-3x text-primary'></i>
                                        <div class='mt-2 fw-bold' style='font-size: 1.2rem;'>{{ trans('langEduApiPleaseWait') }}</div>
                                        <div class='text-muted small'>{{ trans('langEduApiSyncDuration') }}</div>
                                    </div>
                                </div>
                            @endif
                        @endif

                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

@if (!isset($error) && !empty($offerings_rows))
<script>
    $(function() {
        function updateCount() {
            $('#eduapi-selected-count').text($('.eduapi-offering-cb:checked').length);
        }

        $('#eduapi-select-all').on('click', function() {
            $('.eduapi-offering-cb').prop('checked', true);
            updateCount();
        });
        $('#eduapi-deselect-all').on('click', function() {
            $('.eduapi-offering-cb').prop('checked', false);
            updateCount();
        });
        $('#eduapi-select-open-active').on('click', function() {
            $('.eduapi-offering-cb').each(function() {
                $(this).prop('checked', $(this).data('open') == 1 && $(this).data('active') == 1);
            });
            updateCount();
        });
        $(document).on('change', '.eduapi-offering-cb', updateCount);

        $('#importForm').on('submit', function(e) {
            if ($('.eduapi-offering-cb:checked').length === 0) {
                e.preventDefault();
                alert('{!! addslashes(trans('langEduApiNoOfferingsSelected')) !!}');
                return false;
            }
            $('#loading-overlay').show();
        });
    });
</script>
@endif

@endsection
