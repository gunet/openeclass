<?

/*
      +----------------------------------------------------------------------+
      | e-Class version 1.9                                                  |
      | based on CLAROLINE version 1.3.0 $Revision$                   |
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2002 Universite catholique de Louvain (UCL)      |
      | Copyright (c) 2003 GUNet                                             |
      +----------------------------------------------------------------------+
      |   This program is free software; you can redistribute it and/or      |
      |   modify it under the terms of the GNU General Public License        |
      |   as published by the Free Software Foundation; either version 2     |
      |   of the License, or (at your option) any later version.             |
      |                                                                      |
      |   This program is distributed in the hope that it will be useful,    |
      |   but WITHOUT ANY WARRANTY; without even the implied warranty of     |
      |   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the      |
      |   GNU General Public License for more details.                       |
      |                                                                      |
      |   You should have received a copy of the GNU General Public License  |
      |   along with this program; if not, write to the Free Software        |
      |   Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA          |
      |   02111-1307, USA. The GNU GPL license is also available through     |
      |   the world-wide-web at http://www.gnu.org/copyleft/gpl.html         |
      +----------------------------------------------------------------------+
      | e-Class Authors:    Costas Tsibanis <costas@noc.uoa.gr>              |
      |                     Yannis Exidaridis <jexi@noc.uoa.gr>              |
      |                     Alexandros Diamantidis <adia@noc.uoa.gr>         |
      |		   (v2 changes)	Sakis Agorastos <th_agorastos@hotmail.com>	     |
      |                                                                      |
      | Claroline Authors:  Thomas Depraetere <depraetere@ipm.ucl.ac.be>     |
      |                     Hugues Peeters    <peeters@ipm.ucl.ac.be>        |
      |                     Christophe Geschι <gesche@ipm.ucl.ac.be>         |
      |                                                                      |
      +----------------------------------------------------------------------+
*/

/**
 * COURSE SITE CREATION TOOL
 * GOALS
 * *******
 * Allow professors and administrative staff to create course sites.
 * This big script makes, basically, 6 things:
 *     1. Create a database whose name=course code (sort of course id)
 *     2. Create tables in this base and fill some of them
 *     3. Create a www directory with the same name as the db name
 *     4. Add the course to the main icampus/course table
 *     5. Check whether the course code is not already taken.
 *     6. Associate the current user id with the course in order to let
 *        him administer it.
 *
 * One of the functions of this script is to merge the different
 * Open Source Tools used in the courses (statistics by EzBoo,
 * forum by phpBB...) under one unique user session and one unique
 * course id.
 * ******************************************************************
 */

$require_login = TRUE;
$require_prof = TRUE;

$langFiles = array('create_course', 'opencours');

$local_head = "

<script language=\"JavaScript\">

function validate()
{
	if (document.forms[0].intitule.value==\"\")
 	{
   		alert(\"Παρακαλώ συμπληρώστε τον τίτλο του μαθήματος!\"); 
   		return false;
 	}
 	
 	if (document.forms[0].description.value==\"\")
 	{
   		alert(\"Παρακαλώ συμπληρώστε την περιγραφή του μαθήματος!\"); 
   		return false;
 	}
 	
 	if (document.forms[0].titulaires.value==\"\")
 	{
   		alert(\"Παρακαλώ συμπληρώστε τουλάχιστον έναν διαχειριστή για το μάθημα!\"); 
   		return false;
 	}
 	

 	return true;
}
 
</script>

";


//ektypwnei ena <td> </td> me hyperlink pros to help me vash kapoio $topic
function help ($topic)
{
	echo"
	<td valign=\"middle\">
		<a href=\"../help/help.php?topic=$topic\" onclick=\"window.open('../help/help.php?topic=$topic','help','toolbar=no,location=no,directories=no,status=yes,menubar=no,scrollbars=yes,resizable=yes,width=400,height=500,left=300,top=10'); return false;\"><img src=\"../../images/help.gif\" border=\"0\"></a></td>";
}



include '../../include/init.php';

$titulaire_probable="$prenom $nom";
$local_style = "input { font-size: 12px; }";
begin_page($langCreateSite);

	//ean to mathima hdh yparxei emfanise mhnyma (elegxos mesw $_GET)
	if(isset($_GET["course_exists"]))
	{
		@include("../lang/$language/create_course.inc.php");

		echo "<tr bgcolor=\"$color2\">
		<td colspan=\"3\" valign=\"top\">
		<font face=\"arial, helvetica\" size=\"2\">
			$langCodeTaken.
		</td></tr>";
	}

###################### FORM  #########################################
<<<<<<< create_course.php
echo "
<!-- S T E P  1   [start] -->    

<tr bgcolor=\"$color1\">
	<td>
		<table bgcolor=\"$color1\" border=\"2\">
			<tr valign=\"top\" align=\"middle\">
				<td colspan=\"3\" valign=\"middle\">
					<table width=\"100%\">
						<tr>
							<td align=\"left\">
								<font face=\"arial, helvetica\" size=\"4\" color=\"gray\">$langCreateCourse</font>
							</td>
							<td align=\"right\">
								<font face=\"arial, helvetica\" size=\"4\" color=\"gray\">$langCreateCourseStep&nbsp;1&nbsp;$langCreateCourseStep2&nbsp;3</font>
							</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr><td colspan=\"3\">&nbsp;</td></tr>
			<tr valign=\"top\">
				<td colspan=\"5\" valign=\"middle\">
					<font face=\"arial, helvetica\" size=\"2\"><b>$langCreateCourseStep1Title</b></font>
					<hr>
					<font face=\"arial, helvetica\" size=\"2\">$langFieldsRequ</font>
				</td>
			</tr>
			<tr><td colspan=\"3\">&nbsp;</td></tr>
			<tr valign=\"top\">
				<td valign=\"top\" align=\"right\">
					<form method=\"post\" action=\"create_course2.php\" onsubmit=\"return validate();\">
					<font face=\"arial, helvetica\" size=\"2\"><b>$langTitle:</b></font>
				</td>
				<td valign=\"top\">
					<font face=\"arial, helvetica\" size=\"2\">
					<input type=\"Text\" name=\"intitule\" size=\"60\">$langFieldsRequAsterisk<br>$langEx</font>
				</td>";
				help("CreateCourse_Title");
echo"
			</tr>
		</td>
	<tr>
	<td valign=\"top\" align=\"right\">
	<font face=\"arial, helvetica\" size=\"2\"><b>$langFac:</b></font>
	</td>
	<td valign=\"top\">
=======
if(!isset($submit)) {
    echo "<tr bgcolor=\"$color2\">
    <td colspan=\"3\">
    <table bgcolor=\"$color2\">
    <tr valign=\"top\">
    <td colspan=\"2\" valign=\"top\">
    <font face=\"arial, helvetica\" size=\"2\"><b>$langFieldsRequ</b></font>
    </td>
    </tr>
    <tr valign=\"top\">
    <td width=\"100\" valign=\"top\">
    <form method=\"post\" action=\"$_SERVER[PHP_SELF]\">
    <font face=\"arial, helvetica\" size=\"2\">$langTitle&nbsp;:</font>
    </td>
    <td valign=top>
    <font face=\"arial, helvetica\" size=\"2\">
    <input type=\"Text\" name=\"intitule\" size=\"60\"><br>$langEx</font>
    </td></tr>
    <tr>
    <td valign=top>
    <font face=\"arial, helvetica\" size=\"2\">$langFac&nbsp;:</font>
    </td>
    <td valign=\"top\">
>>>>>>> 1.10

<select name=\"faculte\">";

$resultFac=mysql_query("SELECT name FROM faculte ORDER BY number");

while ($myfac = mysql_fetch_array($resultFac)) {
    if($myfac['name'] == $facu)
        echo "<option selected>$myfac[name]</option>";
    else
        echo "<option>$myfac[name]</option>";
}
<<<<<<< create_course.php
echo "</select>$langFieldsRequAsterisk<br><font face=\"arial, helvetica\" size=\"2\">$langTargetFac</font>
	</td>
	";
	help("CreateCourse_faculte");
	
echo "</tr>
=======
echo "</select><br><font face=\"arial, helvetica\" size=\"2\">$langTargetFac</font>
    </td></tr>
>>>>>>> 1.10
       <tr valign=\"top\">
       <td width=\"100\" valign=\"top\" align=\"right\">
       <font face=\"arial, helvetica\" size=\"2\"><b>$langDescrInfo:</b></font>
       </td>
       <td valign=top>
       <font face=\"arial, helvetica\" size=\"2\">
       <textarea name=\"description\" cols=\"30\" rows=\"4\"></textarea>
	   $langFieldsRequAsterisk
       <br>$langDescrInfo
       </font>
       </td>";
	   help("CreateCourse_description");
echo "
      </tr>";

unset($repertoire);

<<<<<<< create_course.php
/* SAKIS:  edw na valw ta kwdikia gia thn epilogh twn diaxeiristwn!!!!!! */

echo "<tr valign=\"top\">
	<td width=\"100\" valign=\"top\" align=\"right\">
	<font face=\"arial, helvetica\" size=\"2\"><b>$langProfessors:</b></font>
	</td>
	<td valign=\"top\">
	<input type=\"Text\" name=\"titulaires\" size=\"60\" value=\"$titulaire_probable\"><font face=\"arial, helvetica\" size=\"2\">$langFieldsRequAsterisk<br>$langProfessorsInfo</font>
	</td>";
	help("CreateCourse_titulaires");

echo "
	</tr>
	<tr>
	<td align=\"right\"><font face=\"arial, helvetica\" size=\"2\"><b>$m[type]:</b></font></td>
	<td>";
=======
echo "<tr valign=\"top\"><td width=\"100\" valign=\"top\">
    <font face=\"arial, helvetica\" size=\"2\">$langProfessors&nbsp;:</font>
    </td>
    <td valign=\"top\">
    <input type=\"Text\" name=\"titulaires\" size=\"60\" value=\"$titulaire_probable\">
    </td>
    </tr>
    <tr>
    <td><font face=\"arial, helvetica\" size=\"2\">$m[type]&nbsp;:</font></td>
    <td>";
>>>>>>> 1.10
selection(array('pre' => $m['pre'], 'post' => $m['post'], 'other' => $m['other']), 'type');

<<<<<<< create_course.php
echo "
	<font face=\"arial, helvetica\" size=\"2\">$langFieldsRequAsterisk<br>$langCourseCategory</br></td>";
					help("CreateCourse_Type");
echo "
	<tr>
		<td>
			&nbsp;
		</td>
		<td align=\"left\">
			<input type=\"button\" disabled=\"disabled\" name=\"button\" value=\"$langPreviousStep\">
		</td>
		<td align=\"right\">
			<input type=\"Submit\" name=\"submit\" value=\"$langNextStep\">
		</td>
	</tr>
	</table>
	</td>
	</tr>
	</table>
</form>";
=======
echo "</td></tr>
        <tr><td><font face=\"arial, helvetica\" size=\"2\">$langLn&nbsp;:</font></td>
        <td>
<select name=\"languageCourse\">";
$dirname = "../lang/";
if($dirname[strlen($dirname)-1]!='/')
    $dirname.='/';
$handle=opendir($dirname);
while ($entries = readdir($handle)) {
    if ($entries=='.'||$entries=='..'||$entries=='CVS')
        continue;
    if (is_dir($dirname.$entries)) {
        echo "<option value=\"$entries\"";
        if ($entries == $language)
            echo " selected ";
        echo ">";
        if (!empty($langNameOfLang[$entries]) && $langNameOfLang[$entries]!="" && $langNameOfLang[$entries]!=$entries)
        echo "$langNameOfLang[$entries] - ";
        echo "$entries
        </option>";
    }
}
closedir($handle);

echo "</select></td></tr>
    <tr><td>&nbsp;</td>
    <td>
    <input type=\"Submit\" name=\"submit\" value=\"$langCreate\">
</form>
    </td>
    </tr>
    <tr><td>&nbsp;</td>
    <td>
    <font face=\"arial, helvetica\" size=2>
        $langExplanation.
    </font>
    <br>
    </td>
    </tr>
    </table>
    </td>
    </tr>";

}   // IF ! SUBMIT

###########################################################################
####################SORT THE FORM #########################################
###########################################################################

###########################################################################
#### 1. CHECK IF DIRECTORY/COURSE_CODE ALREADY TAKEN ######################
###########################################################################

###########################################################################
####### CREATE THE COURSE AND THE DATABASE OF IT ##########################
###########################################################################

else {


    $repertoire = new_code(find_faculty_by_name($faculte));
    $language=$languageCourse;
    @include("../lang/$language/create_course.inc.php");
    if(empty($intitule) OR empty($repertoire)) {
        echo "<tr bgcolor=\"$color2\" height=\"400\">
        <td bgcolor=\"$color2\" colspan=\"2\" valign=\"top\">
            <br>
            <font face=\"arial, helvetica\" size=\"2\">
            $langEmpty
            <p>&nbsp;</p><p>&nbsp;</p>
            </font>
        </td>
    </tr>";
    } else {	// if all form fields fulfilled
        // remplace lower case letters by upper case in code_cours md 2121 > MD 2121
        $repertoire=strtoupper($repertoire);
        $faculte_lower=strtolower($faculte);

        //remove space in code_cours > MD 2121 > MD2121
        $repertoire = str_replace (" ", "", $repertoire);
        $repertoire_lower=strtolower($repertoire);

        $dbList = mysql_list_dbs();
        $cnt = 0;
        $dbNumber = mysql_num_rows($dbList);
        while ($cnt < $dbNumber) {
            $dbCode = mysql_db_name($dbList, $cnt);
            if ($dbCode == $repertoire) {
            echo "<tr bgcolor=\"$color2\" height=\"400\">
            <td colspan=\"2\" valign=\"top\">
            <font face=\"arial, helvetica\" size=\"2\">
                $langCodeTaken.
                <br>
                <p>&nbsp;</p>
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


##############################################################################
######## CREATE AND POPULATE TABLES FORUM PHPBB 1.4 www.phpbb.com #######
##############################################################################

        mysql_select_db($repertoire);
        if (mysql_version()) {
        $sql ="
        CREATE TABLE access (
        access_id int(10) NOT NULL auto_increment,
        access_title varchar(20),
        PRIMARY KEY (access_id))
        TYPE=MyISAM DEFAULT CHARSET=greek";
        } else {
        $sql ="
        CREATE TABLE access (
        access_id int(10) NOT NULL auto_increment,
        access_title varchar(20),
        PRIMARY KEY (access_id))
        TYPE=MyISAM";
        }
    mysql_query($sql);

    mysql_query("INSERT INTO access VALUES (
                 '-1',
                 'Deleted'
                 )");

    mysql_query("INSERT INTO access VALUES (
                 '1',
                 'User'
                 )");

    mysql_query("INSERT INTO access VALUES (
                 '2',
                 'Moderator'
                 )");

    mysql_query("INSERT INTO access VALUES (
                 '3',
                 'Super Moderator'
                 )");

    mysql_query("INSERT INTO access VALUES (
                 '4',
                 'Administrator'
                 )");

// checking if the mysql version is > 4.1
if (mysql_version()) {

    mysql_query("CREATE TABLE banlist (
                 ban_id int(10) NOT NULL auto_increment,
                 ban_userid int(10),
                 ban_ip varchar(16),
                 ban_start int(32),
                 ban_end int(50),
                 ban_time_type int(10),
                 PRIMARY KEY (ban_id),
                 KEY ban_id (ban_id))
         TYPE=MyISAM DEFAULT CHARSET=greek");

    mysql_query("
CREATE TABLE catagories (
    cat_id int(10) NOT NULL auto_increment,
    cat_title varchar(100),
    cat_order varchar(10),
    PRIMARY KEY (cat_id))
    TYPE=MyISAM DEFAULT CHARSET=greek");

// Create a hidden category for group forums
mysql_query("INSERT INTO catagories VALUES (1,'$langCatagoryGroup',NULL)");

// Create an example category
mysql_query("INSERT INTO catagories VALUES (2,'$langCatagoryMain',NULL)");

mysql_query("CREATE TABLE config (
                 config_id int(10) NOT NULL auto_increment,
                 sitename varchar(100),
                 allow_html int(2),
                 allow_bbcode int(2),
                 allow_sig int(2),
                 allow_namechange int(2) DEFAULT '0',
                 admin_passwd varchar(32),
                 selected int(2) DEFAULT '0' NOT NULL,
                 posts_per_page int(10),
                 hot_threshold int(10),
                 topics_per_page int(10),
                 allow_theme_create int(10),
                 override_themes int(2) DEFAULT '0',
                 email_sig varchar(255),
                 email_from varchar(100),
                 default_lang varchar(255),
                 PRIMARY KEY (config_id),
                 UNIQUE selected (selected))
         TYPE=MyISAM DEFAULT CHARSET=greek");

    mysql_query("INSERT INTO config VALUES (
                 '1',
                 '$intitule',
                 '1',
                 '1',
                 '1',
                 '0',
                 NULL,
                 '1',
                 '200',
                 '15',
                 '500',
                 NULL,
                 '0',
                 '$langFormula',
                 '$email',
                 '$langForumLanguage'
                 )");

    mysql_query("CREATE TABLE disallow (
                  disallow_id int(10) NOT NULL auto_increment,
                  disallow_username varchar(50),
                  PRIMARY KEY (disallow_id))
          TYPE=MyISAM DEFAULT CHARSET=greek");

    mysql_query("CREATE TABLE forum_access (
                 forum_id int(10) DEFAULT '0' NOT NULL,
                 user_id int(10) DEFAULT '0' NOT NULL,
                 can_post tinyint(1) DEFAULT '0' NOT NULL,
                 PRIMARY KEY (forum_id, user_id))
        TYPE=MyISAM DEFAULT CHARSET=greek");

    mysql_query("CREATE TABLE forum_mods (
                 forum_id int(10) DEFAULT '0' NOT NULL,
                 user_id int(10) DEFAULT '0' NOT NULL)
        TYPE=MyISAM DEFAULT CHARSET=greek");

    mysql_query("INSERT INTO forum_mods VALUES (
                 '1',
                 '1'
                 )");

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

    mysql_query("CREATE TABLE headermetafooter (
                 header text,
                 meta text,
                 footer text)
        TYPE=MyISAM DEFAULT CHARSET=greek");

    mysql_query("INSERT INTO headermetafooter VALUES (
                 '<center><a href=../$repertoire><img border=0 src=../$repertoire/image/logo.png></a></center>',
                 '',
                 ''
                 )");

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

    mysql_query("CREATE TABLE priv_msgs (
                msg_id int(10) NOT NULL auto_increment,
                from_userid int(10) DEFAULT '0' NOT NULL,
                to_userid int(10) DEFAULT '0' NOT NULL,
                msg_time varchar(20),
                poster_ip varchar(16),
                msg_status int(10) DEFAULT '0',
                msg_text text,
                PRIMARY KEY (msg_id),
                KEY msg_id (msg_id),
                KEY to_userid (to_userid))
        TYPE=MyISAM DEFAULT CHARSET=greek");

    mysql_query("CREATE TABLE ranks (
               rank_id int(10) NOT NULL auto_increment,
               rank_title varchar(50) NOT NULL,
               rank_min int(10) DEFAULT '0' NOT NULL,
               rank_max int(10) DEFAULT '0' NOT NULL,
               rank_special int(2) DEFAULT '0',
               rank_image varchar(255),
               PRIMARY KEY (rank_id),
               KEY rank_min (rank_min),
               KEY rank_max (rank_max))
        TYPE=MyISAM DEFAULT CHARSET=greek");

    mysql_query("CREATE TABLE sessions (
               sess_id int(10) unsigned DEFAULT '0' NOT NULL,
               user_id int(10) DEFAULT '0' NOT NULL,
               start_time int(10) unsigned DEFAULT '0' NOT NULL,
               remote_ip varchar(15) NOT NULL,
               PRIMARY KEY (sess_id),
               KEY sess_id (sess_id),
               KEY start_time (start_time),
               KEY remote_ip (remote_ip))
        TYPE=MyISAM DEFAULT CHARSET=greek");

    mysql_query("CREATE TABLE themes (
               theme_id int(10) NOT NULL auto_increment,
               theme_name varchar(35),
               bgcolor varchar(10),
               textcolor varchar(10),
               color1 varchar(10),
               color2 varchar(10),
               table_bgcolor varchar(10),
               header_image varchar(50),
               newtopic_image varchar(50),
               reply_image varchar(50),
               linkcolor varchar(15),
               vlinkcolor varchar(15),
               theme_default int(2) DEFAULT '0',
               fontface varchar(100),
               fontsize1 varchar(5),
               fontsize2 varchar(5),
               fontsize3 varchar(5),
               fontsize4 varchar(5),
               tablewidth varchar(10),
               replylocked_image varchar(255),
               PRIMARY KEY (theme_id))
        TYPE=MyISAM DEFAULT CHARSET=greek");

    mysql_query("INSERT INTO themes VALUES (
               '1',
               'Default',
               '#000000',
               '#FFFFFF',
               '#6C706D',
               '#2E4460',
               '#001100',
               'images/header-dark.jpg',
               'images/new_topic-dark.jpg',
               'images/reply-dark.jpg',
               '#0000FF',
               '#800080',
               '0',
               'sans-serif',
               '1',
               '2',
               '-2',
               '+1',
               '95%',
               'images/reply_locked-dark.jpg'
               )");

    mysql_query("INSERT INTO themes VALUES (
               '2',
               'Ocean',
               '#FFFFFF',
               '#000000',
               '#CCCCCC',
               '#9BB6DA',
               '#000000',
               'images/header.jpg',
               'images/new_topic.jpg',
               'images/reply.jpg',
                '#0000FF',
               '#800080',
               '0',
               'sans-serif',
               '1',
               '2',
               '-2',
               '+1',
               '95%',
               'images/reply_locked-dark.jpg'
               )");

    mysql_query("INSERT INTO themes VALUES (
                '3',
                'OCPrices.com',
                '#FFFFFF',
                '#000000',
                '#F5F5F5',
                '#E6E6E6',
                '#FFFFFF',
                'images/forum.jpg',
                'images/nouveausujet.jpg',
                'images/repondre.jpg',
               '#0000FF',
               '#800080',
                '1',
                'Arial,Helvetica, Sans-serif',
                '1',
                '2',
                '-2',
                '+1',
                '600',
                'images/reply_locked-dark.jpg'
                )");

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

    mysql_query("CREATE TABLE whosonline (
               id int(3) NOT NULL auto_increment,
               ip varchar(255),
               name varchar(255),
               count varchar(255),
               date varchar(255),
               username varchar(40),
               forum int(10),
               PRIMARY KEY (id))
        TYPE=MyISAM DEFAULT CHARSET=greek");

    mysql_query("CREATE TABLE words (
               word_id int(10) NOT NULL auto_increment,
               word varchar(100),
               replacement varchar(100),
               PRIMARY KEY (word_id))
        TYPE=MyISAM DEFAULT CHARSET=greek");


} else {

mysql_query("CREATE TABLE banlist (
                 ban_id int(10) NOT NULL auto_increment,
                 ban_userid int(10),
                 ban_ip varchar(16),
                 ban_start int(32),
                 ban_end int(50),
                 ban_time_type int(10),
                 PRIMARY KEY (ban_id),
                 KEY ban_id (ban_id))
         TYPE=MyISAM");

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

mysql_query("CREATE TABLE config (
                 config_id int(10) NOT NULL auto_increment,
                 sitename varchar(100),
                 allow_html int(2),
                 allow_bbcode int(2),
                 allow_sig int(2),
                 allow_namechange int(2) DEFAULT '0',
                 admin_passwd varchar(32),
                 selected int(2) DEFAULT '0' NOT NULL,
                 posts_per_page int(10),
                 hot_threshold int(10),
                 topics_per_page int(10),
                 allow_theme_create int(10),
                 override_themes int(2) DEFAULT '0',
                 email_sig varchar(255),
                 email_from varchar(100),
                 default_lang varchar(255),
                 PRIMARY KEY (config_id),
                 UNIQUE selected (selected))
         TYPE=MyISAM");

    mysql_query("INSERT INTO config VALUES (
                 '1',
                 '$intitule',
                 '1',
                 '1',
                 '1',
                 '0',
                 NULL,
                 '1',
                 '200',
                 '15',
                 '500',
                 NULL,
                 '0',
                 '$langFormula',
                 '$email',
                 '$langForumLanguage'
                 )");

    mysql_query("CREATE TABLE disallow (
                  disallow_id int(10) NOT NULL auto_increment,
                  disallow_username varchar(50),
                  PRIMARY KEY (disallow_id))
          TYPE=MyISAM");

    mysql_query("CREATE TABLE forum_access (
                 forum_id int(10) DEFAULT '0' NOT NULL,
                 user_id int(10) DEFAULT '0' NOT NULL,
                 can_post tinyint(1) DEFAULT '0' NOT NULL,
                 PRIMARY KEY (forum_id, user_id))
        TYPE=MyISAM");

    mysql_query("CREATE TABLE forum_mods (
                 forum_id int(10) DEFAULT '0' NOT NULL,
                 user_id int(10) DEFAULT '0' NOT NULL)
        TYPE=MyISAM");

    mysql_query("INSERT INTO forum_mods VALUES (
                 '1',
                 '1'
                 )");

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

    mysql_query("CREATE TABLE headermetafooter (
                 header text,
                 meta text,
                 footer text)
        TYPE=MyISAM");

    mysql_query("INSERT INTO headermetafooter VALUES (
                 '<center><a href=../$repertoire><img border=0 src=../$repertoire/image/logo.png></a></center>',
                 '',
                 ''
                 )");

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

    mysql_query("CREATE TABLE priv_msgs (
                msg_id int(10) NOT NULL auto_increment,
                from_userid int(10) DEFAULT '0' NOT NULL,
                to_userid int(10) DEFAULT '0' NOT NULL,
                msg_time varchar(20),
                poster_ip varchar(16),
                msg_status int(10) DEFAULT '0',
                msg_text text,
                PRIMARY KEY (msg_id),
                KEY msg_id (msg_id),
                KEY to_userid (to_userid))
        TYPE=MyISAM");

    mysql_query("CREATE TABLE ranks (
               rank_id int(10) NOT NULL auto_increment,
               rank_title varchar(50) NOT NULL,
               rank_min int(10) DEFAULT '0' NOT NULL,
               rank_max int(10) DEFAULT '0' NOT NULL,
               rank_special int(2) DEFAULT '0',
               rank_image varchar(255),
               PRIMARY KEY (rank_id),
               KEY rank_min (rank_min),
               KEY rank_max (rank_max))
        TYPE=MyISAM");

    mysql_query("CREATE TABLE sessions (
               sess_id int(10) unsigned DEFAULT '0' NOT NULL,
               user_id int(10) DEFAULT '0' NOT NULL,
               start_time int(10) unsigned DEFAULT '0' NOT NULL,
               remote_ip varchar(15) NOT NULL,
               PRIMARY KEY (sess_id),
               KEY sess_id (sess_id),
               KEY start_time (start_time),
               KEY remote_ip (remote_ip))
        TYPE=MyISAM");

    mysql_query("CREATE TABLE themes (
               theme_id int(10) NOT NULL auto_increment,
               theme_name varchar(35),
               bgcolor varchar(10),
               textcolor varchar(10),
               color1 varchar(10),
               color2 varchar(10),
               table_bgcolor varchar(10),
               header_image varchar(50),
               newtopic_image varchar(50),
               reply_image varchar(50),
               linkcolor varchar(15),
               vlinkcolor varchar(15),
               theme_default int(2) DEFAULT '0',
               fontface varchar(100),
               fontsize1 varchar(5),
               fontsize2 varchar(5),
               fontsize3 varchar(5),
               fontsize4 varchar(5),
               tablewidth varchar(10),
               replylocked_image varchar(255),
               PRIMARY KEY (theme_id))
        TYPE=MyISAM");

    mysql_query("INSERT INTO themes VALUES (
               '1',
               'Default',
               '#000000',
               '#FFFFFF',
               '#6C706D',
               '#2E4460',
               '#001100',
               'images/header-dark.jpg',
               'images/new_topic-dark.jpg',
               'images/reply-dark.jpg',
               '#0000FF',
               '#800080',
               '0',
               'sans-serif',
               '1',
               '2',
               '-2',
               '+1',
               '95%',
               'images/reply_locked-dark.jpg'
               )");

    mysql_query("INSERT INTO themes VALUES (
               '2',
               'Ocean',
               '#FFFFFF',
               '#000000',
               '#CCCCCC',
               '#9BB6DA',
               '#000000',
               'images/header.jpg',
               'images/new_topic.jpg',
               'images/reply.jpg',
                '#0000FF',
               '#800080',
               '0',
               'sans-serif',
               '1',
               '2',
               '-2',
               '+1',
               '95%',
               'images/reply_locked-dark.jpg'
               )");

    mysql_query("INSERT INTO themes VALUES (
                '3',
                'OCPrices.com',
                '#FFFFFF',
                '#000000',
                '#F5F5F5',
                '#E6E6E6',
                '#FFFFFF',
                'images/forum.jpg',
                'images/nouveausujet.jpg',
                'images/repondre.jpg',
               '#0000FF',
               '#800080',
                '1',
                'Arial,Helvetica, Sans-serif',
                '1',
                '2',
                '-2',
                '+1',
                '600',
                'images/reply_locked-dark.jpg'
                )");

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

    mysql_query("CREATE TABLE whosonline (
               id int(3) NOT NULL auto_increment,
               ip varchar(255),
               name varchar(255),
               count varchar(255),
               date varchar(255),
               username varchar(40),
               forum int(10),
               PRIMARY KEY (id))
        TYPE=MyISAM");

    mysql_query("CREATE TABLE words (
               word_id int(10) NOT NULL auto_increment,
               word varchar(100),
               replacement varchar(100),
               PRIMARY KEY (word_id))
        TYPE=MyISAM");

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
        type tinyint(4) UNSIGNED default '1' NOT NULL,
                random smallint(6) default '0' NOT NULL,
        active tinyint(4) default NULL,
        PRIMARY KEY  (id))
        TYPE=MyISAM DEFAULT CHARSET=greek");

mysql_query("INSERT INTO exercices VALUES ( '1', '$langExerciceEx', '$langAntique', '1', '0', NULL)");

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
        type tinyint(4) UNSIGNED default '1' NOT NULL,
                random smallint(6) default '0' NOT NULL,
        active tinyint(4) default NULL,
        PRIMARY KEY  (id))
        TYPE=MyISAM");

mysql_query("INSERT INTO exercices VALUES ( '1', '$langExerciceEx', '$langAntique', '1', '0', NULL)");

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

if (mysql_version()) {

mysql_query("CREATE TABLE accueil (
               id int(11) NOT NULL auto_increment,
               rubrique varchar(100), lien varchar(255),
               image varchar(100),
               visible tinyint(4),
               admin varchar(200),
               address varchar(120),
               define_var VARCHAR(50),
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
               define_var VARCHAR(50),
               PRIMARY KEY (id))
		TYPE=MyISAM");


}


    // Content accueil (homepage) Table
mysql_query("INSERT INTO accueil VALUES (

	1,
	'$langAgenda',
	'../../modules/agenda/agenda.php',
	'../../../images/agenda.png',
	'1',
	'0',
	'../../../images/pastillegris.png',
	'MODULE_ID_AGENDA')");

    mysql_query("INSERT INTO accueil VALUES (
               2,
               '$langLinks',
               '../../modules/link/link.php',
               '../../../images/liens.png',
               '1',
               '0',
               '../../../images/pastillegris.png',
               'MODULE_ID_LINKS'
               )");

    mysql_query("INSERT INTO accueil VALUES (
               3,
               '$langDoc',
               '../../modules/document/document.php',
               '../../../images/documents.png',
               '1',
               '0',
               '../../../images/pastillegris.png',
               'MODULE_ID_DOCS'
               )");

	//videos >> to be removed!
	mysql_query("INSERT INTO accueil VALUES (

               4,
               '$langVideo',
               '../../modules/video/video.php',
               '../../../images/videos.png',
               '0',
               '0',
               '../../../images/pastillegris.png'
               )");

           mysql_query("INSERT INTO accueil VALUES (
               5,
               '$langWorks',
               '../../modules/work/work.php',
               '../../../images/works.png',
               '0',
               '0',
               '../../../images/pastillegris.png',
               'MODULE_ID_ASSIGN'
               )");

    mysql_query("INSERT INTO accueil VALUES (
               6,
               '$langVideoLinks',
               '../../modules/video/videolinks.php',
               '../../../images/videos.png',
               '0',
               '0',
               '../../../images/pastillegris.png',
               'MODULE_ID_VIDEO'
               )");

    mysql_query("INSERT INTO accueil VALUES (
               7,
               '$langAnnouncements',
               '../../modules/announcements/announcements.php',
               '../../../images/valves.png',
               '1',
               '0',
               '../../../images/pastillegris.png',
               'MODULE_ID_ANNOUNCE'
               )");


	mysql_query("INSERT INTO accueil VALUES (
		8,
		'$langUsers',
		'../../modules/user/user.php',
		'../../../images/membres.png',
		'0',
		'0',
		'../../../images/pastillegris.png',
		'MODULE_ID_USERS'
		)");


    mysql_query("INSERT INTO accueil VALUES (
               9,
               '$langForums',
               '../../modules/phpbb/index.php',
               '../../../images/forum.png',
               '0',
               '0',
               '../../../images/pastillegris.png',
               'MODULE_ID_FORUM'
               )");

    mysql_query("INSERT INTO accueil VALUES (
               10,
               '$langExercices',
               '../../modules/exercice/exercice.php',
               '../../../images/quiz.png',
               '0',
               '0',
               '../../../images/pastillegris.png',
               'MODULE_ID_EXERCISE'
               )");


	mysql_query("INSERT INTO accueil VALUES (
		15,
		'$langGroups',
		'../../modules/group/group.php',
		'../../../images/group.png',
		'0',
		'0',
		'../../../images/pastillegris.png',
		'MODULE_ID_GROUPS'
		)");

	mysql_query("INSERT INTO accueil VALUES (
		16,
		'$langDropBox',
		'../../modules/dropbox/index.php',
		'../../../images/dropbox.png',
		'0',
		'0',
		'../../../images/pastillegris.png',
		'MODULE_ID_DROPBOX'
		)");



#####################ACCUEIL - PROF ONLY ######################################

    mysql_query("INSERT INTO accueil VALUES (
               11,
               '$langStatistics',
               '../../modules/stat/index2.php?table=stat_accueil&reset=0&period=jour',
               '../../../images/statistiques.png',
               '0',
               '1',
               '',
               'MODULE_ID_STAT'
               )");

                mysql_query("INSERT INTO accueil VALUES (
               12,
               '$langAddPageHome',
               '../../modules/import/import.php?',
               '../../../images/page.png',
               '0',
               '1',
               '',
               'MODULE_ID_IMPORT'
               )");

    mysql_query("INSERT INTO accueil VALUES (
               13,
               '$langLinkSite',
               '../../modules/external_module/external_module.php?',
               '../../../images/npage.png',
               '0',
               '1',
               '',
               'MODULE_ID_EXTERNAL'
               )");

    mysql_query("INSERT INTO accueil VALUES (
               14,
               '$langModifyInfo',
               '../../modules/course_info/infocours.php?',
               '../../../images/referencement.png',
               '0',
               '1',
               '',
               'MODULE_ID_COURSEINFO'
               )");

	mysql_query("INSERT INTO accueil VALUES (
	19,
	'$langChat',
	'../../modules/chat/chat.php',
	'../../../images/forum.png',
	'0',
	'0',
	'../../../images/pastillegris.png',
	'MODULE_ID_CHAT'
	)");

    mysql_query("INSERT INTO accueil VALUES (
               20,
               '$langCourseDesc',
               '../../modules/course_description/',
               '../../../images/info.png',
               '1',
               '0',
               '../../../images/pastillegris.png',
               'MODULE_ID_DESCRIPTION'
               )");

    mysql_query("INSERT INTO accueil VALUES (
               21,
               '$langLearnPath',
               '../../modules/learnPath/learningPathList.php',
               '../../../images/learnpath.gif',
               '1',
               '0',
               '../../../images/pastillegris.png',
               'MODULE_ID_LP'
               )");


    mysql_query("INSERT INTO accueil VALUES (
               22,
               '$langToolManagement',
               '../../modules/course_tools/course_tools.php',
               '../../../images/course_tools.gif',
               '0',
               '1',
               '../../../images/pastillegris.png',
               'MODULE_ID_TOOLADMIN'
               )");

    mysql_query("INSERT INTO accueil VALUES (
               23,
               '$langUsage',
               '../../modules/usage/usage.php',
               '../../../images/usage.gif',
               '0',
               '1',
               '../../../images/pastillegris.png',
               ''
               )");


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
    visibility char(1) DEFAULT 'v' NOT NULL,
    comment varchar(255),
    PRIMARY KEY (id))
    TYPE=MyISAM DEFAULT CHARSET=greek");

############################# VIDEO ###########################################

    mysql_query("CREATE TABLE video (
               id int(11) NOT NULL auto_increment,
               url varchar(200),
               titre varchar(200),
               description text,
               PRIMARY KEY (id))
        TYPE=MyISAM DEFAULT CHARSET=greek");

################################# VIDEO LINKS ################################

        mysql_query("CREATE TABLE videolinks (
               id int(11) NOT NULL auto_increment,
               url varchar(200),
               titre varchar(200),
               description text,
               visibility CHAR(1) DEFAULT '1' NOT NULL,
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

############################## INTRODUCTION #######################################

    mysql_query("CREATE TABLE introduction (
               id int(11) NOT NULL default '1',
               texte_intro text,
               PRIMARY KEY (id))
        TYPE=MyISAM DEFAULT CHARSET=greek");

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
              `contentType` enum('CLARODOC','DOCUMENT','EXERCISE','HANDMADE','SCORM','LABEL','COURSE_DESCRIPTION') NOT NULL,
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

############################## INTRODUCTION #######################################

    mysql_query("CREATE TABLE introduction (
               id int(11) NOT NULL default '1',
               texte_intro text,
               PRIMARY KEY (id))
        TYPE=MyISAM");

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
    mysql_query("INSERT INTO liste_domaines VALUES ( '38', 'cd', 'Congo, (Rιpublique dιmocratique du)')");
    mysql_query("INSERT INTO liste_domaines VALUES ( '40', 'cg', 'Congo')");
    mysql_query("INSERT INTO liste_domaines VALUES ( '41', 'ch', 'Suisse')");
    mysql_query("INSERT INTO liste_domaines VALUES ( '44', 'cl', 'Chili')");
    mysql_query("INSERT INTO liste_domaines VALUES ( '45', 'cm', 'Cameroun')");
    mysql_query("INSERT INTO liste_domaines VALUES ( '46', 'cn', 'Chine')");
    mysql_query("INSERT INTO liste_domaines VALUES ( '47', 'co', 'Colombie')");
    mysql_query("INSERT INTO liste_domaines VALUES ( '48', 'cr', 'Costa Rica')");
    mysql_query("INSERT INTO liste_domaines VALUES ( '49', 'cu', 'Cuba')");
    mysql_query("INSERT INTO liste_domaines VALUES ( '53', 'cz', 'Tchιque (Rιpublique)')");
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
    mysql_query("INSERT INTO liste_domaines VALUES ( '197', 'sn', 'Sιnιgal')");
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

##########################################################################
############### UPDATE BASE ICAMPUS #####################################
##########################################################################

mysql_select_db("$mysqlMainDb");

// here we must add 2 fields
    mysql_query("INSERT INTO cours SET
        cours_id = '',
        code = '$code',
        languageCourse = '$languageCourse',
        intitule = '$intitule',
        description = '$description',
        faculte = '$faculte',
        visible = '2',
        cahier_charges = '',
        scoreShow = '',
        titulaires = '$titulaires',
        fake_code = '$code',
        type = '$type'");

    mysql_query("INSERT INTO cours_user SET
        code_cours = '$repertoire',
        user_id = '$uid',
        statut = '1',
        role = '$langProfessor',
        tutor='1'");

mysql_query("INSERT INTO cours_faculte VALUES ( '', '$faculte', '$repertoire')");

###########################################################################
################ CREATE DIRECTORIES #######################################
###########################################################################

    umask(0);
    mkdir("../../courses/$repertoire", 0777);
    mkdir("../../courses/$repertoire/image", 0777);
    mkdir("../../courses/$repertoire/document", 0777);
     mkdir("../../courses/$repertoire/page", 0777);
     mkdir("../../courses/$repertoire/video", 0777);
    mkdir("../../courses/$repertoire/work", 0777);
    mkdir("../../courses/$repertoire/group", 0777);
    mkdir("../../courses/$repertoire/temp", 0777);
    mkdir("../../courses/$repertoire/scormPackages", 0777);


    $titou='$dbname';

####################################################################
################CREER PAGE ACCUEIL #################################
####################################################################
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

echo "<tr bgcolor=$color2>
    <td colspan=3>
    <font face=\"arial, helvetica\" size=2>
    $langJustCreated $repertoire<br><br><br>
    <a href='../../courses/$repertoire/index.php'>$langEnter</a><br>
    </font><br>
    </td></tr>";
 } // else

} // if all fields fulfilled

echo "</table>";

######################   Function list  #######################################

/*****************************************************************************************
*                                 DefaultScoring Function
******************************************************************************************
*
* Goal : compute a default scoring for a grouped multiple choise.
* ----
*
*
*******************************************************************************************/
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

}//End of function DefaultScoring


>>>>>>> 1.10
?>
</body>
</html>