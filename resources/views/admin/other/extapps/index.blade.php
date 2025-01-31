@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
    <div class='{{ $container }} main-container extapps-container'>
        <div class="row m-auto">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])
                    

                    @include('layouts.partials.legend_view')

                    @if(isset($action_bar))
                        {!! $action_bar !!}
                    @else
                        <div class='mt-4'></div>
                    @endif

                    @include('layouts.partials.show_alert') 

                    <div class="extapp">

                        <div class="col-12 mb-3 d-flex d-lg-none col-md-6 col-sm-12">
                            <select name="filter-dropdown" class="filter-dropdown form-select">
                                <option value="dropdown-all" data-category="all">{{ trans('langExtAppAll') }}</option>
                                @foreach (array_keys(ExtAppManager::$AppCategories) as $category)
                                    <option value="{{ $category }}" data-category="{{ $category }}">{{ trans('langExtApp' . ucfirst($category)) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 mb-3 d-flex gap-1 d-none d-lg-flex">
                            <button class="btn btn-success filter-btn" data-category="all">{{ trans('langExtAppAll') }}</button>
                            @foreach (array_keys(ExtAppManager::$AppCategories) as $category)
                                <button class="btn btn-primary filter-btn" data-category="{{ $category }}">{{ trans('langExtApp' . ucfirst($category)) }}</button>
                            @endforeach
                        </div>
                        <div class='col-12 table-responsive'>
                            <table class="table-default extapp-table">
                                <thead class='list-header'>
                                    <th>{{ trans('langExtAppName') }}</th>
                                    <th>{{ trans('langDescription') }}</th>
                                </thead>
                                <tbody>
                                @foreach (ExtAppManager::getApps() as $app)
                                    <tr data-category="{{ ExtAppManager::getAppCategory($app->getName()) }}">
                                    <!--WARNING!!!! LEAVE THE SIZE OF THE IMAGE TO BE DOUBLE THE SIZE OF THE ACTUAL PNG FILE, TO SUPPORT HDPI DISPLAYS!!!!-->
                                        <td style="width:140px; padding:0px;">
                                            <div style="padding:10px;">
                                                <a class="appIcon d-flex flex-column justify-content-center align-items-center" href="{{ $urlAppend . $app->getConfigUrl() }}">
                                                @if ($app->getAppIcon() !== null)
                                                    <div class="d-flex flex-column justify-content-center align-items-center" style="width:89px;height: 89px">
                                                        <img width="89" src="{{ $app->getAppIcon() }}" alt="{{ trans('langTool') }}:{{ $app->getName() }}">
                                                    </div>
                                                @endif
                                                <span class="fw-bold text-center">{{ $app->getDisplayName() }}</span>
                                                </a>
                                            </div>
                                        </td>

                                        <td class="text-muted clearfix">
                                            <div class="extapp-dscr-wrapper">
                                                {!! $app->getShortDescription() !!}
                                            </div>
                                            <div class="extapp-controls mt-2">
                                                <div class="btn-group btn-group-sm gap-2">
                                                    @if ($app->isConfigured())
                                                        @if (showSecondFactorChallenge() != "")
                                                            <button aria-label="{{ $app->getName() }}" onclick="var totp=prompt('Type 2FA:','');this.setAttribute('data-app', this.getAttribute('data-app')+','+escape(totp));"  type="button" class="btn{!! $app->isEnabled() ? ' submitAdminBtn' : ' deleteAdminBtn' !!} extapp-status" data-app="{{ getIndirectReference($app->getName()) }}">
                                                        @else
                                                            <button aria-label="{{ $app->getName() }}" type="button" class="btn{!! $app->isEnabled() ? ' submitAdminBtn' : ' deleteAdminBtn' !!} extapp-status" data-app="{{ getIndirectReference($app->getName()) }}">
                                                        @endif
                                                            {!! $app->isEnabled() ? '<i class="fa fa-toggle-on"></i>' : '<i class="fa fa-toggle-off"></i>' !!}
                                                        </button>
                                                    @else
                                                        <button aria-label="{{ $app->getName() }}" type="button" class="btn cancelAdminBtn" data-app="{{ getIndirectReference($app->getName()) }}"  data-bs-toggle='modal' data-bs-target='#noSettings'>
                                                            <i class="fa fa-warning"></i>
                                                        </button>
                                                    @endif
                                                    <a href="{{ $urlAppend . $app->getConfigUrl() }}" class="btn submitAdminBtn" aria-label="Exterior app">
                                                        <i class="fa fa-sliders fw"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class='modal fade' id='noSettings' tabindex='-1' role='dialog' aria-labelledby='myModalLabel'>
                        <div class='modal-dialog' role='document'>
                            <div class='modal-content'>
                                <div class='modal-header'>
                                    <div class='modal-title' id='myModalLabel'>{{ trans('langNotConfigured') }}</div>
                                    <button type='button' class='close' data-bs-dismiss='modal' aria-label="{{ trans('langClose') }}">
                                    </button>
                                    
                                </div>
                                <div class='modal-body'>
                                {{ trans('langEnableAfterConfig') }}
                                </div>
                            </div>
                        </div>
                    </div>
               
        </div>
    </div>
</div>


<script>
     // External Apps activate/deactivate button
     $('.extapp-status').on('click', function () {
        var url = window.location.href;
        var button = $(this).children('i');
        var state = button.hasClass('fa-toggle-on') ? "fa-toggle-on" : "fa-toggle-off";
        var appName = button.parent('button').attr('data-app');

        button.removeClass(state).addClass('fa-spinner fa-spin');

        $.post( url,
                {state: state,
                 appName: appName},
                function (data) {
                    var newState = (data === "0")? "fa-toggle-off":"fa-toggle-on";
                    button.removeClass('fa-spinner fa-spin').addClass(newState);
                    btnColorState = button.parent('button').hasClass('submitAdminBtn')?'submitAdminBtn':'deleteAdminBtn';
                    newBtnColorState = button.parent('button').hasClass('submitAdminBtn')?'deleteAdminBtn':'submitAdminBtn';
                    button.parent('button').removeClass(btnColorState).addClass(newBtnColorState);
                });
    });

     $('.filter-dropdown').on('change', function () {
         var category = $(this).find('option:selected').data('category');
         $('.filter-btn').removeClass('btn-success').addClass('btn-primary');
         $('.filter-btn[data-category="' + category + '"]').removeClass('btn-primary').addClass('btn-success');
         if (category === 'all') {
             $('tr[data-category]').removeClass('d-none');
         } else {
             $('tr[data-category]').addClass('d-none');
             $('tr[data-category="' + category + '"]').removeClass('d-none');
         }
     });

     $('.filter-btn').on('click', function () {
         var category = $(this).data('category');
         $('.filter-btn').removeClass('btn-success').addClass('btn-primary');
         $(this).removeClass('btn-primary').addClass('btn-success');
         $('.filter-dropdown option').removeAttr('selected');
         $('.filter-dropdown option[data-category="' + category + '"]').attr('selected', 'selected');
         $('.filter-dropdown').change();
         if (category === 'all') {
             $('tr[data-category]').removeClass('d-none');
         } else {
             $('tr[data-category]').addClass('d-none');
             $('tr[data-category="' + category + '"]').removeClass('d-none');
         }
     });

</script>
@endsection
