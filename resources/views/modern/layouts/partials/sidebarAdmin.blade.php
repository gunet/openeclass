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
    <div class='panel panel-admin px-lg-4 py-lg-3 bg-white'>
        <div class='panel-heading bg-body'>
            <div class='col-12 Help-panel-heading'>
                <span class='panel-title text-uppercase Help-text-panel-heading'>{{ trans('langQuickLinks') }}</span>
            </div>
        </div>
        <div class='panel-body'>
            
            <div class='row p-2'>
                <div class='col-lg-4 col-12 d-flex justify-content-center'>
                    <a class='btn submitAdminBtn w-100 border-0' href="search_user.php">
                        {{ trans('langSearchUser') }}
                    </a>
                </div>
                <div class='col-lg-4 col-12 mt-lg-0 mt-3 d-flex justify-content-center'>
                    @if($is_admin or $is_departmentmanage_user or $is_power_user)
                        <a  href="searchcours.php" class='btn submitAdminBtn w-100 border-0'>
                            {{ trans('langSearchCourse') }} 
                        </a>
                    @else
                        <a tabindex="0" role="button" data-bs-toggle="popover" data-bs-placement="bottom" data-bs-trigger="focus" title="{{trans('langForbidden')}}" class='btn submitAdminBtn w-100 opacity-help'>
                          {{ trans('langSearchCourse') }}
                        </a>
                    @endif
                </div>
                <div class='col-lg-4 col-12 mt-lg-0 mt-3 d-flex justify-content-center'>
                    @if($is_admin)
                        <a href="hierarchy.php" class='btn submitAdminBtn w-100 border-0'>
                           {{ trans('langHierarchy') }}
                        </a>
                    @else
                        <a tabindex="0" role="button" data-bs-toggle="popover" data-bs-placement="bottom" data-bs-trigger="focus" title="{{trans('langForbidden')}}" class='btn submitAdminBtn w-100 opacity-help'>
                           {{ trans('langHierarchy') }}  
                        </a>
                    @endif
                </div>
            </div>

            <div class='row p-2'>
                
                <div class='col-lg-4 col-12 d-flex justify-content-center'>
                    
                    @if($is_admin)
                        <a href="eclassconf.php" class='btn submitAdminBtn w-100 border-0'>
                            {{ trans('langConfig') }}
                        </a>
                    @else
                    <a tabindex="0" role="button" data-bs-toggle="popover" data-bs-placement="bottom" data-bs-trigger="focus" title="{{trans('langForbidden')}}" class='btn submitAdminBtn w-100 opacity-help'>
                        {{ trans('langConfig') }}
                    </a>
                    @endif
                    
                </div>
                <div class='col-lg-4 col-12 mt-lg-0 mt-3 d-flex justify-content-center'>
                    
                        @if($is_admin)
                        <a href="theme_options.php" class='btn submitAdminBtn w-100 border-0'>
                           {{ trans('langThemeSettings') }}   
                        </a>
                        @else
                        <a tabindex="0" role="button" data-bs-toggle="popover" data-bs-placement="bottom" data-bs-trigger="focus" title="{{trans('langForbidden')}}" class='btn submitAdminBtn w-100 opacity-help'>
                            {{ trans('langThemeSettings') }} 
                        </a>
                        @endif
                   
                </div>
                <div class='col-lg-4 col-12 mt-lg-0 mt-3 d-flex justify-content-center'>
                    
                        @if($is_admin)
                        <a href="extapp.php" class='btn submitAdminBtn w-100 border-0'>
                          {{ trans('langExternalTools') }}  
                        </a>
                        @else
                        <a tabindex="0" role="button" data-bs-toggle="popover" data-bs-placement="bottom" data-bs-trigger="focus" title="{{trans('langForbidden')}}" class='btn submitAdminBtn w-100 opacity-help'>
                           {{ trans('langExternalTools') }} 
                        </a>
                        @endif
                    
                </div>

            </div>
        </div>
    </div>
</div>

@php $countNewPanel = 0; @endphp
@foreach ($toolArr as $key => $tool_group)
<div class='@if(count($toolArr) == 1) and ($is_power_user) col-lg-12 col-12 mt-3 @else col-lg-6 col-12 mt-3 @endif'>
    
    @if($countNewPanel == 2)
    <div class='panel panel-admin px-lg-4 py-lg-3 bg-white mb-3'>
        <div class='panel-heading bg-body'>
            <div class='col-12 Help-panel-heading'>
                <span class='panel-title text-uppercase Help-text-panel-heading'>{{trans('langAdministratorTools')}}</span>
            </div>
        </div>
        <div class='panel-body'>
            <ul class="list-group list-group-flush Borders">
                @if ($is_power_user or $is_departmentmanage_user)
                    @if ($is_admin)
                        <li class="list-group-item border-0 admin-list-group Borders">
                            <a href="{{$urlAppend}}modules/admin/addadmin.php" class='list-group-item'>
                                <span class='msmall-text toolAdminText'>{!!  $GLOBALS['langAdmins'] !!}</span>   
                            </a>
                        </li>
                    @endif
                    @if (isset($is_admin) and $is_admin)
                        <li class="list-group-item border-0 admin-list-group Borders">
                            <a href="{{$urlAppend}}modules/admin/adminannouncements.php" class='list-group-item'>
                                <span class='msmall-text toolAdminText'>{!!  $GLOBALS['langAdminAn'] !!}</span>
                            </a>
                        </li>
                        @php $manual_language = ($language == 'el')? $language: 'en'; @endphp
                        <li class="list-group-item border-0 admin-list-group Borders">
                            <a href="http://docs.openeclass.org/{{$manual_language}}/admin" class='list-group-item'>
                                <span class='msmall-text toolAdminText'>{!!  $GLOBALS['langAdminManual'] !!}</span>      
                            </a>
                        </li>
                    @endif
                @endif
            </ul>
        </div>
    </div>
    @endif

    <div class='panel panel-admin px-lg-4 py-lg-3 bg-white m-auto'>
        <div class='panel-heading bg-body'>
            <div class='col-12 Help-panel-heading'>
                <span class='panel-title text-uppercase Help-text-panel-heading'>{{ $tool_group[0]['text'] }}</span>
            </div>
        </div>
        <div class='panel-body'>
            <ul class="list-group list-group-flush Borders">
                @foreach ($tool_group[1] as $key2 => $tool)
                    <li class="list-group-item border-0 admin-list-group Borders">
                        <a href="{!! $tool_group[2][$key2] !!}" class='list-group-item {{ module_path($tool_group[2][$key2]) == $current_module_dir ? " active" : ""}}' {{ is_external_link($tool_group[2][$key2]) || $tool_group[3][$key2] == 'fa-external-link' ? ' target="_blank"' : "" }}>
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
                <div class='panel panel-admin px-lg-4 py-lg-3 bg-white mt-3'>
                    <div class='panel-heading bg-body'>
                        <div class='col-12 Help-panel-heading'>
                            <span class='panel-title text-uppercase Help-text-panel-heading'>{{trans('langFaculties')}}</span>
                        </div>
                    </div>
                    <div class='panel-body'>
                        <ul class="list-group list-group-flush Borders">
                            <li class="list-group-item border-0 admin-list-group Borders">
                                <a href="{{$urlAppend}}modules/admin/hierarchy.php" class='list-group-item'>
                                    <span class='msmall-text toolAdminText'>{!!  $GLOBALS['langHierarchy'] !!}</span>     
                                </a>
                            </li>
                            <li class="list-group-item border-0 admin-list-group Borders">
                                <a href="{{$urlAppend}}modules/admin/coursecategory.php" class='list-group-item'>
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




