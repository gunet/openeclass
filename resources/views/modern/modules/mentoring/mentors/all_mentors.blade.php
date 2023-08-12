                         
@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
    <div class='{{ $container }}'>
        <div class="row m-auto">

                    @if(isset($_SESSION['uid']))
                        <div class='col-12'>
                            <nav class='breadcrumb_mentoring' style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='currentColor'/%3E%3C/svg%3E&#34;);" aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a class="@if(!isset($_SESSION['uid'])) no_uid_menu @endif TextSemiBold" href="{{ $urlAppend }}modules/mentoring/mentoring_platform_home.php"><span class='fa fa-home'></span>&nbsp{{ trans('langHomeMentoringPlatform') }}</a></li>
                                    <li class="breadcrumb-item active TextMedium" aria-current="page">{{ $toolName }}</li>
                                </ol>
                            </nav>
                        </div>
                    @endif

                    @include('modules.mentoring.common.common_current_title')

                    <div class='col-12 mb-4'>
                        <div class='col-lg-7 col-md-9 col-12 ms-auto me-auto '>
                            <p class='TextMedium text-center text-justify'>{{ trans('langInfoMentorsText')}}</p>
                        </div>
                    </div>
                    
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

                    <div id="loaderMentors" class="modal fade in" role="dialog">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-body bg-light d-flex justify-content-center align-items-center">
                                    <img src='{{ $urlAppend }}template/modern/img/ajax-loader.gif'>
                                    <span>{{ trans('langPlsWait') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    
                    @if(count($all_specializations))
                        <div id = "CloseSidebar"></div>
                        <div class='row ms-0 RowMentors'>
                            <div class='d-block d-lg-none'>
                                <div class='col-12 ps-2 pe-2'>
                                    <a class="ShowSidebarSpecializations btn btn-sm bgEclass blackBlueText float-end mb-3">
                                        <span class='fa fa-bars fs-5'></span>
                                    </a>
                                </div>
                            </div>
                            <div class='col-lg-3 px-0 SidebarSpecializations'>
                                @include('modules.mentoring.mentors.searchMentorByTags',['all_specializations' => $all_specializations])
                            </div>
                            <div class='col-lg-9 col-12 px-0'>
                                <div class='col-12 ps-lg-3'>
                                    <div class='col-12 ps-2 pe-2'>
                                        <div class="input-group mb-3 searchMentorsByKeys shadow-sm">
                                            <span class="input-group-text bg-white border-0" id="basic-searchKey">
                                                <span class='fa fa-search'></span>
                                            </span>
                                            <input id='searchMentorsByAddKey' type="text" class="form-control border-0" placeholder="{{ trans('langByKewWords') }}" aria-label="{{ trans('langByKewWords') }}" aria-describedby="basic-searchKey">
                                        </div>
                                    </div>
                                </div>
                                <div id="MentorsChoosing"></div>
                            </div>
                        </div>
                    @else
                        <div class='col-12 ps-3 pe-3'>
                            <div class='col-12 bg-white p-3 rounded-2 solidPanel'><div class='alert alert-warning rounded-2'>{{ trans('langNoAvailableMentoringMentors') }}</div></div>
                        </div>
                    @endif
                       
                


        </div>
      
    </div>
</div>

<script>

    $('.ShowSidebarSpecializations').on('click',function(){
        $('.SidebarSpecializations').addClass('SidebarOn');
    });
    $('#closeSidebarSpecializations').on('click',function(){
        $('.SidebarSpecializations').removeClass('SidebarOn');
    });

    if($('#availableId').is(":checked")){
        $('#unavailableId').attr('disabled',true);
    }
    $('#availableId').on('click',function(){
        if($('#availableId').is(":checked")){
            $('#unavailableId').attr('disabled',true);
        }else{
            $('#unavailableId').prop("disabled", false);
        }
    });


    if($('#unavailableId').is(":checked")){
        $('#availableId').attr('disabled',true);
    }
    $('#unavailableId').on('click',function(){
        if($('#unavailableId').is(":checked")){
            $('#availableId').attr('disabled',true);
        }else{
            $('#availableId').prop("disabled", false);
        }
        
    });

    $(document).ready(function(){
        var array_tags = [];
        var array_specializations = [];
        var loop = 1;
        var availableValue = 1;
        sendAllTags(array_tags,loop,availableValue);
        sendTags(array_tags,array_specializations);

        $('.checkAvailable').on('click',function(){
            if($('#availableId').is(":checked")){
                availableValue = $('#availableId').val();
            }else if($('#unavailableId').is(":checked")){
                availableValue = $('#unavailableId').val();
            }else{
                availableValue = 2;
            }
        });
        
        $('#SearchMentors').click(function(){

            $('.SidebarSpecializations').removeClass('SidebarOn');

            $('#loaderMentors').modal('toggle');
            setTimeout(function() { 
                $('#loaderMentors').modal('hide');
                var jsonString = JSON.stringify(array_tags);
                var specializations = JSON.stringify(array_specializations);
                $.ajax({
                    type: "POST",
                    url: "{{ $urlAppend }}modules/mentoring/mentors/all_mentors.php",
                    data: {dataa : jsonString, isAvailable : availableValue, Specialization : specializations, FirstLoop : 'false'}, 
                    cache: true,
                    success: function(json){
                        if(json){
                            $('#MentorsChoosing').html(json);
                        }
                    }
                });
            }, 1000);
        });

        $('.uncheckBtn').on('click',function(){
            uncheckAll(array_tags,array_specializations);
        });

        $('#searchMentorsByAddKey').keyup(function() {   
            loadMentor();
        });

    });

    function loadMentor(){
        var searchval = $("#searchMentorsByAddKey").val();
        $.ajax({
            type: "POST",
            url: '{{ $urlAppend }}modules/mentoring/mentors/all_mentors.php?term='+searchval,
            success: function(json){
                if(json){
                    $('#MentorsChoosing').html(json);
                }
            }
        })
    }


    function sendTags(array_tags,array_specializations){
        $(".tagClick").change(function(){

            if ($(this).is(':checked')) {
                var tag_id = ($(this).val());

                var new_tag_id = tag_id.split(',');
                var new_tag_id2 = tag_id.split(',');

                tag_id = new_tag_id[0];
                var specialization_id = new_tag_id2[1];


                array_tags.push(tag_id);
                array_specializations.push(specialization_id);

            } else {
                var tag_id = ($(this).val());

                var new_tag_id = tag_id.split(',');
                var new_tag_id2 = tag_id.split(',');

                tag_id = new_tag_id[0];
                var specialization_id = new_tag_id2[1];

                array_tags.splice($.inArray(tag_id, array_tags),1);
                array_specializations.splice($.inArray(specialization_id, array_specializations),1);
            }
        });  
    }

    function sendAllTags(array_tags,loop,AvailableMentors){
        if(loop == 1){
            $(".tagClick").trigger("click");
            var all_tags = [];
            all_tags.push($('#allTagsSelect').val());
            for(i=0; i<all_tags.length; i++){
                for(j=0; j<$('#allTagsSelect').val().length; j++){
                    array_tags.push(all_tags[i][j]);
                }
            }

            var jsonString = JSON.stringify(array_tags);
            var availableValue = AvailableMentors;
            $.ajax({
                type: "POST",
                url: "{{ $urlAppend }}modules/mentoring/mentors/all_mentors.php",
                data: {dataa : jsonString , isAvailable : availableValue, FirstLoop : 'true'}, 
                cache: true,
                success: function(json){
                    if(json){
                        $('#MentorsChoosing').html(json);
                    }
                }
            });
            
            loop++;
            $('.tagClick').prop('checked',false);
            array_tags.splice(0,array_tags.length);
        }
    }

    function uncheckAll(array_tags,array_specializations){
        $('input[type="checkbox"]:checked').prop('checked',false);
        array_tags.splice(0,array_tags.length);
        array_specializations.splice(0,array_specializations.length);
    }


</script>

@endsection