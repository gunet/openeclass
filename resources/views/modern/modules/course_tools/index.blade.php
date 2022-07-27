
@extends('layouts.default')

@push('head_scripts')
    <script type="text/javascript">
        var langEmptyGroupName = '{{ trans('langNoPgTitle') }}'
    </script>
@endpush

@section('content')

<div class="pb-3 pt-3">

    <div class="container-fluid main-container">

        <div class="row">

            <div id="background-cheat-leftnav" class="col-xl-2 col-lg-3 col-md-0 col-sm-0 col-0 justify-content-center col_sidebar_active"> 
                <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block">
                    @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                </div>
            </div>

            <div class="col-xl-10 col-lg-9 col-md-12 col-sm-12 col-12 justify-content-center col_maincontent_active">
                    
                <div class="row p-lg-5 p-md-5 ps-1 pe-2 pt-5 pb-5">

                    <nav class="navbar navbar-expand-lg navrbar_menu_btn">
                        <button type="button" id="menu-btn" class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block btn btn-primary menu_btn_button">
                            <i class="fas fa-align-left"></i>
                            <span></span>
                        </button>
                        
                       
                        <a class="btn btn-primary d-lg-none mr-auto" type="button" data-bs-toggle="offcanvas" href="#collapseTools" role="button" aria-controls="collapseTools" style="margin-top:-10px;">
                            <i class="fas fa-tools"></i>
                        </a>
                    </nav>

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])


                    <div class="offcanvas offcanvas-start d-lg-none mr-auto" tabindex="-1" id="collapseTools" aria-labelledby="offcanvasExampleLabel">
                        <div class="offcanvas-header">
                            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                        </div>
                        <div class="offcanvas-body">
                            @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                        </div>
                    </div>

                    

                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

                    

                    
                    @if(Session::has('message'))
                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mt-5'>
                        <p class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                            {{ Session::get('message') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </p>
                    </div>
                    @endif

                    <div class='col-sm-12'>
                        <div class="panel panel-default">
                            <div class='p-2 bg-light'>
                                <h3 class='panel-title text-center'>{{ trans('langActivateCourseTools') }}</h3>
                            </div>
                            <form name="courseTools" action="{{ $_SERVER['SCRIPT_NAME'] }}?course={{ $course_code }}" method="post" enctype="multipart/form-data">
                                <div class="table-responsive" style='margin-top:-25px;'>
                                    <table class="announcements_table">
                                        <tr class='notes_thead'>
                                        <th width="45%" class="text-white text-center">{{ trans('langInactiveTools') }}</th>
                                        <th width="10%" class="text-white text-center">{{ trans('langMove') }}</th>
                                        <th width="45%" class="text-white text-center">{{ trans('langActiveTools') }}</th>
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
                                                <button type="button" class="btn btn-secondary" onClick="move('inactive_box','active_box')"><span class="fa fa-arrow-right"></span></button><br><br>
                                                <button type="button" class="btn btn-secondary" onClick="move('active_box','inactive_box')"><span class="fa fa-arrow-left"></span></button>
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
                    </div>

                    <div class='col-sm-12 mt-3'>
                        <div class='panel panel-default panel-action-btn-default'>
                            <div class='panel-heading'>
                                <div class='float-end pt-3 pb-2 pe-3'>
                                    <div id='operations_container'>
                                        <a class='btn btn-success mt-1 ms-md-0 ms-3' href='{{ $_SERVER['SCRIPT_NAME'] }}?course={{ $course_code }}&amp;action=true'><span class='fa fa-plus-circle'></span> {{ trans('langAddExtLink') }}</a>
                                    </div>
                                </div>
                                <div class='p-3'>
                                    <h3 class='panel-title control-label-notes pt-2'> {{ trans('langOperations') }}</h3>
                                </div>
                            </div>
                            
                            <table class='announcements_table mb-2 bg-light'>
                            @foreach($q as $externalLinks)
                                <tr>
                                    <td class='text-left'>
                                        <div style='display:inline-block; width: 80%;'>
                                            <strong>{{  $externalLinks->title }}</strong>
                                            <div style='padding-top:8px;'><small class='text-muted'>{{ $externalLinks->url }}</small></div>
                                        </div>
                                        <div class='float-end'>
                                            <a class='text-danger' href='?course={{ $course_code }}&amp;delete={{ getIndirectReference($externalLinks->id) }}'><span class='fa fa-times'></span></a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </table>
                            
                        </div>
                    </div>

                    <div class='col-sm-12 mt-3'>
                        <div class='panel panel-default'>
                            <div class='panel-heading'>
                                <span class='panel-title control-label-notes pt-2' style='line-height: 45px;'>{{ trans('langLtiConsumer') }}</span>
                                <span class='float-end pt-1'>
                                    <a class='btn btn-success' href='../lti_consumer/index.php?course={{ $course_code }}&amp;add=1'>
                                <span class='fa fa-plus-circle'></span>{{ trans('langNewLTITool') }}</a>
                            </div>
                        </div>
                    </div>

                    {!! lti_app_details() !!}
                </div>
            </div>

        </div>
    </div>
</div>

@endsection