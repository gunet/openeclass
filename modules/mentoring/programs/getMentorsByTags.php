<?php         

$require_login = TRUE;


require_once '../../../include/baseTheme.php';   
require_once 'modules/mentoring/mentoring_log.class.php';
require_once 'modules/mentoring/functions.php';

$program = $_POST['program'];
$loop = $_POST['FirstLoop'];

$MentorsChoosing = array();
if($program == 'create'){
    $MentorsChoosing = json_decode($_POST['Mentors']);
}


if($program == 'edit'){
    mentoring_program_access();
    $MentorsChoosing = json_decode($_POST['Mentors']);
}

$selectedTags = array();
$selectedSpecialization = array();
$values = '';
$values2 = '';

$tagsChoosing = json_decode($_POST['dataa']);

if($loop == 'false'){
    $specializationChoosing = json_decode($_POST['Specialization']);
}


if($tagsChoosing){
    foreach($tagsChoosing as $d){
        $selectedTags[] = $d;
    }
    $values = implode(',', $selectedTags);

    if($loop == 'false'){
        foreach($specializationChoosing as $s){
            $selectedSpecialization[] = $s;
        }
        $values2 = implode(',', $selectedSpecialization);
    }
    

    if($program != 'edit'){//create program so get only available mentors
        $result_mentors =  Database::get()->queryArray("SELECT *FROM user 
                                                            WHERE id IN (SELECT user_id FROM mentoring_mentor_skills WHERE skill_id IN ($values) AND specialization_id IN ($values2)) 
                                                            AND id IN (SELECT user_id FROM mentoring_mentor_availability WHERE start <= NOW() AND end >= NOW())
                                                            AND is_mentor = ?d",1);
    }else{//edit program so get all mentors available and unvailable and current mentors of program and mentor dont participate as mentee in current program
        $mp_id = current_program();
        if($loop == 'false'){
            $result_mentors =  Database::get()->queryArray("SELECT *FROM user 
                                                                WHERE id IN (SELECT user_id FROM mentoring_mentor_skills 
                                                                                WHERE skill_id IN ($values) AND specialization_id IN ($values2)) 
                                                                OR id IN (SELECT user_id FROM mentoring_programs_user WHERE mentoring_program_id = ?d AND mentor = ?d)
                                                                                AND is_mentor = ?d",$mp_id,1,1);
        }else{
            $result_mentors =  Database::get()->queryArray("SELECT *FROM user 
                                                                WHERE id IN (SELECT user_id FROM mentoring_mentor_skills 
                                                                                WHERE skill_id IN ($values)) 
                                                                OR id IN (SELECT user_id FROM mentoring_programs_user WHERE mentoring_program_id = ?d AND mentor = ?d)
                                                                                AND is_mentor = ?d",$mp_id,1,1);
        }
    }
    

    if(count($result_mentors) > 0){
        foreach($result_mentors as $row){
            $data[] = [
                'name' => $row->givenname.' '.$row->surname.' '.getUnvailableMentorOfProgram($row->id),
                'email' => ($row->email ? $row->email : '-'),
                'choose' => initialization_create_edit($row->id,$program,$MentorsChoosing)
            ];
        }
    }else{
        $data[] = [
            'name' => $langNoFound,
            'email' => $langNoFound,
            'choose' => $langNoFound
        ];
    }
}else{
    if($program == 'edit'){
        //get only mentors of program. dont remove them by clear of tags.
        $mp_id = current_program();
        $result_mentors =  Database::get()->queryArray("SELECT *FROM user 
                                                            WHERE id IN (SELECT user_id FROM mentoring_programs_user WHERE mentoring_program_id = ?d AND mentor = ?d)",$mp_id,1);
        if(count($result_mentors) > 0){
            foreach($result_mentors as $row){
                $data[] = [
                    'name' => $row->givenname.' '.$row->surname.' '.getUnvailableMentorOfProgram($row->id),
                    'email' => ($row->email ? $row->email : '-'),
                    'choose' => initialization_create_edit($row->id,$program,$MentorsChoosing)
                ];
            }
        }
    }else{
        $data[] = [
            'name' => $langNoFound,
            'email' => $langNoFound,
            'choose' => $langNoFound
        ];
    }
    
}
        
header('Content-Type: application/json');

echo json_encode($data);

exit();

function current_program(){
    global $mentoring_program_id;
    return $mentoring_program_id;
}


function getUnvailableMentorOfProgram($user_id){
   global $langUnvailableMentor;

   $html_user = '';

   $checkUserHasExpired = Database::get()->queryArray("SELECT *FROM mentoring_mentor_availability WHERE end < NOW() AND user_id = ?d",$user_id);
   $checkUserExist = Database::get()->querySingle("SELECT COUNT(*) as total FROM mentoring_mentor_availability WHERE user_id = ?d",$user_id)->total;
   if(count($checkUserHasExpired) > 0 or $checkUserExist == 0){
        $html_user .= "<button style='width:10px; height:20px;' type='button' class='btn deleteAdminBtn d-flex justify-content-center align-items-center' data-bs-toggle='tooltip' data-bs-placement='bottom' title='$langUnvailableMentor'>
                            <span class='fa fa-info'></span>
                        </button>";
   }

   return $html_user;
}

function initialization_create_edit($user_id,$program,$MentorsSelected){
    global $mentoring_program_id;

    $html_string = '';
    $is_mentor_user = 0;
    $check_mentor_edit = false;
    $checked = '';
    $disabled = '';

    if($program == 'create'){
        if(in_array($user_id,$MentorsSelected)){
            $checked = 'checked';
        }
    }

    if($program == 'edit'){
        $check_mentor_edit = true;
    }

    if($check_mentor_edit){
        
        $is_mentors_user = Database::get()->queryArray("SELECT *FROM mentoring_programs_user 
                                                    WHERE mentoring_program_id = ?d AND user_id = ?d",
                                                        $mentoring_program_id, $user_id);
        foreach($is_mentors_user as $m_user){
            $is_mentor_user = $m_user->mentor;
        }                                 
        
        if($is_mentor_user == 1 or in_array($user_id,$MentorsSelected)){
            $checked = 'checked';
        }

        //if a mentor is mentee of program , disable checkbox
        $mentor_participate_as_mentee_in_program = Database::get()->querySingle("SELECT COUNT(*) as total FROM user
                                                                                    WHERE id = ?d AND status = ?d
                                                                                    AND id IN (SELECT user_id FROM mentoring_programs_user WHERE is_guided = ?d
                                                                                                AND mentoring_program_id = ?d)",$user_id,USER_TEACHER,1,$mentoring_program_id)->total;
        if($mentor_participate_as_mentee_in_program > 0){
            $disabled = 'disabled';
        }
    }

    $html_string .= "<div class='text-center'><label class='label-container'><input id='userM$user_id' class='clickerMentor' type='checkbox' value='$user_id' $checked $disabled><span class='checkmark'></span></label></div>";

    return $html_string;
}


    
