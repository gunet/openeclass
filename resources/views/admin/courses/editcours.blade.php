@extends('layouts.default')

@section('content')
    {!! isset($action_bar) ?  $action_bar : '' !!}
    @if (isset($c))
    <!--Display course information and link to edit-->
    <table class='table-default'>
        <th colspan='2'>{{ trans('langCourseInfo') }} {!! icon('fa-gear',trans('langModify'), "infocours.php?c=".$c) !!}</th>
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
    <!--Display course quota and link to edit-->
    <table class='table-default'>
	<th colspan='2'>{{ trans('langQuota') }}  {!! icon('fa-gear', trans('langModify'), "quotacours.php?c=".$c) !!}</th>
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
    <!--Display course type and link to edit-->
    <table class='table-default'>
        <th colspan='2'>
            {{ trans('langCourseStatus') }} {!! icon('fa-gear', trans('langModify'), "statuscours.php?c=".$c) !!}
        </th>
        <tr>
            <th width='250'>{{ trans('langCurrentStatus') }}:</th>
            <td>{{ course_status_message($cId) }}</td>
        </tr>
    </table>
    <!--Display other available choices-->
    <table class='table-default'>
        <th colspan='2'>{{ trans('langOtherActions') }}</th>
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
                <a href='delcours.php?c={{ getIndirectReference($cId) }}'>
                    {{ trans('langCourseDel') }}
                </a>
            </td>
	</tr>
    </table>
    @else
        <div class='alert alert-warning'>{{ trans('langErrChoose') }}</div>
    @endif
@endsection