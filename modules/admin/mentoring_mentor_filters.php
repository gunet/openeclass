<?php

$require_login = TRUE;

require_once '../../include/baseTheme.php';


$toolName = $langAddfiltersformentor.' -- '.$langListFilters;

load_js('select2');

$head_content .= "
<script>
    $(function () {
        $('#tag-skills').select2({
                
                minimumInputLength: 2,
                tags: true,
                tokenSeparators: [','],
                width: '100%',
                selectOnClose: true,
                createSearchChoice: function(term, data) {
                  if ($(data).filter(function() {
                    return this.text.localeCompare(term) === 0;
                  }).length === 0) {
                    return {
                      id: term,
                      text: term
                    };
                  }
                },
                ajax: {
                    url: 'mentoring_mentor_feed_skills.php',
                    dataType: 'json',
                    data: function(term, page) {
                        return {
                            q: term
                        };
                    },
                    processResults: function(data, page) {
                        return {results: data};
                    }
                }
        });

        $('#tag-keywords').select2({
                
            minimumInputLength: 2,
            tags: true,
            tokenSeparators: [','],
            width: '100%',
            selectOnClose: true,
            createSearchChoice: function(term, data) {
              if ($(data).filter(function() {
                return this.text.localeCompare(term) === 0;
              }).length === 0) {
                return {
                  id: term,
                  text: term
                };
              }
            },
            ajax: {
                url: 'mentoring_mentor_feed_keywords.php',
                dataType: 'json',
                data: function(term, page) {
                    return {
                        q: term
                    };
                },
                processResults: function(data, page) {
                    return {results: data};
                }
            }
        });

    });
</script>";


if(isset($_POST['submitFilters'])){
    if(!empty($_POST['Expertise']) and !empty($_POST['SkillsTags']) and !empty($_POST['KeywordsTags'])){
        $array_skills_ids = array();
        $specialization = Database::get()->query("INSERT INTO mentoring_specializations SET name = ?s",$_POST['Expertise']);
        if($specialization){//kainouria ejeidikeush
            Database::get()->query("INSERT INTO mentoring_specializations_translations SET specialization_id = ?d, name = ?s, lang = ?s",$specialization->lastInsertID, $_POST['Expertise'], $_POST['language_form']);
            foreach($_POST['SkillsTags'] as $s){
                $exist_name_skill = Database::get()->querySingle("SELECT COUNT(*) as total FROM mentoring_skills WHERE name = ?s",$s)->total;
                if($exist_name_skill == 0){//den uparxei to skill sth vash
                    $skill = Database::get()->query("INSERT INTO mentoring_skills SET name = ?s",$s);
                    $specialization_skill = Database::get()->query("INSERT INTO mentoring_specializations_skills SET skill_id = ?d, specialization_id = ?d",$skill->lastInsertID,$specialization->lastInsertID);
                    array_push($array_skills_ids,$skill->lastInsertID);
                    Database::get()->query("INSERT INTO mentoring_skills_translations SET skill_id = ?d, name = ?s, lang = ?s",$skill->lastInsertID, $s, $_POST['language_form']);
                }else{// uparxei to skill sthh vash , opote exoume diasunthesi me allh ejeidikeysh
                    $new_skill_id = Database::get()->querySingle("SELECT id FROM mentoring_skills WHERE name = ?s",$s)->id;
                    $specialization_skill = Database::get()->query("INSERT INTO mentoring_specializations_skills SET skill_id = ?d, specialization_id = ?d",$new_skill_id,$specialization->lastInsertID);
                    array_push($array_skills_ids,$new_skill_id);
                }
            }
            foreach($_POST['KeywordsTags'] as $k){
                if(count($array_skills_ids) > 0){
                    foreach($array_skills_ids as $s_id){
                        $key = Database::get()->query("INSERT INTO mentoring_keywords SET name = ?s, specialization_id = ?d, skill_id = ?d",$k,$specialization->lastInsertID,$s_id);
                    }
                }
            }
        }

        if($key){
            Session::flash('message',$langAddFiltersSuccessMsg);
            Session::flash('alert-class', 'alert-success');
        }else{
            Session::flash('message',$langAddFiltersNoSuccessMsg);
            Session::flash('alert-class', 'alert-warning');
        }
        
        redirect_to_home_page('modules/admin/mentoring_mentor_filters.php');
    }
}

if(isset($_POST['submitTranslationSkill'])){
    $checkIfTranlationExist = Database::get()->queryArray("SELECT *FROM mentoring_skills_translations WHERE skill_id = ?d AND lang = ?s",$_POST['translationSkillId'],$_POST['language_form']);
    if(count($checkIfTranlationExist) == 0){
        Database::get()->query("INSERT INTO mentoring_skills_translations SET skill_id = ?d, name = ?s, lang = ?s",$_POST['translationSkillId'],$_POST['skillNameTranslation'],$_POST['language_form']);
    }else{
        Database::get()->query("UPDATE mentoring_skills_translations SET name = ?s, lang = ?s WHERE skill_id = ?d AND lang = ?s",$_POST['skillNameTranslation'],$_POST['language_form'],$_POST['translationSkillId'],$_POST['language_form']);
    }

    Session::flash('message',$langAddTranslationSuccessMsg);
    Session::flash('alert-class', 'alert-success');

    redirect_to_home_page('modules/admin/mentoring_mentor_filters.php');
}

if(isset($_POST['submitTranslationSpecialization'])){
    $checkIfTranlationExist = Database::get()->queryArray("SELECT *FROM mentoring_specializations_translations WHERE specialization_id = ?d AND lang = ?s",$_POST['translationSpecializationId'],$_POST['language_form']);
    if(count($checkIfTranlationExist) == 0){
        Database::get()->query("INSERT INTO mentoring_specializations_translations SET specialization_id = ?d, name = ?s, lang = ?s",$_POST['translationSpecializationId'],$_POST['specializationNameTranslation'],$_POST['language_form']);
    }else{
        Database::get()->query("UPDATE mentoring_specializations_translations SET name = ?s, lang = ?s WHERE specialization_id = ?d AND lang = ?s",$_POST['specializationNameTranslation'],$_POST['language_form'],$_POST['translationSpecializationId'],$_POST['language_form']);
    }

    Session::flash('message',$langAddTranslationSuccessMsg);
    Session::flash('alert-class', 'alert-success');

    redirect_to_home_page('modules/admin/mentoring_mentor_filters.php');
}

if(isset($_POST['deleteSpecialization'])){
    $specialization_id = $_POST['delSpecialization'];
    $del = Database::get()->query("DELETE FROM mentoring_specializations WHERE id = ?d",$specialization_id);
    if($del){
        Session::flash('message',$langDelSpecializationSuccessMsg);
        Session::flash('alert-class', 'alert-success');
    }else{
        Session::flash('message',$langDelSpecializationNoSuccessMsg);
        Session::flash('alert-class', 'alert-danger');
    }
    redirect_to_home_page('modules/admin/mentoring_mentor_filters.php');
}

if(isset($_POST['submitAddSkill'])){
    $specialization_id = $_POST['specialization_id'];
    $skill = Database::get()->query("INSERT INTO mentoring_skills SET name = ?s",$_POST['skillName']);
    $add_specialization_skill = Database::get()->query("INSERT INTO mentoring_specializations_skills SET skill_id = ?d, specialization_id = ?d",$skill->lastInsertID,$specialization_id);
    if($skill and $add_specialization_skill){// den uparxei to skill sth vash ara na mpei
        Database::get()->query("INSERT INTO mentoring_skills_translations SET skill_id = ?d, name = ?s, lang = ?s",$skill->lastInsertID, $_POST['skillName'], $_POST['language_form']);
        $keyWordsList = Database::get()->queryArray("SELECT name FROM mentoring_keywords WHERE specialization_id = ?d",$specialization_id);
        if(count($keyWordsList) > 0){
            foreach($keyWordsList as $k){
                Database::get()->query("INSERT INTO mentoring_keywords SET name = ?s, skill_id = ?d, specialization_id = ?d",$k->name,$skill->lastInsertID,$specialization_id);
            }
        }
        Session::flash('message',$langAddSkillSuccessMsg);
        Session::flash('alert-class', 'alert-success');
    }else{
        $exist_skill = Database::get()->querySingle("SELECT COUNT(*) AS total FROM mentoring_specializations_skills 
                                                        WHERE skill_id IN (SELECT id FROM mentoring_skills WHERE name = ?s) 
                                                        AND specialization_id = ?d",$_POST['skillName'],$specialization_id)->total;
        if($exist_skill == 0){// to skill den yparxei me thn idia ejeidikeysh sth vash , ara mporei na diasundethei kai me allh ejeidikeysh
            $new_skill = Database::get()->querySingle("SELECT id FROM mentoring_skills WHERE name = ?s",$_POST['skillName'])->id;
            $add_only_skill = Database::get()->query("INSERT INTO mentoring_specializations_skills SET skill_id = ?d, specialization_id = ?d",$new_skill,$specialization_id);
            if($add_only_skill){
                $keyWordsList = Database::get()->queryArray("SELECT name FROM mentoring_keywords WHERE specialization_id = ?d",$specialization_id);
                if(count($keyWordsList) > 0){
                    foreach($keyWordsList as $k){
                        Database::get()->query("INSERT INTO mentoring_keywords SET name = ?s, skill_id = ?d, specialization_id = ?d",$k->name,$new_skill,$specialization_id);
                    }
                }
                Session::flash('message',$langAddSkillSuccessMsg);
                Session::flash('alert-class', 'alert-success');
            }
        }else{// to skill uparxei sthn vash me thn idia ejeidikeush, ara mhhnuma warning
            Session::flash('message',$langAddSkillNoSuccessMsg);
            Session::flash('alert-class', 'alert-warning');
        }
    }
    
    redirect_to_home_page('modules/admin/mentoring_mentor_filters.php');
}

if(isset($_POST['submitdelSkill'])){
    if(!empty($_POST['delSkillsIds'])){
        $specialization_id = $_POST['specialization_id'];
        foreach($_POST['delSkillsIds'] as $sk_id){
            $del = Database::get()->query("DELETE FROM mentoring_specializations_skills WHERE skill_id = ?d AND specialization_id = ?d",$sk_id,$specialization_id);
            //delete keyword for this skill and specialization
            $del_keyword = Database::get()->query("DELETE FROM mentoring_keywords WHERE skill_id = ?d AND specialization_id = ?d",$sk_id,$specialization_id);
            //delete mentors from skill and specialization
            $del_mentors = Database::get()->querySingle("DELETE FROM mentoring_mentor_skills WHERE skill_id = ?d AND specialization_id = ?d",$sk_id,$specialization_id);
        }
        if($del){
            Session::flash('message',$langSkillSuccessMsg);
            Session::flash('alert-class', 'alert-success');
        }
    }else{
        Session::flash('message',$langSkillNoSuccessMsg);
        Session::flash('alert-class', 'alert-warning');
    }

    redirect_to_home_page('modules/admin/mentoring_mentor_filters.php');
}

if(isset($_POST['submitAddKey'])){
    if(!empty($_POST['KeyName'])){
        //First, get skill_ids
        $skill_ids = Database::get()->queryArray("SELECT skill_id FROM mentoring_specializations_skills WHERE specialization_id = ?d",$_POST['specialization_id']);
        if(count($skill_ids) > 0){
            foreach($skill_ids as $s){
                $addKey = Database::get()->query("INSERT INTO mentoring_keywords SET name = ?s, specialization_id = ?d, skill_id = ?d",$_POST['KeyName'],$_POST['specialization_id'],$s->skill_id);
            }
            if($addKey){
                Session::flash('message',$langKeySuccessMsg);
                Session::flash('alert-class', 'alert-success');
            }
        }
    }else{
        Session::flash('message',$langMissingKeyNameMsg);
        Session::flash('alert-class', 'alert-warning');
    }

    redirect_to_home_page('modules/admin/mentoring_mentor_filters.php');
}

if(isset($_POST['submitDelKey'])){
    if(!empty($_POST['delKeyNames'])){
        foreach($_POST['delKeyNames'] as $sk_id){
            $delKey = Database::get()->query("DELETE FROM mentoring_keywords WHERE name = ?s AND specialization_id = ?d",$sk_id,$_POST['specialization_id']);
        }
        if($delKey){
            Session::flash('message',$langDelKeyNameMsgSuccess);
            Session::flash('alert-class', 'alert-success');
        }
    }else{
        Session::flash('message',$langDontSelectKeyNameMsg);
        Session::flash('alert-class', 'alert-warning');
    }

    redirect_to_home_page('modules/admin/mentoring_mentor_filters.php');
}

$data['list_specializations'] = Database::get()->queryArray("SELECT *FROM mentoring_specializations");

$data['action_bar'] = action_bar([
    [ 'title' => trans('langBack'),
        'url' => $urlServer.'modules/admin/mentoring_platform_enable.php',
        'icon' => 'fa-reply',
        'level' => 'primary-label',
        'button-class' => 'btn-secondary']
    ], false);

view('admin.mentoring_platform.mentoring_mentor_filters', $data);


