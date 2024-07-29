@push('head_scripts')
    <script type='text/javascript'>
        $(document).ready(function() {
            $('#user_last_logins').DataTable({
                'sPaginationType': 'full_numbers',
                'bAutoWidth': true,
                'searchDelay': 1000,
                'aoColumns': [
                    {'bSortable' : false, 'sWidth': '70%' },
                    {'bSortable' : true },
                    {'bSortable' : false },
                ],
                'order' : [],
                'oLanguage': {
                    'sLengthMenu': '{{ trans('langDisplay') }} _MENU_ {{ trans('langResults2') }}',
                    'sZeroRecords': '{{ trans('langNoResult') }}',
                    'sInfo': '{{ trans('langDisplayed') }} _START_ {{ trans('langTill') }} _END_ {{ trans('langFrom2') }} _TOTAL_ {{ trans('langTotalResults') }}',
                    'sInfoEmpty': '{{ trans('langDisplayed') }} 0 {{ trans('langTill') }} 0 {{ trans('langFrom2') }} 0 {{ trans('langResults2') }}',
                    'sInfoFiltered': '',
                    'sInfoPostFix': '',
                    'sSearch': '',
                    'sUrl': '',
                    'oPaginate': {
                        'sFirst': '&laquo;',
                        'sPrevious': '&lsaquo;',
                        'sNext': '&rsaquo;',
                        'sLast': '&raquo;'
                    }
                }
            });
            $('.dataTables_filter input').attr({
                'class': 'form-control input-sm ms-0 mb-3',
                'placeholder': '{{ trans('langSearch') }}...',
                'aria-label' : '{{ trans('langSearch') }}'
            });
        });

    </script>
@endpush

@extends('layouts.default')

@section('content')

    <div class="col-12 main-section">
        <div class='{{ $container }} main-container'>
            <div class="row m-auto">

                @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                @include('layouts.partials.legend_view')

                {!! $action_bar !!}

                <div class='col-12'>
                    @if (count($result) > 0)
                        <div class='table-responsive'>
                            <table id='user_last_logins' class='table-default'><thead><tr class='list-header'>
                                <th>{{ trans('langUserLastLogins') }} <small>({{ trans('langLastYear') }})</small></th><th>{{ trans('langAction') }}</th><th>{{ trans('langIpAddress') }}</th>
                            </tr>
                            <tbody>
                            @foreach ($result as $lastVisit)
                                <tr>
                                    <td> {{ format_locale_date(strtotime($lastVisit->when))  }} </td>
                                    <td> {{ action_text($lastVisit->action) }} </td>
                                    <td>{{ $lastVisit->ip }}</td>
                                </tr>
                            @endforeach
                            </tbody></table>
                        </div>
                    @else
                        <div class='alert alert-info'>
                            <i class='fa-solid fa-circle-info fa-lg'></i>
                            <span>{{ trans('langNoUserLastLogins') }}</span>
                        </div>
                    @endif
                </div>

            </div>
        </div>
    </div>
@endsection
