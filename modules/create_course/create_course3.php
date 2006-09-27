<?

/**=============================================================================
       	GUnet e-Class 2.0 
        E-learning and Course Management Program  
================================================================================
       	Copyright(c) 2003-2006  Greek Universities Network - GUnet
        Á full copyright notice can be read in "/info/copyright.txt".
        
       	Authors:    Costas Tsibanis <k.tsibanis@noc.uoa.gr>
        	    	Yannis Exidaridis <jexi@noc.uoa.gr> 
      		    	Alexandros Diamantidis <adia@noc.uoa.gr> 

        For a full list of contributors, see "credits.txt".  
     
        This program is a free software under the terms of the GNU 
        (General Public License) as published by the Free Software 
        Foundation. See the GNU License for more details. 
        The full license can be read in "license.txt".
     
       	Contact address: GUnet Asynchronous Teleteaching Group, 
        Network Operations Center, University of Athens, 
        Panepistimiopolis Ilissia, 15784, Athens, Greece
        eMail: eclassadmin@gunet.gr
==============================================================================*/

/*===========================================================================
	create_course3.php
	@last update: 18-07-2006 by Sakis Agorastos
	@authors list: Agorastos Sakis <th_agorastos@hotmail.com>
==============================================================================        
        @Description: 3rd/final step for the Create New Course Wizard

    The script transfers data from the 1st and 2nd step of the wizard in 
    hidden input tags.
        
 	The script requires some fields to be filled-in, thus it checks the
 	validity of the entries by javascripts.
 	
 	After gathering all required data, the script createsthe new course
 	folders, its database and fills it with data.
 	
 	This is the script where all users should insert/modify their MySQL
 	queries for all the new courses.
 	
 	If $GET[finish_create_course] isset then the script creates the new
 	course, otherwise it shows the 3rd step form.
==============================================================================*/


$require_login = TRUE;
$require_prof = TRUE;

$langFiles = array('create_course', 'opencours');

$require_help = TRUE;
$helpTopic = 'CreateCourse';


$local_head = "<script language=\"javascript\">
function previous_step()
{
    document.location.href = \"./create_course2.php\";
}

function first_step()
{
    alert(\"\");
    document.location.href = \"./create_course.php\";
}
</script>";


//ektypwnei ena <td> </td> me hyperlink pros to help me vash kapoio $topic
function help ($topic)
{
    @$tool_content .= "
    <td valign=\"middle\">
        <a href=\"../help/help.php?topic=$topic\" onclick=\"window.open('../help/help.php?topic=$topic','help','toolbar=no,location=no,directories=no,status=yes,menubar=no,scrollbars=yes,resizable=yes,width=400,height=500,left=300,top=10'); return false;\"><img src=\"../../template/classic/img/help.gif\" border=\"0\"></a></td>";
}


include '../../include/baseTheme.php';

$nameTools = $langCreateCourse;

$tool_content = "";

// ------------------------------------------------------------------
// ------------------------ form ------------------------------------
// ------------------------------------------------------------------

if(!isset($_GET["finish_create_course"])) {
    $tool_content .= "
        <table bgcolor=\"$color1\" border=\"2\">
            <tr valign=\"top\" align=\"middle\">
                <td colspan=\"3\" valign=\"middle\" align=\"center\">
                    <font face=\"arial, helvetica\" size=\"4\" color=\"gray\">$langCreateCourseStep&nbsp;1&nbsp;$langCreateCourseStep2&nbsp;3</font>
                </td>
            </tr>
            <tr valign=\"top\">
                <td colspan=\"5\" valign=\"middle\">
                    <font face=\"arial, helvetica\" size=\"3\"><b>$langCreateCourseStep3Title</b></font>
                </td>
            </tr>
            


<form method=\"post\" action=\"$_SERVER[PHP_SELF]?finish_create_course\">

<!-- S T E P  1   [form data] -->
<input type=\"hidden\" name=\"intitule\" value=\"$intitule\">
<input type=\"hidden\" name=\"faculte\" value=\"$faculte\">
<input type=\"hidden\" name=\"titulaires\" value=\"$titulaires\">
<input type=\"hidden\" name=\"type\" value=\"$type\">

<!-- S T E P  2   [form data] -->
<input type=\"hidden\" name=\"description\" value=\"$description\">
<input type=\"hidden\" name=\"course_addon\" value=\"$course_addon\">
<input type=\"hidden\" name=\"course_keywords\" value=\"$course_keywords\">


    <!-- S T E P  3   [start] -->

    <tr>
    <td colspan=\"4\">
    <table bgcolor=\"$color1\" border=\"2\">
    <tr>
    <td valign=\"top\" align=\"right\">
    <font face=\"arial, helvetica\" size=\"2\"><b>$langAccess</b></font>
    </td>
    <td valign=\"top\">
    <font face=\"arial, helvetica\" size=\"2\">
    <fieldset>
    <legend>$langAvailableTypes</legend>
    <p>
    <input name=\"formvisible\" type=\"radio\" value=\"2\" checked=\"checked\" />$langPublic<br />
    <input name=\"formvisible\" type=\"radio\" value=\"1\" />$langPrivOpen<br />
    <input name=\"formvisible\" type=\"radio\" value=\"0\" />$langPrivate</p>
    </fieldset>
		$langAccessType
    </font>
    </td>";
		
    help("CreateCourse_formvisible");
		
$tool_content .= "
          </tr>
      <tr>
      <td valign=\"top\" align=\"right\">
      <font face=\"arial, helvetica\" size=\"2\"><b>$langModules</b></font>
      </td>
      <td valign=\"top\">
      <font face=\"arial, helvetica\" size=\"2\">
      <table border=\"1\">
      <tr>
      <td><input name=\"subsystems[]\" type=\"checkbox\" value=\"1\" checked=\"checked\" />$langAgenda</td>
      <td><input name=\"subsystems[]\" type=\"checkbox\" value=\"2\" checked=\"checked\" />$langLinks</td>
      <td><input name=\"subsystems[]\" type=\"checkbox\" value=\"3\" checked=\"checked\" />$langDoc</td>
      <td><input name=\"subsystems[]\" type=\"checkbox\" value=\"4\" checked=\"checked\" />$langVideo</td>
      </tr>
      <tr>
      <td><input type=\"checkbox\" name=\"subsystems[]\" value=\"5\" />$langWorks</td>
      <td><input type=\"checkbox\" name=\"subsystems[]\" value=\"6\" />$langVideoLinks</td>
      <td><input type=\"checkbox\" name=\"subsystems[]\" value=\"7\" />$langAnnouncements</td>
      <td><input type=\"checkbox\" name=\"subsystems[]\" value=\"9\" />$langForums</td>
      </tr>
      <tr>
      <td><input type=\"checkbox\" name=\"subsystems[]\" value=\"10\" />$langExercices</td>
      <td><input type=\"checkbox\" name=\"subsystems[]\" value=\"15\" />$langDropBox</td>
      <td><input type=\"checkbox\" name=\"subsystems[]\" value=\"16\" />$langGroups</td>
      <td><input type=\"checkbox\" name=\"subsystems[]\" value=\"19\" />$langConference</td>
      </tr>
      <tr>
      <td><input type=\"checkbox\" name=\"subsystems[]\" value=\"20\" />$langCourseDesc</td>
      <td><input type=\"checkbox\" name=\"subsystems[]\" value=\"21\" />$langQuestionnaire</td>
      <td><input type=\"checkbox\" name=\"subsystems[]\"  value=\"23\" checked=\"checked\" />$langLearnPath</td>
      <td></td>
      </tr>
      </table>
      <br>$langSubsystems
      </td>
      ";
			
       help("CreateCourse_subsystems");
			 
		$tool_content .= "</td></tr>
    <tr><td align=\"right\"><font face=\"arial, helvetica\" size=\"2\"><b>$langLn:</b></font></td>
    <td>
<select name=\"languageCourse\">";

$dirname = "../lang/";
if($dirname[strlen($dirname)-1]!='/')
    $dirname.='/';
$handle=opendir($dirname);
while ($entries = readdir($handle)) {
    if ($entries=='.'||$entries=='..' || $entries=='CVS')
        continue;
    if (is_dir($dirname.$entries)) {
				$tool_content .= "<option value=\"$entries\"";
        if ($entries == $language)
            $tool_content .= " selected ";
        $tool_content .= ">";
        if (!empty($langNameOfLang[$entries]) && $langNameOfLang[$entries]!="" && $langNameOfLang[$entries]!=$entries)
        $tool_content .= "$langNameOfLang[$entries] - ";
        $tool_content .= "$entries
        </option>";
    }
}
closedir($handle);

$tool_content .= "</select><br><font face=\"arial, helvetica\" size=\"2\">$langLanguageTip</font></td>";

help("CreateCourse_lang");

$tool_content .= "</tr>
    <tr><td>&nbsp;</td>
        <td align=\"center\">
            <input type=\"Submit\" name=\"submit\" value=\"$langFinalize\">
        </td>
        <td align=\"right\">
            &nbsp;
        </td>
    </tr>
    </table>
    </td>
    </tr>

</table>
    </td>
    </tr>
</form>";

}   // end of if.. submit


// create the course and the course database

else {

    //h metavlhth faculte periexei to fac_id kai to onoma tou tmhmatos xwrismena me dyo dashes
    //to $facid pairnei timh apo thn $faculte
    list($facid, $facname) = split("--", $faculte);
    //to $faculte ksanapairnei thn timh mono tou onomatos tou tmhmatos gia logous compability
    $faculte = $facname;

    $repertoire = new_code(find_faculty_by_name($faculte));
    $language=$languageCourse;
    @include("../lang/$language/create_course.inc.php");
    if(empty($intitule) OR empty($repertoire)) {
        $tool_content .= "<tr bgcolor=\"$color2\" height=\"400\">
        <td bgcolor=\"$color2\" colspan=\"2\" valign=\"top\">
            <br>
            <font face=\"arial, helvetica\" size=\"2\">
            $langEmpty
            </font>
        </td>
    </tr>";
    } else {	// if all form fields fulfilled
        // replace lower case letters by upper case in code_cours
        $repertoire=strtoupper($repertoire);
        $faculte_lower=strtolower($faculte);

        //remove space in code_cours
        $repertoire = str_replace (" ", "", $repertoire);
        $repertoire_lower=strtolower($repertoire);

        $dbList = mysql_list_dbs();
        $cnt = 0;
        $dbNumber = mysql_num_rows($dbList);
        while ($cnt < $dbNumber) {
            $dbCode = mysql_db_name($dbList, $cnt);
            if ($dbCode == $repertoire) {
            $tool_content .= "<tr bgcolor=\"$color2\" height=\"400\">
            <td colspan=\"2\" valign=\"top\">
            <font face=\"arial, helvetica\" size=\"2\">
                $langCodeTaken.
                <br>
                <p>&nbsp;</p>
            </td></tr></table>";
            exit();
            }			// end if ($dbCode == $repertoire)
            $cnt++;
        }				// end while ($cnt < $dbNumbert)

        // If code cours free THEN create DB and fill with all stuff
        if (mysql_version())
            $cdb=mysql_query("CREATE DATABASE `$repertoire` DEFAULT CHARSET greek");
        else
            $cdb=mysql_query("CREATE DATABASE `$repertoire`");
        $code=$repertoire;

// create phpbb 1.4 tables

// checking if the mysql version is > 4.1
if (mysql_version()) {

	mysql_query("CREATE TABLE catagories (
		    cat_id int(10) NOT NULL auto_increment,
		    cat_title varchar(100),
		    cat_order varchar(10),
		    PRIMARY KEY (cat_id))
		    TYPE=MyISAM DEFAULT CHARSET=greek");

	// Create a hidden category for group forums
	mysql_query("INSERT INTO catagories VALUES (1,'$langCatagoryGroup',NULL)");

	// Create an example category
	mysql_query("INSERT INTO catagories VALUES (2,'$langCatagoryMain',NULL)");

	mysql_query("CREATE TABLE forums (
		 forum_id int(10) NOT NULL auto_increment,
		 forum_name varchar(150),
		 forum_desc text,
		 forum_access int(10) DEFAULT '1',
		 forum_moderator int(10),
		 forum_topics int(10) DEFAULT '0' NOT NULL,
		 forum_posts int(10) DEFAULT '0' NOT NULL,
		 forum_last_post_id int(10) DEFAULT '0' NOT NULL,
		 cat_id int(10),
		 forum_type int(10) DEFAULT '0',
		 PRIMARY KEY (forum_id),
		 KEY forum_last_post_id (forum_last_post_id))
		TYPE=MyISAM DEFAULT CHARSET=greek");

	mysql_query("INSERT INTO forums VALUES (1,'$langTestForum','$langDelAdmin',2,1,1,1,1,2,0)");

	mysql_query("CREATE TABLE posts (
	    post_id int(10) NOT NULL auto_increment,
	    topic_id int(10) DEFAULT '0' NOT NULL,
	    forum_id int(10) DEFAULT '0' NOT NULL,
	    poster_id int(10) DEFAULT '0' NOT NULL,
	    post_time varchar(20),
	    poster_ip varchar(16),
	    nom varchar(30),
	    prenom varchar(30),
	    PRIMARY KEY (post_id),
	    KEY post_id (post_id),
	    KEY forum_id (forum_id),
	    KEY topic_id (topic_id),
	    KEY poster_id (poster_id))
	    TYPE=MyISAM DEFAULT CHARSET=greek");

	mysql_query("INSERT INTO posts VALUES (1,1,1,1,NOW(),'130.104.1.1','$nom','$prenom')");

	    mysql_query("CREATE TABLE posts_text (
                post_id int(10) DEFAULT '0' NOT NULL,
                post_text text,
                PRIMARY KEY (post_id))
		TYPE=MyISAM DEFAULT CHARSET=greek");

	mysql_query("INSERT INTO posts_text VALUES ('1','$langMessage')");

	mysql_query("CREATE TABLE topics (
               topic_id int(10) NOT NULL auto_increment,
               topic_title varchar(100),
               topic_poster int(10),
               topic_time varchar(20),
               topic_views int(10) DEFAULT '0' NOT NULL,
               topic_replies int(10) DEFAULT '0' NOT NULL,
               topic_last_post_id int(10) DEFAULT '0' NOT NULL,
               forum_id int(10) DEFAULT '0' NOT NULL,
               topic_status int(10) DEFAULT '0' NOT NULL,
               topic_notify int(2) DEFAULT '0',
	    nom varchar(30),
	    prenom varchar(30),
               PRIMARY KEY (topic_id),
               KEY topic_id (topic_id),
               KEY forum_id (forum_id),
               KEY topic_last_post_id (topic_last_post_id))
		TYPE=MyISAM DEFAULT CHARSET=greek");

	mysql_query("INSERT INTO topics VALUES (1,'$langExMessage',-1,'2001-09-18 20:25',1,'',1,1,'0','1', '$nom', '$prenom')");

	mysql_query("CREATE TABLE users (
               user_id int(10) NOT NULL auto_increment,
               username varchar(40) NOT NULL,
               user_regdate varchar(20) NOT NULL,
               user_password varchar(32) NOT NULL,
               user_email varchar(50),
               user_icq varchar(15),
               user_website varchar(100),
               user_occ varchar(100),
               user_from varchar(100),
               user_intrest varchar(150),
               user_sig varchar(255),
               user_viewemail tinyint(2),
               user_theme int(10),
               user_aim varchar(18),
               user_yim varchar(25),
               user_msnm varchar(25),
               user_posts int(10) DEFAULT '0',
               user_attachsig int(2) DEFAULT '0',
               user_desmile int(2) DEFAULT '0',
               user_html int(2) DEFAULT '0',
               user_bbcode int(2) DEFAULT '0',
               user_rank int(10) DEFAULT '0',
               user_level int(10) DEFAULT '1',
               user_lang varchar(255),
               user_actkey varchar(32),
               user_newpasswd varchar(32),
               PRIMARY KEY (user_id))
        TYPE=MyISAM DEFAULT CHARSET=greek");

    mysql_query("INSERT INTO users VALUES (
               '1',
               '$nom $prenom',
               NOW(),
               'password',
               '$email',
               NULL,
               NULL,
               NULL,
               NULL,
               NULL,
               NULL,
               NULL,
               NULL,
               NULL,
               NULL,
               NULL,
               '0',
               '0',
               '0',
               '0',
               '0',
               '0',
               '1',
               NULL,
               NULL,
               NULL
               )");


mysql_query("INSERT INTO users VALUES (
               '-1',
               '$langAnonymous',
               NOW(),
               'password',
               '',
               NULL,
               NULL,
               NULL,
               NULL,
               NULL,
               NULL,
               NULL,
               NULL,
               NULL,
               NULL,
               NULL,
               '0',
               '0',
               '0',
               '0',
               '0',
               '0',
               '1',
               NULL,
               NULL,
               NULL
               )");
} else {

    mysql_query("
CREATE TABLE catagories (
    cat_id int(10) NOT NULL auto_increment,
    cat_title varchar(100),
    cat_order varchar(10),
    PRIMARY KEY (cat_id))
    TYPE=MyISAM");

// Create a hidden catagory for group forums
mysql_query("INSERT INTO catagories VALUES (1,'$langCatagoryGroup',NULL)");

// Create an example catagory
mysql_query("INSERT INTO catagories VALUES (2,'$langCatagoryMain',NULL)");

    mysql_query("CREATE TABLE forums (
                 forum_id int(10) NOT NULL auto_increment,
                 forum_name varchar(150),
                 forum_desc text,
                 forum_access int(10) DEFAULT '1',
                 forum_moderator int(10),
                 forum_topics int(10) DEFAULT '0' NOT NULL,
                 forum_posts int(10) DEFAULT '0' NOT NULL,
                 forum_last_post_id int(10) DEFAULT '0' NOT NULL,
                 cat_id int(10),
                 forum_type int(10) DEFAULT '0',
                 PRIMARY KEY (forum_id),
                 KEY forum_last_post_id (forum_last_post_id))
        TYPE=MyISAM");

mysql_query("INSERT INTO forums VALUES (1,'$langTestForum','$langDelAdmin',2,1,1,1,1,2,0)");

    mysql_query("CREATE TABLE posts (
    post_id int(10) NOT NULL auto_increment,
    topic_id int(10) DEFAULT '0' NOT NULL,
    forum_id int(10) DEFAULT '0' NOT NULL,
    poster_id int(10) DEFAULT '0' NOT NULL,
    post_time varchar(20),
    poster_ip varchar(16),
    nom varchar(30),
    prenom varchar(30),
    PRIMARY KEY (post_id),
    KEY post_id (post_id),
    KEY forum_id (forum_id),
    KEY topic_id (topic_id),
    KEY poster_id (poster_id))
    TYPE=MyISAM");

mysql_query("INSERT INTO posts VALUES (1,1,1,1,NOW(),'130.104.1.1','$nom','$prenom')");

    mysql_query("CREATE TABLE posts_text (
                post_id int(10) DEFAULT '0' NOT NULL,
                post_text text,
                PRIMARY KEY (post_id))
        TYPE=MyISAM");

mysql_query("INSERT INTO posts_text VALUES ('1','$langMessage')");

    mysql_query("CREATE TABLE topics (
               topic_id int(10) NOT NULL auto_increment,
               topic_title varchar(100),
               topic_poster int(10),
               topic_time varchar(20),
               topic_views int(10) DEFAULT '0' NOT NULL,
               topic_replies int(10) DEFAULT '0' NOT NULL,
               topic_last_post_id int(10) DEFAULT '0' NOT NULL,
               forum_id int(10) DEFAULT '0' NOT NULL,
               topic_status int(10) DEFAULT '0' NOT NULL,
               topic_notify int(2) DEFAULT '0',
	    nom varchar(30),
	    prenom varchar(30),
               PRIMARY KEY (topic_id),
               KEY topic_id (topic_id),
               KEY forum_id (forum_id),
               KEY topic_last_post_id (topic_last_post_id))
        TYPE=MyISAM");


mysql_query("INSERT INTO topics VALUES (1,'$langExMessage',-1,'2001-09-18 20:25',1,'',1,1,'0','1', '$nom', '$prenom')");

    mysql_query("CREATE TABLE users (
               user_id int(10) NOT NULL auto_increment,
               username varchar(40) NOT NULL,
               user_regdate varchar(20) NOT NULL,
               user_password varchar(32) NOT NULL,
               user_email varchar(50),
               user_icq varchar(15),
               user_website varchar(100),
               user_occ varchar(100),
               user_from varchar(100),
               user_intrest varchar(150),
               user_sig varchar(255),
               user_viewemail tinyint(2),
               user_theme int(10),
               user_aim varchar(18),
               user_yim varchar(25),
               user_msnm varchar(25),
               user_posts int(10) DEFAULT '0',
               user_attachsig int(2) DEFAULT '0',
               user_desmile int(2) DEFAULT '0',
               user_html int(2) DEFAULT '0',
               user_bbcode int(2) DEFAULT '0',
               user_rank int(10) DEFAULT '0',
               user_level int(10) DEFAULT '1',
               user_lang varchar(255),
               user_actkey varchar(32),
               user_newpasswd varchar(32),
               PRIMARY KEY (user_id))
        TYPE=MyISAM");

    mysql_query("INSERT INTO users VALUES (
               '1',
               '$nom $prenom',
               NOW(),
               'password',
               '$email',
               NULL,
               NULL,
               NULL,
               NULL,
               NULL,
               NULL,
               NULL,
               NULL,
               NULL,
               NULL,
               NULL,
               '0',
               '0',
               '0',
               '0',
               '0',
               '0',
               '1',
               NULL,
               NULL,
               NULL
               )");


mysql_query("INSERT INTO users VALUES (
               '-1',
               '$langAnonymous',
               NOW(),
               'password',
               '',
               NULL,
               NULL,
               NULL,
               NULL,
               NULL,
               NULL,
               NULL,
               NULL,
               NULL,
               NULL,
               NULL,
               '0',
               '0',
               '0',
               '0',
               '0',
               '0',
               '1',
               NULL,
               NULL,
               NULL
               )");
}


###############################################################################
####### CREATE AND POPULATE OTHER TABLES ######################################
###############################################################################

    mysql_select_db($repertoire);

####################### EXERCICES ###########################################

// Scoring table for grouped True/False

if (mysql_version()) {
mysql_query("CREATE TABLE mc_scoring
                (id INT  not null AUTO_INCREMENT, choice_count INT  not null , false_count INT  not null , score INT  not null  ,
                PRIMARY KEY (id, choice_count, false_count))
            TYPE=MyISAM DEFAULT CHARSET=greek");

} else {
mysql_query("CREATE TABLE mc_scoring
                (id INT  not null AUTO_INCREMENT, choice_count INT  not null , false_count INT  not null , score INT  not null  ,
                PRIMARY KEY (id, choice_count, false_count))
            TYPE=MyISAM");
    }
    $maxChoiceCount=8;
    $weight=20;
    for ($choiceCount=1;$choiceCount<=$maxChoiceCount;$choiceCount++){ //count for row

        for ($falseCount=0;$falseCount<=$choiceCount;$falseCount++){ //count for colomn

            $defaultScore = DefaultScoring($choiceCount,$falseCount,$weight);

            mysql_query("INSERT INTO mc_scoring (id, choice_count, false_count, score)
                        VALUES ('', '$choiceCount', '$falseCount', '$defaultScore')");
            } 	// for
    } 	// for


if (mysql_version())  {
// EXERCICES
mysql_query("CREATE TABLE exercices (
        id tinyint(4) NOT NULL auto_increment,
        titre varchar(250) default NULL,
        description text,
      type tinyint(4) unsigned NOT NULL default '1',
      StartDate datetime default NULL,
      EndDate datetime default NULL,
      TimeConstrain int(11) default '0',
      AttemptsAllowed int(11) default '0',
      random smallint(6) NOT NULL default '0',
        active tinyint(4) default NULL,
        PRIMARY KEY  (id))
        TYPE=MyISAM DEFAULT CHARSET=greek");

    mysql_query("INSERT INTO exercices VALUES ( '1', '$langExerciceEx', '$langAntique', '1', NULL, NULL, '0', '0', '0', NULL)");

    mysql_query("CREATE TABLE exercise_user_record (
      eurid int(11) NOT NULL auto_increment,
      eid tinyint(4) NOT NULL default '0',
      uid mediumint(8) NOT NULL default '0',
      RecordStartDate datetime NOT NULL default '0000-00-00 00:00:00',
      RecordEndDate datetime NOT NULL default '0000-00-00 00:00:00',
      TotalScore int(11) NOT NULL default '0',
      TotalWeighting int(11) default '0',
      attempt int(11) NOT NULL default '0',
      PRIMARY KEY  (eurid))
      TYPE=MyISAM DEFAULT CHARSET=greek");

// QUESTIONS
mysql_query("CREATE TABLE questions (
        id int(11) NOT NULL auto_increment,
        question text,
        description text,
        ponderation int(11) default NULL,
        q_position int(11) default 1,
        type int(11) default 2,
        PRIMARY KEY  (id))
        TYPE=MyISAM DEFAULT CHARSET=greek");

mysql_query("INSERT INTO questions VALUES ( '1', '$langSocraticIrony', '$langManyAnswers', NULL, '1', '1')");


// REPONSES
mysql_query("CREATE TABLE reponses (
        id int(11) NOT NULL default '0',
        question_id int(11) NOT NULL default '0',
        reponse text,
        correct int(11) default NULL,
        comment text,
                ponderation smallint(5),
                r_position int(11) default NULL,
        PRIMARY KEY  (id, question_id))
        TYPE=MyISAM DEFAULT CHARSET=greek");

mysql_query("INSERT INTO reponses VALUES ( '1', '1', '$langRidiculise', '0', '$langNoPsychology', '-5', '1')");

mysql_query("INSERT INTO reponses VALUES ( '2', '1', '$langAdmitError', '0', '$langNoSeduction', '-5', '2')");

mysql_query("INSERT INTO reponses VALUES ( '3', '1', '$langForce', '1', '$langIndeed', '-5', '3')");

mysql_query("INSERT INTO reponses VALUES ( '4', '1', '$langContradiction', '1', '$langNotFalse', '-5', '4')");

// EXERCICE_QUESTION
mysql_query("CREATE TABLE exercice_question (
                question_id int(11) NOT NULL default '0',
                exercice_id int(11) NOT NULL default '0',
                PRIMARY KEY  (question_id,exercice_id))
        TYPE=MyISAM DEFAULT CHARSET=greek");

mysql_query("INSERT INTO exercice_question VALUES ( '1', '1')");

} else {
// EXERCICES
mysql_query("CREATE TABLE exercices (
        id tinyint(4) NOT NULL auto_increment,
        titre varchar(250) default NULL,
        description text,
      type tinyint(4) unsigned NOT NULL default '1',
      StartDate datetime default NULL,
      EndDate datetime default NULL,
      TimeConstrain int(11) default '0',
      AttemptsAllowed int(11) default '0',
      random smallint(6) NOT NULL default '0',
        active tinyint(4) default NULL,
        PRIMARY KEY  (id))
        TYPE=MyISAM");

    mysql_query("INSERT INTO exercices VALUES ( '1', '$langExerciceEx', '$langAntique', '1', NULL, NULL, '0', '0', '0', NULL)");

    mysql_query("CREATE TABLE exercise_user_record (
      eurid int(11) NOT NULL auto_increment,
      eid tinyint(4) NOT NULL default '0',
      uid mediumint(8) NOT NULL default '0',
      RecordStartDate datetime NOT NULL default '0000-00-00 00:00:00',
      RecordEndDate datetime NOT NULL default '0000-00-00 00:00:00',
      TotalScore int(11) NOT NULL default '0',
      TotalWeighting int(11) default '0',
      attempt int(11) NOT NULL default '0',
      PRIMARY KEY  (eurid))
      TYPE=MyISAM");

// QUESTIONS
mysql_query("CREATE TABLE questions (
        id int(11) NOT NULL auto_increment,
        question text,
        description text,
        ponderation int(11) default NULL,
        q_position int(11) default 1,
        type int(11) default 2,
        PRIMARY KEY  (id))
        TYPE=MyISAM");

mysql_query("INSERT INTO questions VALUES ( '1', '$langSocraticIrony', '$langManyAnswers', NULL, '1', '1')");


// REPONSES
mysql_query("CREATE TABLE reponses (
        id int(11) NOT NULL default '0',
        question_id int(11) NOT NULL default '0',
        reponse text,
        correct int(11) default NULL,
        comment text,
        ponderation smallint(5),
        r_position int(11) default NULL,
        PRIMARY KEY  (id, question_id))
        TYPE=MyISAM");

mysql_query("INSERT INTO reponses VALUES ( '1', '1', '$langRidiculise', '0', '$langNoPsychology', '-5', '1')");

mysql_query("INSERT INTO reponses VALUES ( '2', '1', '$langAdmitError', '0', '$langNoSeduction', '-5', '2')");

mysql_query("INSERT INTO reponses VALUES ( '3', '1', '$langForce', '1', '$langIndeed', '-5', '3')");

mysql_query("INSERT INTO reponses VALUES ( '4', '1', '$langContradiction', '1', '$langNotFalse', '-5', '4')");

// EXERCICE_QUESTION
mysql_query("CREATE TABLE exercice_question (
                question_id int(11) NOT NULL default '0',
                exercice_id int(11) NOT NULL default '0',
                PRIMARY KEY  (question_id,exercice_id))
        TYPE=MyISAM");

mysql_query("INSERT INTO exercice_question VALUES ( '1', '1')");


}



#######################COURSE_DESCRIPTION ################################

if (mysql_version()) {

mysql_query("CREATE TABLE `course_description`
(
    `id` TINYINT UNSIGNED DEFAULT '0' NOT NULL,
    `title` VARCHAR(255),
    `content` TEXT,
    `upDate` DATETIME NOT NULL,
    UNIQUE (`id`)
)
TYPE=MyISAM DEFAULT CHARSET=greek");

} else {

mysql_query("CREATE TABLE `course_description`
(
    `id` TINYINT UNSIGNED DEFAULT '0' NOT NULL,
    `title` VARCHAR(255),
    `content` TEXT,
    `upDate` DATETIME NOT NULL,
    UNIQUE (`id`)
)
TYPE=MyISAM");

}


#######################ACCUEIL ###########################################


    //arxikopoihsh tou array gia ta checkboxes
    for ($i=0; $i<=50; $i++)
    {
        $sbsystems[$i] = 0;
    }

    //allagh timwn sto array analoga me to poio checkbox exei epilegei
    foreach ( $subsystems as $sb )
    {
        $sbsystems[$sb] = 1;
    }




if (mysql_version()) {

mysql_query("CREATE TABLE accueil (
               id int(11) NOT NULL auto_increment,
               rubrique varchar(100), lien varchar(255),
               image varchar(100),
               visible tinyint(4),
               admin varchar(200),
               address varchar(120),
               define_var varchar(50),
               PRIMARY KEY (id))
        TYPE=MyISAM DEFAULT CHARSET=greek");
} else {

mysql_query("CREATE TABLE accueil (
               id int(11) NOT NULL auto_increment,
               rubrique varchar(100), lien varchar(255),
               image varchar(100),
               visible tinyint(4),
               admin varchar(200),
               address varchar(120),
               define_var varchar(50),
               PRIMARY KEY (id))
        TYPE=MyISAM");
}

    // Content accueil (homepage) Table
    mysql_query("INSERT INTO accueil VALUES (
                '1',
                '$langAgenda',
                '../../modules/agenda/agenda.php',
                'calendar',
                '".$sbsystems[1]."',
                '0',
                '../../../images/pastillegris.png',
                'MODULE_ID_AGENDA')");

    mysql_query("INSERT INTO accueil VALUES (
               '2',
               '$langLinks',
               '../../modules/link/link.php',
               'links',
               '".$sbsystems[2]."',
               '0',
               '../../../images/pastillegris.png',
               'MODULE_ID_LINKS'
               )");

    mysql_query("INSERT INTO accueil VALUES (
               '3',
               '$langDoc',
               '../../modules/document/document.php',
               'docs',
               '".$sbsystems[3]."',
               '0',
               '../../../images/pastillegris.png',
               'MODULE_ID_DOCS'
               )");
    //den yparxei akomh MODULE_ID_ gia to module VIDEO opote prepei na symplhrwthei
    mysql_query("INSERT INTO accueil VALUES (
               '4',
               '$langVideo',
               '../../modules/video/video.php',
               'videos',
               '".$sbsystems[4]."',
               '0',
               '../../../images/pastillegris.png',
               'MODULE_ID_VIDEO'
               )");

           mysql_query("INSERT INTO accueil VALUES (
               '5',
               '$langWorks',
               '../../modules/work/work.php',
               'assignments',
               '".$sbsystems[5]."',
               '0',
               '../../../images/pastillegris.png',
               'MODULE_ID_ASSIGN'
               )");

    mysql_query("INSERT INTO accueil VALUES (
               '7',
               '$langAnnouncements',
               '../../modules/announcements/announcements.php',
               'announcements',
               '".$sbsystems[7]."',
               '0',
               '../../../images/pastillegris.png',
               'MODULE_ID_ANNOUNCE'
               )");


    mysql_query("INSERT INTO accueil VALUES (
               '9',
               '$langForums',
               '../../modules/phpbb/index.php',
               'forum',
               '".$sbsystems[9]."',
               '0',
               '../../../images/pastillegris.png',
               'MODULE_ID_FORUM'
               )");

    mysql_query("INSERT INTO accueil VALUES (
               '10',
               '$langExercices',
               '../../modules/exercice/exercice.php',
               'exercise',
               '".$sbsystems[10]."',
               '0',
               '../../../images/pastillegris.png',
               'MODULE_ID_EXERCISE'
               )");

    mysql_query("INSERT INTO accueil VALUES (
        '15',
        '$langGroups',
        '../../modules/group/group.php',
        'groups',
        '".$sbsystems[15]."',
        '0',
        '../../../images/pastillegris.png',
        'MODULE_ID_GROUPS'
        )");

    mysql_query("INSERT INTO accueil VALUES (
        '16',
        '$langDropBox',
        '../../modules/dropbox/index.php',
        'dropbox',
        '".$sbsystems[16]."',
        '0',
        '../../../images/pastillegris.png',
        'MODULE_ID_DROPBOX'
        )");
    mysql_query("INSERT INTO accueil VALUES (
                '19',
                '$langConference',
                '../../modules/conference/conference.php',
                'conference',
                '".$sbsystems[19]."',
                '0',
                '../../../images/pastillegris.png',
                'MODULE_ID_CHAT'
                )");

    mysql_query("INSERT INTO accueil VALUES (
               '20',
               '$langCourseDesc',
               '../../modules/course_description/',
               'description',
               '".$sbsystems[20]."',
               '0',
               '../../../images/pastillegris.png',
               'MODULE_ID_DESCRIPTION'
               )");


    mysql_query("INSERT INTO accueil VALUES (
                '21',
                '$langQuestionnaire',
                '../../modules/questionnaire/questionnaire.php',
                'questionnaire',
                '".$sbsystems[21]."',
                '0',
                '../../../images/pastillegris.png',
                'MODULE_ID_QUESTIONNAIRE'
                )");

    mysql_query("INSERT INTO accueil VALUES (
               '23',
               '$langLearnPath',
               '../../modules/learnPath/learningPathList.php',
               'lp',
               '".$sbsystems[23]."',
               '0',
               '../../../images/pastillegris.png',
               'MODULE_ID_LP'
               )");

    mysql_query("INSERT INTO accueil VALUES (
               25,
               '$langToolManagement',
               '../../modules/course_tools/course_tools.php',
               'tooladmin',
               '0',
               '1',
               '../../../images/pastillegris.png',
               'MODULE_ID_TOOLADMIN'
               )");


// --------------------- prof only ------------------------------------------

        mysql_query("INSERT INTO accueil VALUES (
        '8',
        '$langUsers',
        '../../modules/user/user.php',
        'users',
        '0',
        '1',
        '',
        'MODULE_ID_USERS'
        )");

    mysql_query("INSERT INTO accueil VALUES (
               '11',
               '$langStatistics',
               '../../modules/stat/index2.php?table=stat_accueil&reset=0&period=jour',
               'stat',
               '".$sbsystems[11]."',
               '1',
               '',
               'MODULE_ID_STAT'
               )");

/*
    mysql_query("INSERT INTO accueil VALUES (
               '12',
               '$langAddPageHome',
               '../../modules/import/import.php?',
               'import',
               '".$sbsystems[12]."',
               '1',
               '',
               'MODULE_ID_IMPORT'
               )");

    mysql_query("INSERT INTO accueil VALUES (
               '13',
               '$langLinkSite',
               '../../modules/external_module/external_module.php?',
               'external',
               '".$sbsystems[13]."',
               '1',
               '',
               'MODULE_ID_EXTERNAL'
               )");

*/

    mysql_query("INSERT INTO accueil VALUES (
               '14',
               '$langModifyInfo',
               '../../modules/course_info/infocours.php?',
               'course_info',
               '".$sbsystems[14]."',
               '1',
               '',
               'MODULE_ID_COURSEINFO'
               )");

    mysql_query("INSERT INTO accueil VALUES (
                '24',
                '".$langUsage."',
                '../../modules/usage/usage.php',
                'usage',
                '".$sbsystems[24]."',
                '1',
                '../../../images/pastillegris.png',
                'MODULE_ID_USAGE')");



#################################### USAGE ################################
// no db version specific stuff
db_query("CREATE TABLE action_types (
            id int(11) NOT NULL auto_increment,
            name varchar(200),
            PRIMARY KEY (id))");
db_query("INSERT INTO action_types VALUES ('1', 'access')");
db_query("CREATE TABLE actions (
            id int(11) NOT NULL auto_increment,
            user_id int(11) NOT NULL,
            module_id int(11) NOT NULL,
            action_type_id int(11) NOT NULL,
            date_time DATETIME NOT NULL default '0000-00-00 00:00:00',
            duration int(11) NOT NULL default 900,
            PRIMARY KEY (id))");

db_query("CREATE TABLE actions_summary (
            id int(11) NOT NULL auto_increment,
            module_id int(11) NOT NULL,
            visits int(11) NOT NULL,
            start_date DATETIME NOT NULL default '0000-00-00 00:00:00',
            end_date DATETIME NOT NULL default '0000-00-00 00:00:00',
            duration int(11) NOT NULL,
            PRIMARY KEY (id))");

if (mysql_version())   {


#################################### AGENDA ################################
mysql_query("CREATE TABLE agenda (
    id int(11) NOT NULL auto_increment,
    titre varchar(200),
    contenu text,
    day date NOT NULL default '0000-00-00',
    hour time NOT NULL default '00:00:00',
    lasting varchar(20),
    PRIMARY KEY (id))
    TYPE=MyISAM DEFAULT CHARSET=greek");

############################# PAGES ###########################################

    mysql_query("CREATE TABLE pages (
               id int(11) NOT NULL auto_increment,
               url varchar(200),
               titre varchar(200),
               description text,
               PRIMARY KEY (id))
        TYPE=MyISAM DEFAULT CHARSET=greek");


############################# DOCUMENTS ###########################################

mysql_query ("CREATE TABLE document (id int(4) NOT NULL auto_increment,
    path varchar(255) NOT NULL,
    filename text,
    visibility char(1) DEFAULT 'v' NOT NULL,
    comment varchar(255),
    category text,
    title text,
    creator text,
    date datetime default NULL,
    date_modified datetime default NULL,
    subject text,
    description text,
    author text,
    format text,
    language text,
    copyrighted text,
    PRIMARY KEY (id))
    TYPE=MyISAM DEFAULT CHARSET=greek");

############################# VIDEO ###########################################
    mysql_query("CREATE TABLE video (
               id int(11) NOT NULL auto_increment,
               url varchar(200),
               titre varchar(200),
               description text,
               creator varchar(200),
               publisher varchar(200),
               date DATETIME,
               PRIMARY KEY (id))
        TYPE=MyISAM DEFAULT CHARSET=greek");

################################# VIDEO LINKS ################################

        mysql_query("CREATE TABLE videolinks (
               id int(11) NOT NULL auto_increment,
               url varchar(200),
               titre varchar(200),
               description text,
	       creator varchar(200),
	       publisher varchar(200),
	       date DATETIME,
               PRIMARY KEY (id))
        TYPE=MyISAM DEFAULT CHARSET=greek");


############################# WORKS ###########################################

mysql_query("
CREATE TABLE work (
    id int(11) NOT NULL auto_increment,
    url varchar(200),
    titre varchar(200),
    description varchar(250),
    auteurs varchar(200),
    active tinyint(1),
    accepted tinyint(1),
    PRIMARY KEY (id))
    TYPE=MyISAM DEFAULT CHARSET=greek");


mysql_query("CREATE TABLE work_student (
    work_id int(11) NOT NULL,
    uname varchar(30),
    PRIMARY KEY (work_id,uname))
    TYPE=MyISAM DEFAULT CHARSET=greek");

// new queries
db_query("CREATE TABLE `assignments` (
    `id` int(11) NOT NULL auto_increment,
    `title` varchar(200) NOT NULL default '',
    `description` text NOT NULL,
    `comments` text NOT NULL,
    `deadline` date NOT NULL default '0000-00-00',
    `submission_date` date NOT NULL default '0000-00-00',
    `active` char(1) NOT NULL default '1',
    `secret_directory` varchar(30) NOT NULL,
    `group_submissions` CHAR(1) DEFAULT '0' NOT NULL,
    UNIQUE KEY `id` (`id`))
    TYPE=MyISAM DEFAULT CHARSET=greek");


db_query("CREATE TABLE `assignment_submit` (
    `id` int(11) NOT NULL auto_increment,
    `uid` int(11) NOT NULL default '0',
    `assignment_id` int(11) NOT NULL default '0',
    `submission_date` date NOT NULL default '0000-00-00',
    `submission_ip` varchar(16) NOT NULL default '',
    `file_path` varchar(200) NOT NULL default '',
    `file_name` varchar(200) NOT NULL default '',
    `comments` text NOT NULL,
    `grade` varchar(50) NOT NULL default '',
    `grade_comments` text NOT NULL,
    `grade_submission_date` date NOT NULL default '0000-00-00',
    `grade_submission_ip` varchar(16) NOT NULL default '',
    `group_id` INT( 11 ) DEFAULT NULL,
    UNIQUE KEY `id` (`id`))
    TYPE=MyISAM DEFAULT CHARSET=greek");


############################## LIENS #############################################

    mysql_query("CREATE TABLE liens (
               id int(11) NOT NULL auto_increment,
               url varchar(150),
               titre varchar(150),
               description text,
           category int(4) default '0' NOT NULL,
           ordre mediumint(8) default '0' NOT NULL,
               PRIMARY KEY (id))
        TYPE=MyISAM DEFAULT CHARSET=greek");

    mysql_query("INSERT INTO liens VALUES (
               '1',
               'http://www.google.com',
               'Google',
               '$langGoogle','0','0'
               )");

    mysql_query("CREATE TABLE `link_categories` (
      `id` int(6) NOT NULL auto_increment,
      `categoryname` varchar(255) default NULL,
      `description` text,
      `ordre` mediumint(8) NOT NULL default '0',
      PRIMARY KEY (`id`))
    TYPE=MyISAM DEFAULT CHARSET=greek");

###################################### DROPBOX #####################################

    mysql_query("CREATE TABLE dropbox_file (
      id int(11) unsigned NOT NULL auto_increment,
      uploaderId int(11) unsigned NOT NULL default '0',
      filename varchar(250) NOT NULL default '',
      filesize int(11) unsigned NOT NULL default '0',
      title varchar(250) default '',
      description varchar(250) default '',
      author varchar(250) default '',
      uploadDate datetime NOT NULL default '0000-00-00 00:00:00',
      lastUploadDate datetime NOT NULL default '0000-00-00 00:00:00',
      PRIMARY KEY  (id),
      UNIQUE KEY UN_filename (filename))
    TYPE=MyISAM DEFAULT CHARSET=greek");


    mysql_query("CREATE TABLE dropbox_person (
      fileId int(11) unsigned NOT NULL default '0',
      personId int(11) unsigned NOT NULL default '0',
      PRIMARY KEY  (fileId,personId))
    TYPE=MyISAM DEFAULT CHARSET=greek");

    mysql_query("CREATE TABLE dropbox_post (
      fileId int(11) unsigned NOT NULL default '0',
      recipientId int(11) unsigned NOT NULL default '0',
      PRIMARY KEY  (fileId,recipientId))
    TYPE=MyISAM DEFAULT CHARSET=greek");

/*
############################## INTRODUCTION #######################################

    mysql_query("CREATE TABLE introduction (
               id int(11) NOT NULL default '1',
               texte_intro text,
               PRIMARY KEY (id))
        TYPE=MyISAM DEFAULT CHARSET=greek");

    mysql_query("INSERT INTO introduction SET texte_intro = '$course_intronote'");

############################## GROUPS ###########################################

*/
mysql_query("
CREATE TABLE student_group
(
    id int(11) NOT NULL auto_increment,
    name varchar(100) default NULL,
    description text,
    tutor int(11) default NULL,
    forumId int(11) default NULL,
    maxStudent int(11) NOT NULL default '0',
    secretDirectory varchar(30) NOT NULL default '0',
    PRIMARY KEY  (id)
)
TYPE=MyISAM DEFAULT CHARSET=greek");

mysql_query("CREATE TABLE user_group (
    id int(11) NOT NULL auto_increment,
    user int(11) NOT NULL default '0',
    team int(11) NOT NULL default '0',
    status int(11) NOT NULL default '0',
    role varchar(50) NOT NULL default '',
    PRIMARY KEY  (id)
    )
    TYPE=MyISAM DEFAULT CHARSET=greek");

mysql_query("CREATE TABLE group_properties (
    id tinyint(4) NOT NULL auto_increment,
    self_registration tinyint(4) default '1',
    private tinyint(4) default '0',
    forum tinyint(4) default '1',
    document tinyint(4) default '1',
    wiki tinyint(4) default '0',
    agenda tinyint(4) default '0',
    PRIMARY KEY  (id)
    )
    TYPE=MyISAM DEFAULT CHARSET=greek");

mysql_query("INSERT INTO group_properties
    (id, self_registration, private, forum, document, wiki, agenda)
    VALUES (NULL, '1', '0', '1', '1', '0', '0')");


####################STATISTIQUES ################################################

    mysql_query("CREATE TABLE stat_accueil (
               id int(11) NOT NULL auto_increment,
               request char(100) NOT NULL,
               host char(100) NOT NULL,
               address char(100) NOT NULL,
               agent char(100) NOT NULL,
               date datetime,
               referer char(200) NOT NULL,
               country char(50) NOT NULL,
               provider char(100) NOT NULL,
               os char(50) NOT NULL,
               wb char(50) NOT NULL,
               PRIMARY KEY (id),
               KEY id (id))
        TYPE=MyISAM DEFAULT CHARSET=greek");

#################### QUESTIONNAIRE ###############################################

    mysql_query("CREATE TABLE survey (
      sid bigint(14) NOT NULL auto_increment,
      creator_id mediumint(8) unsigned NOT NULL default '0',
      course_id varchar(20) NOT NULL default '0',
      name varchar(255) NOT NULL default '',
      creation_date datetime NOT NULL default '0000-00-00 00:00:00',
      start_date datetime NOT NULL default '0000-00-00 00:00:00',
      end_date datetime NOT NULL default '0000-00-00 00:00:00',
      type int(11) NOT NULL default '0',
      active int(11) NOT NULL default '0',
      PRIMARY KEY  (sid))
    TYPE=MyISAM DEFAULT CHARSET=greek");

    mysql_query("CREATE TABLE survey_answer (
        aid bigint(12) NOT NULL default '0',
      creator_id mediumint(8) unsigned NOT NULL default '0',
      sid bigint(12) NOT NULL default '0',
      date datetime NOT NULL default '0000-00-00 00:00:00',
      PRIMARY KEY  (aid))
 TYPE=MyISAM DEFAULT CHARSET=greek");

    mysql_query("CREATE TABLE survey_answer_record (
      arid int(11) NOT NULL auto_increment,
      aid bigint(12) NOT NULL default '0',
      question_text varchar(250) NOT NULL default '',
      question_answer varchar(250) NOT NULL default '',
      PRIMARY KEY  (arid))
 TYPE=MyISAM DEFAULT CHARSET=greek");

    mysql_query("CREATE TABLE survey_question (
      sqid bigint(12) NOT NULL default '0',
      sid bigint(12) NOT NULL default '0',
      question_text varchar(250) NOT NULL default '',
      PRIMARY KEY  (sqid))
    TYPE=MyISAM DEFAULT CHARSET=greek");

    mysql_query("CREATE TABLE survey_question_answer (
      sqaid int(11) NOT NULL auto_increment,
      sqid bigint(12) NOT NULL default '0',
      answer_text varchar(250) default NULL,
      PRIMARY KEY  (sqaid))
  TYPE=MyISAM DEFAULT CHARSET=greek");

    mysql_query("CREATE TABLE poll (
      pid bigint(14) NOT NULL auto_increment,
      creator_id mediumint(8) unsigned NOT NULL default '0',
      course_id varchar(20) NOT NULL default '0',
      name varchar(255) NOT NULL default '',
      creation_date datetime NOT NULL default '0000-00-00 00:00:00',
      start_date datetime NOT NULL default '0000-00-00 00:00:00',
      end_date datetime NOT NULL default '0000-00-00 00:00:00',
      type int(11) NOT NULL default '0',
      active int(11) NOT NULL default '0',
      PRIMARY KEY  (pid))
    TYPE=MyISAM DEFAULT CHARSET=greek");

    mysql_query("CREATE TABLE poll_answer (
      aid bigint(12) NOT NULL default '0',
      creator_id mediumint(8) unsigned NOT NULL default '0',
      pid bigint(12) NOT NULL default '0',
      date datetime NOT NULL default '0000-00-00 00:00:00',
      PRIMARY KEY  (aid))
    TYPE=MyISAM DEFAULT CHARSET=greek");

    mysql_query("CREATE TABLE poll_answer_record (
      arid int(11) NOT NULL auto_increment,
      aid bigint(12) NOT NULL default '0',
      question_text varchar(250) NOT NULL default '',
      question_answer varchar(250) NOT NULL default '',
      PRIMARY KEY  (arid))
    TYPE=MyISAM DEFAULT CHARSET=greek");

    mysql_query("CREATE TABLE poll_question (
      pqid bigint(12) NOT NULL default '0',
      pid bigint(12) NOT NULL default '0',
      question_text varchar(250) NOT NULL default '',
      PRIMARY KEY  (pqid))
    TYPE=MyISAM DEFAULT CHARSET=greek");

    mysql_query("CREATE TABLE poll_question_answer (
      pqaid int(11) NOT NULL auto_increment,
      pqid bigint(12) NOT NULL default '0',
      answer_text varchar(250) default NULL,
      PRIMARY KEY  (pqaid))
    TYPE=MyISAM DEFAULT CHARSET=greek");

###########################################################################

    mysql_query("CREATE TABLE liste_domaines (
               id int(11) NOT NULL auto_increment,
               domaine char(20) NOT NULL,
               description char(50) NOT NULL,
               PRIMARY KEY (id))
    TYPE=MyISAM DEFAULT CHARSET=greek");

############################# LEARNING PATH ######################################

mysql_query("CREATE TABLE `lp_module` (
              `module_id` int(11) NOT NULL auto_increment,
              `name` varchar(255) NOT NULL default '',
              `comment` text NOT NULL,
              `accessibility` enum('PRIVATE','PUBLIC') NOT NULL default 'PRIVATE',
              `startAsset_id` int(11) NOT NULL default '0',
              `contentType` enum('CLARODOC','DOCUMENT','EXERCISE','HANDMADE','SCORM','LABEL','COURSE_DESCRIPTION','LINK') NOT NULL,
              `launch_data` text NOT NULL,
              PRIMARY KEY  (`module_id`)
             ) TYPE=MyISAM DEFAULT CHARSET=greek");
             //COMMENT='List of available modules used in learning paths';

mysql_query("CREATE TABLE `lp_learnPath` (
              `learnPath_id` int(11) NOT NULL auto_increment,
              `name` varchar(255) NOT NULL default '',
              `comment` text NOT NULL,
              `lock` enum('OPEN','CLOSE') NOT NULL default 'OPEN',
              `visibility` enum('HIDE','SHOW') NOT NULL default 'SHOW',
              `rank` int(11) NOT NULL default '0',
              PRIMARY KEY  (`learnPath_id`),
              UNIQUE KEY rank (`rank`)
            ) TYPE=MyISAM DEFAULT CHARSET=greek");
            //COMMENT='List of learning Paths';

mysql_query("CREATE TABLE `lp_rel_learnPath_module` (
                `learnPath_module_id` int(11) NOT NULL auto_increment,
                `learnPath_id` int(11) NOT NULL default '0',
                `module_id` int(11) NOT NULL default '0',
                `lock` enum('OPEN','CLOSE') NOT NULL default 'OPEN',
                `visibility` enum('HIDE','SHOW') NOT NULL default 'SHOW',
                `specificComment` text NOT NULL,
                `rank` int(11) NOT NULL default '0',
                `parent` int(11) NOT NULL default '0',
                `raw_to_pass` tinyint(4) NOT NULL default '50',
                PRIMARY KEY  (`learnPath_module_id`)
              ) TYPE=MyISAM DEFAULT CHARSET=greek");
              //COMMENT='This table links module to the learning path using them';

mysql_query("CREATE TABLE `lp_asset` (
              `asset_id` int(11) NOT NULL auto_increment,
              `module_id` int(11) NOT NULL default '0',
              `path` varchar(255) NOT NULL default '',
              `comment` varchar(255) default NULL,
              PRIMARY KEY  (`asset_id`)
            ) TYPE=MyISAM DEFAULT CHARSET=greek");
            //COMMENT='List of resources of module of learning paths';

mysql_query("CREATE TABLE `lp_user_module_progress` (
              `user_module_progress_id` int(22) NOT NULL auto_increment,
              `user_id` mediumint(9) NOT NULL default '0',
              `learnPath_module_id` int(11) NOT NULL default '0',
              `learnPath_id` int(11) NOT NULL default '0',
              `lesson_location` varchar(255) NOT NULL default '',
              `lesson_status` enum('NOT ATTEMPTED','PASSED','FAILED','COMPLETED','BROWSED','INCOMPLETE','UNKNOWN') NOT NULL default 'NOT ATTEMPTED',
              `entry` enum('AB-INITIO','RESUME','') NOT NULL default 'AB-INITIO',
              `raw` tinyint(4) NOT NULL default '-1',
              `scoreMin` tinyint(4) NOT NULL default '-1',
              `scoreMax` tinyint(4) NOT NULL default '-1',
              `total_time` varchar(13) NOT NULL default '0000:00:00.00',
              `session_time` varchar(13) NOT NULL default '0000:00:00.00',
              `suspend_data` text NOT NULL,
              `credit` enum('CREDIT','NO-CREDIT') NOT NULL default 'NO-CREDIT',
              PRIMARY KEY  (`user_module_progress_id`)
            ) TYPE=MyISAM DEFAULT CHARSET=greek");
            //COMMENT='Record the last known status of the user in the course';

} else {

#################################### AGENDA ################################
mysql_query("CREATE TABLE agenda (
    id int(11) NOT NULL auto_increment,
    titre varchar(200),
    contenu text,
    day date NOT NULL default '0000-00-00',
    hour time NOT NULL default '00:00:00',
    lasting varchar(20),
    PRIMARY KEY (id))
    TYPE=MyISAM");

############################# PAGES ###########################################

    mysql_query("CREATE TABLE pages (
               id int(11) NOT NULL auto_increment,
               url varchar(200),
               titre varchar(200),
               description text,
               PRIMARY KEY (id))
        TYPE=MyISAM");


############################# DOCUMENTS ###########################################

mysql_query ("CREATE TABLE document (id int(4) NOT NULL auto_increment,
    path varchar(255) NOT NULL,
    visibility char(1) DEFAULT 'v' NOT NULL,
    comment varchar(255),
    PRIMARY KEY (id))
    TYPE=MyISAM");

############################# VIDEO ###########################################

    mysql_query("CREATE TABLE video (
               id int(11) NOT NULL auto_increment,
               url varchar(200),
               titre varchar(200),
               description text,
               PRIMARY KEY (id))
        TYPE=MyISAM");

################################# VIDEO LINKS ################################

        mysql_query("CREATE TABLE videolinks (
               id int(11) NOT NULL auto_increment,
               url varchar(200),
               titre varchar(200),
               description text,
               visibility CHAR(1) DEFAULT '1' NOT NULL,
               PRIMARY KEY (id))
        TYPE=MyISAM");


############################# WORKS ###########################################

mysql_query("
CREATE TABLE work (
    id int(11) NOT NULL auto_increment,
    url varchar(200),
    titre varchar(200),
    description varchar(250),
    auteurs varchar(200),
    active tinyint(1),
    accepted tinyint(1),
    PRIMARY KEY (id))
    TYPE=MyISAM");


mysql_query("CREATE TABLE work_student (
    work_id int(11) NOT NULL,
    uname varchar(30),
    PRIMARY KEY (work_id,uname))
    TYPE=MyISAM");

// new queries
db_query("CREATE TABLE `assignments` (
    `id` int(11) NOT NULL auto_increment,
    `title` varchar(200) NOT NULL default '',
    `description` text NOT NULL,
    `comments` text NOT NULL,
    `deadline` date NOT NULL default '0000-00-00',
    `submission_date` date NOT NULL default '0000-00-00',
    `active` char(1) NOT NULL default '1',
    `secret_directory` varchar(30) NOT NULL,
    `group_submissions` CHAR(1) DEFAULT '0' NOT NULL,
    UNIQUE KEY `id` (`id`))
    TYPE=MyISAM");


db_query("CREATE TABLE `assignment_submit` (
    `id` int(11) NOT NULL auto_increment,
    `uid` int(11) NOT NULL default '0',
    `assignment_id` int(11) NOT NULL default '0',
    `submission_date` date NOT NULL default '0000-00-00',
    `submission_ip` varchar(16) NOT NULL default '',
    `file_path` varchar(200) NOT NULL default '',
    `file_name` varchar(200) NOT NULL default '',
    `comments` text NOT NULL,
    `grade` varchar(50) NOT NULL default '',
    `grade_comments` text NOT NULL,
    `grade_submission_date` date NOT NULL default '0000-00-00',
    `grade_submission_ip` varchar(16) NOT NULL default '',
    `group_id` INT( 11 ) DEFAULT NULL,
    UNIQUE KEY `id` (`id`))
    TYPE=MyISAM");


############################## LIENS #############################################

    mysql_query("CREATE TABLE liens (
               id int(11) NOT NULL auto_increment,
               url varchar(150),
               titre varchar(150),
               description text,
           category int(4) default '0' NOT NULL,
           ordre mediumint(8) default '0' NOT NULL,
               PRIMARY KEY (id))
        TYPE=MyISAM");

    mysql_query("INSERT INTO liens VALUES (
               '1',
               'http://www.google.com',
               'Google',
               '$langGoogle','0','0'
               )");

    mysql_query("CREATE TABLE `link_categories` (
      `id` int(6) NOT NULL auto_increment,
      `categoryname` varchar(255) default NULL,
      `description` text,
      `ordre` mediumint(8) NOT NULL default '0',
      PRIMARY KEY (`id`))
    TYPE=MyISAM");

###################################### DROPBOX #####################################

    mysql_query("CREATE TABLE dropbox_file (
      id int(11) unsigned NOT NULL auto_increment,
      uploaderId int(11) unsigned NOT NULL default '0',
      filename varchar(250) NOT NULL default '',
      filesize int(11) unsigned NOT NULL default '0',
      title varchar(250) default '',
      description varchar(250) default '',
      author varchar(250) default '',
      uploadDate datetime NOT NULL default '0000-00-00 00:00:00',
      lastUploadDate datetime NOT NULL default '0000-00-00 00:00:00',
      PRIMARY KEY  (id),
      UNIQUE KEY UN_filename (filename))
    TYPE=MyISAM");


    mysql_query("CREATE TABLE dropbox_person (
      fileId int(11) unsigned NOT NULL default '0',
      personId int(11) unsigned NOT NULL default '0',
      PRIMARY KEY  (fileId,personId))
    TYPE=MyISAM");

    mysql_query("CREATE TABLE dropbox_post (
      fileId int(11) unsigned NOT NULL default '0',
      recipientId int(11) unsigned NOT NULL default '0',
      PRIMARY KEY  (fileId,recipientId))
    TYPE=MyISAM");
/*
############################## INTRODUCTION #######################################

    mysql_query("CREATE TABLE introduction (
		               id int(11) NOT NULL default '1',
									 texte_intro text,
									PRIMARY KEY (id))
									TYPE=MyISAM");

		mysql_query("INSERT INTO introduction SET texte_intro = '$course_intronote'");


*/
############################## GROUPS ###########################################

mysql_query("
CREATE TABLE student_group
(
    id int(11) NOT NULL auto_increment,
    name varchar(100) default NULL,
    description text,
    tutor int(11) default NULL,
    forumId int(11) default NULL,
    maxStudent int(11) NOT NULL default '0',
    secretDirectory varchar(30) NOT NULL default '0',
    PRIMARY KEY  (id)
)
TYPE=MyISAM");

mysql_query("CREATE TABLE user_group (
    id int(11) NOT NULL auto_increment,
    user int(11) NOT NULL default '0',
    team int(11) NOT NULL default '0',
    status int(11) NOT NULL default '0',
    role varchar(50) NOT NULL default '',
    PRIMARY KEY  (id)
    )
    TYPE=MyISAM");

mysql_query("CREATE TABLE group_properties (
    id tinyint(4) NOT NULL auto_increment,
    self_registration tinyint(4) default '1',
    private tinyint(4) default '0',
    forum tinyint(4) default '1',
    document tinyint(4) default '1',
    wiki tinyint(4) default '0',
    agenda tinyint(4) default '0',
    PRIMARY KEY  (id)
    )
    TYPE=MyISAM");

mysql_query("INSERT INTO group_properties
    (id, self_registration, private, forum, document, wiki, agenda)
    VALUES (NULL, '1', '0', '1', '1', '0', '0')");


####################STATISTIQUES ################################################

    mysql_query("CREATE TABLE stat_accueil (
               id int(11) NOT NULL auto_increment,
               request char(100) NOT NULL,
               host char(100) NOT NULL,
               address char(100) NOT NULL,
               agent char(100) NOT NULL,
               date datetime,
               referer char(200) NOT NULL,
               country char(50) NOT NULL,
               provider char(100) NOT NULL,
               os char(50) NOT NULL,
               wb char(50) NOT NULL,
               PRIMARY KEY (id),
               KEY id (id))
        TYPE=MyISAM");


#################### SURVEY ###############################################

    mysql_query("CREATE TABLE survey (
      sid bigint(14) NOT NULL auto_increment,
      creator_id mediumint(8) unsigned NOT NULL default '0',
      course_id varchar(20) NOT NULL default '0',
      name varchar(255) NOT NULL default '',
      creation_date datetime NOT NULL default '0000-00-00 00:00:00',
      start_date datetime NOT NULL default '0000-00-00 00:00:00',
      end_date datetime NOT NULL default '0000-00-00 00:00:00',
      type int(11) NOT NULL default '0',
      active int(11) NOT NULL default '0',
      PRIMARY KEY  (sid))
    TYPE=MyISAM");

    mysql_query("CREATE TABLE survey_answer (
        aid bigint(12) NOT NULL default '0',
      creator_id mediumint(8) unsigned NOT NULL default '0',
      sid bigint(12) NOT NULL default '0',
      date datetime NOT NULL default '0000-00-00 00:00:00',
      PRIMARY KEY  (aid))
 TYPE=MyISAM");

    mysql_query("CREATE TABLE survey_answer_record (
      arid int(11) NOT NULL auto_increment,
      aid bigint(12) NOT NULL default '0',
      question_text varchar(250) NOT NULL default '',
      question_answer varchar(250) NOT NULL default '',
      PRIMARY KEY  (arid))
 TYPE=MyISAM");

    mysql_query("CREATE TABLE survey_question (
      sqid bigint(12) NOT NULL default '0',
      sid bigint(12) NOT NULL default '0',
      question_text varchar(250) NOT NULL default '',
      PRIMARY KEY  (sqid))
    TYPE=MyISAM");

    mysql_query("CREATE TABLE survey_question_answer (
      sqaid int(11) NOT NULL auto_increment,
      sqid bigint(12) NOT NULL default '0',
      answer_text varchar(250) default NULL,
      PRIMARY KEY  (sqaid))
  TYPE=MyISAM");


#################### POLL #################################################

    mysql_query("CREATE TABLE poll (
      pid bigint(14) NOT NULL auto_increment,
      creator_id mediumint(8) unsigned NOT NULL default '0',
      course_id varchar(20) NOT NULL default '0',
      name varchar(255) NOT NULL default '',
      creation_date datetime NOT NULL default '0000-00-00 00:00:00',
      start_date datetime NOT NULL default '0000-00-00 00:00:00',
      end_date datetime NOT NULL default '0000-00-00 00:00:00',
      type int(11) NOT NULL default '0',
      active int(11) NOT NULL default '0',
      PRIMARY KEY  (pid))
    TYPE=MyISAM");

    mysql_query("CREATE TABLE poll_answer (
      aid bigint(12) NOT NULL default '0',
      creator_id mediumint(8) unsigned NOT NULL default '0',
      pid bigint(12) NOT NULL default '0',
      date datetime NOT NULL default '0000-00-00 00:00:00',
      PRIMARY KEY  (aid))
    TYPE=MyISAM");

    mysql_query("CREATE TABLE poll_answer_record (
      arid int(11) NOT NULL auto_increment,
      aid bigint(12) NOT NULL default '0',
      question_text varchar(250) NOT NULL default '',
      question_answer varchar(250) NOT NULL default '',
      PRIMARY KEY  (arid))
    TYPE=MyISAM");

    mysql_query("CREATE TABLE poll_question (
      pqid bigint(12) NOT NULL default '0',
      pid bigint(12) NOT NULL default '0',
      question_text varchar(250) NOT NULL default '',
      PRIMARY KEY  (pqid))
    TYPE=MyISAM");

    mysql_query("CREATE TABLE poll_question_answer (
      pqaid int(11) NOT NULL auto_increment,
      pqid bigint(12) NOT NULL default '0',
      answer_text varchar(250) default NULL,
      PRIMARY KEY  (pqaid))
    TYPE=MyISAM");

###########################################################################

    mysql_query("CREATE TABLE liste_domaines (
               id int(11) NOT NULL auto_increment,
               domaine char(20) NOT NULL,
               description char(50) NOT NULL,
               PRIMARY KEY (id))
               TYPE=MyISAM");

############################# LEARNING PATH ######################################

mysql_query("CREATE TABLE `lp_module` (
              `module_id` int(11) NOT NULL auto_increment,
              `name` varchar(255) NOT NULL default '',
              `comment` text NOT NULL,
              `accessibility` enum('PRIVATE','PUBLIC') NOT NULL default 'PRIVATE',
              `startAsset_id` int(11) NOT NULL default '0',
              `contentType` enum('CLARODOC','DOCUMENT','EXERCISE','HANDMADE','SCORM','LABEL','COURSE_DESCRIPTION') NOT NULL,
              `launch_data` text NOT NULL,
              PRIMARY KEY  (`module_id`)
             ) TYPE=MyISAM");
             //COMMENT='List of available modules used in learning paths';

mysql_query("CREATE TABLE `lp_learnPath` (
              `learnPath_id` int(11) NOT NULL auto_increment,
              `name` varchar(255) NOT NULL default '',
              `comment` text NOT NULL,
              `lock` enum('OPEN','CLOSE') NOT NULL default 'OPEN',
              `visibility` enum('HIDE','SHOW') NOT NULL default 'SHOW',
              `rank` int(11) NOT NULL default '0',
              PRIMARY KEY  (`learnPath_id`),
              UNIQUE KEY rank (`rank`)
            ) TYPE=MyISAM");
            //COMMENT='List of learning Paths';

mysql_query("CREATE TABLE `lp_rel_learnPath_module` (
                `learnPath_module_id` int(11) NOT NULL auto_increment,
                `learnPath_id` int(11) NOT NULL default '0',
                `module_id` int(11) NOT NULL default '0',
                `lock` enum('OPEN','CLOSE') NOT NULL default 'OPEN',
                `visibility` enum('HIDE','SHOW') NOT NULL default 'SHOW',
                `specificComment` text NOT NULL,
                `rank` int(11) NOT NULL default '0',
                `parent` int(11) NOT NULL default '0',
                `raw_to_pass` tinyint(4) NOT NULL default '50',
                PRIMARY KEY  (`learnPath_module_id`)
              ) TYPE=MyISAM");
              //COMMENT='This table links module to the learning path using them';

mysql_query("CREATE TABLE `lp_asset` (
              `asset_id` int(11) NOT NULL auto_increment,
              `module_id` int(11) NOT NULL default '0',
              `path` varchar(255) NOT NULL default '',
              `comment` varchar(255) default NULL,
              PRIMARY KEY  (`asset_id`)
            ) TYPE=MyISAM");
            //COMMENT='List of resources of module of learning paths';

mysql_query("CREATE TABLE `lp_user_module_progress` (
              `user_module_progress_id` int(22) NOT NULL auto_increment,
              `user_id` mediumint(9) NOT NULL default '0',
              `learnPath_module_id` int(11) NOT NULL default '0',
              `learnPath_id` int(11) NOT NULL default '0',
              `lesson_location` varchar(255) NOT NULL default '',
              `lesson_status` enum('NOT ATTEMPTED','PASSED','FAILED','COMPLETED','BROWSED','INCOMPLETE','UNKNOWN') NOT NULL default 'NOT ATTEMPTED',
              `entry` enum('AB-INITIO','RESUME','') NOT NULL default 'AB-INITIO',
              `raw` tinyint(4) NOT NULL default '-1',
              `scoreMin` tinyint(4) NOT NULL default '-1',
              `scoreMax` tinyint(4) NOT NULL default '-1',
              `total_time` varchar(13) NOT NULL default '0000:00:00.00',
              `session_time` varchar(13) NOT NULL default '0000:00:00.00',
              `suspend_data` text NOT NULL,
              `credit` enum('CREDIT','NO-CREDIT') NOT NULL default 'NO-CREDIT',
              PRIMARY KEY  (`user_module_progress_id`)
            ) TYPE=MyISAM");
            //COMMENT='Record the last known status of the user in the course';

}

###########################################################################

    mysql_query("INSERT INTO liste_domaines VALUES ( '14', 'au', 'Australie')");
    mysql_query("INSERT INTO liste_domaines VALUES ( '20', 'be', 'Belgique')");
    mysql_query("INSERT INTO liste_domaines VALUES ( '28', 'bo', 'Bolivie')");
    mysql_query("INSERT INTO liste_domaines VALUES ( '29', 'br', 'Brasil')");
    mysql_query("INSERT INTO liste_domaines VALUES ( '36', 'ca', 'Canada')");
    mysql_query("INSERT INTO liste_domaines VALUES ( '38', 'cd', 'Congo, (République démocratique du)')");
    mysql_query("INSERT INTO liste_domaines VALUES ( '40', 'cg', 'Congo')");
    mysql_query("INSERT INTO liste_domaines VALUES ( '41', 'ch', 'Suisse')");
    mysql_query("INSERT INTO liste_domaines VALUES ( '44', 'cl', 'Chili')");
    mysql_query("INSERT INTO liste_domaines VALUES ( '45', 'cm', 'Cameroun')");
    mysql_query("INSERT INTO liste_domaines VALUES ( '46', 'cn', 'Chine')");
    mysql_query("INSERT INTO liste_domaines VALUES ( '47', 'co', 'Colombie')");
    mysql_query("INSERT INTO liste_domaines VALUES ( '48', 'cr', 'Costa Rica')");
    mysql_query("INSERT INTO liste_domaines VALUES ( '49', 'cu', 'Cuba')");
    mysql_query("INSERT INTO liste_domaines VALUES ( '53', 'cz', 'Tchéque (République)')");
    mysql_query("INSERT INTO liste_domaines VALUES ( '54', 'de', 'Allemagne')");
    mysql_query("INSERT INTO liste_domaines VALUES ( '56', 'dk', 'Denmark')");
    mysql_query("INSERT INTO liste_domaines VALUES ( '59', 'dz', 'Algerie')");
    mysql_query("INSERT INTO liste_domaines VALUES ( '62', 'eg', 'Egypte')");
    mysql_query("INSERT INTO liste_domaines VALUES ( '65', 'es', 'Espagne')");
    mysql_query("INSERT INTO liste_domaines VALUES ( '66', 'et', 'Ethiopie')");
    mysql_query("INSERT INTO liste_domaines VALUES ( '72', 'fr', 'France')");
    mysql_query("INSERT INTO liste_domaines VALUES ( '76', 'gf', 'Guyane France')");
    mysql_query("INSERT INTO liste_domaines VALUES ( '85', 'gr', 'Greece')");
    mysql_query("INSERT INTO liste_domaines VALUES ( '87', 'gt', 'Guatemala')");
    mysql_query("INSERT INTO liste_domaines VALUES ( '96', 'hu', 'Hungary')");
    mysql_query("INSERT INTO liste_domaines VALUES ( '97', 'id', 'Indonesie')");
    mysql_query("INSERT INTO liste_domaines VALUES ( '98', 'ie', 'Irland')");
    mysql_query("INSERT INTO liste_domaines VALUES ( '99', 'il', 'Israel')");
    mysql_query("INSERT INTO liste_domaines VALUES ( '101', 'in', 'India')");
    mysql_query("INSERT INTO liste_domaines VALUES ( '103', 'iq', 'Iraq')");
    mysql_query("INSERT INTO liste_domaines VALUES ( '104', 'ir', 'Iran (Republique Islamique d\')')");
    mysql_query("INSERT INTO liste_domaines VALUES ( '106', 'it', 'Italie')");
    mysql_query("INSERT INTO liste_domaines VALUES ( '110', 'jp', 'Japon')");
    mysql_query("INSERT INTO liste_domaines VALUES ( '111', 'ke', 'Kenya')");
    mysql_query("INSERT INTO liste_domaines VALUES ( '113', 'kh', 'Cambodge')");
    mysql_query("INSERT INTO liste_domaines VALUES ( '123', 'lb', 'Liban')");
    mysql_query("INSERT INTO liste_domaines VALUES ( '130', 'lu', 'Luxembourg')");
    mysql_query("INSERT INTO liste_domaines VALUES ( '133', 'ma', 'Marooo')");
    mysql_query("INSERT INTO liste_domaines VALUES ( '151', 'mx', 'Mexico')");
    mysql_query("INSERT INTO liste_domaines VALUES ( '159', 'ni', 'Nicaragua')");
    mysql_query("INSERT INTO liste_domaines VALUES ( '160', 'nl', 'Pays Bas')");
    mysql_query("INSERT INTO liste_domaines VALUES ( '161', 'no', 'Norvege')");
    mysql_query("INSERT INTO liste_domaines VALUES ( '165', 'nz', 'New Zealand')");
    mysql_query("INSERT INTO liste_domaines VALUES ( '168', 'pe', 'Perou')");
    mysql_query("INSERT INTO liste_domaines VALUES ( '173', 'pl', 'Pologne')");
    mysql_query("INSERT INTO liste_domaines VALUES ( '177', 'pt', 'Portugal')");
    mysql_query("INSERT INTO liste_domaines VALUES ( '182', 'ro', 'Roumanie')");
    mysql_query("INSERT INTO liste_domaines VALUES ( '183', 'ru', 'Russie')");
    mysql_query("INSERT INTO liste_domaines VALUES ( '184', 'rw', 'Rwanda')");
    mysql_query("INSERT INTO liste_domaines VALUES ( '189', 'se', 'Suede')");
    mysql_query("INSERT INTO liste_domaines VALUES ( '197', 'sn', 'Sénégal')");
    mysql_query("INSERT INTO liste_domaines VALUES ( '212', 'tn', 'Tunisie')");
    mysql_query("INSERT INTO liste_domaines VALUES ( '215', 'tr', 'Turquie')");
    mysql_query("INSERT INTO liste_domaines VALUES ( '218', 'tw', 'Taiwan')");
    mysql_query("INSERT INTO liste_domaines VALUES ( '222', 'uk', 'Royaume Uni')");
    mysql_query("INSERT INTO liste_domaines VALUES ( '223', 'gb', 'Royaume Uni')");
    mysql_query("INSERT INTO liste_domaines VALUES ( '225', 'us', 'Etats Unis')");
    mysql_query("INSERT INTO liste_domaines VALUES ( '228', 'va', 'Vatican')");
    mysql_query("INSERT INTO liste_domaines VALUES ( '230', 've', 'Venezuela')");
    mysql_query("INSERT INTO liste_domaines VALUES ( '239', 'yu', 'Yugoslavie')");
    mysql_query("INSERT INTO liste_domaines VALUES ( '244', 'com', '.COM')");
    mysql_query("INSERT INTO liste_domaines VALUES ( '245', 'net', '.NET')");
    mysql_query("INSERT INTO liste_domaines VALUES ( '246', 'org', '.ORG')");
    mysql_query("INSERT INTO liste_domaines VALUES ( '247', 'edu', 'Education')");
    mysql_query("INSERT INTO liste_domaines VALUES ( '249', 'arpa', '.ARPA')");
    mysql_query("INSERT INTO liste_domaines VALUES ( '250', 'at', 'Autriche')");
    mysql_query("INSERT INTO liste_domaines VALUES ( '251', 'gov', 'Gouvernement')");
    mysql_query("INSERT INTO liste_domaines VALUES ( '252', 'mil', 'Miltaire')");

// ----------------------------------------
// ------------- update main Db------------
// ----------------------------------------

mysql_select_db("$mysqlMainDb");

/*print "INSERT INTO cours SET
        code = '$code',
        languageCourse = '$languageCourse',
        intitule = '$intitule',
        description = '$description',
        course_addon = '$course_addon',
        course_keywords = '$course_keywords',
        faculte = '$facname',
        visible = '$formvisible',
        cahier_charges = '',
        titulaires = '$titulaires',
        fake_code = '$code',
        `type` = '$type',
        faculteid = '$facid'";
*/
    mysql_query("INSERT INTO cours SET
        code = '$code',
        languageCourse = '$languageCourse',
        intitule = '$intitule',
        description = '$description',
        course_addon = '$course_addon',
        course_keywords = '$course_keywords',
        faculte = '$facname',
        visible = '$formvisible',
        cahier_charges = '',
        titulaires = '$titulaires',
        fake_code = '$code',
        `type` = '$type',
        faculteid = '$facid'");


    mysql_query("INSERT INTO cours_user SET
        code_cours = '$repertoire',
        user_id = '$uid',
        statut = '1',
        role = '$langProfessor',
        tutor='1'");

mysql_query("INSERT INTO cours_faculte SET
         faculte = '$faculte',
         code = '$repertoire',
         facid = '$facid'");

// create directories
    
		umask(0);
    mkdir("../../courses/$repertoire", 0777);
    mkdir("../../courses/$repertoire/image", 0777);
    mkdir("../../courses/$repertoire/document", 0777);
    mkdir("../../courses/$repertoire/page", 0777);
    mkdir("../../courses/$repertoire/work", 0777);
    mkdir("../../courses/$repertoire/group", 0777);
    mkdir("../../courses/$repertoire/temp", 0777);
    mkdir("../../courses/$repertoire/scormPackages", 0777);


    //mkdir("../../courses/$repertoire/video", 0777);
    mkdir("../../video/$repertoire", 0777);
    //symlink("../../video/$repertoire","../../courses/$repertoire/video");

    $titou='$dbname';


// ---------------------------------------------
// ----------- main course index.php -----------
// ---------------------------------------------
    
		$fd=fopen("../../courses/$repertoire/index.php", "w");
$string="<?php
session_start();
$titou=\"$repertoire\";
session_register(\"dbname\");
include(\"../../modules/course_home/course_home.php\");
?>";

fwrite($fd, "$string");
$status[$repertoire]=1;
session_register("status");

$tool_content .= "<tr bgcolor=$color2>
    <td colspan=3>
    <font face='arial, helvetica' size=2>
    $langJustCreated $repertoire<br><br><br>
    <a href=\"../../courses/$repertoire/index.php\">$langEnter</a><br><br><br>
    $langEnterMetadata
    </font><br>
    </td></tr>";
		
 } // else

} // if all fields fulfilled

$tool_content .= "</table>";

draw($tool_content, '1', '', $local_head);



/*
* Default Scoring function
* Goal : compute a default scoring for a grouped multiple choise.
*/

function DefaultScoring($ChoiceCount,$Z,$weight) {

    if ($Z==0)
    {
        $score = 10;
    }
    else{

        $m=20;
        $n=-0.2;
        $o=8;
        $p=-1.3;

        //intermediate computations

        $a=$m*pow($ChoiceCount,$n);
        $b=$o*pow($ChoiceCount,$p);

        //Scoring computation

        $score=(round(($a*exp(-$b*$Z))*2))/2;
    }

    return $score/10*$weight;

} //End of function DefaultScoring

?>
</body>
</html>
