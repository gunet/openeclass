@php 
    $program_details = show_mentoring_program_details($mentoring_program_code,$mentoring_program_id); 
    $tutors_programs = Database::get()->queryArray("SELECT givenname,surname FROM user
                                                    WHERE id IN (SELECT user_id FROM mentoring_programs_user
                                                                 WHERE mentoring_program_id = ?d AND tutor = ?d)",$mentoring_program_id,1);
@endphp

@if(count($program_details) > 0)
    @foreach($program_details as $d)
        <div class='col-12 mb-3 currentProgramInfo'>
            <div class='col-xl-6 col-md-8 col-12 ms-auto me-auto'>
                <div class="card">
                    <div class="row g-0">
                        <div class="col-md-4 d-flex align=items-strech">
                            @if(!empty($d->program_image))
                                <img class='img-fluid rounded-start w-100 rounded-circle solidPanel' src='{{ $urlAppend }}mentoring_programs/{{ $mentoring_program_code }}/image/{{ $d->program_image }}'>
                            @else
                                <img class="img-fluid rounded-start w-100 rounded-circle solidPanel" alt="..." src="{{ $urlAppend }}template/modern/images/nocontentyet.jpg">
                            @endif
                        </div>
                        <div class="col-md-8">
                            <div class="card-body">
                                <a href='{{ $urlAppend }}mentoring_programs/{{ $mentoring_program_code }}/index.php'><p class="card-title TextSemiBold lightBlueText">{{ $d->title }}</p></a>
                                @if(count($tutors_programs) > 0)
                                    @foreach($tutors_programs as $t)
                                        <p class="card-text small-text TextSemiBold help-block mb-0">{{ $t->givenname }}&nbsp{{ $t->surname }}</p>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class='card-footer bg-white'>{!! $action_bar !!}</div>
                </div>
            </div>
        </div>
    @endforeach
@endif