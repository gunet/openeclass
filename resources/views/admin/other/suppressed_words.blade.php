@extends('layouts.default')

@section('content')

<main id="main" class="col-12 main-section">
    <div class="{{ $container }} main-container">
        <div class="row m-auto">

            @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

            @include('layouts.partials.legend_view')

            @if(isset($action_bar))
                {!! $action_bar !!}
            @else
                <div class='mt-4'></div>
            @endif

            @include('layouts.partials.show_alert')

            <div class="col-12 mt-4">
                <div class="card shadow-sm-eclass border-0">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="suppressed_words_table" class="table-default">
                                <thead>
                                    <tr>
                                        <th>{{ trans('langWord') }}</th>
                                        <th>{{ trans('langCreator') }}</th>
                                        <th>{{ trans('langDate') }}</th>
                                        <th class='text-end'>{{ trans('langActions') }}</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 mt-4">
                <div class="card shadow-sm-eclass border-0">
                    <div class="card-body">
                        <h3 class="card-title h5 mb-3">{{ trans('langAdd') }} (Bulk)</h3>
                        <form action="{{ $_SERVER['SCRIPT_NAME'] }}" method="POST">
                            {!! generate_csrf_token_form_field() !!}
                            <div class="form-group mb-3">
                                <label for="bulk_words" class="form-label mb-2">Εισαγωγή λέξεων (μία ανά γραμμή):</label>
                                <textarea name="bulk_words" id="bulk_words" rows="10" class="form-control" placeholder="Λέξη 1&#10;Λέξη 2&#10;..."></textarea>
                            </div>
                            <div class="text-end">
                                <button type="submit" name="submit_bulk" class="btn btn-primary submitAdminBtn">
                                    <i class="fa-solid fa-plus-circle me-1"></i> {{ trans('langAdd') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
    $(document).ready(function() {
        var table = $('#suppressed_words_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ $_SERVER["SCRIPT_NAME"] }}?ajax=1',
                type: 'POST'
            },
            language: {
                lengthMenu: "{{ trans('langDisplay') }} _MENU_ {{ trans('langResults2') }}",
                zeroRecords: "{{ trans('langNoResult') }}",
                info: "{{ trans('langDisplayed') }} _START_ {{ trans('langTill') }} _END_ {{ trans('langFrom2') }} _TOTAL_ {{ trans('langTotalResults') }}",
                infoEmpty: "{{ trans('langNoResult') }}",
                infoFiltered: "(filtered from _MAX_ total records)",
                search: "{{ trans('langSearch') }}",
                paginate: {
                    first: "&laquo;",
                    last: "&raquo;",
                    next: "&rsaquo;",
                    previous: "&lsaquo;"
                }
            },
            columns: [
                { sortable: true },
                { sortable: true },
                { sortable: true },
                { sortable: false }
            ],
            order: [[2, 'desc']],
            drawCallback: function(settings) {
                popover_init();
            }
        });

        // Handle delete confirmation for dynamically loaded rows
        $(document).on('click', '.confirmAction', function (e) {
            e.preventDefault();
            e.stopPropagation();

            var message = $(this).attr('data-message');
            var title = $(this).attr('data-title');
            var cancel_text = $(this).attr('data-cancel-txt');
            var action_text = $(this).attr('data-action-txt');
            var action_btn_class = $(this).attr('data-action-class');
            var form = $(this).closest('form').clone().appendTo('body');

            var $icon = '';
            if (action_btn_class == 'deleteAdminBtn') {
                $icon = "<div class='icon-modal-default'><i class='fa-regular fa-trash-can fa-xl Accent-200-cl'></i></div>";
            }

            bootbox.dialog({
                closeButton: false,
                message: "<p class='text-center'>" + message + "</p>",
                title: $icon + "<h2 class='modal-title-default text-center mb-0'>" + title + "</h2>",
                buttons: {
                    cancel_btn: {
                        label: cancel_text,
                        className: "cancelAdminBtn position-center"
                    },
                    action_btn: {
                        label: action_text,
                        className: action_btn_class + " " + "position-center",
                        callback: function () {
                            form.submit();
                        }
                    }
                }
            });
        });
    });
</script>
@endsection
