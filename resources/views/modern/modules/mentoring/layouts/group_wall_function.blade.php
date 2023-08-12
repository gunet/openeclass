
@push('head_scripts')
    <script src="{{ $urlServer }}/js/autosize/autosize.min.js"></script>
    <link href="{{ $urlServer }}/js/jstree3/themes/proton/style.min.css?v=4.0-dev" rel="stylesheet" type="text/css">
    <script src="{{ $urlServer }}/js/jstree3/jstree.js"></script>
    <script type='text/javascript'>
        function expand_form() {
            $("#resources_panel").collapse('show');
        }
    </script>
    <script> autosize(document.querySelector('textarea')); </script>
@endpush


@if(allow_to_post($mentoring_program_id, $uid, $is_editor_wall_common_group))

    @php 
        $content = Session::has('content')? Session::get('content'): '';
        $extvideo = Session::has('extvideo')? Session::get('extvideo'): '';
    @endphp

    <div class="card border-0 p-0 rounded mb-3">
        <div class='card-body p-1 border-0 bg-white'>
            <form id="wall_form" method="post" action="{{ $urlServer }}modules/mentoring/programs/wall/index.php?group_id={!! getInDirectReference($group_id) !!}&fromCommonWall=true" enctype="multipart/form-data">
                <fieldset> 
                    <div class="form-group">
                        <textarea style='min-height:100px' id="textr" onfocus="expand_form();" class="form-control bg-white rounded-2" placeholder="{{ trans('langPressMessage') }}" name="message">{!! $content !!}</textarea>
                    </div>
                    <div id="resources_panel" class="panel panel-default collapse mt-3 rounded-2 border-0">
                        <div class="panel-body rounded-2">
                            <ul class="nav nav-tabs mb-3">
                                <li class="nav-item"><a id="nav_extvideo" class="nav-link active" data-bs-toggle="tab" href="#extvideo_video_div">{{ trans('langWallExtVideo') }}</a></li>
                                @if ($is_editor_wall_common_group || visible_module(MODULE_ID_DOCS))
                                    <li class="nav-item"><a id="nav_docs" class="nav-link" data-bs-toggle="tab" href="#docs_div">{{ trans('langDoc') }}</a></li>
                                @endif
                                @if (($is_editor_wall_common_group && get_config('mydocs_teacher_enable')) || (!$is_editor_wall_common_group && get_config('mydocs_student_enable')))
                                    <li class="nav-item"><a id="nav_mydocs" class="nav-link" data-bs-toggle="tab" href="#mydocs_div">{{ trans('langMyDocs') }}</a></li>
                                @endif
                                
                                @if ($is_editor_wall_common_group || visible_module(MODULE_ID_FORUM))
                                    <li class="nav-item"><a id="nav_forums" class="nav-link" data-bs-toggle="tab" href="#forums_div">{{ trans('langForum') }}</a></li>
                                @endif
                            </ul>
                            <div class="tab-content">
                                <div class="form-group tab-pane fade show active" id="extvideo_video_div" role="tabpanel" aria-labelledby="nav_extvideo" style="padding:10px">
                                    <label class='control-label-notes' for="extvideo_video">{{ trans('langWallExtVideoLink') }}</label>
                                    <input class="form-control rounded-2" type="url" name="extvideo" id="extvideo_video" value="{!! $extvideo !!}">
                                </div>


                                @if ($is_editor_wall_common_group || visible_module(MODULE_ID_DOCS))
                                    <div class="form-group tab-pane fade" id="docs_div" role="tabpanel" aria-labelledby="nav_docs" style="padding:10px">
                                        <input type="hidden" name="doc_ids" id="docs">
                                        {!! list_docs() !!}
                                    </div>
                                
                                @endif

                                @if (($is_editor_wall_common_group && get_config('mydocs_teacher_enable')) || (!$is_editor_wall && get_config('mydocs_student_enable')))
                                    <div class="form-group tab-pane fade" id="mydocs_div" role="tabpanel" aria-labelledby="nav_mydocs" style="padding:10px">
                                        <input type="hidden" name="mydoc_ids" id="mydocs">
                                            {!! list_docs(NULL,'mydocs') !!}
                                    </div>
                                @endif


                                @if ($is_editor_wall_common_group || visible_module(MODULE_ID_FORUM)) 
                                    <div class="form-group tab-pane fade" id="forums_div" role="tabpanel" aria-labelledby="nav_forums" style="padding:10px">
                                        {!! list_forums() !!}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="form-group mt-3">
                        <div class='col-12 d-flex justify-content-start align-items-center'>
                            {!!
                                form_buttons(array(
                                    array(
                                        'class' => 'btnSubmitWallPost TextRegular',
                                        'text'  =>  trans('langSubmit'),
                                        'name'  =>  'submit',
                                        'value' =>  trans('langSubmit')
                                    )
                                ))
                            !!} 
                        </div>
                    </div>  
                </fieldset>      
            </form>
        </div>
    </div>
@endif


@push('head_scripts')
    <script type="text/javascript" src="{{ $urlServer }}/js/waypoints/jquery.waypoints.min.js?v=4.0-dev"></script>
    <script type="text/javascript" src="{{ $urlServer }}/js/waypoints/shortcuts/infinite.min.js?v=4.0-dev"></script>
    <link rel="stylesheet" type="text/css" href="{{ $urlServer }}/modules/rating/style.css">
    <script src="{{ $urlServer }}/modules/rating/js/thumbs_up/rating.js" type="text/javascript"></script>
    <script>
        var infinite = new Waypoint.Infinite({
            element: $(".infinite-container")[0]
        });
    </script>
    <script type='text/javascript'>
        $('body').on('click', '.colorboxframe', function() {
            $('.colorboxframe').colorbox();
        });
        $('body').on('click', '.colorbox', function() {
            $('.colorbox').colorbox();
        });
    </script>
    <script src="{{ $urlServer }}/js/screenfull/screenfull.min.js"></script>
    <script type='text/javascript'>
            $(function(){
                $('.fileModal').click(function (e)
                {
                    e.preventDefault();
                    var fileURL = $(this).attr('href');
                    var downloadURL = $(this).prev('input').val();
                    var fileTitle = $(this).attr('title');
                    var buttons = {};
                    if (downloadURL) {
                        buttons.download = {
                                label: '<i class=\"fa fa-download\"></i> {{ trans("langDownload") }}',
                                className: 'submitAdminBtn',
                                callback: function (d) {
                                    window.location = downloadURL;
                                }
                        };
                    }
                    buttons.print = {
                                label: '<i class=\"fa fa-print\"></i> {{ trans("langPrint") }}',
                                className: 'submitAdminBtn',
                                callback: function (d) {
                                    var iframe = document.getElementById('fileFrame');
                                    iframe.contentWindow.print();
                                }
                            };
                    if (screenfull.enabled) {
                        buttons.fullscreen = {
                            label: '<i class=\"fa fa-arrows-alt\"></i> {{ trans("langFullScreen") }}',
                            className: 'submitAdminBtn',
                            callback: function() {
                                screenfull.request(document.getElementById('fileFrame'));
                                return false;
                            }
                        };
                    }
                    buttons.newtab = {
                        label: '<i class=\"fa fa-plus\"></i> {{ trans("langNewTab") }}',
                        className: 'submitAdminBtn',
                        callback: function() {
                            window.open(fileURL);
                            return false;
                        }
                    };
                    buttons.cancel = {
                                label: '{{ trans("langCancel") }}',
                                className: 'cancelAdminBtn'
                            };
                    bootbox.dialog({
                        size: 'large',
                        title: fileTitle,
                        message: '<div class=\"row\">'+
                                    '<div class=\"col-sm-12\">'+
                                        '<div class=\"iframe-container\"><iframe id=\"fileFrame\" src=\"'+fileURL+'\" style=\"width:100%; height:500px;\"></iframe></div>'+
                                    '</div>'+
                                '</div>',
                        buttons: buttons
                    });
                });
            });

    </script>
@endpush

