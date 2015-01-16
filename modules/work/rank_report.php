<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2012  Greek Universities Network - GUnet
 * A full copyright notice can be read in "/info/copyright.txt".
 * For a full list of contributors, see "credits.txt".
 *
 * Open eClass is an open platform distributed in the hope that it will
 * be useful (without any warranty), under the terms of the GNU (General
 * Public License) as published by the Free Software Foundation.
 * The full license can be read in "/info/license/license_gpl.txt".
 *
 * Contact address: GUnet Asynchronous eLearning Group,
 *                  Network Operations Center, University of Athens,
 *                  Panepistimiopolis Ilissia, 15784, Athens, Greece
 *                  e-mail: info@openeclass.org
 * ======================================================================== */


/*
 * Σκοπός είναι η δημιουργία ενός πίνακα κατάταξης των εκπαιδευόμενων, σύμφωνα με την
 * βαθμολογία που έχουν λάβει για μία εργασία που έχουν αναλάβει. Κάθε εκπαιδευόμενος
 * υποβάλει την δική του καταχώρηση, και βαθμολογείται αυτόματα γι' αυτήν. Αυτό τον πίνακα
 * θα μπορεί να το κατεβάσει σε μορφή pdf ο κάθε εκπαιδευόμενος. Αρχικά λαμβάνουμε μέσω GET
 * το id της εργασίας, και μέσω αυτού λαμβάνουμε τα δεδομένα της εργασίας και των αντίστοιχων
 * καταχωρήσεων των εκπαιδευόμενων, τις οποίες και ταξινομούμε με αύξουσα αρίθμηση βάσει του βαθμού
 * και του χρόνου υποβολής της εργασίας σε περίπτωση ισοβαθμίας.  
 * 
 */
 
$require_current_course = true;
require_once '../../include/baseTheme.php';

// Include the main TCPDF library 
require_once __DIR__.'/../../include/tcpdf/tcpdf_include.php';
require_once __DIR__.'/../../include/tcpdf/tcpdf.php';

require_once 'work_functions.php';




if (isset($_GET['assignment'])) {
	// declare variables
    global $tool_content, $course_title, $m;
    $as_id = intval($_GET['assignment']);
    $assign = get_assignment_details($as_id);
    $submissions = find_submissions_by_assigment($as_id);
    $i = 1;
    $i++;

	$nameTools = sprintf($langAutoJudgeRankReport, $assign->title);

    
    if($assign==null)
    {
        redirect_to_home_page('modules/work/index.php?course='.$course_code);
    }
    
    $navigation[] = array("url" => "index.php?course=$course_code", "name" => $langWorks);
    $navigation[] = array("url" => "index.php?course=$course_code&amp;id=$as_id", "name" => q($assign->title));

    if (count($submissions)>0) {
         if(!isset($_GET['downloadpdf'])){
			show_report($assign, $submissions,$i);
			draw($tool_content, 2);
          }else {
               download_pdf_file($assign,$submissions); 
          }
       } else {
         Session::Messages($m['WorkNoSubmission'], 'alert-danger');
         redirect_to_home_page('modules/work/index.php?course='.$course_code.'&id='.$id);
       }
   } else {
        redirect_to_home_page('modules/work/index.php?course='.$course_code);
    }

// Returns an array of the details of assignment $id
function get_assignment_details($id) {
    global $course_id;
    return Database::get()->querySingle("SELECT * FROM assignment WHERE course_id = ?d AND id = ?d", $course_id, $id);
}

// returns an array of the submissions of an assigment
function find_submissions_by_assigment($id) {
	return Database::get()->queryArray("SELECT assignment_submit.grade, assignment_submit.submission_date, assignment_submit.grade_comments, user.username FROM assignment_submit Inner Join user on (user.id=assignment_submit.uid) WHERE assignment_id = ?d ORDER BY grade DESC, submission_date", $id);
 
}

function get_course_title() {
    global $course_id;
    $course = Database::get()->querySingle("SELECT title FROM course WHERE id = ?d",$course_id);
    return $course->title;
}

function show_report($assign,$submissions) {
		global $m, $tool_content,$course_code, $langAutoJudgeRank, $langAutoJudgeStudent,
                $langAutoJudgeScenariosPassed, $langAutoJudgeDownloadPdf;
           $tool_content = "
                                <table  style=\"table-layout: fixed; width: 99%\" class='table-default'>
                                <tr>
                                     <td><b>$langAutoJudgeRank</b></td>
                                     <td><b>$langAutoJudgeStudent</b></td>
                                     <td><b>".$m['grade']."</b></td>
                                     <td><b>$langAutoJudgeScenariosPassed</b></td>
                                </tr>". get_table_content($assign,$submissions) . "
                                
                                </table>
                                 <p align='left'><a  class='btn btn-primary' href='rank_report.php?course=".$course_code."&assignment=".$assign->id."&downloadpdf=1'>$langAutoJudgeDownloadPdf</a></p>
                                <br>";
  }

function get_table_content($assign,$submissions) {
    global $themeimg;
    $table_content = "";
    $i=1;

 // End of Condition about rank position and color of medal    
    
    foreach($submissions as $submission){
                     $s = $i;
                     // Condition about rank position and color of medal
                     if ($i==1 or $i == 2) {$s.=" <img src=\"http://".$_SERVER['HTTP_HOST'].$themeimg."/work_medals/Gold_medal_with_cup.svg\" style=\"width: 30px; height: 30px\">";}
                     if ($i==3 or $i == 4) {$s.=" <img src=\"http://".$_SERVER['HTTP_HOST'].$themeimg."/work_medals/Silver_medal_with_cup.svg\"  style=\"width: 30px; height: 30px\">";}
                     if ($i==5 or $i == 6) {$s.=" <img src=\"http://".$_SERVER['HTTP_HOST'].$themeimg."/work_medals/Bronze_medal_with_cup.svg\" style=\"width: 30px; height: 30px\">";}
                     $table_content.="
                                      <tr>
                                      <td style=\"word-break:break-all;\">".$s."</td>
                                      <td style=\"word-break:break-all;\">".$submission->username."</td>
                                      <td style=\"word-break:break-all;\">".$submission->grade."/". $assign->max_grade  ."</td>
                                      <td align=\"center\">".$submission->grade_comments."</td></tr>";
                     $i++;
                }
    return $table_content;
  }

// help function convert greek chars to english 
function greeklish($Name)
{ 
$greek   = array('α','ά','Ά','Α','β','Β','γ', 'Γ', 'δ','Δ','ε','έ','Ε','Έ','ζ','Ζ','η','ή','Η','θ','Θ','ι','ί','ϊ','ΐ','Ι','Ί', 'κ','Κ','λ','Λ','μ','Μ','ν','Ν','ξ','Ξ','ο','ό','Ο','Ό','π','Π','ρ','Ρ','σ','ς', 'Σ','τ','Τ','υ','ύ','Υ','Ύ','φ','Φ','χ','Χ','ψ','Ψ','ω','ώ','Ω','Ώ',' ',"'","'",',');
$english = array('a', 'a','A','A','b','B','g','G','d','D','e','e','E','E','z','Z','i','i','I','th','Th', 'i','i','i','i','I','I','k','K','l','L','m','M','n','N','x','X','o','o','O','O','p','P' ,'r','R','s','s','S','t','T','u','u','Y','Y','f','F','ch','Ch','ps','Ps','o','o','O','O','_','_','_','_');
$string  = str_replace($greek, $english, $Name);
return $string;
}

  
//function download_pdf_file($assign_title, $course_title,  $username, $grade, $auto_judge_scenarios, $auto_judge_scenarios_output){ 

function download_pdf_file($assign,$submissions){ 
    // create new PDF document
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // set document information
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor(PDF_AUTHOR);
    $pdf->SetTitle('Rank Report');
    $pdf->SetSubject('Rank Report');
    // set default header data
    $pdfHeaderStr ='Αναφορά κατάταξης εκπαιδευόμενων για το μάθημα '. get_course_title() . ' και την εργασία ' .  $assign->title;
    $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, $pdfHeaderStr);

    // set header and footer fonts
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

    // set default monospaced font
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

    // set margins
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetHeaderMargin(3);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

    // set auto page breaks
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

    // set image scale factor
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

    // set some language-dependent strings (optional)
    if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
        require_once(dirname(__FILE__).'/lang/eng.php');
        $pdf->setLanguageArray($l);
    }

    // add a page
    $pdf->AddPage();

 $report_details ='
        <style>
    table.first{
        width: 100%;
        border-collapse: collapse;
         vertical-align: center;
    }

    td {
        font-size: 1em;
        border: 1px solid #000000;
        padding: 3px 7px 2px 7px;
        text-align: center;
    }

     th {
        font-size: 1.1em;
        text-align: left;
        padding-top: 5px;
        padding-bottom: 4px;
        background-color: #3399FF;
        color: #ffffff;
        border: 1px solid #000000;
    }
    </style>
     
     <table class="first">
            <tr>
            <th>Κατάταξη</th>
            <th>Εκπαιδευόμενος</th>
            <th>Βαθμός</th>
            <th>Περασμένα Σενάρια</th> 
            </tr>
             '. get_table_content($assign,$submissions).'
             </table>
             ';
    
            
             

    $pdf->writeHTML($report_details, true, false, true, false, '');
    $pdf->Ln();     
    $pdf->Output('Rank Report_'. greeklish(get_course_title().'_'.$assign->title).'.pdf', 'D');
    
}
