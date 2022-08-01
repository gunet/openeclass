@extends('layouts.default')

@section('content')

<div class="pb-lg-3 pt-lg-3 pb-0 pt-0">

    <div class="container-fluid main-container">

        <div class="row rowMedium">

            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 justify-content-center col_maincontent_active_Homepage">
                    
                <div class="row p-lg-5 p-md-5 ps-1 pe-1 pt-5 pb-5">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

                    @if(Session::has('message'))
                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mt-5'>
                        <p class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                            {{ Session::get('message') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </p>
                    </div>
                    @endif


                    {!! isset($action_bar) ?  $action_bar : '' !!}

                    @if (isset($c))
                    <!--Display course information and link to edit-->
                    <div class='table-responsive'>
                        <table class='announcements_table'>
                            <th class='notes_thead text-white' colspan='2'>{{ trans('langCourseInfo') }}{!! icon('fa-gear text-white ps-2',trans('langModify'), "infocours.php?c=".$c) !!}</th>
                            <tr>
                                <th width='250'>{{ trans('langFaculty') }}</th>
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
                                <th>{{ trans('langCode') }}:</th>
                                <td>{{ $course->code }}</td>
                            </tr>
                            <tr>
                                <th><b>{{ trans('langTitle') }}:</b></th>
                                <td>{{ $course->title }}</td>
                            </tr>
                            <tr>
                                <th>
                                        <b>{{ trans('langTutor') }}:</b>
                                    </th>
                                <td>{{ $course->prof_names }}</td>
                            </tr>
                        </table>    
                    </div>

                    <!--Display course quota and link to edit-->
                    <div class='table-responsive mt-3'>
                        <table class='announcements_table'>
                            <th class='notes_thead text-white' colspan='2'>{{ trans('langQuota') }}  {!! icon('fa-gear text-white ps-2', trans('langModify'), "quotacours.php?c=".$c) !!}</th>
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

                    <!--Display course type and link to edit-->
                    <div class='table-responsive mt-3'>
                        <table class='announcements_table'>
                            <th class='notes_thead text-white' colspan='2'>
                                {{ trans('langCourseStatus') }} {!! icon('fa-gear text-white ps-2', trans('langModify'), "statuscours.php?c=".$c) !!}
                            </th>
                            <tr>
                                <th width='250'>{{ trans('langCurrentStatus') }}:</th>
                                <td>{{ course_status_message($cId) }}</td>
                            </tr>
                        </table>
                    </div>


                    <!--Display other available choices-->
                    <div class='table-responsive mt-3'>
                        <table class='announcements_table'>
                            <th class='notes_thead text-white' colspan='2'>{{ trans('langOtherActions') }}</th>
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
                    @else
                        <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12'><div class='alert alert-warning'>{{ trans('langErrChoose') }}</div></div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>
@endsection