<!------------------------------------------------------------------------------------------------>
<!------------------------------------------------------------------------------------------------>
<!--------------------------------------- Admin panels ------------------------------------------->
<!------------------------------------------------------------------------------------------------>
<!------------------------------------------------------------------------------------------------>

@if ($release_info and version_compare($release_info->release, ECLASS_VERSION) > 0)
    <div class='col-12 mb-4'>
        <div class='card panelCard px-lg-4 py-lg-3'>
            <div class='card-header border-0 bg-white d-flex justify-content-between align-items-center'>
                <div class='text-uppercase normalColorBlueText TextBold fs-6'>{{ trans('langNewEclassVersion') }}</div>
            </div>
            <div class='card-body'>
                {!! sprintf( trans('langNewEclassVersionInfo'), "<strong>" . q($release_info->release) . "</strong>",
                            "<a href='https://www.openeclass.org/' target='_blank'>www.openeclass.org</a>") !!}
            </div>
        </div>
    </div>
@endif

<div class='col-12'>
    <div class='card panelCard BorderSolid px-lg-4 py-lg-3'>
        <div class='card-header border-0 bg-white'>
            <span class='text-uppercase normalColorBlueText TextBold fs-6'>{{ trans('langQuickLinks') }}</span>
        </div>
        <div class='card-body'>
            <div class='d-flex flex-wrap'>
                <a class='btn submitAdminBtn btn-sm small-text me-2 mb-2' href="search_user.php">
                    {{ trans('langSearchUser') }}
                </a>
            
                @if($is_admin or $is_departmentmanage_user or $is_power_user)
                    <a  href="searchcours.php" class='btn submitAdminBtn btn-sm small-text me-2 mb-2'>
                        {{ trans('langSearchCourse') }} 
                    </a>
                @else
                    <a tabindex="0" role="button" data-bs-toggle="popover" data-bs-placement="bottom" data-bs-trigger="focus" title="{{trans('langForbidden')}}" class='btn btn-outline-secondary btn-sm small-text opacity-help me-2 mb-2'>
                        {{ trans('langSearchCourse') }}
                    </a>
                @endif
            
                @if($is_admin)
                    <a href="hierarchy.php" class='btn submitAdminBtn btn-sm small-text me-2 mb-2'>
                    {{ trans('langHierarchy') }}
                    </a>
                @else
                    <a tabindex="0" role="button" data-bs-toggle="popover" data-bs-placement="bottom" data-bs-trigger="focus" title="{{trans('langForbidden')}}" class='btn btn-outline-secondary btn-sm small-text opacity-help me-2 mb-2'>
                    {{ trans('langHierarchy') }}  
                    </a>
                @endif

                @if($is_admin)
                    <a href="eclassconf.php" class='btn submitAdminBtn btn-sm small-text me-2 mb-2'>
                        {{ trans('langConfig') }}
                    </a>
                @else
                    <a tabindex="0" role="button" data-bs-toggle="popover" data-bs-placement="bottom" data-bs-trigger="focus" title="{{trans('langForbidden')}}" class='btn btn-outline-secondary btn-sm small-text opacity-help me-2 mb-2'>
                        {{ trans('langConfig') }}
                    </a>
                @endif

                @if($is_admin)
                    <a href="theme_options.php" class='btn submitAdminBtn btn-sm small-text me-2 mb-2'>
                    {{ trans('langThemeSettings') }}   
                    </a>
                @else
                    <a tabindex="0" role="button" data-bs-toggle="popover" data-bs-placement="bottom" data-bs-trigger="focus" title="{{trans('langForbidden')}}" class='btn btn-outline-secondary btn-sm small-text opacity-help me-2 mb-2'>
                        {{ trans('langThemeSettings') }} 
                    </a>
                @endif

                @if($is_admin)
                    <a href="extapp.php" class='btn submitAdminBtn btn-sm small-text me-2 mb-2'>
                    {{ trans('langExternalTools') }}  
                    </a>
                @else
                    <a tabindex="0" role="button" data-bs-toggle="popover" data-bs-placement="bottom" data-bs-trigger="focus" title="{{trans('langForbidden')}}" class='btn btn-outline-secondary btn-sm small-text opacity-help me-2 mb-2'>
                    {{ trans('langExternalTools') }} 
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>


@php 
    $col_size = '';
    if($is_admin){
        $col_size = '3';
    }
    else if($is_power_user or $is_departmentmanage_user){
        $col_size = '2';
    }else if($is_usermanage_user){
        $col_size = '1';
    }
@endphp
<div class='col-12 mt-4'>
    <div class="row row-cols-1 row-cols-lg-{{ $col_size }} g-4">
        @foreach ($toolArr as $key => $tool_group)
            <div class='col'>
                <div class='card panelCard BorderSolid px-lg-4 py-lg-3 bg-white m-auto h-100'>
                    <div class='card-header border-0 bg-white'>
                        <span class='text-uppercase normalColorBlueText TextBold fs-6'>{{ $tool_group[0]['text'] }}</span>
                    </div>
                    <div class='card-body'>
                        <ul>
                            @foreach ($tool_group[1] as $key2 => $tool)
                                <li class="p-1">
                                    <a href="{!! $tool_group[2][$key2] !!}" class='link_admin_tool fs-6 TextSemiBold {{ module_path($tool_group[2][$key2]) == $current_module_dir ? " active" : ""}}' {{ is_external_link($tool_group[2][$key2]) || $tool_group[3][$key2] == 'fa-external-link' ? ' target="_blank"' : "" }}>
                                        <span class='msmall-text toolAdminText'>{!! $tool !!}</span>  
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
        <div class="row row-cols-1 row-cols-lg-2 g-4">
            <div class='col'>
                <div class='card panelCard BorderSolid px-lg-4 py-lg-3 bg-white h-100'>
                    <div class='card-header border-0 bg-white'>
                        <span class='text-uppercase normalColorBlueText TextBold fs-6'>{{trans('langAdministratorTools')}}</span>
                    </div>
                    <div class='card-body'>
                        <ul>
                            @if ($is_admin)
                                <li class="p-1">
                                    <a class='fs-6 TextSemiBold link_admin_tool' href="{{$urlAppend}}modules/admin/addadmin.php">
                                        <span class='msmall-text toolAdminText'>{!!  $GLOBALS['langAdmins'] !!}</span>   
                                    </a>
                                </li>
                            @endif
                            @if (isset($is_admin) and $is_admin)
                                <li class="p-1">
                                    <a class='fs-6 TextSemiBold link_admin_tool' href="{{$urlAppend}}modules/admin/adminannouncements.php">
                                        <span class='msmall-text toolAdminText'>{!!  $GLOBALS['langAdminAn'] !!}</span>
                                    </a>
                                </li>
                                @php $manual_language = ($language == 'el')? $language: 'en'; @endphp
                                <li class="p-1">
                                    <a class='fs-6 TextSemiBold link_admin_tool' href="http://docs.openeclass.org/{{$manual_language}}/admin">
                                        <span class='msmall-text toolAdminText'>{!!  $GLOBALS['langAdminManual'] !!}</span>      
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
            <div class='col'>
                @if($is_admin)
                    <div class='card panelCard BorderSolid px-lg-4 py-lg-3 bg-white h-100'>
                        <div class='card-header border-0 bg-white'>
                            <span class='text-uppercase normalColorBlueText TextBold fs-6'>{{trans('langFaculties')}}</span>
                        </div>
                        <div class='card-body'>
                            <ul>
                                <li class="p-1">
                                    <a href="{{$urlAppend}}modules/admin/hierarchy.php" class='fs-6 TextSemiBold link_admin_tool'>
                                        <span class='msmall-text toolAdminText'>{!!  $GLOBALS['langHierarchy'] !!}</span>     
                                    </a>
                                </li>
                                <li class="p-1">
                                    <a href="{{$urlAppend}}modules/admin/coursecategory.php" class='fs-6 TextSemiBold link_admin_tool'>
                                        <span class='msmall-text toolAdminText'>{!!  $GLOBALS['langCourseCategoryActions'] !!}</span>   
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



