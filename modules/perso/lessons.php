<?PHP
//session_start();

/*
*
*	File : lessons.php
*
*	Lessons View
*
*	This class return all the lessons the user is subscribed to
*	along with additional information regarding each lesson's
*	code, name and professor
*
*	@author Evelthon Prodromou <eprodromou@upnet.gr>
*
*	@access public
*
*	@version 1.0.1
*
*/


//if type is 'data' it returns an array with all lesson data
//if type is 'html' it creates the interface html populated with data and
//returnes it to the calling function
function  getUserLessonInfo($uid, $type) {
	//	?$userID=$uid;
	global $mysqlMainDb;
	$user_courses = "SELECT
								cours.code , cours.fake_code , 
	                                           cours.intitule , cours.titulaires ,
	                                           cours.languageCourse ,
	                                           cours_user.statut 

	                                   FROM    cours, cours_user
	                                  
	                                  WHERE cours.code = cours_user.code_cours
	                                  AND   cours_user.user_id = '".$uid."' ";


	$mysql_query_result = db_query($user_courses, $mysqlMainDb);

	$repeat_val = 0;

	$lesson_titles = array();

	//getting user's lesson titles
	while ($mycourses = mysql_fetch_row($mysql_query_result)) {

		$lesson_titles[$repeat_val] 	= $mycourses[2]; //lesson titles
		$lesson_code[$repeat_val]		= $mycourses[0]; //lesson code used in tables
		$lesson_professor[$repeat_val]	= $mycourses[3]; //lesson professor
		$lesson_statut[$repeat_val]		= $mycourses[5];//statut (user|prof)

		$repeat_val++;
	}
	$max_repeat_val = $repeat_val;

	$ret_val[0] = $max_repeat_val;
	$ret_val[1] = $lesson_titles;
	$ret_val[2]	= $lesson_code;
	$ret_val[3] = $lesson_professor;
	$ret_val[4] = $lesson_statut;

	//check what sort of data should be returned
	if($type == "html") {
		return array($ret_val,htmlInterface($ret_val));
//		return htmlInterface($ret_val);
	} elseif ($type == "data") {
		return $ret_val;
	}

	//	return($ret_val);

}


function htmlInterface($data) {
	$lesson_content = <<<lCont

      		<div class="blocks">
			<table width="100%" class="blocks">
			<thead>
				<tr><th>Τα μαθήματα μου</th></tr>
			</thead><tbody>
lCont;

	for ($i=0; $i<$data[0]; $i++){
		if ($data[4][$i] == 1) {
			$prof_css = "class=\"statut\"";
		} else {
			$prof_css = "";
		}

		$lesson_content .= "
	<tr>
	<td><div $prof_css>
		<a $prof_css href=\"courses/".$data[2][$i]."\" onfocus=\"this.blur()\">".$data[2][$i]." - ".$data[1][$i]."
		<br>".$data[3][$i]."</div></a>
	</td>
	</tr>
	
	";
	}



	$lesson_content .= <<<lCont2
	<tr>
		<td>
		<br>
		<p>Λίστα μαθημάτων | Δημιουργία μαθήματος</p>
		</td>
	</tr>
 		</tbody></table>
		</div>
   		
lCont2;


	return $lesson_content;
}
function LessonsView($param = null)
{

	//$this->user_id			= $this->setDefault($param['user_id'], null);
	//$this->tools_url		= $this->setDefault($param['tools_url'], null);
	$this->max_repeat_val	= $this->setDefault($param['max_repeat_val'], null);
	$this->lesson_titles	= $this->setDefault($param['lesson_titles'], null);
	$this->lesson_code		= $this->setDefault($param['lesson_code'], null);
	$this->lesson_professor	= $this->setDefault($param['lesson_professor'], null);


	//$lv = new DBI();
	//$user_lesson_info = $lv->getUserLessonInfo($this->user_id);

	$max_repeat_val = $this->max_repeat_val;/*$user_lesson_info[0];*/
	$lesson_titles = $this->lesson_titles;/*$user_lesson_info[1];*/
	$lesson_code = $this->lesson_code;/*$user_lesson_info[2];*/




	for ($i=0;$i<$max_repeat_val;$i++)
	{
		$tree_data[$i]['lesson_code']	= $lesson_code[$i];
		$tree_data[$i]['lesson_title'] 	= $this->lesson_titles[$i];
		$tree_data[$i]['lesson_prof']	= $this->lesson_professor[$i];

	}

	return $tree_data;

}

function setDefault($value, $default)
{
	return (isset($value)) ? $value : $default;
}



?>