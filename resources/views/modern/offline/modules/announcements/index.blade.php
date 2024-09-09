@extends('layouts.default')

@push('head_scripts')
<script src="{{ $urlAppend }}js/datatables/media/js/jquery.dataTables.min.js"></script>
<script src="{{ $urlAppend }}js/trunk8.js"></script>
<script type='text/javascript'>
    $(document).ready(function() {

        var oTable = $('#ann_table').DataTable ({
            'sScrollX': true,
            'responsive': true,
            'searchDelay': 1000,
            'sPaginationType': 'full_numbers',
            'bSort': false,
            'oLanguage': {
                'sLengthMenu':   '{{ trans('langDisplay') }} _MENU_ {{ trans('langResults2') }}',
                'sZeroRecords':  '{{ trans('langNoResult') }}',
                'sInfo':         '{{ trans('langDisplayed') }} _START_ {{ trans('langTill') }} _END_ {{ trans('langFrom2') }} _TOTAL_ {{ trans('langTotalResults') }}',
                'sInfoEmpty':    '{{ trans('langDisplayed') }} 0 {{ trans('langTill') }} 0 {{ trans('langFrom2') }} 0 {{ trans('langResults2') }}',
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
<script>
    var readMore = '".js_escape($langReadMore)."';
    var readLess = '".js_escape($langReadLess)."';
    $(function () { $('.trunk8').trunk8({
        lines: 3,
        fill: '&hellip; <a class="read-more" href="#">{{ js_escape($GLOBALS['langViewShow']) }}</a>',
    });

        $(document).on('click', '.read-more', function (event) {
            $(this).parent().trunk8('revert').append(' <a class="read-less" href="#">{{ js_escape($GLOBALS['langViewHide']) }}</a>');
            event.preventDefault();
        });

        $(document).on('click', '.read-less', function (event) {
            $(this).parent().trunk8();
            event.preventDefault();
        });

    });
</script>
@endpush

@push('head_styles')
<link rel='stylesheet' type='text/css' href="{{ $urlAppend }}js/datatables/media/css/jquery.dataTables.css" />
@endpush

@section('content')

<div class="col-12 main-section">
<div class='container module-container py-lg-0'>
        <div class="course-wrapper d-lg-flex align-items-lg-strech w-100">

            @include('layouts.partials.left_menu')

            <div class="col_maincontent_active col_maincontent_active_module">

                <div class="row">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="collapseTools">
                        <div class="offcanvas-header">
                            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="{{ trans('langClose') }}"></button>
                        </div>
                        <div class="offcanvas-body">
                            @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                        </div>
                    </div>

                    @include('layouts.partials.legend_view')


                    <div class="col-12">
                        <div class="table-responsive">
                            <table id='ann_table' class='table-default'>
                                <thead>
                                    <tr class='list-header'>
                                        <th>{{ trans('langAnnouncement') }}</th>
                                        <th>{{ trans('langDate') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($announcements as $ann)
                                        <tr>
                                            <td><div class='table_td'>
                                                    <div class='table_td_header clearfix'>
                                                        <a href="announcement/{{ $ann->id }}.html"> {!! standard_text_escape($ann->title) !!}</a>
                                                    </div>
                                                    <div class='table_td_body' data-id='{{$ann->id}}'>{!! standard_text_escape($ann->content) !!} </div>
                                                </div>
                                            </td>
                                            <td>
                                                {{ format_locale_date(strtotime($ann->date)) }}
                                            </td>
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
</div>


@endsection
