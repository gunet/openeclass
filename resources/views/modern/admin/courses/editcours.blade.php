@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
    <div class='{{ $container }} main-container'>
        <div class="row m-auto">

                @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                @include('layouts.partials.legend_view')

                @if(isset($action_bar))
                    {!! $action_bar !!}
                @else
                    <div class='mt-4'></div>
                @endif

                @if(Session::has('message'))
                    <div class='col-12 all-alerts'>
                        <div class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                            @php 
                                $alert_type = '';
                                if(Session::get('alert-class', 'alert-info') == 'alert-success'){
                                    $alert_type = "<i class='fa-solid fa-circle-check fa-lg'></i>";
                                }elseif(Session::get('alert-class', 'alert-info') == 'alert-info'){
                                    $alert_type = "<i class='fa-solid fa-circle-info fa-lg'></i>";
                                }elseif(Session::get('alert-class', 'alert-info') == 'alert-warning'){
                                    $alert_type = "<i class='fa-solid fa-triangle-exclamation fa-lg'></i>";
                                }else{
                                    $alert_type = "<i class='fa-solid fa-circle-xmark fa-lg'></i>";
                                }
                            @endphp
                            
                            @if(is_array(Session::get('message')))
                                @php $messageArray = array(); $messageArray = Session::get('message'); @endphp
                                {!! $alert_type !!}<span>
                                @foreach($messageArray as $message)
                                    {!! $message !!}
                                @endforeach</span>
                            @else
                                {!! $alert_type !!}<span>{!! Session::get('message') !!}</span>
                            @endif
                            
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                @endif

                @if (isset($c))
                <!--Display course information and link to edit-->
                <div class='col-12'>
                    <div class='table-responsive'>
                        <table class='table-default'>
                            <thead><th class='list-header' colspan='2'>{{ trans('langCourseInfo') }}{!! icon('fa-gear Warning-200-cl ps-2',trans('langModify'), "infocours.php?c=".$c) !!}</th></thead>
                            <tr>
                                <th class='px-2' width='250'>{{ trans('langFaculty') }}</th>
                                <td>
                                @foreach ($departments as $key => $department)
                                    @if ($key > 0)
                                        <br>
                                    @endif
                                    {{ $tree->getFullPath($department) }}
                                @endforeach                  
                                </td>
                            </tr>
                            <tr>
                                <th class='px-2'>{{ trans('langCode') }}</th>
                                <td>{{ $course->code }}</td>
                            </tr>
                            <tr>
                                <th class='px-2'>{{ trans('langTitle') }}</th>
                                <td>{{ $course->title }}</td>
                            </tr>
                            <tr>
                                <th class='px-2'>
                                    {{ trans('langTutor') }}
                                </th>
                                <td>{{ $course->prof_names }}</td>
                            </tr>
                        </table>    
                    </div>
                </div>

                <!--Display course quota and link to edit-->
                <div class='col-12'>
                    <div class='table-responsive mt-4'>
                        <table class='table-default'>
                            <thead><th class='list-header' colspan='2'>{{ trans('langQuota') }}  {!! icon('fa-gear Warning-200-cl ps-2', trans('langModify'), "quotacours.php?c=".$c) !!}</th></thead>
                            <tr>
                                <td colspan='2'>
                                    <div class='sub_title1'>{{ trans('langTheCourse') }} {{ $course->title }}  {{ trans('langMaxQuota') }}</div>
                                </td>
                            </tr>
                            <tr>
                                <td>{{ trans('langLegend') }} <b>{{ trans('langDoc') }}</b>:</td>
                                <td>{{ format_file_size($course->doc_quota) }}</td>
                            </tr>
                            <tr>
                                <td>{{ trans('langLegend') }} <b>{{ trans('langVideo') }}</b>:</td>
                                <td>{{ format_file_size($course->video_quota) }}</td>
                            </tr>
                            <tr>
                                <td width='250'>{{ trans('langLegend') }} <b>{{ trans('langGroups') }}</b>:</td>
                                <td>{{ format_file_size($course->group_quota) }}</td>
                            </tr>
                            <tr>
                                <td>{{ trans('langLegend') }} <b>{{ trans('langDropBox') }}</b>:</td>
                                <td>{{ format_file_size($course->dropbox_quota) }}</td>
                            </tr>
                        </table>    
                    </div>
                </div>

                <!--Display course type and link to edit-->
                <div class='col-12'>
                    <div class='table-responsive mt-4'>
                        <table class='table-default'>
                            <thead><th class='list-header' colspan='2'>
                                {{ trans('langCourseStatus') }} {!! icon('fa-gear Warning-200-cl ps-2', trans('langModify'), "statuscours.php?c=".$c) !!}
                            </th></thead>
                            <tr>
                                <th class='px-2' width='250'>{{ trans('langCurrentStatus') }}:</th>
                                <td>{{ course_status_message($cId) }}</td>
                            </tr>
                        </table>
                    </div>
                </div>


                <!--Display other available choices-->
                <div class='col-12'>
                    <div class='table-responsive mt-4'>
                        <table class='table-default'>
                            <thead><th class='list-header' colspan='2'>{{ trans('langOtherActions') }}</th></thead>
                            <!--Users list-->
                            <tr>
                                <td>
                                    <a href='listusers.php?c={{ $cId }}'>
                                        {{ trans('langListUsersActions') }}
                                    </a>
                                </td>
                            </tr>  
                            <!--Backup course-->
                            <tr>
                                <td>
                                    <a href='../course_info/archive_course.php?c={{ $c }}&amp;{{ generate_csrf_token_link_parameter() }}'>
                                        {{ trans('langTakeBackup') }}
                                    </a>
                                </td>
                            </tr>
                            <!--Course metadata--> 
                            @if (get_config('course_metadata'))
                                <tr>
                                    <td>
                                        <a href='../course_metadata/index.php?course={{ $c }}'>
                                            {{ trans('langCourseMetadata') }}
                                        </a>
                                    </td>
                                </tr>
                            @endif
                            @if (get_config('opencourses_enable'))
                                <tr>
                                    <td>
                                        <a href='../course_metadata/control.php?course={{ $c }}'>
                                            {{ trans('langCourseMetadataControlPanel') }}
                                        </a>
                                    </td>
                                </tr>
                            @endif
                            <!--Delete course-->
                            <tr>
                                <td>
                                    <a href='delcours.php?c={{ $cId }}'>
                                        {{ trans('langCourseDel') }}
                                    </a>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                @else
                    <div class='col-12'><div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>{{ trans('langErrChoose') }}</span></div></div>
                @endif

            
        </div>
    </div>
</div>
@endsection