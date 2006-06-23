<?PHP
//class.courseTools.php
$require_current_course = TRUE;
$langFiles = array('toolManagement', 'create_course');
$require_help = TRUE;
$helpTopic = 'User';

include '../../include/baseTheme.php';
$require_login = true;

$nameTools = $langToolManagement;

$tool_content = "";


if ($is_adminOfCourse){
	global $dbname;

	if(isset($_POST['toolStat'])) $tool_stat = $_POST['toolStat'];
	//	dumpArray($tool_stat);

	$hideSql = "UPDATE  `accueil` SET `visible` = 0 ";

	if (isset($_REQUEST['toolStatus'])) {

		$loopCount = count($tool_stat);
		$i =0;
		$publicTools = array();
		$tool_id = null;
		while ($i< $loopCount) {
			if (!isset($tool_id)) {
				$tool_id = " (`id` = " . $tool_stat[$i] .")" ;
			}
			else {
				$tool_id .= " OR (`id` = " . $tool_stat[$i] .")" ;
			}

			$i++;
		}

		//reset all tools
		db_query($hideSql, $dbname);
		//and activate the ones the professor wants active
		$publicSql = "UPDATE  `accueil`
								SET
								`visible` = 1 WHERE $tool_id";

		db_query($publicSql, $dbname);

	}

	if (isset($delete)) {
		$sql = "DELETE FROM `accueil` WHERE `id` = " . $delete ." ";
		db_query($sql, $dbname);
		unset($sql);
	}

}

if ($is_admin){
	if (isset($_POST['toolName'])) {
		$tool_name = $_POST['toolName'];
	}
	if (isset($_POST['id'])) {
		$tool_id = $_POST['id'];
	}

	if (isset($_REQUEST['toolStatus'])) {

		$loopCount = count($tool_name);
		$i =0;
		$publicTools = array();

		while ($i< $loopCount) {

			if (strlen($tool_name[$i]) > 2){
				$sql = "UPDATE `accueil` SET `rubrique` = '".$tool_name[$i]."'
							WHERE `id`='".$tool_id[$i]."';";

				db_query($sql, $dbname);
			}

			$i++;


		}

		unset($sql);


	}

}


if ($is_adminOfCourse) {

	$tool_content .= <<<tForm
<form action="$_SERVER[PHP_SELF]" method="post" enctype="multipart/form-data">

  <table width="99%">
   <thead>
      <tr>
         <th style="width: 250px;">$langTool</th>
         <th style="width: 100px;">$langStatus</th>

tForm;

	if ($is_admin){
		$tool_content .= "	<th>$langRename</th>
      					";

	}

	$tool_content .= "</tr>
   						</thead>
<tbody>";
	$toolArr = getTools(2);
	$numOfToolGroups = count($toolArr);

	if (is_array($toolArr)){
		$alterRow=0;
		for($i=0; $i< $numOfToolGroups; $i++){

			$numOfTools = count($toolArr[$i][1]);


			for($j=0; $j< $numOfTools; $j++){

				$rowClass = ($alterRow%2) ? "class=\"odd\"" : "";

				if ($i  == 0){

					$tool_content .= "
				    
				      <tr $rowClass>
				         <td>".$toolArr[$i][1][$j]."</td>
				         <td><input name=\"toolStat[]\" type=\"checkbox\" value=\"".$toolArr[$i][4][$j]."\" checked></td>";
					if ($is_admin){
						$tool_content .= "
				        
				         <td><input type=\"text\" name=\"toolName[]\"><input type=\"hidden\" name=\"id[]\" value=\"".$toolArr[$i][4][$j]."\"></td>";
					}
					$tool_content .= "</tr>";

					$alterRow++;
				}  elseif ($i ==  1) {
					$tool_content .= "
					 <tr $rowClass>
				         <td>".$toolArr[$i][1][$j]."</td>
				         <td><input name=\"toolStatDisabled[]\" type=\"checkbox\" value=\"none\" checked disabled></td>";
					if ($is_admin){
						$tool_content .= "
				         <td><input type=\"text\" name=\"toolName[]\"><input type=\"hidden\" name=\"id[]\" value=\"".$toolArr[$i][4][$j]."\"></td>";
					}
					$tool_content .= "</tr>";
					//					If ($alterRow<=$j) $alterRow++;
					$alterRow++;
				} elseif ($i == 2){

					if ($toolArr[$i][4][$j] > 100) {
						$deleteExternLink = $_SERVER['PHP_SELF'] . "?delete=" . $toolArr[$i][4][$j];
						$delLink = " (<a href=\"$deleteExternLink\">$langDelete</a>)";
					}
					if (!isset($delLink)) $delLink = "";
					$tool_content .= "
				      <tr $rowClass>
				         <td>".$toolArr[$i][1][$j]." $delLink</td>
				         <td><input name=\"toolStat[]\" type=\"checkbox\" value=\"".$toolArr[$i][4][$j]."\"></td>";

					if ($is_admin){
						$tool_content .= "
				         <td><input type=\"text\" name=\"toolName[]\"><input type=\"hidden\" name=\"id[]\" value=\"".$toolArr[$i][4][$j]."\"></td>";
					}
					$tool_content .= "</tr>";
					$alterRow++;
				}

			}


		}

	}


	$tool_content .= "
	   </tbody>
	</table>
	
	  <p>
	    <input type=\"submit\" name=\"toolStatus\" value=\"Submit\">
	  </p>
	</form>	";

}

draw($tool_content, 2);

?>