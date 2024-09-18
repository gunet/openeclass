<div id="leftnav" class="d-none d-md-block col-md-5 col-lg-4 sidebar  embeded float-menu">

    <div class="panel-group group-section" id="sidebar-accordion" role='tablist' aria-multiselectable='true'>
        <ul class="list-group list-group-flush">
            <li class="list-group-item px-0 mb-4 bg-transparent">
                <a class='accordion-btn d-flex justify-content-start align-items-start' role='button' data-bs-toggle='collapse' href='#collapse1' aria-expanded='true' aria-controls='#collapse1'>
                    <span class='fa-solid fa-chevron-down'></span>
                    {{ trans('langUpgradeProcess') }}

                </a>

                <div id='collapse1' class='panel-collapse accordion-collapse collapse show border-0 rounded-0' role='tabpanel' data-bs-parent='#sidebar-accordion'>
                    <div class='panel-body bg-transparent Neutral-900-cl px-4'>
                        {!! $upgrade_menu !!}
                    </div>
                </div>
            </li>
        </ul>
    </div>
</div>