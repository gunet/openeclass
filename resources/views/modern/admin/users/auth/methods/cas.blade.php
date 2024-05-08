@push('head_styles')
    <style>
        .select2-container {width:100%!important;margin-bottom: 20px;}
        .select2-container .select2-selection--single .select2-selection__rendered {font-size:12px;}
        #cas_gunet_table_info {display: none}
        #cas_gunet_table {margin-bottom: 20px;}
    </style>
@endpush

@push('head_scripts')
    <script>
        $(document).ready(function() {
            if ($('#cas_gunet').prop('checked')) {
                $('.cas_gunet_container, .cas_port, .cas_logout, .cas_ssout, .cas_context, .casusermailattr, .casuserfirstattr, .casuserlastattr, .casuserstudentid, .cas_altauth, .cas_altauth_use').toggleClass('hide');
            }
            $('#cas_gunet').change(function() {
                $('.cas_gunet_container, .cas_port, .cas_logout, .cas_ssout, .cas_context, .casusermailattr, .casuserfirstattr, .casuserlastattr, .casuserstudentid, .cas_altauth, .cas_altauth_use').toggleClass('hide');
            });

            var data_table = $('#cas_gunet_table').dataTable({
                order: [1, 'asc'],
                searching: false,
                paging: false,
                columnDefs: [
                    { width: '40%', targets: [0, 2] },
                    { width: '10%', targets: [1, 3] },
                    { sortable: false, targets: 4 },
                ],
            });

            $('body').on('click', '.delete-entry', function (e) {
                e.preventDefault();
                let entry_id = $(this).data('id');
                let currentData = $('input[name=\"minedu_department_association\"]').val();
                let dataArray = currentData ? JSON.parse(currentData) : [];

                if (entry_id == '-') {
                    entry_id = '0';
                }
                dataArray = dataArray.filter((val) => val.minedu_id != entry_id);
                $('input[name=\"minedu_department_association\"]').val(JSON.stringify(dataArray));

                $(this).closest('tr').remove();
            });

            $('#minedu_School').prop('disabled', true).select2();

            $('#minedu_institution').select2({
                placeholder: '{{ trans('langWelcomeSelect') }}',
                allowClear: true,
            }).on('select2:select', function (e) {
                $('#minedu_School').val('').trigger('change');
                var selectedInstitution = $(this).val();

                if (selectedInstitution) {
                    $('#minedu_School').prop('disabled', false);
                    $('#minedu_School').select2({
                        allowClear: true,
                        placeholder: '" . js_escape($langWelcomeSelect) . "',
                        ajax: {
                            url: 'get_minedu_departments.php',
                            dataType: 'json',
                            delay: 250,
                            data: {
                                qtype: 'School',
                                Institution: selectedInstitution
                            },
                            processResults: function (data) {
                                console.log('School',data)
                                return {
                                    results: [{ text: '{{ trans('langDefaultCategory') }}', id: '0'}].concat(
                                        data.map(function (item) {
                                            return { text: item.Department, id: item.MineduID };
                                        })
                                    )
                                };
                            }
                        }
                    });
                } else {
                    $('#minedu_School').prop('disabled', true).empty();
                }
            }).trigger('select2:select');

            $('#cas_gunet_add').on('click', function(e) {
                e.preventDefault();
                let minedu_School = $('#minedu_School').select2('data');
                let minedu_School_text = minedu_School[0].text;
                let minedu_School_id = minedu_School[0].id;

                let local_dep_id = $( 'input[name=\"department[]\"]' ).val();
                let local_dep_text = $( '#dialog-set-value' ).val();

                if (minedu_School_id == '0') {
                    minedu_School_id = '-';
                }

                var table = $('#cas_gunet_table').DataTable();
                let newRow = table.row.add([
                    minedu_School_text,
                    minedu_School_id,
                    local_dep_text,
                    local_dep_id,
                    '<button class=\"btn btn-xs delete-entry btn-danger\" title=\"Διαγραφή\" data-id=\"' + minedu_School_id + '\"><span class=\"fa fa-times\"></span></button>'
                ]).draw();

                let currentData = $('input[name=\"minedu_department_association\"]').val();
                let dataArray = currentData ? JSON.parse(currentData) : [];

                dataArray.push({
                    'minedu_id': minedu_School_id,
                    'department_id': local_dep_id
                });
                $('input[name=\"minedu_department_association\"]').val(JSON.stringify(dataArray));
                $('#minedu_School').val('').trigger('change.select2');
            });
        });
    </script>
@endpush

<div class='form-group'>
    <label for='cas_host' class='col-sm-12 control-label-notes'>{{ trans('langcas_host') }}</label>
    <div class='col-sm-12'>
        <input class='form-control' placeholder="{{ trans('langcas_host') }}..." name='cas_host' id='cas_host' type='text' value='{{ isset($auth_data['cas_host']) ? $auth_data['cas_host'] : '' }}'>
    </div>
</div>

<div class='form-group mt-3'>
    <label for='cas_host' class='col-sm-2 control-label'>GUNet:</label>
    <div class='col-sm-10'>
        <input type='checkbox' name='cas_gunet' id='cas_gunet' value='1' {!! $checked !!}>
        <label for='cas_gunet'>{{ trans('langCASGUnetIdentity') }}</label>
        <div class='cas_gunet_container hide'>
            <div>
                <label for='minedu_Institution'>{{ trans('langInstitution') }}</label>
                <select id='minedu_Institution' name='minedu_Institution'></select>
            </div>
            <table id='cas_gunet_table'>
                <thead>
                    <tr>
                        <th>{{ trans('langSchoolDepartment') }}</th>
                        <th>Minedu ID</th>
                        <th>{{ trans('langFaculty') }}</th>
                        <th>ID</th>
                    </tr>
                </thead>
                <tbody>
                    @if ($result)
                        @foreach ($result as $r)
                            <tr>
                                <td>{{ $r->School_Department }}</td>
                                <td>{{ $r->minedu_id }}</td>
                                <td>{{ getSerializedMessage($r->name) }}</td>
                                <td>{{ $r->department_id }}</td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
            <div>
                <div>
                    <div>
                        <label for='minedu_Institution'>{{ trans('langSchoolDepartment') }}</label>
                        <select id='minedu_School'></select>
                    </div>
                    <div>
                        <label for=''>{{ trans('langLocalCategory') }}</label>
                            $html
                    </div>
                </div>
                <button id='cas_gunet_add' class='btn btn-primary'>$langAdd</button>
                <input type='hidden' name='minedu_department_association' value='{{ $minedu_department_association }}'>
            </div>
        </div>
    </div>
</div>

<div class='form-group mt-3'>
    <label for='cas_port' class='col-sm-12 control-label-notes'>{{ trans('langcas_port') }}</label>
    <div class='col-sm-12'>
        <input class='form-control' placeholder="{{ trans('langcas_port') }}..." name='cas_port' id='cas_port' type='text' value='{{ isset($auth_data['cas_port']) ? $auth_data['cas_port'] : '443' }}'>
    </div>
</div>



<div class='form-group mt-3'>
    <label for='cas_context' class='col-sm-12 control-label-notes'>{{ trans('langcas_context') }}</label>
    <div class='col-sm-12'>
        <input class='form-control' placeholder="{{ trans('langcas_context') }}..." name='cas_context' id='cas_context' type='text' value='{{ isset($auth_data['cas_context']) ? $auth_data['cas_context'] : '' }}'>
    </div>
</div>



<div class='form-group mt-3'>
    <label for='cas_logout' class='col-sm-12 control-label-notes'>{{ trans('langcas_logout') }}</label>
    <div class='col-sm-12'>
        <input class='form-control' placeholder="{{ trans('langcas_logout') }}..." name='cas_logout' id='cas_logout' type='text' value='{{ isset($auth_data['cas_logout']) ? $auth_data['cas_logout'] : '' }}'>
    </div>
</div>



<div class='form-group mt-3'>
    <label for='cas_logout' class='col-sm-12 control-label-notes'>{{ trans('langcas_ssout') }}</label>
    <div class='col-sm-12'>
        {!! selection(
            [
                0 => trans('langNo'),
                1 => trans('langYes')
            ],
            'cas_ssout', isset($auth_data['cas_ssout']) ? $auth_data['cas_ssout'] : 0, 'class="form-control"') !!}
    </div>
</div>



<div class='form-group mt-3'>
    <label for='cas_cachain' class='col-sm-12 control-label-notes'>{{ trans('langcas_cachain') }}</label>
    <div class='col-sm-12'>
        <input class='form-control' placeholder="{{ trans('langcas_cachain') }}..." name='cas_cachain' id='cas_cachain' type='text' value='{{ isset($auth_data['cas_cachain']) ? $auth_data['cas_cachain'] : '' }}'>
    </div>
</div>



<div class='form-group mt-3'>
    <label for='casusermailattr' class='col-sm-12 control-label-notes'>{{ trans('langcasusermailattr') }}</label>
    <div class='col-sm-12'>
        <input class='form-control' placeholder="{{ trans('langcasusermailattr') }}..." name='casusermailattr' id='casusermailattr' type='text' value='{{ isset($auth_data['casusermailattr']) ? $auth_data['casusermailattr'] : 'mail' }}'>
    </div>
</div>



<div class='form-group mt-3'>
    <label for='casuserfirstattr' class='col-sm-12 control-label-notes'>{{ trans('langcasuserfirstattr') }}</label>
    <div class='col-sm-12'>
        <input class='form-control' placeholder="{{ trans('langcasuserfirstattr') }}..." name='casuserfirstattr' id='casuserfirstattr' type='text' value='{{ isset($auth_data['casuserfirstattr']) ? $auth_data['casuserfirstattr'] : 'givenName' }}'>
    </div>
</div>



<div class='form-group mt-3'>
    <label for='casuserlastattr' class='col-sm-12 control-label-notes'>{{ trans('langcasuserlastattr') }}</label>
    <div class='col-sm-12'>
        <input class='form-control' placeholder="{{ trans('langcasuserlastattr') }}..." name='casuserlastattr' id='casuserlastattr' type='text' value='{{ isset($auth_data['casuserlastattr']) ? $auth_data['casuserlastattr'] : 'sn' }}'>
    </div>
</div>



<div class='form-group mt-3'>
    <label for='casuserstudentid' class='col-sm-12 control-label-notes'>{{ trans('langcasuserstudentid') }}</label>
    <div class='col-sm-12'>
        <input class='form-control' placeholder="{{ trans('langcasuserstudentid') }}..." name='casuserstudentid' id='casuserstudentid' type='text' value='{{ isset($auth_data['casuserstudentid']) ? $auth_data['casuserstudentid'] : '' }}'>
    </div>
</div>



<div class='form-group mt-3'>
    <label for='cas_altauth' class='col-sm-12 control-label-notes'>{{ trans('langcas_altauth') }}:</label>
    <div class='col-sm-12'>
        {!! selection(
            [
                0 => '-',
                1 => 'eClass',
                2 => 'POP3',
                3 => 'IMAP',
                4 => 'LDAP',
                5 => 'External DB'
            ],
            'cas_altauth', isset($auth_data['cas_altauth']) ? $auth_data['cas_altauth'] : 0, 'class="form-control"') !!}
    </div>
</div>

