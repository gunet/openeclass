<!------------------------------------------------------------------------------------------------>
<!------------------------------------------------------------------------------------------------>
<!--------------------------------------- Admin panels ------------------------------------------->
<!------------------------------------------------------------------------------------------------>
<!------------------------------------------------------------------------------------------------>

@if ($release_info and version_compare($release_info->release, ECLASS_VERSION) > 0)
    <div class='col-12 mb-3'>
        <div class='panel panel-info'>
            <div class='panel-heading text-center'>
                {{ trans('langNewEclassVersion') }}
            </div>
            <div class='panel-body'>
                {!! sprintf( trans('langNewEclassVersionInfo'), "<strong>" . q($release_info->release) . "</strong>",
                            "<a href='https://www.openeclass.org/' target='_blank'>www.openeclass.org</a>") !!}
            </div>
        </div>
    </div>
@endif

<div class='col-12'>
    <div class='card panelCard BorderSolid px-lg-4 py-lg-3'>
        <div class='card-header border-0 bg-white'>
            <span class='text-uppercase normalBlueText TextBold fs-6'>{{ trans('langQuickLinks') }}</span>
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

@php $countNewPanel = 0; @endphp
@foreach ($toolArr as $key => $tool_group)
<div class='@if(count($toolArr) == 1) and ($is_power_user) col-lg-12 col-12 mt-3 @else col-lg-6 col-12 mt-3 @endif'>
    
    @if($countNewPanel == 2)
    <div class='card panelCard BorderSolid px-lg-4 py-lg-3 bg-white mb-3'>
        <div class='card-header border-0 bg-white'>
            <span class='text-uppercase normalBlueText TextBold fs-6'>{{trans('langAdministratorTools')}}</span>
        </div>
        <div class='card-body'>
            <ul>
                @if ($is_power_user or $is_departmentmanage_user)
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
                @endif
            </ul>
        </div>
    </div>
    @endif

    <div class='card panelCard BorderSolid px-lg-4 py-lg-3 bg-white m-auto'>
        <div class='card-header border-0 bg-white'>
             <span class='text-uppercase normalBlueText TextBold fs-6'>{{ $tool_group[0]['text'] }}</span>
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

    @if($countNewPanel == 1)
        @if($is_power_user or $is_departmentmanage_user)
            @if($is_admin)
                <div class='card panelCard BorderSolid px-lg-4 py-lg-3 bg-white mt-3'>
                    <div class='card-header border-0 bg-white'>
                        <span class='text-uppercase normalBlueText TextBold fs-6'>{{trans('langFaculties')}}</span>
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
        @endif
    @endif

</div>

@php $countNewPanel++; @endphp
@endforeach




