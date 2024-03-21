@extends('layouts.default')

@section('content')

<div class="row back-navbar-eclass"></div>
<div class="row back-navbar-eclass2"></div>

    <div class="pb-5">
        <div class="container-fluid notes_container">
            <div class="row">
                    <div class="col-xl-12 col-lg-8 col-md-12 col-sm-12 col-xs-12 justify-content-center courses-details">

                        <div class="row p-lg-5 p-md-5 ps-1 pe-1 pt-5 pb-5">
                        @include('layouts.partials.legend_view')

                            <div class="row p-2"></div>
                            <div class="row p-2"></div>

                            <form class="form_fieldset_notes" action="{{ $urlAppend }}main/notes/index.php" method="post">
                                <div class="table-responsive">
                                    <table class="announcements" id="mynotes_table">
                                        <thead class="notes_thead text-light">
                                            <tr>
                                                <th scope="col"><span class="notes_th_comment">#</span></th>
                                                <th scope="col"><span class="notes_th_comment">{{trans('langHomePageIntroTitle')}}</span></th>
                                                <th scope="col"><span class="notes_th_comment">{{trans('langContent')}}</span></th>
                                                <th scope="col"><span class="notes_th_comment">{{trans('langDate')}}</span></th>
                                                <th scope="col"><span class="notes_th_comment">{{trans('langCourse')}}</span></th>
                                                <th scope="col"><span class="notes_th_comment">{{trans('langElaboration')}}</span></th>
                                                <th scope="col"><span class="notes_th_comment2">{{trans('langDelete')}}</span></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $i=0; ?>
                                            @foreach($user_notes as $note)
                                                <?php $i++; ?>
                                                <tr>
                                                    <th scope="row">{{$i}}</th>
                                                    <td>{{$note->title}}</td>
                                                    <td>
                                                        <a class="content-truncate-note" data-bs-toggle="modal" role="button" aria-expanded="false" data-bs-target="#ModalNote{{$i}}">
                                                            <?php $content_note = strip_tags($note->content); ?>
                                                            <span class="d-inline-block text-truncate" style="max-width: 180px;"><i class="fas fa-arrow-down"></i>{{$content_note}}</span>

                                                        </a>


                                                        <div class="modal fade modalNote" id="ModalNote{{$i}}" tabindex="-1" aria-labelledby="ModalNote{{$i}}" aria-hidden="true">
                                                            <div class="modal-dialog">
                                                                <div class="modal-content">
                                                                    <div class="modal-header">
                                                                        <?php
                                                                                $courses_notes = Database::get()->querySingle("SELECT title FROM course WHERE id={$note->reference_obj_id}; ");
                                                                        ?>
                                                                        <div class="modal-title" id="ModalNote{{$i}}"><strong>Μάθημα</strong><small style="color:orange;"><{{$courses_notes->title}}></small><br><strong>{{trans('langHomePageIntroTitle')}}</strong><small style="color:orange;"><<{{$note->title}}>></small></div>
                                                                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                    </div>
                                                                    <div class="modal-body">
                                                                        {!! $note->content !!}
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                    </td>
                                                    <td>{{$note->date_time}}</td>
                                                    <?php
                                                        $course_note = Database::get()->querySingle("SELECT title,code FROM course WHERE id={$note->reference_obj_id}; ");
                                                    ?>
                                                    <td> <a class="" href="{{$urlServer}}courses/{{$course_note->code}}/index.php">{{$course_note->title}}</a></td>
                                                    <td>
                                                        <button class="modify_note_button" type="submit" name="modify" value="{{$note->id}}"><i class="fas fa-edit" style='color:#003F87'></i></button>
                                                    </td>
                                                    <td>
                                                        <a class="edit_trash_notes" href="" data-bs-toggle="modal" data-bs-target="#DeleteNoteModal{{$note->id}}" >
                                                            <i class="fas fa-trash" style='color:#003F87'></i>
                                                        </a>

                                                    </td>
                                                </tr>


                                                <!-- Modal -->
                                                <div class="modal fade" id="DeleteNoteModal{{$note->id}}" tabindex="-1" aria-labelledby="DeleteModalLabel{{$note->id}}" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <div class="modal-title" id="DeleteModalLabel{{$note->id}}">{{trans('langDelete')}}</div>
                                                                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                {{trans('langCorfimeDelete')}} <strong class="unregCourseStrong">{{$note->title}}</strong>;
                                                            </div>
                                                            <div class="modal-footer">
                                                                <a class="btn btn-secondary" href="" data-bs-dismiss="modal">{{trans('langCancel')}}</a>

                                                                <button class="btn deleteAdminBtn" type="submit" name="delete" value="{{$note->id}}">{{trans('langDelete')}}</button>

                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>


                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </form>
                        </div>
                    </div>

            </div>
        </div>
    </div>


@endsection
