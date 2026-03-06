<nav class="navbar navbar-eclass navbar-learningPath py-0 w-100 h-100" id="lp-header" data-close-url="{{ $returl_q }}" data-prev-module="{{ $prevModuleAttr }}" data-next-module="{{ $nextModuleAttr }}" data-progress="{{ $progressAttr }}">
    <div class="col-12 h-100 d-flex justify-content-between align-items-center px-3 gap-3">
        <img class="img-responsive" src="{{ $logoUrl }}" alt="Logo" style="max-width: 150px; max-height:50px;">
        <div class="progressbar-plr h-auto">
            {!! $progressBarHtml !!}
        </div>
        <div id="navigation-btns" class="d-flex justify-content-end align-items-center gap-2">
            @if ($moduleNb > 1)
                @if ($previousModule !== '')
                    <div class="prevnext">
                        <a class="btn btn-primary btn-next-prev text-decoration-none pt-2 lp-nav-link" href="viewer_noframes.php?course={{ $course_code }}&amp;path_id={{ $path_id }}&amp;module_id={{ $previousModule }}{!! $unitParam !!}" style="min-width: 35px !important; max-width: 35px !important; min-height: 30px !important; max-height: 35px !important;">
                            <span class="fa-solid fa-circle-arrow-left fa-lg"></span>
                        </a>
                    </div>
                @else
                    <div class="prevnext">
                        <a class="btn btn-primary text-decoration-none pt-2 disabled" href="#" style="min-width: 35px !important; max-width: 35px !important; min-height: 30px !important; max-height: 35px !important;">
                            <span class="fa-solid fa-circle-arrow-left fa-lg"></span>
                        </a>
                    </div>
                @endif

                @if ($nextModule !== '')
                    <div class="prevnext">
                        <a class="btn btn-primary btn-next-prev text-decoration-none pt-2 lp-nav-link" href="viewer_noframes.php?course={{ $course_code }}&amp;path_id={{ $path_id }}&amp;module_id={{ $nextModule }}{!! $unitParam !!}" style="min-width: 35px !important; max-width: 35px !important; min-height: 30px !important; max-height: 35px !important;">
                            <span class="fa-solid fa-circle-arrow-right fa-lg"></span>
                        </a>
                    </div>
                @else
                    <div class="prevnext">
                        <a class="btn btn-primary text-decoration-none pt-2 disabled" href="#" style="min-width: 35px !important; max-width: 35px !important; min-height: 30px !important; max-height: 35px !important;">
                            <span class="fa-solid fa-circle-arrow-right fa-lg"></span>
                        </a>
                    </div>
                @endif
            @endif
            <a id="lp-exit" class="btn btn-danger text-decoration-none" href="{{ $returl_q }}" style="min-height: 30px !important; max-height: 35px !important;">
                <span class="fa-solid fa-person-walking-arrow-right fa-lg"></span>
                <span class="hidden-xs">{{ $langClose }}</span>
            </a>
            <button id="lp-sidebar-toggle" class="btn submitAdminBtn d-inline-flex text-decoration-none p-0 m-0" type="button" style="min-width: 35px !important; max-width: 35px !important; min-height: 30px !important; max-height: 35px !important;">
                <span class="fa-solid fa-bars fs-6 m-0 p-0"></span>
            </button>
        </div>
    </div>
</nav>
