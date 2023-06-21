<div class="panel panel-admin margin-top-fat mt-3">
    <div class="panel-heading">
        <div class="panel-title">
            {{ trans('langPortfolioMainContent') }}
        </div>
    </div>
    <div class="panel-body BordersBottom" id="portfolio_widget_main" data-widget-area-id="3">
        @foreach ($portfolio_main_area_widgets as $key => $portfolio_main_area_widget)
            <div class="panel{{!isset($myWidgets) || isset($myWidgets) && $portfolio_main_area_widget->is_user_widget ? ' panel-success widget' : ' panel-default opacity-help pe-none'}} mb-2" data-widget-id="{{ $portfolio_main_area_widget->id }}" data-widget-widget-area-id="{{ $key }}">
                <div class="panel-heading Borders">
                    <a class='text-white' data-bs-toggle="collapse" data-bs-target="#widget_desc_{{ $key }}" href="#widget_desc_{{ $key }}" class="widget_title">
                        {{ $portfolio_main_area_widget->getName() }} 
                        <span class='fa fa-arrow-down ms-1'></span>
                        <small class="float-end">{{ $portfolio_main_area_widget->is_user_widget ? trans('langWidgetPersonal') : trans('langWidgetAdmin') }}</small>
                    </a>
                </div>
                @if (!isset($myWidgets) || isset($myWidgets) && $portfolio_main_area_widget->is_user_widget)
                    <div id="widget_desc_{{ $key }}" class="panel-collapse collapse collapsed">
                        <div class="panel-body">
                            {!! $portfolio_main_area_widget->getOptionsForm($key) !!}
                        </div>
                        <div class="panel-footer clearfix d-flex justify-content-center align-items-center">
                            <a href="#" class="remove btn deleteAdminBtn">
                                {{ trans('langDelete') }}
                            </a>
                           
                            <a href="#" class="btn submitAdminBtn submitOptions ms-1">
                                {{ trans('langSubmit') }}
                            </a>
                           
                        </div>
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</div>

