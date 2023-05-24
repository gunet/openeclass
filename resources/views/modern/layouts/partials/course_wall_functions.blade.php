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

@if (allow_to_post($course_id, $uid, $is_editor))

    @php 
        $content = Session::has('content')? Session::get('content'): '';
        $extvideo = Session::has('extvideo')? Session::get('extvideo'): '';
    @endphp

    <div class="col-12">
        <div class='card panelCard p-md-3 bg-white'>
            <div class='card-header bg-white border-0'>
               
                    <span class='text-uppercase normalColorBlueText TextBold fs-6'>{{trans('langWall')}}</span>
                
            </div>
            <div class='card-body'>
                <div class="form-wrapper form-edit rounded">
                    <form id="wall_form" method="post" action="{{$urlServer}}modules/wall/index.php?course={{$course_code}}&fromCoursePage" enctype="multipart/form-data">
                        <fieldset> 
                            <div class="form-group">
                                
                                <textarea id="textr" onfocus="expand_form();" class="form-control" placeholder="{{ trans('langTypeOutMessage') }}" rows="1" name="message" id="message_input">{!! $content !!}</textarea>
                            </div>
                            <div id="resources_panel" class="panel panel-default collapse mt-3 border-0">
                                <div class="panel-body border-0">
                                    <ul class="nav nav-tabs walltabs border-0">
                                        <li class="nav-item"><a id="nav_extvideo" class="nav-link active" data-bs-toggle="tab" href="#extvideo_video_div">{{ trans('langWallExtVideo') }}</a></li>
                                        @if ($is_editor || visible_module(MODULE_ID_VIDEO))
                                            <li><a id="nav_video" class="nav-link" data-bs-toggle="tab" href="#videos_div">{{ trans('langVideo') }}</a></li>
                                        @endif
                                        @if ($is_editor || visible_module(MODULE_ID_DOCS))
                                            <li><a id="nav_docs" class="nav-link" data-bs-toggle="tab" href="#docs_div">{{ trans('langDoc') }}</a></li>
                                        @endif
                                        @if (($is_editor && get_config('mydocs_teacher_enable')) || (!$is_editor && get_config('mydocs_student_enable')))
                                            <li><a id="nav_mydocs" class="nav-link" data-bs-toggle="tab" href="#mydocs_div">{{ trans('langMyDocs') }}</a></li>
                                        @endif
                                        @if ($is_editor || visible_module(MODULE_ID_LINKS))
                                            <li><a id="nav_links" class="nav-link" data-bs-toggle="tab" href="#links_div">{{ trans('langLinks') }}</a></li>
                                        @endif
                                        @if ($is_editor || visible_module(MODULE_ID_EXERCISE))
                                            <li><a id="nav_exercises" class="nav-link" data-bs-toggle="tab" href="#exercises_div">{{ trans('langExercises') }}</a></li>
                                        @endif
                                        @if ($is_editor || visible_module(MODULE_ID_ASSIGN))
                                            <li><a id="nav_assigments" class="nav-link" data-bs-toggle="tab" href="#assignments_div">{{ trans('langWorks') }}</a></li>
                                        @endif
                                        @if ($is_editor || visible_module(MODULE_ID_CHAT))
                                            <li><a id="nav_chats" class="nav-link" data-bs-toggle="tab" href="#chats_div">{{ trans('langChat') }}</a></li>
                                        @endif
                                        @if ($is_editor || visible_module(MODULE_ID_QUESTIONNAIRE))
                                            <li><a id="nav_polls" class="nav-link" data-bs-toggle="tab" href="#polls_div">{{ trans('langQuestionnaire') }}</a></li>
                                        @endif
                                        @if ($is_editor || visible_module(MODULE_ID_FORUM))
                                            <li><a id="nav_forums" class="nav-link" data-bs-toggle="tab" href="#forums_div">{{ trans('langForum') }}</a></li>
                                        @endif
                                    </ul>
                                    <div class="tab-content mt-4">
                                        <div class="form-group tab-pane fade show active" id="extvideo_video_div" role="tabpanel" aria-labelledby="nav_extvideo" style="padding:10px">
                                            <label for="extvideo_video">{{ trans('langWallExtVideoLink') }}</label>
                                            <input class="form-control" type="url" name="extvideo" id="extvideo_video" value="{!! $extvideo !!}">
                                        </div>

                                        @if ($is_editor || visible_module(MODULE_ID_VIDEO))
                                            <div class="form-group tab-pane fade" id="videos_div" role="tabpanel" aria-labelledby="nav_video" style="padding:10px">
                                                {!! list_videos() !!}
                                            </div>
                                        @endif

                                        @if ($is_editor || visible_module(MODULE_ID_DOCS))
                                            <div class="form-group tab-pane fade" id="docs_div" role="tabpanel" aria-labelledby="nav_docs" style="padding:10px">
                                                <input type="hidden" name="doc_ids" id="docs">
                                                {!! list_docs() !!}
                                            </div>
                                        
                                        @endif

                                        @if (($is_editor && get_config('mydocs_teacher_enable')) || (!$is_editor && get_config('mydocs_student_enable')))
                                            <div class="form-group tab-pane fade" id="mydocs_div" role="tabpanel" aria-labelledby="nav_mydocs" style="padding:10px">
                                                <input type="hidden" name="mydoc_ids" id="mydocs">
                                                    {!! list_docs(NULL,'mydocs') !!}
                                            </div>
                                        @endif

                                        @if ($is_editor || visible_module(MODULE_ID_LINKS))
                                            <div class="form-group tab-pane fade" id="links_div" role="tabpanel" aria-labelledby="nav_links" style="padding:10px">
                                                {!! list_links() !!}
                                            </div>
                                        @endif

                                        @if ($is_editor || visible_module(MODULE_ID_EXERCISE))
                                            <div class="form-group tab-pane fade" id="exercises_div" role="tabpanel" aria-labelledby="nav_exercises" style="padding:10px">
                                                {!! list_exercises() !!}
                                            </div>
                                        @endif

                                        @if ($is_editor || visible_module(MODULE_ID_ASSIGN))
                                            <div class="form-group tab-pane fade" id="assignments_div" role="tabpanel" aria-labelledby="nav_assigments" style="padding:10px">
                                                {!! list_assignments() !!}
                                            </div>

                                        @endif

                                        @if ($is_editor || visible_module(MODULE_ID_CHAT))
                                            <div class="form-group tab-pane fade" id="chats_div" role="tabpanel" aria-labelledby="nav_chats" style="padding:10px">
                                                {!! list_chats() !!}
                                            </div>

                                        @endif

                                        @if ($is_editor || visible_module(MODULE_ID_QUESTIONNAIRE))
                                            <div class="form-group tab-pane fade" id="polls_div" role="tabpanel" aria-labelledby="nav_polls" style="padding:10px">
                                                {!! list_polls() !!}
                                            </div>
                                        @endif

                                        @if ($is_editor || visible_module(MODULE_ID_FORUM)) 
                                            <div class="form-group tab-pane fade" id="forums_div" role="tabpanel" aria-labelledby="nav_forums" style="padding:10px">
                                                {!! list_forums() !!}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="form-group mt-3">
                                {!!
                                    form_buttons(array(
                                        array(
                                            'class' => 'submitAdminBtn',
                                            'text'  =>  trans('langSubmit'),
                                            'name'  =>  'submit',
                                            'value' =>  trans('langSubmit')
                                        )
                                    ))
                                !!} 
                            </div>  
                        </fieldset>      
                    </form>
                </div>
            </div>
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
@php 
    $posts_per_page = 10;
    $posts = Database::get()->queryArray("SELECT id, user_id, content, extvideo, FROM_UNIXTIME(timestamp) as datetime, pinned  FROM wall_post WHERE course_id = ?d ORDER BY pinned DESC, timestamp DESC LIMIT ?d", $course_id, $posts_per_page);
@endphp

    @if (count($posts) == 0)
        <div class="col-12 mt-3"><div class="alert alert-warning">{{ trans('langNoWallPosts') }}</div></div>
    @else
        {!! generate_infinite_container_html($posts, $posts_per_page, 2) !!}<div class='mb-4'></div>
    @endif