@push('head_scripts')
    <script type="text/javascript">
        $(document).ready(function() {
            $('#tc-recordings').DataTable ({
                "columns":  [null, null, null, null, null, null],
                "columnDefs": [
                    { "type": "date", "targets": [1] } // or "date" if it's a date
                ],
                "sPaginationType": 'full_numbers',
                "bAutoWidth": true,
                "searchDelay": 1000,
                "order" : [[1, 'desc']],
                "oLanguage": {
                        "sLengthMenu":   "{{ trans('langDisplay') }} _MENU_ {{ trans('langResults2') }}",
                        "sZeroRecords":  "{{ trans('langNoResult') }}",
                        "sInfo":         " {{ trans('langDisplayed') }} _START_ {{ trans('langTill') }} _END_ {{ trans('langFrom2') }} _TOTAL_ {{ trans('langToralResults') }}",
                        "sInfoEmpty":    " {{ trans('langDisplayed') }} 0 {{ trans('langTill') }} 0 {{ trans('langFrom2') }} 0 {{ trans('langResults2') }}",
                        "sInfoFiltered": '',
                        "sInfoPostFix":  '',
                        "sSearch":       "{{ trans('langSearch') }}",
                        "sUrl":          '',
                        "oPaginate": {
                        "sFirst":    '&laquo;',
                            "sPrevious": '&lsaquo;',
                            "sNext":     '&rsaquo;',
                            "sLast":     '&raquo;'
                        }
                },
                'tabIndex': -1,
                initComplete: function() {
                    $('#tc-recordings .dt-column-order').each(function() {
                        $(this).removeAttr('aria-label');
                        $(this).attr('aria-hidden', 'true');
                    });
                },
            });

            $('#tc-recordings').on('order.dt', function() {
                $('#tc-recordings thead .dt-column-order').each(function() {
                    $(this).removeAttr('aria-label');
                    $(this).attr('aria-hidden', 'true');
                });
            });

            $(document).on('click', '.delete-recording', function(e){
                e.preventDefault();
                var recordingID = $(this).attr('data-id');
                document.getElementById("deleteRecording").value = recordingID;
            });
        });
    </script>
@endpush

@extends('layouts.default')

@section('content')

<main id="main" class="col-12 main-section">

    <div class='{{ $container }} main-container'>
        <div class="row m-auto">

            @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

            @include('layouts.partials.legend_view')

            @if(isset($action_bar))
                {!! $action_bar !!}
            @else
                <div class='mt-4'></div>
            @endif

            @include('layouts.partials.show_alert')

            <div class='col-12'>
                @if (count($arr_recordings) > 0)
                    <div class='alert alert-info'>
                        <i class='fa-solid fa-circle-info fa-lg'></i>
                        <span>{{ trans('langTotalSizeRecordings') }}: {{ $total_size_gb }}</span>
                    </div>
                    <div class='table-responsive'>
                        <table class='table-default' id="tc-recordings">
                            <thead>
                                <tr>
                                    <th>{{ trans('langName') }}</th>
                                    <th>Record ID</th>
                                    <th>Meeting ID</th>
                                    <th>{{ trans('langLink') }}</th>
                                    <th>{{ trans('langSize') }}</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($arr_recordings as $recording)
                                    <tr>
                                        <td>{{ $recording['name'] }}</td>
                                        <td>{{ $recording['recordID'] }}</td>
                                        <td>{{ $recording['meetingID'] }}</td>
                                        <td><a target='_blank' href="{{ $recording['url'] }}">{{ trans('langViewRecording') }}</a></td>
                                        <td class='text-nowrap'>{{ $recording['size'] }}</td>
                                        <td>
                                            {!! action_button(array(
                                                array('title' => trans('langDelete'),
                                                        'url' => "#",
                                                        'icon-class' => "delete-recording",
                                                        'icon-extra' => "data-id='{$recording['recordID']}' data-bs-toggle='modal' data-bs-target='#RecordingDelete'",
                                                        'icon' => 'fa-xmark'
                                                    ))
                                            ) !!}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="alert alert-warning" style="cursor: not-allowed;">
                        <i class="fa-solid fa-triangle-exclamation fa-lg"></i>
                        <span>{{ trans('langNoAvailableRecordings') }}</span>
                    </div>
                @endif
            </div>
        </div>
    </div>
</main>


<div class='modal fade' id='RecordingDelete' tabindex='-1' aria-labelledby='RecordingDeleteLabel' aria-hidden='true'>
    <form method='post' action="{{ $urlAppend }}modules/admin/bbbmoduleconf.php?fetch_recordings=1&server_id={{ $server_id }}">
        <div class='modal-dialog modal-md'>
            <div class='modal-content'>
                <div class='modal-header'>
                    <div class='modal-title'>
                        <div class='icon-modal-default'><i class='fa-regular fa-trash-can fa-xl Accent-200-cl'></i></div>
                        <h2 class="modal-title-default text-center mb-0 mt-2" id="RecordingDeleteLabel">{!! trans('langDelete') !!}</h2>
                    </div>
                </div>
                <div class='modal-body text-center'>
                    {{ trans('langContinueToDelSession') }}
                    <input id="deleteRecording" type='hidden' name='del_recording_id'>
                    {!! generate_csrf_token_form_field() !!}
                </div>
                <div class='modal-footer d-flex justify-content-center align-items-center'>
                    <a class="btn cancelAdminBtn" href="" data-bs-dismiss="modal">{{ trans('langCancel') }}</a>
                    <button type='submit' class="btn deleteAdminBtn">{{ trans('langDelete') }}</button>
                </div>
            </div>
        </div>
    </form>
</div>

@endsection
