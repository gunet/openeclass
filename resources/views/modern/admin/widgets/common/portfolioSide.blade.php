<div class="panel panelCard px-lg-4 py-lg-3 mt-3">
    <div class="panel-heading border-0 d-flex justify-content-between align-items-center gap-3 flex-wrap">
        <h3 class='mb-0'>
            {{ trans('langPortfolioSidebarContent') }}
        </h3>
    </div>
    <div class="panel-body" id="portfolio_widget_sidebar" data-widget-area-id="4">
        @php $countWidgets = 0; @endphp
        @foreach ($portfolio_sidebar_widgets as $key => $portfolio_sidebar_widget)
        <div class="panel panel-success widget rounded-0 @if($countWidgets < (count($portfolio_sidebar_widgets) -1)) mb-3 @endif" data-widget-id="{{ $portfolio_sidebar_widget->id }}" data-widget-widget-area-id="{{ $key }}">
            <div class="panel-heading rounded-0">
                <a class='text-white' data-bs-toggle="collapse" data-bs-target="#widget_desc_{{ $key }}" href="#widget_desc_{{ $key }}" class="widget_title">
                    {{ $portfolio_sidebar_widget->getName() }} 
                    <span class='fa fa-arrow-down ms-1'></span>
                </a>
            </div>
            <div id="widget_desc_{{ $key }}" class="panel-collapse collapse in collapsed">
                <div class="panel-body rounded-0">
                    {!! $portfolio_sidebar_widget->getOptionsForm($key,$final_data_portfolioSide_widget[$countWidgets]) !!}
                </div>
                <div class="panel-footer clearfix d-flex justify-content-start align-items-center gap-2 flex-wrap">
                    <a href="#" class="remove btn deleteAdminBtn">
                        {{ trans('langDelete') }}
                    </a>
                   
                    <a href="#" class="btn submitAdminBtn submitOptions ms-1">
                        {{ trans('langSubmit') }}
                    </a>
                    
                </div>
            </div>
        </div>
        @php $countWidgets++; @endphp
        @endforeach
    </div>
</div>
