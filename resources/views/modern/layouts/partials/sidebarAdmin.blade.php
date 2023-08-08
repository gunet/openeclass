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

@if((!get_config('mentoring_always_active') and get_config('mentoring_platform')) or (!get_config('mentoring_platform')))
<div class='col-12'>
    <div class='card panelCard BorderSolid px-lg-4 py-lg-3'>
        <div class='card-header border-0 bg-white'>
            <h3>{{ trans('langQuickLinks') }}</h3>
        </div>
        <div class='card-body'>
            <div class='d-flex flex-wrap'>
                <a class='m-2' href="search_user.php">
                    <span class='fa fa-link'></span>&nbsp{{ trans('langSearchUser') }}
                </a>
            
                @if($is_admin or $is_departmentmanage_user or $is_power_user)
                    <a  href="searchcours.php" class='m-2'>
                        <span class='fa fa-link'></span>&nbsp{{ trans('langSearchCourse') }} 
                    </a>
                @else
                    <a tabindex="0" role="button" data-bs-toggle="popover" data-bs-placement="bottom" data-bs-trigger="focus" title="{{trans('langForbidden')}}" class='m-2 opacity-help'>
                        <span class='fa fa-link'></span>&nbsp{{ trans('langSearchCourse') }}
                    </a>
                @endif
            
                @if($is_admin)
                    <a href="hierarchy.php" class='m-2'>
                        <span class='fa fa-link'></span>&nbsp{{ trans('langHierarchy') }}
                    </a>
                @else
                    <a tabindex="0" role="button" data-bs-toggle="popover" data-bs-placement="bottom" data-bs-trigger="focus" title="{{trans('langForbidden')}}" class='m-2 opacity-help'>
                        <span class='fa fa-link'></span>&nbsp{{ trans('langHierarchy') }}  
                    </a>
                @endif

                @if($is_admin)
                    <a href="eclassconf.php" class='m-2'>
                        <span class='fa fa-link'></span>&nbsp{{ trans('langConfig') }}
                    </a>
                @else
                    <a tabindex="0" role="button" data-bs-toggle="popover" data-bs-placement="bottom" data-bs-trigger="focus" title="{{trans('langForbidden')}}" class='m-2 opacity-help'>
                        <span class='fa fa-link'></span>&nbsp{{ trans('langConfig') }}
                    </a>
                @endif

                @if($is_admin)
                    <a href="theme_options.php" class='m-2'>
                        <span class='fa fa-link'></span>&nbsp{{ trans('langThemeSettings') }}   
                    </a>
                @else
                    <a tabindex="0" role="button" data-bs-toggle="popover" data-bs-placement="bottom" data-bs-trigger="focus" title="{{trans('langForbidden')}}" class='m-2 opacity-help'>
                        <span class='fa fa-link'></span>&nbsp{{ trans('langThemeSettings') }} 
                    </a>
                @endif

                @if($is_admin)
                    <a href="extapp.php" class='m-2'>
                        <span class='fa fa-link'></span>&nbsp{{ trans('langExternalTools') }}  
                    </a>
                @else
                    <a tabindex="0" role="button" data-bs-toggle="popover" data-bs-placement="bottom" data-bs-trigger="focus" title="{{trans('langForbidden')}}" class='m-2 opacity-help'>
                        <span class='fa fa-link'></span>&nbsp{{ trans('langExternalTools') }} 
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>
@endif


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
                        <h3>{{ $tool_group[0]['text'] }}</h3>
                    </div>
                    <div class='card-body'>
                        <ul>
                            @foreach ($tool_group[1] as $key2 => $tool)
                                <li class="p-1">
                                    <a href="{!! $tool_group[2][$key2] !!}" class='link_admin_tool {{ module_path($tool_group[2][$key2]) == $current_module_dir ? " active" : ""}}' {{ is_external_link($tool_group[2][$key2]) || $tool_group[3][$key2] == 'fa-external-link' ? ' target="_blank"' : "" }}>
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
        <div class="row row-cols-1 row-cols-lg-2 g-4">
            <div class='col'>
                <div class='card panelCard BorderSolid px-lg-4 py-lg-3 bg-white h-100'>
                    <div class='card-header border-0 bg-white'>
                        <h3>{{trans('langAdministratorTools')}}</h3>
                    </div>
                    <div class='card-body'>
                        <ul>
                            @if ($is_admin)
                                <li class="p-1">
                                    <a class='link_admin_tool' href="{{$urlAppend}}modules/admin/addadmin.php">
                                        {!!  $GLOBALS['langAdmins'] !!} 
                                    </a>
                                </li>
                            @endif
                            @if (isset($is_admin) and $is_admin)
                                @if(get_config('mentoring_always_active') and get_config('mentoring_platform'))
                                    <li class="p-1">
                                        <a href="{{ $urlAppend }}modules/admin/mentoring_adminannouncements.php" class='link_admin_tool'>
                                            <span class='msmall-text toolAdminText'>{!!  $GLOBALS['langAdminAn'] !!}</span>
                                        </a>
                                    </li>
                                @else
                                    <li class="p-1">
                                        <a class='link_admin_tool' href="{{$urlAppend}}modules/admin/adminannouncements.php">
                                            {!!  $GLOBALS['langAdminAn'] !!}
                                        </a>
                                    </li>
                                @endif
                                @php $manual_language = ($language == 'el')? $language: 'en'; @endphp
                                <li class="p-1">
                                    <a class='link_admin_tool' href="http://docs.openeclass.org/{{$manual_language}}/admin">
                                        {!!  $GLOBALS['langAdminManual'] !!}     
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
                            <h3>{{trans('langFaculties')}}</h3>
                        </div>
                        <div class='card-body'>
                            <ul>
                                <li class="p-1">
                                    <a href="{{$urlAppend}}modules/admin/hierarchy.php" class='link_admin_tool'>
                                        <span class='toolAdminText'>{!!  $GLOBALS['langHierarchy'] !!}</span>     
                                    </a>
                                </li>
                                <li class="p-1">
                                    <a href="{{$urlAppend}}modules/admin/coursecategory.php" class='link_admin_tool'>
                                        <span class='toolAdminText'>{!!  $GLOBALS['langCourseCategoryActions'] !!}</span>   
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



