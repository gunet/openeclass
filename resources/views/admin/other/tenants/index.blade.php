@extends('layouts.default')

@push('bottom_scripts')
  <script>
    $(function() {
      $('#tenants_table').DataTable ({
        'sPaginationType': 'full_numbers',
        'bAutoWidth': true,
        'oLanguage': {
          'sLengthMenu':   '{{ trans('langDisplay') }} _MENU_ {{ trans('langResults2') }}',
          'sZeroRecords':  '{{ trans('langNoResult') }}',
          'sInfo':         '{{ trans('langDisplayed') }} _START_ {{ trans('langTill') }} _END_ {{ trans('langFrom2') }} _TOTAL_ {{ trans('langTotalResults') }}',
          'sInfoEmpty':    '',
          'sInfoFiltered': '',
          'sInfoPostFix':  '',
          'sSearch':       '{{ trans('langSearch') }}',
          'sUrl':          '',
          'oPaginate': {
            'sFirst':    '&laquo;',
            'sPrevious': '&lsaquo;',
            'sNext':     '&rsaquo;',
            'sLast':     '&raquo;'
          }
        },
        'columnDefs': [
            { 
              'orderable': false, 
              'targets': [5] 
            }
          ]
      });
    });
  </script>
@endpush

@section('content')
<div class="col-12 main-section">
    <div class='{{ $container }} main-container'>
        <div class="row m-auto">

            @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])
            @include('layouts.partials.legend_view')

            @include('layouts.partials.show_alert')

            {!! action_bar([
                  [ 'title' => trans('langAddTenant'),
                    'url' => "tenant_edit.php",
                    'icon' => 'fa-solid fa-plus-circle',
                    'level' => 'primary-label',
                    'button-class' => 'btn-success' ],
                ]) !!}

            @if (!$disk_usage_cron_running)
                @include('admin.other.tenants.tenants_cron_modal')
            @endif

            <div class='col-12'>
                @if ($tenants)
                    <div class='table-responsive'>
                        <table id='tenants_table' class='table-default'>
                            <thead>
                                <tr class='list-header'>
                                    <th scope='col'>ID</th>
                                    <th scope='col'>{{ trans('langName') }}</th>
                                    <th scope='col'>{{ trans('langTenantURL') }}</th>
                                    <th scope='col'>{{ trans('langNbUsers') }}</th>
                                    <th scope='col'>{{ trans('langLectNum') }}</th>
                                    <th scope='col'>{{ trans('langDiskUsage') }}</th>
                                    <th>&nbsp;</th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach ($tenants as $tenant)
                                @php $disk_usage = formatBytes($tenant->disk_usage); @endphp

                                <tr>
                                    <td>{{ $tenant->id }}</td>
                                    <td>{{ $tenant->name }}</td>
                                    <td>{{ $tenant->url }}</td>
                                    <td>{{ $tenant->total_users }}</td>
                                    <td>{{ $tenant->total_courses }}</td>
                                    <td>{{ $disk_usage }}</td>
                                    <td class='option_btn_cell text-center'>
                                        {!! action_button([
                                              [ 'title' => trans('langEditChange'),
                                                'icon' => 'fa-edit',
                                                'url' => "tenant_edit.php?id=$tenant->id" ],
                                              [ 'title' => trans('langTenantProfile'),
                                                'icon' => 'fa-edit',
                                                'url' => "tenant_options.php?id=$tenant->id" ],
                                            ]) !!}
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                  <div class='alert alert-info'><i class='fa-solid fa-info-circle fa-lg'></i><span>{{ trans('langNoTenants') }}</span></div></div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
