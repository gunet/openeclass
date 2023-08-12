<?php

require_once '../../../include/baseTheme.php';
require_once 'modules/mentoring/functions.php';
require_once 'main/eportfolio/eportfolio_functions.php';

if(get_config('mentoring_always_active') ){
    if ($language != get_config('default_language') && isset($_SESSION['uid'])) {
        $language = get_config('default_language');
        // include_messages
        include "lang/$language/common.inc.php";
        $extra_messages = "config/{$language_codes[$language]}.inc.php";
        if (file_exists($extra_messages)) {
            include $extra_messages;
        } else {
            $extra_messages = false;
        }
        include "lang/$language/messages.inc.php";
        if ($extra_messages) {
            include $extra_messages;
        }
    }
}

$toolName = $langOurMentors;

unset($_SESSION['program_code']);
unset($_SESSION['program_id']);
unset($_SESSION['mentoring_group_id']);
unset($mentoring_program_code);
unset($mentoring_program_id);
unset($is_editor_mentoring_program);

if(!isset($mentoring_platform) and !$mentoring_platform){
    header("Location: {$urlServer}modules/mentoring/mentoring_platform_home.php");
    exit;
}

$data['result_mentors'] = array();
$selectedTags = array();
$selectedSpecialization = array();
$values = '';
$values2 = '';



if(isset($_POST['dataa'])){

    $loop = $_POST['FirstLoop'];
    if($loop == 'false'){
        $specializationChoosing = json_decode($_POST['Specialization']);
    }

    $html = "";
    $tagsChoosing = json_decode($_POST['dataa']);

    

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

        if($loop == 'true'){
            if(isset($_POST['isAvailable']) and $_POST['isAvailable'] == 1){
                $data['result_mentors'] = $result_mentors = Database::get()->queryArray("SELECT *FROM user 
                                                                                            WHERE id IN (SELECT user_id FROM mentoring_mentor_skills 
                                                                                                            WHERE skill_id IN ($values)) 
                                                                                                            AND id IN (SELECT user_id FROM mentoring_mentor_availability
                                                                                                                        WHERE start <= NOW() AND end >= NOW())
                                                                                                            AND is_mentor = ?d",1);
                                        
            }elseif(isset($_POST['isAvailable']) and $_POST['isAvailable'] == 0){
                    $data['result_mentors'] = $result_mentors = Database::get()->queryArray("SELECT *FROM user 
                                                                                                WHERE id IN (SELECT user_id FROM mentoring_mentor_skills 
                                                                                                                WHERE skill_id IN ($values)) 
                                                                                                                AND id IN (SELECT user_id FROM mentoring_mentor_availability
                                                                                                                            WHERE end < NOW())
                                                                                                            
                                                                                                                AND is_mentor = ?d",1);
                    
            }else{
                $data['result_mentors'] = $result_mentors = Database::get()->queryArray("SELECT *FROM user 
                                                                                        WHERE id IN (SELECT user_id FROM mentoring_mentor_skills 
                                                                                                        WHERE skill_id IN ($values)) 
                                                                                                        AND is_mentor = ?d",1);
            }
        }else{
            if(isset($_POST['isAvailable']) and $_POST['isAvailable'] == 1){
                    $data['result_mentors'] = $result_mentors = Database::get()->queryArray("SELECT *FROM user 
                                                                                                WHERE id IN (SELECT user_id FROM mentoring_mentor_skills 
                                                                                                                WHERE skill_id IN ($values) AND specialization_id IN ($values2)) 
                                                                                                                AND id IN (SELECT user_id FROM mentoring_mentor_availability
                                                                                                                            WHERE start <= NOW() AND end >= NOW())
                                                                                                                AND is_mentor = ?d",1);
                                            
            }elseif(isset($_POST['isAvailable']) and $_POST['isAvailable'] == 0){
                    $data['result_mentors'] = $result_mentors = Database::get()->queryArray("SELECT *FROM user 
                                                                                                WHERE id IN (SELECT user_id FROM mentoring_mentor_skills 
                                                                                                                WHERE skill_id IN ($values) AND specialization_id IN ($values2)) 
                                                                                                                AND id IN (SELECT user_id FROM mentoring_mentor_availability
                                                                                                                            WHERE end < NOW())
                                                                                                            
                                                                                                                AND is_mentor = ?d",1);
                    
            }else{
                $data['result_mentors'] = $result_mentors = Database::get()->queryArray("SELECT *FROM user 
                                                                                        WHERE id IN (SELECT user_id FROM mentoring_mentor_skills 
                                                                                                        WHERE skill_id IN ($values) AND specialization_id IN ($values2)) 
                                                                                                        AND is_mentor = ?d",1);
            }
        }
        
  
        if(count($result_mentors) > 0){
            $pagesPag = 0;
            $allMentors = 0;
            $temp_pages = 0;
            $html .= "<div class='card-group'>";
                $countCards = 1;
                if($countCards == 1){
                    $pagesPag++;
                }
                foreach($result_mentors as $mentor){
                $temp_pages++;
                $html .= "<div class='col-xl-4 col-lg-6 col-md-6 col-12 details_mentors cardMentor$pagesPag mb-3 ps-lg-3 ps-0'><div class='col-12 p-2'>
                            <a class='clickMentorProfile' href='{$urlAppend}modules/mentoring/mentors/profile_mentor.php?mentor=".getInDirectReference($mentor->id)."'>
                                <div class='card ourMentorsCard card$pagesPag cardImages'>
                                    <div class='card-body'>";
                                        $now = date('Y-m-d H:i:s', strtotime('now')); 
                                        $checking_availability = Database::get()->querySingle("SELECT *FROM mentoring_mentor_availability 
                                                                                            WHERE user_id = ?d AND '$now' BETWEEN start AND end",$mentor->id);
                                        if($checking_availability){
                                            $html .= "<div class='col-12 text-center'><span class='badge badge-mentor text-white rounded-pill pt-1 pb-1 ps-2 pe-2 mb-3'><span class='fa fa-check pe-1'></span>$langAvailableMentorProfile</span></div>";
                                        }else{
                                            $html .= "<div class='col-12 text-center'><span class='badge badge-mentor bgDangerMentor text-white rounded-pill pt-1 pb-1 ps-2 pe-2 mb-3'><span class='fa-solid fa-trash-can pe-1'></span>$langNoAvailableMentorProfile</span></div>";
                                        }
                                        $profile_img = profile_image($mentor->id, IMAGESIZE_LARGE, 'img-responsive p-0 rounded-2 img-profile MentorImage');
                                        $html .= "$profile_img";
                                        $html .= "<p class='text-center mt-3 TextBold blackBlueText text-capitalize fs-6'>$mentor->givenname $mentor->surname</p>";
                            $html .= "</div>";
                            $html .= "</a></div>
                                </div>
                            </div>";
                    if($countCards == 9 and $temp_pages < count($result_mentors)){
                        $pagesPag++;
                        $countCards = 0;
                    }
                    $countCards++;
                    $allMentors++;
                }
            $html .= "</div>";
        
            if($pagesPag > 0){
                $html .= GroupCardsPagination($allMentors,$pagesPag);
            }
        }else{
            $html .= "<div class='col-12 ps-lg-3'><p class='blackBlueText TextSemiBold text-center'>$langNoAvailableMentoringMentors</p></div>";
        }
        
    }else{
        $html .= "<div class='col-12 ps-lg-3'><p class='blackBlueText TextSemiBold text-center'>$langNoAvailableMentoringMentors</p></div>";
    }

    echo ($html);

    exit();
}

if(isset($_GET['term'])){
    $html = "";

    $q = $_GET['term'];

    $taglist = Database::get()->queryArray("SELECT skill_id FROM mentoring_keywords WHERE name LIKE ?s ORDER BY name", "%$q%");
    if ($taglist) {
        foreach ($taglist as $tag) {
            $tags[] = $tag->skill_id;
        }
        $values = implode(',', $tags);

        $data['result_mentors'] = $result_mentors = Database::get()->queryArray("SELECT *FROM user 
                                                                                    WHERE id IN (SELECT user_id FROM mentoring_mentor_skills 
                                                                                                    WHERE skill_id IN ($values)) 
                                                                                                    AND is_mentor = ?d",1);

        if(count($result_mentors) > 0){
            $pagesPag = 0;
            $allMentors = 0;
            $temp_pages = 0;
            $html .= "<div class='card-group'>";
                $countCards = 1;
                if($countCards == 1){
                    $pagesPag++;
                }
                foreach($result_mentors as $mentor){
                    $temp_pages++;
                    $html .= "<div class='col-xl-4 col-lg-6 col-md-6 col-12 details_mentors cardMentor$pagesPag mb-3 ps-lg-3 ps-0'><div class='col-12 p-2'>
                                <a class='clickMentorProfile' href='{$urlAppend}modules/mentoring/mentors/profile_mentor.php?mentor=".getInDirectReference($mentor->id)."'>
                                    <div class='card ourMentorsCard card$pagesPag'>
                                        <div class='card-body'>";
                                            $now = date('Y-m-d H:i:s', strtotime('now')); 
                                            $checking_availability = Database::get()->querySingle("SELECT *FROM mentoring_mentor_availability 
                                                                                                WHERE user_id = ?d AND '$now' BETWEEN start AND end",$mentor->id);
                                            if($checking_availability){
                                                $html .= "<div class='col-12 text-center'><span class='badge badge-mentor text-white rounded-pill pt-1 pb-1 ps-2 pe-2 mb-3'><span class='fa fa-check pe-1'></span>$langAvailableMentorProfile</span></div>";
                                            }else{
                                                $html .= "<div class='col-12 text-center'><span class='badge badge-mentor bgDangerMentor text-white rounded-pill pt-1 pb-1 ps-2 pe-2 mb-3'><span class='fa-solid fa-trash-can pe-1'></span>$langNoAvailableMentorProfile</span></div>";
                                            }
                                            $profile_img = profile_image($mentor->id, IMAGESIZE_LARGE, 'img-responsive p-0 rounded-2 img-profile MentorImage');
                                            $html .= "$profile_img";
                                            $html .= "<p class='text-center mt-3 TextBold blackBlueText text-capitalize fs-6'>$mentor->givenname&nbsp$mentor->surname</p>";
                                $html .= "</div>";
                                $html .= "</a></div>
                                    </div>
                                </div>";
                    if($countCards == 9 and $temp_pages < count($result_mentors)){
                        $pagesPag++;
                        $countCards = 0;
                    }
                    $countCards++;
                    $allMentors++;
                }
            $html .= "</div>";



            if($pagesPag > 0){

                $html .= GroupCardsPagination($allMentors,$pagesPag);
            }


        }else{
            $html .= "<div class='col-12 ps-lg-3'><p class='blackBlueText TextSemiBold text-center'>$langNoAvailableMentoringMentors</p></div>";
        }
    } else {
        $html .= "<div class='col-12 ps-lg-3'><p class='blackBlueText TextSemiBold text-center'>$langNoAvailableMentoringMentors</p></div>";
    }

    echo ($html);

    exit();
}

$data['is_editor_mentoring'] = is_editor_mentoring($uid);

$data['all_specializations'] = Database::get()->queryArray("SELECT *FROM mentoring_specializations");

view('modules.mentoring.mentors.all_mentors', $data);




function GroupCardsPagination($allMentors,$pagesPag){

    $pagination = "";

    $pagination .= "
            <input type='hidden' id='KeyallMentor' value='$allMentors'>
            <input type='hidden' id='KeypagesMentor' value='$pagesPag'>
            <div class='col-12 ps-lg-4 pe-lg-2 ps-2 pe-2 d-flex justify-content-center align-items-center'>
            <div class='col-12 d-flex justify-content-center p-0 overflow-auto bg-white border-card'>
            <nav aria-label='Page navigation example w-100'>
                <ul class='pagination mentors-pagination w-100 mb-0'>
                    <li class='page-item page-item-previous'>
                        <a class='page-link bg-white' href='#'><span class='fa-solid fa-chevron-left'></span></a>
                    </li>";
                    if($pagesPag >=12 ){//>=12 pages
                        for($i=1; $i<=$pagesPag; $i++){
                        
                            if($i>=1 && $i<=5){//1-5
                                if($i==1){
                                    $pagination .="<li id='KeypageCenter{$i}' class='page-item page-item-pages'>
                                                        <a id='Keypage{$i}' class='page-link' href='#'>$i</a>
                                                    </li>";

                                    $pagination .="<li id='KeystartLi' class='page-item page-item-pages d-flex justify-content-center align-items-end d-none'>
                                                        <a>...</a>
                                                    </li>";
                                }else{
                                    if($i<$pagesPag){
                                        $pagination .="<li id='KeypageCenter{$i}' class='page-item page-item-pages'>
                                                            <a id='Keypage{$i}' class='page-link' href='#'>$i</a>
                                                        </li>";
                                    }
                                }
                            }

                            if($i>=6 && $i<=$pagesPag-1){//6-11
                                $pagination .="<li id='KeypageCenter{$i}' class='page-item page-item-pages d-none'>
                                                    <a id='Keypage{$i}' class='page-link' href='#'>$i</a>
                                                </li>";

                                if($i==$pagesPag-1){//11
                                    $pagination .="<li id='KeycloseLi' class='page-item page-item-pages d-flex justify-content-center align-items-end d-block'>
                                                        <a>...</a>
                                                    </li>";
                                }
                            }

                            if($i==$pagesPag){//12
                                $pagination .="<li id='KeypageCenter{$i}' class='page-item page-item-pages'>
                                                    <a id='Keypage{$i}' class='page-link' href='#'>$i</a>
                                                </li>";
                            }
                        }
                    
                    }else{//1-11 pages
                        for($i=1; $i<=$pagesPag; $i++){
                            $pagination .="<li id='KeypageCenter{$i}' class='page-item page-item-pages'>
                                                <a id='Keypage{$i}' class='page-link' href='#'>$i</a>
                                            </li>";
                        }
                    }

                $pagination .="<li class='page-item page-item-next'>
                                    <a class='page-link bg-white' href='#'><span class='fa-solid fa-chevron-right'></span></a>
                                </li>
                </ul>
            </nav></div>
        </div>
        
        <script>

            $('.clickMentorProfile').on('click',function(){
                localStorage.removeItem('MenuMentoring');
            });

            var arrayLeftRight = [];
            
            // init page1
            if(arrayLeftRight.length == 0){
                var totalMentors = $('#KeyallMentor').val();
                for(j=1; j<=totalMentors; j++){
                    if(j!=1){
                        $('.cardMentor'+j).removeClass('d-block');
                        $('.cardMentor'+j).addClass('d-none');
                    }else{
                        $('.page-item-previous').addClass('disabled');
                        $('.cardMentor'+j).removeClass('d-none');
                        $('.cardMentor'+j).addClass('d-block');
                        $('#Keypage1').addClass('active');
                    }
                }
                var totalPages = $('#KeypagesMentor').val();
                if(totalPages == 1){
                    $('.page-item-previous').addClass('disabled');
                    $('.page-item-next').addClass('disabled');
                }
            }


            // prev-button
            $('.page-item-previous .page-link').on('click',function(){

                var prevPage;

                $('.page-item-pages .page-link.active').each(function(index, value){
                    var IDCARD = this.id;
                    var number = parseInt(IDCARD.match(/\d+/g));
                    prevPage = number-1;

                    arrayLeftRight.push(number);
    
                    var totalMentors = $('#KeyallMentor').val();
                    var totalPages = $('#KeypagesMentor').val();
                    for(i=1; i<=totalMentors; i++){
                        if(i == prevPage){
                            $('.cardMentor'+i).removeClass('d-none');
                            $('.cardMentor'+i).addClass('d-block');
                            $('#Keypage'+prevPage).addClass('active');
                        }else{
                            $('.cardMentor'+i).removeClass('d-block');
                            $('.cardMentor'+i).addClass('d-none');
                            $('#Keypage'+i).removeClass('active');
                        }
                    }

                    if(prevPage == 1){
                        $('.page-item-previous').addClass('disabled');
                    }else{
                        if(prevPage < totalPages){
                            $('.page-item-next').removeClass('disabled');
                        }
                        $('.page-item-previous').removeClass('disabled');
                    }


                    //create page-link in center
                    if(number <= totalPages-3 && number >= 6 && totalPages>=12){

                        $('#KeystartLi').removeClass('d-none');
                        $('#KeystartLi').addClass('d-block');
                        
                        for(i=2; i<=totalPages-1; i++){
                            $('#KeypageCenter'+i).removeClass('d-block');
                            $('#KeypageCenter'+i).addClass('d-none');
                        }

                        $('#KeypageCenter'+arrayLeftRight[arrayLeftRight.length-1]).removeClass('d-none');
                        $('#KeypageCenter'+arrayLeftRight[arrayLeftRight.length-1]).removeClass('d-block');

                        var currentPage = number-1;
                        $('#KeypageCenter'+currentPage).removeClass('d-none');
                        $('#KeypageCenter'+currentPage).addClass('d-block');

                        var prevPage = number-2;
                        $('#KeypageCenter'+prevPage).removeClass('d-none');
                        $('#KeypageCenter'+prevPage).addClass('d-block');

                        $('#KeycloseLi').removeClass('d-none');
                        $('#KeycloseLi').addClass('d-block');

                    }else if(number <= 5 && totalPages>=12){

                        $('#KeystartLi').removeClass('d-block');
                        $('#KeystartLi').addClass('d-none');

                        for(i=6; i<=totalPages-1; i++){
                            $('#KeypageCenter'+i).removeClass('d-block');
                            $('#KeypageCenter'+i).addClass('d-none');
                        }

                        $('#KeycloseLi').removeClass('d-none');
                        $('#KeycloseLi').addClass('d-block');

                        
                        for(i=1; i<=number; i++){
                            $('#KeypageCenter'+i).removeClass('d-none');
                            $('#KeypageCenter'+i).addClass('d-block');
                        }

                    }

                });

            });




            // next-button
            $('.page-item-next .page-link').on('click',function(){

                $('.page-item-pages .page-link.active').each(function(index, value){
                    var IDCARD = this.id;
                    var number = parseInt(IDCARD.match(/\d+/g));
                    arrayLeftRight.push(number);
                    var nextPage = number+1;

                    var delPageActive = nextPage-1;
                    $('#Keypage'+delPageActive).removeClass('active');
                    $('#Keypage'+nextPage).addClass('active');
                
                    var totalMentors = $('#KeyallMentor').val();
                    var totalPages = $('#KeypagesMentor').val();
                    
                    for(i=1; i<=totalMentors; i++){
                        if(i == nextPage){
                            $('.cardMentor'+i).removeClass('d-none');
                            $('.cardMentor'+i).addClass('d-block');
                            // $('#Keypage'+nextPage).addClass('active');
                        }else{
                            $('.cardMentor'+i).removeClass('d-block');
                            $('.cardMentor'+i).addClass('d-none');
                            //$('#Keypage'+i).removeClass('active');
                        }
                    }

                    if(totalPages > 1){
                        $('.page-item-previous').removeClass('disabled');
                    }
                    if(nextPage == totalPages){
                        $('.page-item-next').addClass('disabled');
                    }else{
                        $('.page-item-next').removeClass('disabled');
                    }


                    //create page-link in center
                    if(number >= 4 && number < totalPages-5 && totalPages>=12){//5-7

                        $('#KeystartLi').removeClass('d-none');
                        $('#KeystartLi').addClass('d-block');
                        
                        for(i=2; i<=totalPages-1; i++){
                            $('#KeypageCenter'+i).removeClass('d-block');
                            $('#KeypageCenter'+i).addClass('d-none');
                        }

                        $('#KeypageCenter'+arrayLeftRight[arrayLeftRight.length-1]).removeClass('d-none');
                        $('#KeypageCenter'+arrayLeftRight[arrayLeftRight.length-1]).removeClass('d-block');

                        var currentPage = number+1;
                        $('#KeypageCenter'+currentPage).removeClass('d-none');
                        $('#KeypageCenter'+currentPage).addClass('d-block');

                        var nextPage = number+2;
                        $('#KeypageCenter'+nextPage).removeClass('d-none');
                        $('#KeypageCenter'+nextPage).addClass('d-block');

                        $('#KeycloseLi').removeClass('d-none');
                        $('#KeycloseLi').addClass('d-block');

                    }else if(arrayLeftRight[arrayLeftRight.length-1] >= totalPages-5 && totalPages>=12){//>=8

                        $('#KeystartLi').removeClass('d-none');
                        $('#KeystartLi').addClass('d-block');

                        for(i=2; i<=totalPages-5; i++){
                            $('#KeypageCenter'+i).removeClass('d-block');
                            $('#KeypageCenter'+i).addClass('d-none');
                        }

                        $('#KeycloseLi').removeClass('d-block');
                        $('#KeycloseLi').addClass('d-none');

                        var nextPage = arrayLeftRight[arrayLeftRight.length-1] + 1;
                        console.log('nextPage:'+nextPage);
                        for(i=nextPage; i<=totalPages; i++){
                            $('#KeypageCenter'+i).removeClass('d-none');
                            $('#KeypageCenter'+i).addClass('d-block');
                        }

                    }else if(number>=1 && number<=4 && totalPages>=12){
                        $('#KeystartLi').removeClass('d-block');
                        $('#KeystartLi').addClass('d-none');

                        for(i=1; i<=4; i++){
                            $('#KeypageCenter'+i).removeClass('d-none');
                            $('#KeypageCenter'+i).addClass('d-block');
                        }
                    }

                    
                });
            });




            // page-link except prev-next button
            $('.page-item-pages .page-link').on('click',function(){
                
                var IDCARD = this.id;
                var number = parseInt(IDCARD.match(/\d+/g));

                arrayLeftRight.push(number);

                var totalPages = $('#KeypagesMentor').val();
                var totalMentors = $('#KeyallMentor').val();
                for(i=1; i<=totalMentors; i++){
                    if(i!=number){
                        $('.cardMentor'+i).removeClass('d-block');
                        $('.cardMentor'+i).addClass('d-none');
                    }else{
                        $('.cardMentor'+i).removeClass('d-none');
                        $('.cardMentor'+i).addClass('d-block');
                    }

                    // about prev-next button
                    if(number>1){
                        $('.page-item-previous').removeClass('disabled');
                        $('.page-item-next').removeClass('disabled');
                    }if(number == 1){
                        if(totalPages == 1){
                            $('.page-item-previous').addClass('disabled');
                            $('.page-item-next').addClass('disabled');
                        }
                        if(totalPages > 1){
                            $('.page-item-previous').addClass('disabled');
                            $('.page-item-next').removeClass('disabled');
                        }
                    }if(number == totalPages){
                        $('.page-item-next').addClass('disabled');
                    }if(number < totalPages-1){
                        $('.page-item-next').removeClass('disabled');
                    }
                }

                console.log(number);
                if(number>=1 && number<=4 && totalPages>=12){

                    $('#KeystartLi').removeClass('d-block');
                    $('#KeystartLi').addClass('d-none');

                    for(i=1; i<=5; i++){
                        $('#KeypageCenter'+i).removeClass('d-none');
                        $('#KeypageCenter'+i).addClass('d-block'); 
                    }
                    for(i=6; i<=totalPages-1; i++){
                        $('#KeypageCenter'+i).removeClass('d-block');
                        $('#KeypageCenter'+i).addClass('d-none');
                    }

                    $('#KeycloseLi').removeClass('d-none');
                    $('#KeycloseLi').addClass('d-block');
                }
                if(number>=5 && number<=totalPages-5 && totalPages>=12){

                    for(i=5; i<=totalPages-1; i++){
                        $('#KeypageCenter'+i).removeClass('d-block');
                        $('#KeypageCenter'+i).addClass('d-none');
                    }

                    var prevPage = number-1;
                    var nextPage = number+1;
                    var currentPage = number;

                    $('#KeystartLi').removeClass('d-none');
                    $('#KeystartLi').addClass('d-block');

                    for(i=2; i<=4; i++){
                        $('#KeypageCenter'+i).removeClass('d-block');
                        $('#KeypageCenter'+i).addClass('d-none');
                    }

                    $('#KeypageCenter'+prevPage).removeClass('d-none');
                    $('#KeypageCenter'+prevPage).addClass('d-block');

                    $('#KeypageCenter'+currentPage).removeClass('d-none');
                    $('#KeypageCenter'+currentPage).addClass('d-block');

                    $('#KeypageCenter'+nextPage).removeClass('d-none');
                    $('#KeypageCenter'+nextPage).addClass('d-block');

                    $('#KeycloseLi').removeClass('d-none');
                    $('#KeycloseLi').addClass('d-block');

                }
                if(number>=totalPages-4 && totalPages>=12){

                    $('#KeystartLi').removeClass('d-none');
                    $('#KeystartLi').addClass('d-block');

                    for(i=2; i<=totalPages-5; i++){
                        $('#KeypageCenter'+i).removeClass('d-block');
                        $('#KeypageCenter'+i).addClass('d-none');
                    }

                    for(i=totalPages-4; i<=totalPages; i++){
                        $('#KeypageCenter'+i).removeClass('d-none');
                        $('#KeypageCenter'+i).addClass('d-block');
                    }


                    $('#KeycloseLi').removeClass('d-block');
                    $('#KeycloseLi').addClass('d-none');
                }
                if(number==totalPages-4 && arrayLeftRight[arrayLeftRight.length-2]>number && totalPages>=12){

                    $('#KeystartLi').removeClass('d-none');
                    $('#KeystartLi').addClass('d-block');

                    for(i=2; i<=totalPages-1; i++){
                        $('#KeypageCenter'+i).removeClass('d-block');
                        $('#KeypageCenter'+i).addClass('d-none');
                    }

                    var prevPage = number+1;
                    var nextPage = number-1;
                    var currentPage = number;

                    $('#KeypageCenter'+prevPage).removeClass('d-none');
                    $('#KeypageCenter'+prevPage).addClass('d-block');

                    $('#KeypageCenter'+currentPage).removeClass('d-none');
                    $('#KeypageCenter'+currentPage).addClass('d-block');

                    $('#KeypageCenter'+nextPage).removeClass('d-none');
                    $('#KeypageCenter'+nextPage).addClass('d-block');

                    $('#KeycloseLi').removeClass('d-none');
                    $('#KeycloseLi').addClass('d-block');
                }


                // about active page-item
                $('.page-item-pages .page-link').each(function(index, value){
                    $('.page-item-pages .page-link').removeClass('active');
                });
                $(this).addClass('active');

            });
        </script>
        
        ";

    return $pagination;
}



