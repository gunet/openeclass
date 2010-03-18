<?php

/*========================================================================
*   Open eClass 2.3
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2010  Greek Universities Network - GUnet
*  A full copyright notice can be read in "/info/copyright.txt".
*
*  Developers Group:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*			Yannis Exidaridis <jexi@noc.uoa.gr>
*			Alexandros Diamantidis <adia@noc.uoa.gr>
*			Tilemachos Raptis <traptis@noc.uoa.gr>
*
*  For a full list of contributors, see "credits.txt".
*
*  Open eClass is an open platform distributed in the hope that it will
*  be useful (without any warranty), under the terms of the GNU (General
*  Public License) as published by the Free Software Foundation.
*  The full license can be read in "/info/license/license_gpl.txt".
*
*  Contact address: 	GUnet Asynchronous eLearning Group,
*  			Network Operations Center, University of Athens,
*  			Panepistimiopolis Ilissia, 15784, Athens, Greece
*  			eMail: info@openeclass.org
* =========================================================================*/

/**===========================================================================
	scormExport12.inc.php
	@last update: 29-08-2009 by Thanos Kyritsis
	@authors list: Thanos Kyritsis <atkyritsis@upnet.gr>
	               
	based on Claroline version 1.7 licensed under GPL
	      copyright (c) 2001, 2006 Universite catholique de Louvain (UCL)
	      
	      original file: scormExport.inc.php Revision: 1.11.2.4
	      
	Claroline authors: Amand Tihon <amand.tihon@alrj.org>
==============================================================================        
    @Description: This script is for export to SCORM 1.2 package. It is kept
                  for historical and backwards compatibility reasons.
                  
                  How SCORM export should be done

                  1. Get (flat) list of LP's content
                  2. Create export directory structure, with base files 
                  (dtd/xsd, javascript, ...)
                  3. Find if any SCOs are used in the LP
                  4. If it's the case, copy the whole SCORM content into 
                  destination directory
                  5. Rebuild imsmanifest.xml from the list we got in 1.
                     - *EVERY* document must be present in the LP. If an 
                     HTML document includes an image, 
                     it must be declared in the LP, but marked as "invisible".
                     - If a module is "visible", add it both as an <item> and 
                     as a <resource>, otherwise, add it only as a <resource>.
                     - The rebuild must take into acount that modules are ordered 
                     in a tree, not a flat list.

                  Current limitations :
                  - Dependencies between resources are not taken into account.
                  - No multi-page exercises

                  This file is currently supposed to be included by 
                  learningPathList.php, in order to inherit some of its global 
                  variables, like some tables' names.

    @Comments:
 
    @todo: 
==============================================================================
*/

if(!class_exists('ScormExport')):

require_once("../../include/lib/fileManageLib.inc.php");
require_once("../../include/lib/fileUploadLib.inc.php");
require_once("../../include/pclzip/pclzip.lib.php");
require_once('../../include/lib/textLib.inc.php');


$TBL_EXERCICES              = 'exercices';
$TBL_EXERCICE_QUESTION      = 'exercice_question';
$TBL_QUESTIONS              = 'questions';
$TBL_REPONSES               = 'reponses';


require_once('../exercice/exercise.class.php');
require_once('../exercice/question.class.php');
require_once('../exercice/answer.class.php');
require_once('../exercice/exercise.lib.php');

define('UNIQUE_ANSWER',   1);
define('MULTIPLE_ANSWER', 2);
define('FILL_IN_BLANKS',  3);
define('MATCHING',        4);
define('TRUEFALSE',	 	  5);

// for fill in blanks questions
define('TEXTFIELD_FILL', 1);
define('LISTBOX_FILL',	2);
/**
 * Exports a Learning Path to a SCORM package.
 *
 * @author Thanos Kyritsis <atkyritsis@upnet.gr>
 * @author Amand Tihon <amand@alrj.org>
 */
class ScormExport
{
    var $id;
    var $name;
    var $comment;
    var $resourceMap;
    var $itemTree;
    var $fromScorm;
    var $destDir;
    var $srcDirScorm;
    var $srcDirDocument;
    var $srcDirExercise;
    
    var $manifest_itemTree;
    var $scormURL;
    var $mp3Found;
    
    var $error;
    
    /**
     * Constructor
     * 
     * @param $learnPathId The ID of the learning path to export
     * @author Amand Tihon <amand@alrj.org>
     */
    function ScormExport($learnPathId)
    {
        /* Default values */
        $this->id = (int)$learnPathId;
        $this->fromScorm = false;
        $this->mp3Found = false;
        $this->resourceMap = array();
        $this->itemTree = array();
        $this->error = array();
        
    }
    
   
    /**
     * Returns the error
     *
     * @author Amand Tihon <amand@alrj.org>
     */
    function getError()
    {
        return $this->error;
    }
         
    /**
     * Fetch info from the database 
     *
     * @return False on error, true otherwise.
     * @author Thanos Kyritsis <atkyritsis@upnet.gr>
     * @author Amand Tihon <amand@alrj.org>
     */
    function fetch()
    {
        global $TABLELEARNPATH, $TABLELEARNPATHMODULE, $TABLEMODULE, $TABLEASSET, $webDir, $currentCourseID;
        global $langLearningPathNotFound, $langLearningPathEmpty;
    
        /* Get general infos about the learning path */
        $sql = 'SELECT `name`, `comment`
                FROM `'.$TABLELEARNPATH.'`
                WHERE `learnPath_id` = '. $this->id;
                
        $result = db_query($sql);
        if ( empty($result) )
        {
            $this->error[] = $langLearningPathNotFound;
            return false;
        }
        
        $list = mysql_fetch_array($result, MYSQL_ASSOC);
        if ( empty($list) )
        {
            $this->error[] = $langLearningPathNotFound;
            return false;
        }
        
        $this->name = $list['name'];
        $this->comment = $list['comment'];
        
        /* Build various directories' names */
        
        // Replace ',' too, because pclzip doesn't support it.
        $this->destDir = $webDir . "courses/" . $currentCourseID . '/temp/' 
            . str_replace(',', '_', replace_dangerous_char($this->name));
        $this->srcDirDocument = $webDir . "courses/" . $currentCourseID . "/document";
        $this->srcDirExercise  = $webDir . "courses/" . $currentCourseID . "/exercise";
        $this->srcDirScorm    = $webDir . "courses/" . $currentCourseID . "/scormPackages/path_".$this->id;
        
        /* Now, get the complete list of modules, etc... */
        $sql = 'SELECT  LPM.`learnPath_module_id` ID, LPM.`lock`, LPM.`visibility`, LPM.`rank`, 
                        LPM.`parent`, LPM.`raw_to_pass`, LPM.`specificComment` itemComment,
                        M.`name`, M.`contentType`, M.`comment` resourceComment, A.`path`
                FROM `'.$TABLELEARNPATHMODULE.'` AS LPM
                LEFT JOIN `'.$TABLEMODULE.'` AS M
                       ON LPM.`module_id` = M.`module_id`
                LEFT JOIN `'.$TABLEASSET.'` AS A
                       ON M.`startAsset_id` = A.`asset_id`
                WHERE LPM.`learnPath_id` = '. $this->id.'
                ORDER BY LPM.`parent`, LPM.`rank`
               ';

        $result = db_query($sql);
        if ( empty($result) )
        {
            $this->error = $langLearningPathEmpty;
            return false;
        }
        
        while ($module = mysql_fetch_array($result, MYSQL_ASSOC))
        {
            // Check for SCORM content. If at least one module is SCORM, we need to export the existing SCORM package
            if ( $module['contentType'] == 'SCORM' || $module['contentType'] == 'SCORM_ASSET' ) $this->fromScorm = true;
            
            // If it is an exercise, create a filename for it.
            if ( $module['contentType'] == 'EXERCISE' )    $module['fileName'] = 'quiz_' . $module['path'] . '.html';
            
            // Only for clarity :
            $id = $module['ID'];
            $parent = $module['parent'];
            
            // Add to the flat resource map
            $this->resourceMap[$id] = $module;
            
            // Build Item tree, only keeping visible modules
            if ( $module['visibility'] == 'SHOW' ) {
                if ( ! $parent )
                {
                    // parent is 0, item is at root
                    $this->itemTree[$id] = &$this->resourceMap[$id];
                }
                else
                {
                    /* item has a parent. Add it to the list of its children.
                       Note that references are used, not copies. */
                    $this->resourceMap[$parent]['children'][] = &$this->resourceMap[$id];
                }
            }
        }
        
        return true;
    }
    
    
    /**
    * Exports an exercise as a SCO.
    * This method is intended to be called from the prepare method.
    *
    *@note There's a lot of nearly cut-and-paste from exercise.lib.php here
    *      because of some little differences... 
    *      Perhaps something that could be refactorised ?
    * 
    * @see prepare
    * @param $quizId The quiz 
    * @param $raw_to_pass The needed score to attain
    * @return False on error, True if everything went well.
    * @author Thanos Kyritsis <atkyritsis@upnet.gr>
    * @author  Amand Tihon <amand@alrj.org>
    */
    function prepareQuiz($quizId, $raw_to_pass=50)
    {
        global $langQuestion, $langOk, $langScore, $claro_stylesheet, $clarolineRepositorySys;
        global $charset, $langExerciseDone;
        // those two variables are needed by display_attached_file()
        global $attachedFilePathWeb;
        global $attachedFilePathSys;
        $attachedFilePathWeb = 'Exercises';
        $attachedFilePathSys = $this->destDir . '/Exercises';
                
// Generate standard page header 
        $pageHeader = '<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset='.$charset.'">
<meta http-equiv="expires" content="Tue, 05 DEC 2000 07:00:00 GMT">
<meta http-equiv="Pragma" content="no-cache">
<link rel="stylesheet" type="text/css" href="compatible.css" />
<link rel="stylesheet" type="text/css" href="' . $claro_stylesheet . '" media="screen, projection, tv" />
<script language="javascript" type="text/javascript" src="APIWrapper.js"></script>
<script language="javascript" type="text/javascript" src="scores.js"></script>
' . "\n";

        
        $pageBody = '<body onload="loadPage()">
    <div id="claroBody"><form id="quiz">
    <table width="99%" border="0" cellpadding="1" cellspacing="0"><tr><td>' . "\n";
        
        
        // read the exercise
        $quiz = new Exercise();
        if (! $quiz->read($quizId))
        {
            $this->error[] = $langErrorLoadingExercise;
            return false;
        }
        
        // Get the question list
        $questionList = $quiz->selectQuestionList();
        $questionCount = $quiz->selectNbrQuestions();
        
        // Keep track of raw scores (ponderation) for each question
        $questionPonderationList = array();
        
        // Keep track of correct texts for fill-in type questions
        $fillAnswerList = array();

        // Counter used to generate the elements' id. Incremented after every <input> or <select>
        $idCounter = 0;
        
        // Display each question
        $questionCount = 0;
        foreach($questionList as $questionId)
        {
            // Update question number
            $questionCount++;
        
            // read the question, abort on error
            $question = new Question();
            if (!$question->read($questionId))
            {
                $this->error[] = $langErrorLoadingQuestion;
                return false;
            }
            $qtype        = $question->selectType();
            $qtitle       = $question->selectTitle();
            $qdescription = $question->selectDescription();
            $questionPonderationList[$questionId] = $question->selectWeighting();
            
            // Generic display, valid for all kind of question
            $pageBody .= '<table width="99%">
    <tr><th valign="top" colspan="2">' . $langQuestion . ' ' . $questionCount . '</th></tr>
    <tfoot>
        <tr><td valign="top" colspan="2">' . $qtitle . '</td></tr>
        <tr><td valign="top" colspan="2"><i>' . parse_user_text($qdescription) . '</i></td></tr>' . "\n";
            
            // Attached file, if it exists.
            //$attachedFile = $question->selectAttachedFile();
            if ( !empty($attachedFile))
            {
                // copy the attached file
                if ( !claro_copy_file($this->srcDirExercise . '/' . $attachedFile, $this->destDir . '/Exercises') )
                {
                    $this->error[] = $langErrorCopyAttachedFile . $attachedFile;
                    return false;
                }
                
                // Ok, if it was an mp3, we need to copy the flash mp3-player too.
                $extension=substr(strrchr($attachedFile, '.'), 1);
                if ( $extension == 'mp3')   $this->mp3Found = true;
                
                $pageBody .= '<tr><td colspan="2">' . display_attached_file($attachedFile) . '</td></tr>' . "\n";
            }
        
            /*
             * Display the possible answers
             */
            
            $answer = new Answer($questionId);
            $answerCount = $answer->selectNbrAnswers();
            
            // Used for matching:
            $letterCounter = 'A';
            $choiceCounter = 1;
            $Select = array();
            
            for ($answerId=1; $answerId <= $answerCount; $answerId++)
            {
                $answerText = $answer->selectAnswer($answerId);
                $answerCorrect = $answer->isCorrect($answerId);
                
                // Unique answer
                if ( $qtype == UNIQUE_ANSWER || $qtype == TRUEFALSE )
                {
                    // Construct the identifier
                    $htmlQuestionId = 'unique_' . $questionCount . '_x';
                    
                    $pageBody .= '<tr><td width="5%" align="center">
                        <input type="radio" name="' . $htmlQuestionId . '"
                        id="scorm_' . $idCounter . '"
                        value="' . $answer->selectWeighting($answerId) . '"></td>
                    <td width="95%"><label for="scorm_' . $idCounter . '">' . $answerText . '</label>
                    </td></tr>';
                    
                    $idCounter++;
                }
                // Multiple answers
                elseif ( $qtype == MULTIPLE_ANSWER )
                {
                    // Construct the identifier
                    $htmlQuestionId = 'multiple_' . $questionCount . '_' . $answerId;
                    
                    // Compute the score modifier if this answer is checked
                    $raw = $answer->selectWeighting($answerId);
                    
                    $pageBody .= '<tr><td width="5%" align="center">
                        <input type="checkbox" name="' . $htmlQuestionId . '"
                        id="scorm_' . $idCounter . '" 
                        value="' . $raw . '"></td>
                    <td width="95%"><label for="scorm_' . $idCounter . '">' . $answerText . '</label>
                    </td></tr>';
                    
                    $idCounter++;
                }
                // Fill in blanks
                elseif ( $qtype == FILL_IN_BLANKS )
                {
                    $pageBody .= '<tr><td colspan="2">';
                
                    // We must split the text, to be able to treat each input independently
                    
                    // separate the text and the scorings
                    $explodedAnswer = explode( '::',$answerText);
		            $phrase = (isset($explodedAnswer[0]))?$explodedAnswer[0]:'';
		            $weighting = (isset($explodedAnswer[1]))?$explodedAnswer[1]:'';
		            $fillType = (!empty($explodedAnswer[2]))?$explodedAnswer[2]:1;
		            // default value if value is invalid
            		if( $fillType != TEXTFIELD_FILL && $fillType != LISTBOX_FILL )  $fillType = TEXTFIELD_FILL;
            		$wrongAnswers = (!empty($explodedAnswer[3]))?explode('[',$explodedAnswer[3]):array();
                    // get the scorings as a list
                    $fillScoreList = explode(',', $weighting);
                    $fillScoreCounter = 0;
                    
                    if( $fillType == LISTBOX_FILL )// listbox
					{
			            // get the list of propositions (good and wrong) to display in lists
						// add wrongAnswers in the list
						$answerList = $wrongAnswers;
						// add good answers in the list

						// we save the answer because it will be modified
						$temp = $phrase;
						while(1)
						{
							// quits the loop if there are no more blanks
							if(($pos = strpos($temp,'[')) === false)
							{
								break;
							}
							// removes characters till '['
							$temp = substr($temp,$pos+1);
							// quits the loop if there are no more blanks
							if(($pos = strpos($temp,']')) === false)
							{
								break;
							}
							// stores the found blank into the array
							$answerList[] = substr($temp,0,$pos);
			                // removes the character ']'
							$temp = substr($temp,$pos+1);
						}
						// alphabetical sort of the array
						natcasesort($answerList);
					}
                    // Split after each blank
                    $responsePart = explode(']', $phrase);
                    $acount = 0;
                    foreach ( $responsePart as $part )
                    {
                        // Split between text and (possible) blank
                        if( strpos($part,'[') !== false )
                        {
                        	list($rawtext, $blankText) = explode('[', $part);
						}
						else
						{
							$rawtext = $part;
							$blankText = "";
						}
                        
                        $pageBody .= $rawtext;

                        // If there's a blank to fill-in after the text (this is usually not the case at the end)
                        if ( !empty($blankText) )
                        {
                            // Build the element's name
                            $name = 'fill_' . $questionCount . '_' . $acount;
                            // Keep track of the correspondance between element's name and correct value + scoring
                            $fillAnswerList[$name] = array($blankText, $fillScoreList[$fillScoreCounter]);
                            if( $fillType == LISTBOX_FILL )// listbox
                            {
                              	$pageBody .= '<select name="' . $name . '" id="scorm_' . $idCounter . '">'."\n"
								            .'<option value="">&nbsp;</option>';

								foreach($answerList as $answer)
								{
									$pageBody .= '<option value="'.htmlspecialchars($answer).'">'.$answer.'</option>'."\n";
								}

								$pageBody .= '</select>'."\n";
							}
							else
							{
                            	$pageBody .= '<input type="text" name="' . $name . '" size="10" id="scorm_' . $idCounter . '">';
							}
                            $fillScoreCounter++;
                            $idCounter++;
                        }
                    
                        $acount++;
                    }
                    
                    $pageBody .= '</td></tr>' . "\n";
                
                }
                // Matching
                elseif ( $qtype == MATCHING )
                {
                    if ( !$answer->isCorrect($answerId) )
                    {
                        // Add the option as a possible answer.
                        $Select[$answerId] = $answerText;
                    }
                    else
                    {
                        $pageBody .= '<tr><td colspan="2">
                        <table border="0" cellpadding="0" cellspacing="0" width="99%">
                        <tr>
                            <td width="40%" valign="top"><b>' . $choiceCounter . '.</b> ' . $answerText . '</td>
                            <td width="20%" valign="center">&nbsp;<select name="matching_' . $questionCount . '_' . 
                            $answerId . '" id="scorm_' . $idCounter . '">
                            <option value="0">--</option>';
                            
                        $idCounter++;
                        
                        // fills the list-box
                        $letter = 'A';
                        foreach ($Select as $key => $val)
                        {
                            $scoreModifier = ( $key == $answer->isCorrect($answerId) ) ? $answer->selectWeighting($answerId) : 0;
                            $pageBody .= '<option value="' . $scoreModifier . '">' . $letter++ .
                            '</option>';
                        }
                        
                        $pageBody .= '</select></td><td width="40%" valign="top">';
                        if ( isset($Select[$choiceCounter]) )
                        {
                            $pageBody .= '<b>' . $letterCounter . '.</b> ' . $Select[$choiceCounter];
                        }
                        $pageBody .= '&nbsp;</td></tr></table></td></tr>' . "\n";
                        
                        // Done with this one
                        $letterCounter++;
                        $choiceCounter++;
                        
                        // If the left side has been completely displayed :
                        if ( $answerId == $answerCount )
                        {
                            // Add all possibly remaining answers to the right
                            while ( isset($Select[$choiceCounter]) )
                            {
                                $pageBody .= '<tr><td colspan="2">
                                <table border="0" cellpadding="0" cellspacing="0" width="99%">
                                <tr>
                                    <td width="40%">&nbsp;</td>
                                    <td width="20%">&nbsp;</td>
                                    <td width="40%"><b>' . $letterCounter . '.</b> ' . $Select[$choiceCounter] . '</td>
                                </tr>
                                </table>
                                </td></tr>' . "\n";
                                
                                $letterCounter++;
                                $choiceCounter++;
                            } // end while
                        } // end if
                    } // else
                } // end if (MATCHING)
            } // end for each answer
            
            // End of the question
            $pageBody .= '</tfoot></table>' . "\n\n";
            
        } // foreach($questionList as $questionId)
        
        // No more questions, add the button.
        $pageEnd = '</td></tr>
            <tr>
                <td align="center"><br><input type="button" value="' . $langOk . '" onClick="calcScore()"></td>
            </tr>
            </table>
            </form>
            </div></body></html>' . "\n";
            
        /* Generate the javascript that'll calculate the score
         * We have the following variables to help us :
         * $idCounter : number of elements to check. their id are "scorm_XY"
         * $raw_to_pass : score (on 100) needed to pass the quiz
         * $fillAnswerList : a list of arrays (text, score) indexed on <input>'s names
         * 
         */
        $pageHeader .= '
<script type="text/javascript" language="javascript">
    var raw_to_pass = ' . $raw_to_pass . ';
    var weighting = ' . array_sum($questionPonderationList) . ';
    var rawScore;
    var scoreCommited = false;
    var showScore = true;
    var fillAnswerList = new Array();' . "\n";
    
        // Add the data for fillAnswerList
        foreach ($fillAnswerList as $key=>$val)
        {
            $pageHeader .= "    fillAnswerList['" . $key . "'] = new Array('" . $val[0] . "', '" . $val[1] . "');\n";
        }

        // This is the actual code present in every exported exercise.
        $pageHeader .= '
        
    function calcScore()
    {
		if( !scoreCommited )
		{
	        rawScore = CalculateRawScore(document, ' . $idCounter . ', fillAnswerList);
	        var score = Math.max(Math.round(rawScore * 100 / weighting), 0);
	        var oldScore = doLMSGetValue("cmi.core.score.raw");

	        doLMSSetValue("cmi.core.score.max", weighting);
	        doLMSSetValue("cmi.core.score.min", 0);

	        computeTime();

	        if (score > oldScore) // Update only if score is better than the previous time.
	        {
	            doLMSSetValue("cmi.core.score.raw", rawScore);
	        }

	        var mode = doLMSGetValue( "cmi.core.lesson_mode" );
	        if ( mode != "review"  &&  mode != "browse" )
	        {
	            var oldStatus = doLMSGetValue( "cmi.core.lesson_status" )
	            if (score >= raw_to_pass)
	            {
	                doLMSSetValue("cmi.core.lesson_status", "passed");
	            }
	            else if (oldStatus != "passed" ) // If passed once, never mark it as failed.
	            {
	                doLMSSetValue("cmi.core.lesson_status", "failed");
	            }
	        }

	        doLMSCommit();
	        doLMSFinish();
	        scoreCommited = true;
	        if(showScore) alert(\''.clean_str_for_javascript($langScore).' :\n\' + rawScore + \'/\' + weighting + \'\n\' + \''.clean_str_for_javascript($langExerciseDone).'\');
		}
    }

</script>
';
        
        // Construct the HTML file and save it.
        $filename = "quiz_" . $quizId . ".html";
        
        $pageContent = $pageHeader 
                     . $pageBody
                     . $pageEnd;
                
        if (! $f = fopen($this->destDir . '/' . $filename, 'w') )
        {
            $this->error[] = $langErrorCreatingFile . $filename;
            return false;
        }
        fwrite($f, $pageContent);
        fclose($f);

        // Went well.
        return True;
    }

    
    /**
     * Prepare the temporary destination directory that'll be zipped and exported.
     * Existing SCORM, documents, as well as required or helper javascript files and XML schemas
     * are copied into the directory.
     * No manifest created yet.
     * 
     * @return False on error, true otherwise.
     * @see createManifest
     * @author Thanos Kyritsis <atkyritsis@upnet.gr>
     * @author Amand Tihon <amand@alrj.org>
     */
    function prepare()
    {
        global $clarolineRepositorySys, $claro_stylesheet;
        global $langErrorCopyScormFiles, $langErrorCreatingDirectory, $langErrorCopyingScorm, $langErrorCopyAttachedFile;
        // (re)create fresh directory
        claro_delete_file($this->destDir);
        if ( !claro_mkdir($this->destDir, CLARO_FILE_PERMISSIONS , true))
        {
            $this->error[] = $langErrorCreatingDirectory . $this->destDir;
            return false;
        }
        
        // Copy usual files (.css, .js, .xsd, etc)
        if (
               !claro_copy_file('export12/APIWrapper.js', $this->destDir)
            || !claro_copy_file('export12/scores.js', $this->destDir)
            || !claro_copy_file('export12/ims_xml.xsd', $this->destDir)
            || !claro_copy_file('export12/imscp_rootv1p1p2.xsd', $this->destDir)
            || !claro_copy_file('export12/imsmd_rootv1p2p1.xsd', $this->destDir)
            || !claro_copy_file('export12/adlcp_rootv1p2.xsd', $this->destDir)  )
        {
            $this->error[] = $langErrorCopyScormFiles;
            return false;
        }
        
        
        // Copy SCORM package, if needed
        if ($this->fromScorm) 
        {
            // Copy the scorm directory as OrigScorm/
            if (
                   !claro_copy_file($this->srcDirScorm,  $this->destDir)
                || !claro_rename_file($this->destDir.'/path_'.$this->id, $this->destDir.'/OrigScorm')  )
            {
                $this->error[] = $langErrorCopyingScorm;
                return false;
            }
        }
        
        // Create destination directory for "pure" documents
        claro_mkdir($this->destDir.'/Documents', CLARO_FILE_PERMISSIONS, true);
        
        // And for exercises
        claro_mkdir($this->destDir.'/Exercises', CLARO_FILE_PERMISSIONS, true);
        
        // Copy documents into the created directory
        foreach($this->resourceMap as $module)
        {
            if ( $module['contentType'] == 'DOCUMENT' )
            {
                $documentName = basename($module['path']);
                if ( dirname($module['path']) != '/' )
                {
                    $destinationDir = $this->destDir . '/Documents' . dirname($module['path']) . '/';
                }
                else 
                {
                    $destinationDir = $this->destDir . '/Documents/';
                }
                if ( ! is_dir($destinationDir) )
                {
                    claro_mkdir($destinationDir, CLARO_FILE_PERMISSIONS, true);
                }
                @copy($this->srcDirDocument . $module['path'], $destinationDir . $documentName);
            }
            elseif ( $module['contentType'] == 'EXERCISE' )
            {
                if ( !$this->prepareQuiz($module['path'], $module['raw_to_pass']))    return false;
            }
        }
        
        // Did we find an mp3 ?
        if ( $this->mp3Found)
        {        
            if ( !claro_copy_file($clarolineRepositorySys . '/exercice/claroPlayer.swf', $this->destDir) )
            {
                $this->error[] = $langErrorCopyAttachedFile . $clarolineRepositorySys . '/exercice/claroPlayer.swf'; 
                
                // This is *NOT* a fatal error.
                // Do *NOT* return false.
            }
        }
        
        
        return true;
    }
    
    
    /**
     * Create the imsmanifest.xml file.
     *
     * @return False on error, true otherwise.
     * @author Amand Tihon <amand@alrj.org>
     */
    function createManifest()
    {
        /**
         * Create a simple <metadata>
         *
         *
         * @param $title The resource title
         * @param $description The resource description
         * @return A string containing the metadata block.
         * @author Amand Tihon <amand@alrj.org>
         */
        function makeMetaData($title, $description)
        {
            if ( empty($title) and empty($description) ) return '<metadata />';
        
            $out = '<metadata>
    <imsmd:lom>
        <imsmd:general>';
        
            if (!empty($title))
            {
            $out .= '
            <imsmd:title>
                <imsmd:langstring>' . htmlspecialchars($title) . '</imsmd:langstring>
            </imsmd:title>';
            }
        
            if (!empty($description))
            {
            $out .= '    
            <imsmd:description>
                <imsmd:langstring>' . htmlspecialchars($description) . '</imsmd:langstring>
            </imsmd:description>';
            }
            
            $out .= '
        </imsmd:general>
    </imsmd:lom>
</metadata>';

            return $out;
        }
        
        /**
         * Recursive function to deal with the tree representation of the items
         *
         * @param $itemlist the subtree to build
         * @param $depth indentation level. Is it really useful ?
         * @return the (sub-)tree representation
         * @author Amand Tihon <amand@alrj.org>
         */
        $blocking = "";
        
        function createItemList($itemlist, $depth=0)
        {
            global $blocking;
            $out = "";
            $ident = "";
            for ($i=0; $i<$depth; $i++) $ident .= "    ";
            foreach ($itemlist as $item)
            {
                $out .= $ident . '<item identifier="I_'.$item['ID'].'" isvisible="true" ';
                if ( $item['contentType'] != 'LABEL' )
                {
                    $out .= 'identifierref="R_' . $item['ID'] . '" ';
                }
                $out .= '>' . "\n";
                $out .= $ident . '    <title>'.htmlspecialchars($item['name']).'</title>' . "\n";
                
                // Check if previous was blocking
                if (!empty($blocking) && ($item['contentType'] != 'LABEL'))
                {
                    $out .= '        <adlcp:prerequisites type="aicc_script"><![CDATA[I_'.$blocking.']]></adlcp:prerequisites>'."\n";
                }
                
                // Add metadata, except for LABELS
                if ( $item['contentType'] != 'LABEL' )
                {
                    $out .= makeMetaData($item['name'], $item['itemComment']) . "\n";
                }
                
                if ( ! isset($item['children']) )
                {
                    // change only if we do not recurse. 
                    $blocking = ($item['lock'] == 'CLOSE') ? $item['ID'] : '';
                }
                else
                {
                    $out .= createItemList($item['children'], $depth+1);
                }
                $out .= $ident . '</item>' . "\n";
            }
            return $out;
        }
        
        /**
         * Create the frame file that'll hold the document. This frame is supposed to
         * set the SCO's status
         * @param $filename string: the name of the file to create, absolute.
         * @param $targetPath string: The actual document path, relative to the scorm
         * @return False on error, true otherwise.
         * @author Amand Tihon <amand@alrj.org>
         */
        function createFrameFile($fileName, $targetPath)
        {
            global $langErrorCreatingFrame, $langErrorCreatingManifest, $charset;
            
            if ( !($f = fopen($fileName, 'w')) )
            {
                $this->error[] = $langErrorCreatingFrame;
                return false;
            }
            
            fwrite($f, '<html><head>
    <meta http-equiv="Content-Type" content="text/html; charset='.$charset.'">
    <script src="APIWrapper.js" type="text/javascript" language="JavaScript"></script>
    <title>Default Title</title>
</head>
<frameset border="0" rows="100%,*" onload="immediateComplete()">
    <frame src="' . $targetPath . '" scrolling="auto">
    <frame src="SCOFunctions.js">
</frameset>
</html>');
            fclose($f);
            
            return true;
        }
        
        /**
         * Create the frame file that'll hold the course description. 
         * This frame sets the SCO's status
         * @param $filename string: the name of the file to create, absolute.
         * @return False on error, true otherwise.
         *
         * @author Thanos Kyritsis <atkyritsis@upnet.gr>
         */
        function createDescFrameFile($fileName)
        {
            global $langErrorCreatingFrame, $langErrorCreatingManifest, $langThisCourseDescriptionIsEmpty, $charset;
            
            if ( !($f = fopen($fileName, 'w')) )
            {
                $this->error[] = $langErrorCreatingFrame;
                return false;
            }
            
            $course_description = "";
            //mysql_select_db("$currentCourseID",$db);
			$sql = "SELECT `id`,`title`,`content` FROM `course_description` order by id";
			$res = db_query($sql);
			if (mysql_num_rows($res) >0 )
			{
				$course_description .= "
					<hr noshade size=\"1\">";
				while ($bloc = mysql_fetch_array($res))
				{ 
					$course_description .= "
					<H4>
						".$bloc["title"]."
					</H4>
					<font size=2 face='arial, helvetica'>
						".make_clickable(nl2br($bloc["content"]))."
					</font>";
				}
			}
			else
			{
				$course_description .= "<br><h4>$langThisCourseDescriptionIsEmpty</h4>";
			}
            
            fwrite($f, '<html>'."\n"
            	.'<head>'."\n"
            	.'<meta http-equiv="Content-Type" content="text/html; charset='.$charset.'">'."\n"
            	.'<script src="APIWrapper.js" type="text/javascript" language="JavaScript"></script>'."\n"
            	.'</head>'."\n"
				.'<body onload="immediateComplete()">'."\n"
				.'<table width="99%" border="0">'."\n"
				.'<tr>'."\n"
				.'<td colspan="2">'."\n"
				.$course_description."\n"
				.'</td>'."\n"
				.'</tr>'."\n"
				.'<tr name="bottomLine">'."\n"
				.'<td colspan="2">'."\n"
				.'<br>'."\n"
				.'<hr noshade size="1">'."\n"
				.'</td>'."\n"
				.'</tr>'."\n"
				.'</table>'."\n"
				.'</body>'."\n"
				.'</html>'."\n"
            	);
            fclose($f);
            
            return true;
        }
        
        
        
        // Start creating sections for items and resources
        
        // First the items...
        $manifest_itemTree = '<organizations default="A1"><organization identifier="A1">' . "\n"
            . '<title>' . $this->name . '</title>' . "\n"
            . createItemList($this->itemTree)
            . '</organization></organizations>' . "\n";
        
        // ...Then the resources
            
        $manifest_resources = "<resources>\n";
        foreach ( $this->resourceMap as $module )
        {
            if ( $module['contentType'] == 'LABEL' ) continue;
            
            switch ( $module['contentType'] ) 
            {
                case 'DOCUMENT': 
                    $framefile = $this->destDir . '/frame_for_' . $module['ID'] . '.html';
                    $targetfile = 'Documents'.$module['path'];
                    
                    // Create an html file with a frame for the document.
                    if ( !createFrameFile($framefile, 'Documents'.$module['path'])) return false;
                    
                    // Add the resource to the manifest
                    $manifest_resources .= '<resource identifier="R_' . $module['ID'] . '" type="webcontent"  adlcp:scormtype="sco" '
                        . ' href="' . basename($framefile) . '">' . "\n"
                        . '  <file href="' . basename($framefile) . '" />' . "\n"
                        . '  <file href="' . $targetfile . '" />' . "\n"
                        . makeMetaData($module['name'], $module['resourceComment'])
                        . "</resource>\n";
                    break;

                case 'EXERCISE':
                    $targetfile = $module['fileName'];
                    
                    // Add the resource to the manifest
                    $manifest_resources .= '<resource identifier="R_' . $module['ID'] . '" type="webcontent"  adlcp:scormtype="sco" '
                        . ' href="' . $targetfile . '" >' . "\n"
                        . '  <file href="' . $targetfile . '" />' . "\n"
                        . makeMetaData($module['name'], $module['resourceComment'])
                        . "</resource>\n";
                    break;
                        
                case 'SCORM_ASSET' :
                	// Add the resource to the manifest
                    $path = 'OrigScorm';
                    $manifest_resources .= '<resource identifier="R_' . $module['ID'] . '" type="webcontent" '
                        . ' href="OrigScorm' . $module['path'] . '">' . "\n"
                        . '  <file href="OrigScorm' . $module['path'] . '" />' . "\n"
                        . makeMetaData($module['name'], $module['resourceComment'])
                        . "</resource>\n";
                    break;
                    
                case 'SCORM'   : 
                    // Add the resource to the manifest
                    $path = 'OrigScorm';
                    $manifest_resources .= '<resource identifier="R_' . $module['ID'] . '" type="webcontent"  adlcp:scormtype="sco" '
                        . ' href="OrigScorm' . $module['path'] . '">' . "\n"
                        . '  <file href="OrigScorm' . $module['path'] . '" />' . "\n"
                        . makeMetaData($module['name'], $module['resourceComment'])
                        . "</resource>\n";
                    break;
                    
                 case 'COURSE_DESCRIPTION':
                 	$framefile = $this->destDir . '/frame_for_' . $module['ID'] . '.html';
                 	
                 	// Create an html file with a frame for the document.
                    if ( !createDescFrameFile($framefile)) return false;
                    
                    // Add the resource to the manifest
                    $ridentifier = "R_".$module['ID'];
                    $manifest_resources .= '<resource identifier="' . $ridentifier . '" type="webcontent"  adlcp:scormType="sco" '
                        . ' href="' . basename($framefile) . '">' . "\n"
                        . '  <file href="' . basename($framefile) . '">' . "\n"
                        . makeMetaData($module['name'], $module['resourceComment'], $ridentifier)
                        . "</file>\n"
                        . "</resource>\n";
                 	
                 	break;
                 
                 case 'LINK': 
                    $framefile = $this->destDir . '/frame_for_' . $module['ID'] . '.html';
                    $targetfile = $module['path'];
                    
                    // Create an html file with a frame for the document.
                    if ( !createFrameFile($framefile, $targetfile)) return false;
                    
                    // Add the resource to the manifest
                    $ridentifier = "R_".$module['ID'];
                    $manifest_resources .= '<resource identifier="' . $ridentifier . '" type="webcontent"  adlcp:scormType="sco" '
                        . ' href="' . basename($framefile) . '">' . "\n"
                        . '  <file href="' . basename($framefile) . '">' . "\n"
                        . makeMetaData($module['name'], $module['resourceComment'], $ridentifier)
                        . "</file>\n"
                        . "</resource>\n";
                    break;
                    
                default        : break;
            }
            
        }
        $manifest_resources .= '</resources>' . "\n";
                
        $manifestPath = $this->destDir . '/imsmanifest.xml';
        if ( ! $f = fopen($manifestPath, 'w') ) 
        {
            $this->error[] = $langErrorCreatingManifest;
            return false;
        }
        
        // Prepare Metadata
        $metadata = makeMetaData($this->name, $this->comment);
        
        // Write header
        global $charset;
        fwrite($f, '<?xml version="1.0" encoding="' . $charset . '" ?>
<manifest identifier="SingleCourseManifest" version="1.1"
            xmlns="http://www.imsproject.org/xsd/imscp_rootv1p1p2"
            xmlns:adlcp="http://www.adlnet.org/xsd/adlcp_rootv1p2"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:imsmd="http://www.imsglobal.org/xsd/imsmd_rootv1p2p1"
            xsi:schemaLocation="http://www.imsproject.org/xsd/imscp_rootv1p1p2 imscp_rootv1p1p2.xsd
            http://www.imsglobal.org/xsd/imsmd_rootv1p2p1 imsmd_rootv1p2p1.xsd
            http://www.adlnet.org/xsd/adlcp_rootv1p2 adlcp_rootv1p2.xsd">' . "\n");
        fwrite($f, $metadata);
        fwrite($f, $manifest_itemTree);
        fwrite($f, $manifest_resources);
        fwrite($f, "</manifest>\n");
        fclose($f);
     
        return true;   
    }
    
    
    /**
     * Create the final zip file.
     *
     * @return False on error, True otherwise.
     * @author Amand Tihon <amand@alrj.org>
     */
    function zip()
    {
        global $langErrorCreatingScormArchive;
        
        $list = 1;
        $zipFile = new PclZip($this->destDir . '.zip');
        $list = $zipFile->create($this->destDir, PCLZIP_OPT_REMOVE_PATH, $this->destDir);
        
        if ( !$list )
        {
            $this->error[] = $langErrorCreatingScormArchive;
            return false;
        }
        
        // Temporary directory can be deleted, now that the zip is made.
        claro_delete_file($this->destDir);

        return true;
        
    }
    
    /**
     * Send the .zip file to the browser.
     * 
     * @return Does NOT return !
     * @author Amand Tihon <amand@alrj.org>
     */
    function send()
    {
        $filename = $this->destDir . '.zip';
        header('Content-Description: File Transfer');
        header('Content-Type: application/zip');
        header('Content-Length: ' . filesize($filename));
	header("Content-Disposition: attachment; filename=\"" . my_basename($filename) . "\""); 
        readfile($filename);
        exit(0);
    }

    /**
     * Helper method : take care of everything
     *
     * @return False on error. Does NOT return on success.
     * @author Amand Tihon <amand@alrj.org>
     */
    function export()
    {
        if ( !$this->fetch() ) return false;
        if ( !$this->prepare() ) return false;
        if ( !$this->createManifest() ) return false;
        if ( !$this->zip() ) return false;
        $this->send();
        return True;
    }

}
endif; // !class_exists(ScormExport)
