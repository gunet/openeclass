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
                        <h3>{{ trans('langEduApiConfirmCodesTitle') }}</h3>

                        @if ($fallback_warning)
                            <div class='alert alert-warning'>{{ $fallback_warning }}</div>
                        @endif

                        <div class='alert alert-info'>{!! sprintf(trans('langEduApiSyncTypeChosen'), '<strong>' . e($sync_type_label) . '</strong>') !!} |
                            <strong>{{ trans('langEduApiSelectedCount') }}</strong> {{ $selected_count }}</div>

                        <form method='post' action='{{ $urlAppend }}modules/eduapi/index.php' id='confirmForm'>
                            <input type='hidden' name='session' value='{{ $session_id }}'>
                            <input type='hidden' name='sync_type' value='{{ $sync_type }}'>
                            <input type='hidden' name='orgs_step' value='1'>
                            @foreach ($offering_ids as $offeringId)
                                <input type='hidden' name='offerings[]' value='{{ $offeringId }}'>
                            @endforeach
                            {!! generate_csrf_token_form_field() !!}

                            @if (!empty($org_items))
                                <h4 class='mt-4'>{{ trans('langEduApiOrgNodesTitle') }}</h4>
                                <p class='text-muted small'>{{ trans('langEduApiOrgNodesHelp') }}</p>
                                @foreach ($org_items as $item)
                                    <div class='form-check' style='margin-left: {{ $item['depth'] * 24 }}px'>
                                        <input class='form-check-input' type='checkbox' name='approved_orgs[]'
                                            value='{{ $item['id'] }}' id='{{ $item['checkbox_id'] }}' checked>
                                        <label class='form-check-label' for='{{ $item['checkbox_id'] }}'>{{ $item['name'] }}</label>
                                        @if ($item['direct_count'] > 0)
                                            <small class='text-muted'>({{ sprintf(trans('langEduApiOrgOfferings'), $item['direct_count']) }})</small>
                                        @endif
                                        @if ($item['exists'])
                                            <small class='text-muted'>({{ trans('langEduApiOrgExists') }})</small>
                                        @endif
                                    </div>
                                @endforeach
                            @endif

                            <h4 class='mt-4'>{{ trans('langEduApiSchoolNodeTitle') }}</h4>
                            <p class='text-muted small'>{{ trans('langEduApiSchoolNodeHelp') }}</p>
                            <div class='mb-2'>{!! $picker_html !!}</div>

                            <h4 class='mt-4'>{{ trans('langEduApiCodePrefix') }}</h4>
                            <p class='text-muted small'>{{ trans('langEduApiConfirmCodesIntro') }}</p>
                            <div class='table-responsive'>
                                <table class='table table-striped'>
                                    <thead>
                                        <tr>
                                            <th>{{ trans('langEduApiOrganization') }}</th>
                                            <th>{{ trans('langEduApiOfferingsCount') }}</th>
                                            <th>{{ trans('langEduApiCodePrefix') }}</th>
                                            <th>{{ trans('langEduApiExampleCode') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($prefix_rows as $row)
                                            <tr>
                                                <td>{{ $row['org_name'] }}</td>
                                                <td>{{ $row['count'] }}</td>
                                                <td>
                                                    <input type='text' class='form-control eduapi-prefix-input' name='code_prefix[{{ $row['field_key'] }}]'
                                                        value='{{ $row['prefix'] }}' maxlength='20' pattern='[A-Za-z0-9-]{1,20}'>
                                                </td>
                                                <td><code class='eduapi-example'>{{ $row['prefix'] }}-100</code></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class='mt-4 d-flex justify-content-end gap-2'>
                                <button type='submit' name='import' value='1' class='btn submitAdminBtn'>
                                    <i class='fa fa-download'></i>&nbsp;&nbsp;{{ trans('langEduApiContinueSync') }}
                                </button>
                                <a href='{{ $back_url }}' class='btn cancelAdminBtn'>
                                    <i class='fa fa-arrow-left'></i>&nbsp;&nbsp;{{ trans('langBack') }}
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
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    $(function() {
        $(document).on('input', '.eduapi-prefix-input', function() {
            $(this).closest('tr').find('.eduapi-example').text(this.value.toUpperCase() + '-100');
        });
        $('#confirmForm').on('submit', function() {
            $('#loading-overlay').show();
        });
    });
</script>

@endsection
