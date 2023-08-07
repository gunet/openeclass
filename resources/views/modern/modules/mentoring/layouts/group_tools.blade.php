   
<ul class='tools-menu-group p-0'>
    <li class='p-0'>
        <a class='btn normalBlueText small-text textSemiBold text-start p-0 w-100 mb-2' href="{{ $urlAppend }}modules/mentoring/programs/group/members.php?group_id={!! getInDirectReference($group_id) !!}">
            <span class='fa fa-user'></span>&nbsp{{ trans('langGroupMembers')}}
        </a>
    </li>

    @php 
        $announcements = Database::get()->querySingle("SELECT announcements FROM mentoring_group_properties
                                                    WHERE mentoring_program_id = ?d
                                                    AND group_id = ?d",$mentoring_program_id,$group_id)->announcements; 
    @endphp

    @if($announcements == 1)
    <li class='p-0'>
        <a class='btn normalBlueText small-text textSemiBold text-start p-0 w-100 mb-2' href="{{ $urlAppend }}modules/mentoring/programs/group/announcements/index.php?group_id={!! getInDirectReference($group_id) !!}">
            <span class='fa fa-bullhorn fa-fw'></span>&nbsp{{ trans('langAnnouncements')}}
        </a>
    </li>
    @endif
    
    @php 
        $wall = Database::get()->querySingle("SELECT wall FROM mentoring_group_properties
                                                    WHERE mentoring_program_id = ?d
                                                    AND group_id = ?d",$mentoring_program_id,$group_id)->wall; 
    @endphp

    @if($wall == 1)
    <li class='p-0'>
        <a class='btn normalBlueText small-text textSemiBold text-start p-0 w-100 mb-2' href="{{ $urlAppend }}modules/mentoring/programs/wall/my_doc_wall.php?group_id={!! getInDirectReference($group_id) !!}&wall">
            <span class='fa fa-list'></span>&nbsp{{ trans('langWall')}}
        </a>
    </li>
    @endif
    
    @php 
        $documents = Database::get()->querySingle("SELECT documents FROM mentoring_group_properties
                                                    WHERE mentoring_program_id = ?d
                                                    AND group_id = ?d",$mentoring_program_id,$group_id)->documents; 
    @endphp
    @if($documents == 1)
    <li class='p-0'>
        <a class='btn normalBlueText small-text textSemiBold text-start p-0 w-100 mb-2' href="{{ $urlAppend }}modules/mentoring/programs/group/document/mydoc.php?group_id={!! getInDirectReference($group_id) !!}">
            <span class='fa fa-file'></span>&nbsp{{ trans('langDocsMentoringGroups')}}
        </a>
    </li>
    @endif
    

    
    @php 
        $forum = Database::get()->querySingle("SELECT forum FROM mentoring_group_properties
                                                    WHERE mentoring_program_id = ?d
                                                    AND group_id = ?d",$mentoring_program_id,$group_id)->forum; 
    @endphp
    @if($forum == 1)
    <li class='p-0'>
        <a class='btn normalBlueText small-text textSemiBold text-start p-0 w-100 mb-2' href="{{ $urlAppend }}modules/mentoring/programs/group/forum_group.php?forum_group_id={!! getInDirectReference($group_id) !!}">
            <span class='fa fa-comment'></span>&nbsp{{ trans('langForumMentoringGroups')}}
        </a>
    </li>
    @endif
    

    @if($isCommonGroup != 1)
        <li class='p-0'>
            <a class='btn normalBlueText small-text textSemiBold text-start p-0 w-100 mb-2' href="{{ $urlAppend }}modules/mentoring/programs/group/meeting_space.php?group_id={!! getInDirectReference($group_id) !!}">
                @php 
                    if($is_admin or $is_tutor_of_mentoring_program){
                        $totalMeetings = Database::get()->querySingle("SELECT COUNT(*) as total FROM mentoring_rentezvous
                                                                        WHERE mentoring_program_id = ?d AND group_id = ?d
                                                                        AND end > NOW()",$mentoring_program_id,$group_id)->total;
                    }elseif($is_editor_current_group){
                        $totalMeetings = Database::get()->querySingle("SELECT COUNT(*) as total FROM mentoring_rentezvous
                                                                        WHERE mentoring_program_id = ?d AND group_id = ?d
                                                                        AND end > NOW()
                                                                        AND mentor_id = ?d",$mentoring_program_id,$group_id,$uid)->total;
                    }elseif($is_mentee){
                        $totalMeetings = Database::get()->querySingle("SELECT COUNT(*) as total FROM mentoring_rentezvous
                                                                        WHERE mentoring_program_id = ?d AND group_id = ?d
                                                                        AND end > NOW()
                                                                        AND id IN (SELECT mentoring_rentezvous_id FROM mentoring_rentezvous_user WHERE mentee_id = ?d)",$mentoring_program_id,$group_id,$uid)->total;
                    }
                @endphp
                <span class='fa-solid fa-handshake'></span>&nbspMeetings
                
                @if($totalMeetings > 0 and ($is_admin or $is_editor_current_group or $is_tutor_of_mentoring_program or $is_mentee)) 
                    &nbsp<span class='badge bg-primary text-white'>{{ $totalMeetings }}</span> 
                @endif
                
            </a>
        </li>

        <li class='p-0'>
            <a class='btn normalBlueText small-text textSemiBold text-start p-0 w-100 mb-2' href="{{ $urlAppend }}modules/mentoring/programs/group/bookingsMentee/mybookings.php?space_group_id={!! getInDirectReference($group_id) !!}">
                @php 
                    if($is_admin or $is_tutor_of_mentoring_program){
                        $totalBookings = Database::get()->querySingle("SELECT COUNT(*) as total FROM mentoring_booking
                                                                        WHERE mentoring_program_id = ?d AND group_id = ?d AND accepted = ?d
                                                                        AND end > NOW()",$mentoring_program_id,$group_id,0)->total;
                    }elseif($is_editor_current_group){
                        $totalBookings = Database::get()->querySingle("SELECT COUNT(*) as total FROM mentoring_booking
                                                                        WHERE mentoring_program_id = ?d AND group_id = ?d AND accepted = ?d
                                                                        AND end > NOW()
                                                                        AND mentor_id = ?d",$mentoring_program_id,$group_id,0,$uid)->total;
                    }elseif($is_mentee){
                        $totalBookings = Database::get()->querySingle("SELECT COUNT(*) as total FROM mentoring_booking
                                                                        WHERE mentoring_program_id = ?d AND group_id = ?d AND accepted = ?d
                                                                        AND end > NOW()
                                                                        AND id IN (SELECT mentoring_booking_id FROM mentoring_booking_user WHERE mentee_id = ?d)",$mentoring_program_id,$group_id,0,$uid)->total;
                    }
                @endphp
                <span class='fa fa-ticket'></span>&nbspBookings
                @if($totalBookings > 0 and ($is_admin or $is_editor_current_group or $is_tutor_of_mentoring_program or $is_mentee)) 
                    &nbsp<span class='badge bg-primary text-white'>{{ $totalBookings }}</span> 
                @endif
               
            </a>
        </li>
    @endif
    
    @php 
        $self_request = Database::get()->querySingle("SELECT self_request FROM mentoring_group_properties
                                                    WHERE mentoring_program_id = ?d
                                                    AND group_id = ?d",$mentoring_program_id,$group_id)->self_request; 
    @endphp
    @if($self_request == 1)
        @if($is_editor_current_group or $is_tutor_of_mentoring_program or $is_admin)
            <li class='p-0'>
                @php 
                    $countRequestsReg = Database::get()->querySingle("SELECT COUNT(group_id) AS gi FROM mentoring_group_members 
                                                                    WHERE group_id = ?d AND is_tutor = ?d AND status_request = ?d", $group_id, 0, 0)->gi;
                @endphp
                <a class='btn normalBlueText small-text textSemiBold text-start p-0 w-100 mb-2' href="{{ $urlAppend }}modules/mentoring/programs/group/request_group.php?group_id={!! getInDirectReference($group_id) !!}">
                    <span class='fa fa-registered'></span>&nbsp{{ trans('langRequestsRegisterGroups')}}
                    @if($countRequestsReg > 0)
                        &nbsp<span class='badge bg-primary'>{{ $countRequestsReg }}</span>
                    @endif
                </a>
            </li>
        @endif
    @endif
    

    @if($is_editor_current_group or $is_tutor_of_mentoring_program or $is_admin)
        <li class='p-0'>
            <a class='btn normalBlueText small-text textSemiBold text-start p-0 w-100 mb-2' href='{{ $urlAppend }}modules/mentoring/programs/group/edit_group.php?edit_group=1&group_id={!! getIndirectReference($group_id) !!}'>
                <span class='fa fa-edit'></span>&nbsp{{ trans('langEditGroupMentoring') }}
            </a>
        </li>
            
        @if($isCommonGroup == 0)
            <li class='p-0'>
                <button class="btn text-danger small-text p-0 mb-2 mt-1"
                    data-bs-toggle="modal" data-bs-target="#DeleteGroupModal" >
                    <span class='fa fa-times'></span>&nbsp{{ trans('langDeleteMentoringGroup') }}
                </button>
            </li>
        @endif
    @endif
    
</ul>