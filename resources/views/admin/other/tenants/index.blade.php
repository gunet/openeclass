@extends('layouts.default')

@push('bottom_scripts')
  <script>
    $(function() {
      $('#tentants_table').DataTable ({
        'sPaginationType': 'full_numbers',
        'bAutoWidth': true,
        'oLanguage': {
          'sLengthMenu':   '{{ trans('langDisplay') }} _MENU_ {{ trans('langResults2') }}',
          'sZeroRecords':  '{{ trans('langNoResult') }}',
          'sInfo':         '{{ trans('$langDisplayed') }} _START_ {{ trans('$langTill') }} _END_ {{ trans('$langFrom2') }} _TOTAL_ {{ trans('$langTotalResults') }}',
          'sInfoEmpty':    '{{ trans('$langDisplayed') }} 0 {{ trans('$langTill') }} 0 {{ trans('$langFrom2') }} 0 {{ trans('$langResults2') }}',
          'sInfoFiltered': '',
          'sInfoPostFix':  '',
          'sSearch':       '{{ trans('$langSearch') }}',
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

            <div class='col-12'>
                @if ($tenants)
                    <div class='table-responsive'>
                        <table id='tenants_table' class='table-default'>
                            <thead>
                                <tr class='list-header'>
                                    <th scope='col'>{{ trans('langName') }}</th>
                                    <th>&nbsp;</th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach ($tenants as $tenant)
                                <tr>
                                    <td>{{ $tenant->name }}</td>
                                    <td class='option_btn_cell text-center'>
                                        {!! action_button([
                                              [ 'title' => trans('langEditChange'),
                                                'icon' => 'fa-edit',
                                                'url' => "tenant_edit.php?id=$tenant->id" ]
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
