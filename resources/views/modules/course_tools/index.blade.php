@extends('layouts.default')

@push('head_scripts')
    <script type="text/javascript">
        var langEmptyGroupName = '{{ trans('langNoPgTitle') }}'
    </script>
@endpush

@section('content')

    <div class="panel panel-default panel-action-btn-default">
        <div class='panel-heading list-header'>
            <h3 class='panel-title'>{{ trans('langActivateCourseTools') }}</h3>
        </div>
        <form name="courseTools" action="{{ $_SERVER['SCRIPT_NAME'] }}?course={{ $course_code }}" method="post" enctype="multipart/form-data">
            <div class="table-responsive">
                <table class="table-default">
                    <tr>
                    <th width="45%" class="text-center">{{ trans('langInactiveTools') }}</th>
                    <th width="10%" class="text-center">{{ trans('langMove') }}</th>
                    <th width="45%" class="text-center">{{ trans('langActiveTools') }}</th>
                    </tr>
                    <tr>
                        <td class="text-center">
                            <select class="form-control" name="toolStatInactive[]" id='inactive_box' size='17' multiple>
                                @foreach($toolSelection[0] as $item)
                                    <option value="{{ $item->id }}">{{ $item->title }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td class="text-center" style="vertical-align: middle;">
                            <button type="button" class="btn btn-default" onClick="move('inactive_box','active_box')"><span class="fa fa-arrow-right"></span></button><br><br>
                            <button type="button" class="btn btn-default" onClick="move('active_box','inactive_box')"><span class="fa fa-arrow-left"></span></button>
                        </td>
                        <td class="text-center">
                            <select class="form-control" name="toolStatActive[]" id='active_box' size='17' multiple>
                                @foreach($toolSelection[1] as $item)
                                    <option value="{{ $item->id }}">{{ $item->title }}</option>
                                @endforeach
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3" class="text-center">
                            <input type="submit" class="btn btn-primary" value="{{ trans('langSubmit') }}" name="toolStatus" onClick="selectAll('active_box',true)" />
                        </td>
                    </tr>
                </table>
            </div>
            {!! $csrf !!}
        </form>
    </div>

    <div class='panel panel-default panel-action-btn-default'>
        <div class='pull-right' style='padding:8px;'>
            <div id='operations_container'>
                <a class='btn btn-success' href='{{ $_SERVER['SCRIPT_NAME'] }}?course={{ $course_code }}&amp;action=true'><span class='fa fa-plus-circle'> {{ trans('langAddExtLink') }}</span></a>
            </div>
        </div>
        <div class='panel-heading list-header'>
            <h3 class='panel-title'> {{ trans('langOperations') }}</h3>
        </div>
        <table class='table-default'>
        @foreach($q as $externalLinks)
            <tr>
                <td class='text-left'>
                    <div style='display:inline-block; width: 80%;'>
                        <strong>{{  $externalLinks->title }}</strong>
                        <div style='padding-top:8px;'><small class='text-muted'>{{ $externalLinks->url }}</small></div>
                    </div>
                    <div class='pull-right' style='font-size: 20px; padding-right: 20px'>
                        <a class='text-danger' href='?course={{ $course_code }}&amp;delete={{ getIndirectReference($externalLinks->id) }}'><span class='fa fa-times'></span></a>
                    </div>
                </td>
            </tr>
        @endforeach
        </table>
    </div>


@endsection