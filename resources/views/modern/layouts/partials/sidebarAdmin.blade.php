<!------------------------------------------------------------------------------------------------>
<!------------------------------------------------------------------------------------------------>
<!--------------------------------------- Admin panels ------------------------------------------->
<!------------------------------------------------------------------------------------------------>
<!------------------------------------------------------------------------------------------------>

@if ($release_info and version_compare($release_info->release, ECLASS_VERSION) > 0)
    <div class='col-12 mb-4'>
        <div class='card panelCard px-lg-4 py-lg-3'>
            <div class='card-header border-0 bg-white d-flex justify-content-between align-items-center'>
                <h3>{{ trans('langNewEclassVersion') }}</h3>
            </div>
            <div class='card-body'>
                {!! sprintf( trans('langNewEclassVersionInfo'), "<strong>" . q($release_info->release) . "</strong>",
                            "<a href='https://www.openeclass.org/' target='_blank'>www.openeclass.org</a>") !!}
            </div>
        </div>
    </div>
@endif


<div class='col-12 d-flex justify-content-md-start justify-content-center align-items-start gap-3 flex-wrap pb-4'>

    <a class='quickLink' href="search_user.php">
        <i class="fa-solid fa-user Primary-500-cl settings-icon"></i>{{ trans('langSearchUser') }}
    </a>

    @if($is_admin or $is_departmentmanage_user or $is_power_user)
        <a  href="searchcours.php" class='quickLink'>
            <i class="fa-solid fa-book-open Primary-500-cl settings-icon"></i>{{ trans('langSearchCourse') }}
        </a>
    @else
        <a tabindex="0" role="button" data-bs-toggle="popover" data-bs-placement="bottom" data-bs-trigger="focus" title="{{trans('langForbidden')}}" class='quickLink opacity-help'>
            <i class="fa-solid fa-book-open Primary-500-cl settings-icon"></i>{{ trans('langSearchCourse') }}
        </a>
    @endif

    @if($is_admin)
        <a href="hierarchy.php" class='quickLink'>
            <i class="fa-solid fa-sitemap Primary-500-cl settings-icon"></i>{{ trans('langHierarchy') }}
        </a>
    @else
        <a tabindex="0" role="button" data-bs-toggle="popover" data-bs-placement="bottom" data-bs-trigger="focus" title="{{trans('langForbidden')}}" class='quickLink opacity-help'>
            <i class="fa-solid fa-sitemap Primary-500-cl settings-icon"></i>{{ trans('langHierarchy') }}
        </a>
    @endif

    @if($is_admin)
        <a href="eclassconf.php" class='quickLink'>
            <i class="fa-solid fa-gear Primary-500-cl settings-icon"></i>{{ trans('langConfig') }}
        </a>
    @else
        <a tabindex="0" role="button" data-bs-toggle="popover" data-bs-placement="bottom" data-bs-trigger="focus" title="{{trans('langForbidden')}}" class='quickLink opacity-help'>
            <i class="fa-solid fa-gear Primary-500-cl settings-icon"></i>{{ trans('langConfig') }}
        </a>
    @endif

    @if($is_admin)
        <a href="theme_options.php" class='quickLink'>
            <i class="fa-solid fa-display Primary-500-cl settings-icon"></i>{{ trans('langThemeSettings') }}
        </a>
    @else
        <a tabindex="0" role="button" data-bs-toggle="popover" data-bs-placement="bottom" data-bs-trigger="focus" title="{{trans('langForbidden')}}" class='quickLink opacity-help'>
            <i class="fa-solid fa-display Primary-500-cl settings-icon"></i>{{ trans('langThemeSettings') }}
        </a>
    @endif

    @if($is_admin)
        <a href="extapp.php" class='quickLink'>
            <i class="fa-solid fa-wrench Primary-500-cl settings-icon"></i>{{ trans('langExternalTools') }}
        </a>
    @else
        <a tabindex="0" role="button" data-bs-toggle="popover" data-bs-placement="bottom" data-bs-trigger="focus" title="{{trans('langForbidden')}}" class='quickLink opacity-help'>
            <i class="fa-solid fa-wrench Primary-500-cl settings-icon"></i>{{ trans('langExternalTools') }}
        </a>
    @endif

</div>



@php
    $col_size = '';
    if($is_admin) {
        $col_size = '3';
    }
    else if($is_power_user or $is_departmentmanage_user) {
        $col_size = '2';
    } else if($is_usermanage_user) {
        $col_size = '1';
    }
@endphp
<div class='col-12 mt-4'>
    <div class="row row-cols-1 row-cols-lg-{{ $col_size }} g-5">
        @foreach ($toolArr as $key => $tool_group)
            <div class='col'>
                <div class='card panelCard p-0 bg-white m-auto h-100 border-0'>
                    <div class='card-header border-0 bg-white p-0 d-flex justify-content-start align-items-center gap-2'>
                        @if($tool_group[0]['class'] == 'user_admin')
                            <i class="fa-solid fa-user-group settings-icons-lg"></i>
                        @elseif($tool_group[0]['class'] == 'course_admin')
                            <i class="fa-solid fa-book-open settings-icons-lg"></i>
                        @else
                            <i class="fa-solid fa-gear settings-icons-lg"></i>
                        @endif
                        <h3 class='mb-0'>{{ $tool_group[0]['text'] }}</h3>
                    </div>
                    <div class='card-body px-0'>
                        <ul class='list-group list-group-flush'>
                            @foreach ($tool_group[1] as $key2 => $tool)
                                <li class="list-group-item admin-list-group px-0 border-bottom-default">
                                    <a href="{!! $tool_group[2][$key2] !!}" class='TextBold link_admin_tool {{ module_path($tool_group[2][$key2]) == $current_module_dir ? " active" : ""}}' {{ is_external_link($tool_group[2][$key2]) || $tool_group[3][$key2] == 'fa-external-link' ? ' target="_blank"' : "" }}>
                                        {!! $tool !!}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

@if($is_admin)
    <div class='col-12 mt-4'>
        <div class="row row-cols-1 row-cols-lg-3 g-5">
            <div class='col'>
                <div class='card panelCard p-0 border-0 bg-white h-100'>
                    <div class='card-header border-0 bg-white p-0 d-flex justify-content-start align-items-center gap-2'>
                        <i class="fa-solid fa-toolbox settings-icons-lg"></i>
                        <h3 class='mb-0'>{{trans('langAdministratorTools')}}</h3>
                    </div>
                    <div class='card-body px-0'>
                        <ul class='list-group list-group-flush'>
                            @if ($is_admin)
                                <li class="list-group-item admin-list-group px-0 border-bottom-default">
                                    <a class='TextBold link_admin_tool' href="{{$urlAppend}}modules/admin/addadmin.php">
                                        {!! trans('langAdmins') !!}
                                    </a>
                                </li>
                            @endif
                            @if (isset($is_admin) and $is_admin)

                                <li class="list-group-item admin-list-group px-0 border-bottom-default">
                                    <a class='TextBold link_admin_tool' href="{{$urlAppend}}modules/admin/adminannouncements.php">
                                        {!! trans('langAdminAn') !!}
                                    </a>
                                </li>

                                @php $manual_language = ($language == 'el')? $language: 'en'; @endphp
                                <li class="list-group-item admin-list-group px-0 border-bottom-default">
                                    <a class='TextBold link_admin_tool' href="http://docs.openeclass.org/{{$manual_language}}/admin">
                                        {!! trans('langAdminManual') !!}
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
            <div class='col'>
                @if($is_admin)
                    <div class='card panelCard p-0 border-0 bg-white h-100'>
                        <div class='card-header border-0 bg-white p-0 d-flex justify-content-start align-items-center gap-2'>
                            <i class="fa-solid fa-list-ul settings-icons-lg"></i>
                            <h3 class='mb-0'>{{trans('langFaculties')}}</h3>

                        </div>
                        <div class='card-body px-0'>
                            <ul class='list-group list-group-flush'>
                                <li class="list-group-item admin-list-group px-0 border-bottom-default">
                                    <a href="{{$urlAppend}}modules/admin/hierarchy.php" class='TextBold link_admin_tool'>
                                    {!! trans('langHierarchy') !!}
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                @endif
            </div>
            <div class='col'>
                @if($is_admin)
                    <div class='card panelCard p-0 border-0 bg-white h-100'>
                        <div class='card-header border-0 bg-white p-0 d-flex justify-content-start align-items-center gap-2'>
                            <i class="fa-solid fa-sitemap settings-icons-lg"></i>
                            <h3 class='mb-0'>{{ trans('langEclassThemes') }}</h3>
                        </div>
                        <div class='card-body px-0'>
                            <ul class='list-group list-group-flush'>
                                <li class="list-group-item admin-list-group px-0 border-bottom-default">
                                    <a href="{{$urlAppend}}modules/admin/theme_options.php" class='TextBold link_admin_tool'>
                                        {!! trans('langThemeSettings') !!}
                                    </a>
                                </li>
                                <li class="list-group-item admin-list-group px-0 border-bottom-default">
                                    <a href="{{$urlAppend}}modules/admin/homepageTexts_create.php" class='TextBold link_admin_tool'>
                                        {!! trans('langAdminCreateHomeTexts') !!}
                                    </a>
                                </li>
                                <li class="list-group-item admin-list-group px-0 border-bottom-default">
                                    <a href="{{$urlAppend}}modules/admin/faq_create.php" class='TextBold link_admin_tool'>
                                        {!! trans('langAdminCreateFaq') !!}
                                    </a>
                                </li>
                                <li class="list-group-item admin-list-group px-0 border-bottom-default">
                                    <a href="{{$urlAppend}}modules/admin/contact_info.php" class='TextBold link_admin_tool'>
                                        {!! trans('langUpgContact') !!}
                                    </a>
                                </li>

                            </ul>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endif



