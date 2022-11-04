<!------------------------------------------------------------------------------------------------>
<!------------------------------------------------------------------------------------------------>
<!--------------------------------------- Admin panels ------------------------------------------->
<!------------------------------------------------------------------------------------------------>
<!------------------------------------------------------------------------------------------------>

@if ($release_info and version_compare($release_info->release, ECLASS_VERSION) > 0)
    <div class='col-12 mb-3 rounded-0'>
        <div class='panel panel-info rounded-0'>
            <div class='panel-heading text-center rounded-0'>
                {{ trans('langNewEclassVersion') }}
            </div>
            <div class='panel-body rounded-0'>
                {!! sprintf( trans('langNewEclassVersionInfo'), "<strong>" . q($release_info->release) . "</strong>",
                            "<a href='https://www.openeclass.org/' target='_blank'>www.openeclass.org</a>") !!}
            </div>
        </div>
    </div>
@endif

<div class='col-12'>
    <div class='panel panel-admin border-0 rounded-0 p-md-3 bg-white'>
        <div class='panel-heading bg-body rounded-0'>
            <div class='col-12 Help-panel-heading rounded-0'>
                <span class='panel-title text-uppercase Help-text-panel-heading'>{{ trans('langQuickLinks') }}</span>
            </div>
        </div>
        <div class='panel-body'>
            
            <div class='row p-2'>
                <div class='col-lg-4 col-12'>
                    <a class='btn btn-sm btn-light w-auto m-auto d-block' href="search_user.php">
                        <span class='fa fa-link mt-1 me-1 orangeText'></span>
                        <span class='toolAdminText'>{{ trans('langSearchUser') }}</span>
                    </a>
                </div>
                <div class='col-lg-4 col-12 mt-lg-0 mt-3'>
                    @if($is_admin or $is_departmentmanage_user or $is_power_user)
                        <a  href="searchcours.php" class='btn btn-sm btn-light w-auto m-auto d-block'>
                            <div class='d-inline-flex'>
                                <span class='fa fa-link mt-1 me-1 orangeText'></span>
                                <span class='toolAdminText'>{{ trans('langSearchCourse') }}</span> 
                            </div>     
                        </a>
                    @else
                        <a tabindex="0" role="button" data-bs-toggle="popover" data-bs-placement="bottom" data-bs-trigger="focus" title="{{trans('langForbidden')}}" class='btn btn-sm btn-default w-auto opacity-help m-auto d-block'>
                            <div class='d-inline-flex'>
                                <span class='fa fa-link mt-1 me-1 orangeText'></span>
                                <span class='toolAdminText'>{{ trans('langSearchCourse') }}</span> 
                            </div>     
                        </a>
                    @endif
                </div>
                <div class='col-lg-4 col-12 mt-lg-0 mt-3'>
                    @if($is_admin)
                        <a href="hierarchy.php" class='btn btn-sm btn-light w-auto m-auto d-block'>
                            <div class='d-inline-flex'>
                                <span class='fa fa-link mt-1 me-1 orangeText'></span>
                                <span class='toolAdminText'>{{ trans('langHierarchy') }}</span>  
                            </div>    
                        </a>
                    @else
                        <a tabindex="0" role="button" data-bs-toggle="popover" data-bs-placement="bottom" data-bs-trigger="focus" title="{{trans('langForbidden')}}" class='btn btn-sm btn-default w-auto opacity-help m-auto d-block'>
                            <div class='d-inline-flex'>
                                <span class='fa fa-link mt-1 me-1 orangeText'></span>
                                <span class='toolAdminText'>{{ trans('langHierarchy') }}</span>  
                            </div>    
                        </a>
                    @endif
                </div>
            </div>

            <div class='row p-2'>
                
                <div class='col-lg-4 col-12'>
                    
                    @if($is_admin)
                        <a href="eclassconf.php" class='btn btn-sm btn-light w-auto m-auto d-block'>
                            <div class='d-inline-flex'>
                                <span class='fa fa-link mt-1 me-1 orangeText'></span>
                                <span class='toolAdminText'>{{ trans('langConfig') }}</span>   
                            </div>   
                        </a>
                    @else
                    <a tabindex="0" role="button" data-bs-toggle="popover" data-bs-placement="bottom" data-bs-trigger="focus" title="{{trans('langForbidden')}}" class='btn btn-sm btn-default w-auto opacity-help m-auto d-block'>
                        <div class='d-inline-flex'>
                            <span class='fa fa-link mt-1 me-1 orangeText'></span>
                            <span class='toolAdminText'>{{ trans('langConfig') }}</span>   
                        </div>   
                    </a>
                    @endif
                    
                </div>
                <div class='col-lg-4 col-12 mt-lg-0 mt-3'>
                    
                        @if($is_admin)
                        <a href="theme_options.php" class='btn btn-sm btn-light w-auto m-auto d-block'>
                            <div class='d-inline-flex'>
                                <span class='fa fa-link mt-1 me-1 orangeText'></span>
                                <span class='toolAdminText'>{{ trans('langThemeSettings') }}</span>  
                            </div>    
                        </a>
                        @else
                        <a tabindex="0" role="button" data-bs-toggle="popover" data-bs-placement="bottom" data-bs-trigger="focus" title="{{trans('langForbidden')}}" class='btn btn-sm btn-default w-auto opacity-help m-auto d-block'>
                            <div class='d-inline-flex'>
                                <span class='fa fa-link mt-1 me-1 orangeText'></span>
                                <span class='toolAdminText'>{{ trans('langThemeSettings') }}</span>  
                            </div>    
                        </a>
                        @endif
                   
                </div>
                <div class='col-lg-4 col-12 mt-lg-0 mt-3'>
                    
                        @if($is_admin)
                        <a href="extapp.php" class='btn btn-sm btn-light w-auto m-auto d-block'>
                            <div class='d-inline-flex'>
                                <span class='fa fa-link mt-1 me-1 orangeText'></span>
                                <span class='toolAdminText'>{{ trans('langExternalTools') }}</span>  
                            </div>    
                        </a>
                        @else
                        <a tabindex="0" role="button" data-bs-toggle="popover" data-bs-placement="bottom" data-bs-trigger="focus" title="{{trans('langForbidden')}}" class='btn btn-sm btn-default w-auto opacity-help m-auto d-block'>
                            <div class='d-inline-flex'>
                                <span class='fa fa-link mt-1 me-1 fw-bold orangeText'></span>
                                <span class='toolAdminText fw-bold'>{{ trans('langExternalTools') }}</span>  
                            </div>    
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
    <div class='panel panel-admin border-0 rounded-0 p-md-3 bg-white mb-3'>
        <div class='panel-heading bg-body rounded-0'>
            <div class='col-12 Help-panel-heading rounded-0'>
                <span class='panel-title text-uppercase Help-text-panel-heading'>{{trans('langAdministratorTools')}}</span>
            </div>
        </div>
        <div class='panel-body'>
            <ul class="list-group list-group-flush">
                @if ($is_power_user or $is_departmentmanage_user)
                    @if ($is_admin)
                        <li class="list-group-item border-0 admin-list-group">
                            <a href="{{$urlAppend}}modules/admin/addadmin.php" class='list-group-item'>
                                <div class='d-inline-flex'>
                                    <span class='fa fa-caret-right fa-fw mt-1 orangeText'></span>
                                    <span class='toolAdminText'>{!!  $GLOBALS['langAdmins'] !!}</span>   
                                 </div>  
                            </a>
                        </li>
                    @endif
                    @if (isset($is_admin) and $is_admin)
                        <li class="list-group-item border-0 admin-list-group">
                            <a href="{{$urlAppend}}modules/admin/adminannouncements.php" class='list-group-item'>
                                <div class='d-inline-flex'>
                                    <span class='fa fa-caret-right fa-fw mt-1 orangeText'></span>
                                    <span class='toolAdminText'>{!!  $GLOBALS['langAdminAn'] !!}</span>
                                </div>    
                            </a>
                        </li>
                        @php $manual_language = ($language == 'el')? $language: 'en'; @endphp
                        <li class="list-group-item border-0 admin-list-group">
                            <a href="http://docs.openeclass.org/{{$manual_language}}/admin" class='list-group-item'>
                                <div class='d-inline-flex'>
                                    <span class='fa fa-caret-right fa-fw mt-1 orangeText'></span>
                                    <span class='toolAdminText'>{!!  $GLOBALS['langAdminManual'] !!}</span>      
                                </div>
                            </a>
                        </li>
                    @endif
                @endif
            </ul>
        </div>
    </div>
    @endif

    <div class='panel panel-admin border-0 rounded-0 p-md-3 bg-white m-auto'>
        <div class='panel-heading bg-body rounded-0'>
            <div class='col-12 Help-panel-heading rounded-0'>
                <span class='panel-title text-uppercase Help-text-panel-heading'>{{ $tool_group[0]['text'] }}</span>
            </div>
        </div>
        <div class='panel-body'>
            <ul class="list-group list-group-flush">
                @foreach ($tool_group[1] as $key2 => $tool)
                    <li class="list-group-item border-0 admin-list-group">
                        <a href="{!! $tool_group[2][$key2] !!}" class='list-group-item {{ module_path($tool_group[2][$key2]) == $current_module_dir ? " active" : ""}}' {{ is_external_link($tool_group[2][$key2]) || $tool_group[3][$key2] == 'fa-external-link' ? ' target="_blank"' : "" }}>
                            <div class='d-inline-flex'>
                                <span class='fa fa-caret-right fa-fw mt-1 orangeText'></span>
                                <span class='toolAdminText'>{!! $tool !!}</span>
                            </div>
                               
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    @if($countNewPanel == 1)
        @if($is_power_user or $is_departmentmanage_user)
            @if($is_admin)
                <div class='panel panel-admin border-0 rounded-0 p-md-3 bg-white mt-3'>
                    <div class='panel-heading bg-body rounded-0'>
                        <div class='col-12 Help-panel-heading rounded-0'>
                            <span class='panel-title text-uppercase Help-text-panel-heading'>{{trans('langFaculties')}}</span>
                        </div>
                    </div>
                    <div class='panel-body'>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item border-0 admin-list-group">
                                <a href="{{$urlAppend}}modules/admin/hierarchy.php" class='list-group-item'>
                                    <div class='d-inline-flex'>
                                        <span class='fa fa-caret-right fa-fw mt-1 orangeText'></span>
                                        <span class='toolAdminText'>{!!  $GLOBALS['langHierarchy'] !!}</span>   
                                    </div>   
                                </a>
                            </li>
                            <li class="list-group-item border-0 admin-list-group">
                                <a href="{{$urlAppend}}modules/admin/coursecategory.php" class='list-group-item'>
                                        <div class='d-inline-flex'>
                                            <span class='fa fa-caret-right fa-fw mt-1 orangeText'></span>
                                            <span class='toolAdminText'>{!!  $GLOBALS['langCourseCategoryActions'] !!}</span>
                                        </div>      
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




