<!------------------------------------------------------------------------------------------------>
<!------------------------------------------------------------------------------------------------>
<!--------------------------------------- Admin panels ------------------------------------------->
<!------------------------------------------------------------------------------------------------>
<!------------------------------------------------------------------------------------------------>

@if ($release_info and version_compare($release_info->release, ECLASS_VERSION) > 0)
    <div class='col-12'>
        <div class='panel panel-info shadow-lg rounded-0 shadow-lg'>
            <div class='panel-heading text-center fs-6'>
                {{ trans('langNewEclassVersion') }}
            </div>
            <div class='panel-body rounded-0'>
                {!! sprintf( trans('langNewEclassVersionInfo'), "<strong>" . q($release_info->release) . "</strong>",
                            "<a href='https://www.openeclass.org/' target='_blank'>www.openeclass.org</a>") !!}
            </div>
        </div>
    </div>
@endif

@php $countNewPanel = 0; @endphp
@foreach ($toolArr as $key => $tool_group)
<div class='@if(count($toolArr) == 1) and ($is_power_user) col-lg-12 col-12 mt-3 @else col-lg-6 col-12 mt-3 @endif'>
    
    @if($countNewPanel == 2)
    <div class='panel panel-admin mb-3 shadow-lg'>
        <div class='panel-heading text-center'>
            <span class='colorPalette'>{{trans('langAdministratorTools')}}</span>
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

    <div class='panel panel-admin m-auto shadow-lg'>
        <div class='panel-heading text-center'>
            <span class='colorPalette'>{{ $tool_group[0]['text'] }}</span>
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
                <div class='panel panel-admin mt-3 shadow-lg'>
                    <div class='panel-heading text-center'>
                        <span class='colorPalette'>{{trans('langFaculties')}}</span>
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




