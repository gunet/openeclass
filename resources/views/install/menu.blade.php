<div id="leftnav" class="d-none d-md-block col-md-5 col-lg-4 sidebar  embeded float-menu">

    <div class="panel-group" id="sidebar-accordion">
        <div class="panel">
            <a class="collapsed parent-menu" data-bs-toggle="collapse" data-bs-parent="#sidebar-accordion" href="#collapse1">
                <div class="panel-heading">
                    <h2 class="text-heading-h3 panel-title">
                        <span class="fa fa-chevron-right"></span>
                        <span><strong>{{ trans('langInstallProgress') }}</strong></span>
                    </h2>
                </div>
            </a>

            <div id="collapse1" class="panel-collapse list-group collapse in show">
                {!! $installer_menu !!}
            </div>

        </div>

    </div>
</div>
