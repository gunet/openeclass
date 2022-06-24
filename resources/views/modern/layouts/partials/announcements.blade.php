@if(count($announcements)>0)
    <div class="table-responsive">
        <table class="table announcements_table" id="" style="overflow: inherit">
            <thead class="notes_thead">
                <tr>
                    <th class='text-white'>{{trans('langAnnouncement')}}</th>
                    <th class='text-white'>{{trans('langDate')}}</th>
                    <th class='text-white'>{{trans('langCourse')}}</th>
                    
                    @if($is_editor == 1)
                        <th class='text-white'>{{trans('langCurrentStatus')}}</th>
                        <th class='text-white text-end'><i class="fas fa-cogs"></i></th>
                    @endif
                </tr>
            </thead>
            <tbody>
                <?php $size = count($announcements);?>
                    <?php for($i=0; $i<$size; $i++){?>
                        <tr>
                            <td>{!! $announcements[$i][0] !!}</td>
                            
                            <td>{{$announcements[$i][1]}}</td>
                            <td>{{$title_course}}</td>
                            
                            @if($is_editor == 1)
                            <td>{!! $announcements[$i][2] !!}</td>
                            <td>
                                    <div class="dropdown text-end">
                                        <button class="btn btn-secondary dropdown-toggle myDropDownDocument" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="fas fa-cogs"></i>
                                        </button>
                                        <ul class="row p-4 dropdown-menu myuls" aria-labelledby="dropdownMenuButton1">
                                            <li><a href="{{$urlAppend}}modules/announcements/edit.php?course={{$course_code}}&modify={{$announcements_ids[$i]}}"><i class="fas fa-edit"></i> {{trans('langElaboration')}}</a></li>
                                            <li>
                                                <form action="{{$urlAppend}}modules/announcements/index.php" method="POST">
                                                    <input type="hidden" name="course-announcement" value="{{$course_code}}">
                                                    <input type="hidden" name="editor" value="{{$is_editor}}">
                                                    <input type="hidden" name="action" value="visible">
                                                    <input type="hidden" name="value" value="{{$announcements_ids[$i]}}">
                                                    <?php
                                                        $appear = ''; 
                                                        $an_id = $announcements_ids[$i];
                                                        $annouc = Database::get()->querySingle("SELECT * FROM `announcement` WHERE `announcement`.`id`='{$an_id}' ");
                                                        if($annouc->visible == 0){
                                                            $appear = 1;
                                                        }else{
                                                            $appear = 0;
                                                        }
                                                    ?>
                                                    <input type="hidden" name="visible" value="{{$appear}}">
                                                    @if($appear == 1)
                                                    <button class="visible-button" type="submit"><i class="fas fa-eye"></i> {{trans('langShow')}}</button>
                                                    @else
                                                    <button class="visible-button" type="submit"><i class="fas fa-eye-slash"></i> {{trans('langViewHide')}}</button>
                                                    @endif
                                                    
                                                </form>
                                            </li>
                                            <li>
                                                <!-- <a class="announcement-item" href="#"><i class="fas fa-trash"></i> Διαγραφή</a> -->
                                                <a class='text-white' role="button" data-bs-toggle="modal" data-bs-target="#deleteModal{{$announcements_ids[$i]}}"><i class="fas fa-trash"></i> {{trans('langDelete')}}</a>
                                            </li>
                                        </ul>
                                    </div>


                                    <div class="modal fade" id="deleteModal{{$announcements_ids[$i]}}" tabindex="-1" aria-labelledby="deleteModalLabel{{$announcements_ids[$i]}}" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="deleteModalLabel{{$announcements_ids[$i]}}">{{trans('langDelete')}}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    {{trans('langConfirmDelete')}}
                                                </div>
                                                <div class="modal-footer">
                                                    <form action="{{$urlAppend}}modules/announcements/index.php" method="POST">
                                                        <input type="hidden" name="course-announcement" value="{{$course_code}}">
                                                        <input type="hidden" name="editor" value="{{$is_editor}}">
                                                        <input type="hidden" name="value" value="{{$announcements_ids[$i]}}">
                                                        <input type="hidden" name="action" value="delete">

                                                        <a  class="btn btn-secondary" data-bs-dismiss="modal">{{trans('langCancel')}}</a>
                                                        <button class="btn btn-danger" type="submit" name="action" value="delete" class="btn btn-danger">{{trans('langDelete')}}</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                
                            </td>
                            @endif
                        </tr>
                    <?php }?>
            </tbody>
        </table>
    </div>
@else
   <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12'>
        <div class="alert alert-warning" role="alert">
            {{trans('langNoAnnounce')}}
        </div>
   </div>
@endif


