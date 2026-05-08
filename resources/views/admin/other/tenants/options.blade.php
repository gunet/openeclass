@extends('layouts.default')

@section('content')
    <main id="main" class="col-12 main-section">
        <div class='{{ $container }} main-container'>
            <div class="row m-auto">

                @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                @include('layouts.partials.legend_view')

                @include('layouts.partials.show_alert')

                {!! $action_bar !!}

                <div class='col-12'>

                    <form id='tenant_options_form' class='form-horizontal' role='form' action='{{ $_SERVER['SCRIPT_NAME'] }}?id={{ $tenant_id }}' enctype='multipart/form-data' method='post'>
                        <div class='container-fluid'>
                            @if (get_config('enable_white_label'))
                                <div class='form-group'>
                                    <label for='urlField' class='col-md-3 col-form-label'>
                                        {{ trans('langTenantURL') }}
                                    </label>
                                    <div class='col-md-9'>
                                        <small class='d-block text-muted'>
                                            {{ trans('langTenantURLText') }}
                                        </small>
                                        <input type='text' class='form-control' name='url' id='urlField' placeholder='https://eclass.example.com/' value='{{ $tenantUrl }}'>
                                        {!! $urlMessage !!}
                                    </div>
                                </div>

                                <div class='form-group mt-4'>
                                    <label for='platform_title' class='col-md-3 col-form-label'>
                                        {{ trans('langSiteTitle') }}
                                    </label>
                                    <div class='col-md-9'>
                                        <input type='text' class='form-control' name='platform_title' id='platform_title' value='{{ $platform_title }}'>
                                    </div>
                                </div>

                                <div class='form-group mt-4'>
                                    <label for='platform_intro' class='col-md-3 col-form-label'>
                                        {{ trans('langSiteDescr') }}
                                    </label>
                                    <div class='col-md-9'>
                                        {!! $rich_text_editor !!}
                                    </div>
                                </div>

                                <div class='form-group mt-4'>
                                    <div class='col-sm-12 control-label-notes mb-2'>{{ trans('langLogo') }}
                                        <small>{{ trans('langLogoNormal') }}</small>
                                        :
                                    </div>
                                    <div class='col-sm-12 d-inline-flex justify-content-start align-items-center'>
                                        @if ($tenantLogo)
                                            <img src='{{ $urlServer }}{{ $tenantLogo }}' style='max-height:100px;max-width:150px;' alt='Logo upload' />
                                            <a class='btn deleteAdminBtn ms-2' href='{{ $_SERVER['SCRIPT_NAME'] }}?id={{ $tenant_id }}&delete_image=imageUpload'>{{ trans('langDelete') }}</a>
                                            <input type='hidden' name='imageUpload' value='{{ $tenantLogo }}'>
                                        @else
                                            <input type='file' name='imageUpload' id='imageUpload' class='form-control-file'>
                                        @endif
                                    </div>
                                </div>
                                <div class='form-group mt-4'>
                                    <div class='col-sm-12 control-label-notes mb-2'>{{ trans('langLogo') }} <small>{{ trans('langLogoSmall') }}</small>:</div>
                                    <div class='col-sm-12 d-inline-flex justify-content-start align-items-center'>
                                        @if ($tenantLogoSmall)
                                            <img src='{{ $urlServer }}{{ $tenantLogoSmall }}' style='max-height:100px;max-width:150px;' alt='Small logo upload' /> <a class='btn deleteAdminBtn ms-2' href='{{ $_SERVER['SCRIPT_NAME'] }}?id={{ $tenant_id }}&delete_image=imageUploadSmall'>{{ trans('langDelete') }}</a>
                                            <input type='hidden' name='imageUploadSmall' value='{{ $tenantLogoSmall }}'>
                                        @else
                                            <input type='file' name='imageUploadSmall' id='imageUploadSmall' class='form-control-file'>
                                        @endif

                                    </div>
                                </div>
                                <div class='form-group mt-4'>
                                    <div class='col-sm-12 control-label-notes mb-2'>{{ trans('langFavicon') }}</div>
                                    <div class='col-sm-12 d-inline-flex justify-content-start align-items-center'>
                                        @if ($tenantFavicon)
                                            <img src='{{ $urlServer }}{{ $tenantFavicon }}' style='max-height:100px;max-width:150px;' alt='Favicon upload' /> <a class='btn deleteAdminBtn ms-2' href='{{ $_SERVER['SCRIPT_NAME'] }}?id={{ $tenant_id }}&delete_image=faviconUpload'>{{ trans('langDelete') }}</a>
                                            <input type='hidden' name='faviconUpload' value='{{ $tenantFavicon }}'>
                                        @else
                                            <label for='faviconUpload' aria-label='$langFavicon'></label><input type='file' name='faviconUpload' id='faviconUpload'>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            <div class='form-group mt-4'>
                                <label for='contact_email' class='col-md-3 col-form-label'>{{ trans('langEmail') }}</label>
                                <div class='col-md-9'>
                                    <input type='email' class='form-control' name='contact_email' id='contact_email' value='{{ $contact_email }}'>
                                </div>
                            </div>

                            <div class='form-group mt-4'>
                                <label for='contact_phone' class='col-md-3 col-form-label'>{{ trans('langPhone') }}$langPhone</label>
                                <div class='col-md-9'>
                                    <input type='tel' class='form-control' name='contact_phone' id='contact_phone' value='{{ $contact_phone }}'>
                                </div>
                            </div>

                            <div class='form-group mt-4'>
                                <label for='contact_address' class='col-md-3 col-form-label'>
                                    {{ trans('langPostMail') }}
                                </label>
                                <div class='col-md-9'>
                                    <textarea id='contact_address' name='contact_address' class='form-control' rows='3'>{{ $contact_address }}</textarea>
                                </div>
                            </div>

                            <div class='form-group mt-5'>
                                <div class='col-sm-12'>
                                    <h4 class='mb-3'>
                                        {{ trans('langConfig') }}
                                    </h4>

                                    <div class='checkbox'>
                                        <label class='label-container'>
                                            <input type='checkbox'
                                                   name='allow_teacher_clone_course'
                                                   value='1' {!! $cbox_allow_teacher_clone_course !!} >
                                            <span class='checkmark'></span>
                                            {{ trans('lang_allow_teacher_clone_course') }}
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class='form-group mt-4'>
                                <div class='col-md-9 d-flex justify-content-end'>
                                    <input class='btn btn-primary' name='optionsSave' type='submit' value='{{ trans('langSubmit') }}'>
                                </div>
                            </div>
                        </div> {!! generate_csrf_token_form_field() !!}
                    </form>

                    @if ($tenantUrl && !$tenantUrlActive)
                        <div class='modal fade' id='checkModal' tabindex='-1' aria-labelledby='checkModalLabel' aria-hidden='true'>
                            <div class='modal-dialog'>
                                <div class='modal-content'>
                                    <div class='modal-header'>
                                        <h5 class='modal-title' id='checkModalLabel'>
                                            {{ trans('langTenantActivateURL') }}
                                        </h5>
                                        <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                                    </div>
                                    <div class='modal-body text-center'>
                                        <p id='dns-check-result'>
                                            <span class='spinner-border spinner-border-sm' role='status' aria-hidden='true'></span>
                                            {{ trans('langTenantURLChecking') }}
                                        </p>
                                    </div>
                                    <div class='modal-footer'>
                                        <button type='button' class='btn btn-secondary' data-bs-dismiss='modal' id='modal-close-btn'>
                                            {{ trans('langClose') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class='container-fluid'>
                        <div class='col-md-9 mt-5 mb-5'>
                            <h4>
                                {{ trans('langAdmins') }}
                            </h4>
                            <div class='table-responsive'>
                                <table id='admins-table' class='table-default '>
                                    <thead>
                                        <tr class='list-header'>
                                            <th>ID</th>
                                            <th>{{ trans('langName') }}</th>
                                            <th>{{ trans('langSurname') }}</th>
                                            <th>{{ trans('langEmail') }}/th>
                                            <th>{{ trans('langUsername') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($tenant_admins as $admin)
                                            <tr>
                                                <td>{{ $admin->id }}</td>
                                                <td>{{ $admin->givenname }}</td>
                                                <td>{{ $admin->surname }}</td>
                                                <td>{{ $admin->email }}</td>
                                                <td>{{ $admin->username }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </main>

    <script type='text/javascript'>
        $(document).ready(function() {
            var checkModal = document.getElementById('checkModal');

            checkModal.addEventListener('shown.bs.modal', function () {
                checkServer();
            });

            var checkServer = function () {
                $.get('{{ $urlAppend }}modules/admin/check_server.php', function (data) {
                    var msg;
                    if (data == 'OK') {
                        msg = "{{ trans('langTenantURLCheckSuccess') }}<br><span class=\"spinner-border spinner-border-sm\" role=\"status\" aria-hidden=\"true\"></span> {{ trans('langTenantURLActivating') }}";
                        setTimeout(checkServer, 5000); // Poll every 5 seconds
                    } else if (data == 'ENABLED') {
                        msg = "{{ trans('langTenantURLActivated') }}";
                        $('#modal-close-btn')
                            .removeClass('btn-secondary')
                            .addClass('btn-primary')
                            .html('{{ trans("langTenantGotoURL") }}')
                            .click(function () {
                                window.location.href = '{{ $customerUrl }}';
                            });
                    } else {
                        $('#dns-check-result').removeClass('text-center');
                        msg = "{!! varmsg(trans('langTenantURLCheckFail'), ['host' => $host, 'server' => $server]) !!}";
                        $('#dns-check-result').html('<p>' + msg + '</p>');
                    }
                });
            };

            var oTable = $('#admins-table').DataTable ({
                'bStateSave': true,
                'bProcessing': false,
                'bServerSide': false,
                'sScrollX': true,
                'responsive': true,
                'searchDelay': 1000,
                'lengthMenu': [10, 15, 20 , -1],
                'fnDrawCallback': function( oSettings ) {
                    $('#admins-table_wrapper .dt-search input').attr({
                        'class' : 'form-control input-sm ms-0 mb-3',
                        'placeholder' : '{{ trans('langSearch') }}...'
                    });
                    $('#admins-table_wrapper .dt-search label').attr('aria-label', '{{ trans('langSearch') }}');
                },
                'sPaginationType': 'full_numbers',
                'bSort': false,
                'oLanguage': {
                    'lengthLabels': {
                        '-1': '{{ trans('langAllOfThem') }}'
                    },
                    'sLengthMenu':   '{{ trans('langDisplay') }} _MENU_ {{ trans('langResults2') }}',
                    'sZeroRecords':  '{{ trans('langNoResult') }}',
                    'sEmptyTable':  '{{ trans('langNoResult') }}',
                    'sInfo':         '{{ trans('langDisplayed') }} _START_ {{ trans('langTill') }} _END_ {{ trans('langFrom2') }} _TOTAL_ {{ trans('langTotalResults') }}',
                    'sInfoEmpty':    '',
                    'sInfoFiltered': '',
                    'sInfoPostFix':  '',
                    'sSearch':       '',
                    'sUrl':          '',
                    'oPaginate': {
                        'sFirst':    '&laquo;',
                        'sPrevious': '&lsaquo;',
                        'sNext':     '&rsaquo;',
                        'sLast':     '&raquo;'
                    }
                }
            });
        });
    </script>
@endsection