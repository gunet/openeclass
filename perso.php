<?php
//perso.php

//include  block files (announcemets.php etc.) from /modules/perso
include("./modules/perso/lessons.php");
include("./modules/perso/assignments.php");
//include("./modules/perso/announcements.php");

//	BEGIN Get user's last login date]==============================================

$last_login_query = 	"SELECT  `id_user` ,  `when` ,  `action`
						FROM  $mysqlMainDb.loginout
						WHERE  `action`  =  'LOGIN' AND  `id_user`  = $uid
						ORDER BY  `when`  DESC 
						LIMIT 1,1 ";

$login_date_result 	= db_query($last_login_query, $mysqlMainDb);
if (mysql_num_rows($login_date_result)) {
	$login_date_fetch	= mysql_fetch_row($login_date_result);
	$_user["persoLastLogin"] = substr($login_date_fetch[1],0,10);
	$_user["lastLogin"] = eregi_replace("-", " ", substr($login_date_fetch[1],0,10));
} else {
	$_user["persoLastLogin"] = date(Y-m-d);
	$_user["lastLogin"] = eregi_replace("-", " ", substr($_user["persoLastLogin"],0,10));
}

//dumpArray($_user);
//	END Get user's last login date]================================================

//	BEGIN user's status query]=====================================================

$user_status_query = db_query("SELECT statut FROM user WHERE user_id = '$uid'", $mysqlMainDb);
if ($row = mysql_fetch_row($user_status_query)) {
	$statut = $row[0];
}
//dumpArray($statut);
//	END user's status query]=======================================================


$user_lesson_info = getUserLessonInfo($uid, "html");

$param = array(	'uid'	=> $uid,
				'max_repeat_val' 	=> $user_lesson_info[0][0], //max repeat val (num of lessons)
				'lesson_titles'	=> $user_lesson_info[0][1],
				'lesson_code'	=> $user_lesson_info[0][2],
				'lesson_professor'	=> $user_lesson_info[0][3],
				'lesson_statut'		=> $user_lesson_info[0][4]

);
$user_assignments = getUserAssignments($param, "html");

$lesson_content = $user_lesson_info[1];

//dumpArray($user_lesson_info);
$tool_content2="";
$tool_content2 .= <<<lCont

      <br>
      <table >
      <thead>
      	<tr>
      		<th> Σύνδεση Χρήστη </th>
      	</tr>
      </thead>
      <tbody>
      	<tr class="odd">
      		<td>
      			<form action="index.php" method="post">
      		  $langUserName <br>
        			<input  name="uname" size="20"><br>
       			 $langPass <br>
        			<input name="pass" type="password" size="20"><br><br>
       			 <input value="$langEnter" name="submit" type="submit"><br>
				$warning<br>
				<a href="modules/auth/lostpass.php">$lang_forgot_pass</a>
     			 </form>
     		</td>
     	</tr>
      </tbody>
      </table>
      <br>
      <table >
      <thead>
      	<tr>
      		<th> Σύνδεση Χρήστη </th>
      	</tr>
      </thead>
      <tbody>
      	<tr class="odd">
      		<td>
      			<form action="index.php" method="post">
      		  $langUserName <br>
        			<input  name="uname" size="20"><br>
       			 $langPass <br>
        			<input name="pass" type="password" size="20"><br><br>
       			 <input value="$langEnter" name="submit" type="submit"><br>
				$warning<br>
				<a href="modules/auth/lostpass.php">$lang_forgot_pass</a>
     			 </form>
     		</td>
     	</tr>
      </tbody>
      </table>
<br><br>
</div>

<div id="box2">
<br>
 <table >
      <thead>
      	<tr>
      		<th> Σύνδεση Χρήστη </th>
      	</tr>
      </thead>
      <tbody>
      	<tr class="odd">
      		<td>
      			<form action="index.php" method="post">
      		  $langUserName <br>
        			<input  name="uname" size="20"><br>
       			 $langPass <br>
        			<input name="pass" type="password" size="20"><br><br>
       			 <input value="$langEnter" name="submit" type="submit"><br>
				$warning<br>
				<a href="modules/auth/lostpass.php">$lang_forgot_pass</a>
     			 </form>
     		</td>
     	</tr>
      </tbody>
      </table>
      <br>
      <table>
      <thead>
      	<tr>
      		<th> Σύνδεση Χρήστη </th>
      	</tr>
      </thead>
      <tbody>
      	<tr class="odd">
      		<td>
      			<form action="index.php" method="post">
      		  $langUserName <br>
        			<input  name="uname" size="20"><br>
       			 $langPass <br>
        			<input name="pass" type="password" size="20"><br><br>
       			 <input value="$langEnter" name="submit" type="submit"><br>
				$warning<br>
				<a href="modules/auth/lostpass.php">$lang_forgot_pass</a>
     			 </form>
     		</td>
     	</tr>
      </tbody>
      </table>
      <br>
      <table >
      <thead>
      	<tr>
      		<th> Σύνδεση Χρήστη </th>
      	</tr>
      </thead>
      <tbody>
      	<tr class="odd">
      		<td>
      			<form action="index.php" method="post">
      		  $langUserName <br>
        			<input  name="uname" size="20"><br>
       			 $langPass <br>
        			<input name="pass" type="password" size="20"><br><br>
       			 <input value="$langEnter" name="submit" type="submit"><br>
				$warning<br>
				<a href="modules/auth/lostpass.php">$lang_forgot_pass</a>
     			 </form>
     		</td>
     	</tr>
      </tbody>
      </table>

</div>



lCont;
?>