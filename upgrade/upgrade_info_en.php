<?php header('Content-Type: text/html; charset=UTF-8');
?><!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv=Content-Type content="text/html; charset=UTF-8">
<title>Upgrade Instructions Open eClass 2.3</title>
<link rel="stylesheet" type="text/css" href="upgrade_info.css">
</head>
<body>
<h2>Upgrade Instructions of Open eClass 2.3</h2>
<h3>The Upgrade Process </h3>
<p>The new Open eClass version 2.3 retains backward compatibility (with previous versions). For that reason, you can upgrade an already installed platform from prior versions (1.7, 2.0, 2.1.x, 2.2) to the current 2.3 easily and quickly, following the upgrade instructions provided below. Keeping a safety record of the course contents and the database before beginning to upgrade is recommended.</p>
<div class="note">
<p><b>WARNING!</b>
<ul>
<li>Please ensure that during the platform's upgrade process there is no access to Open eClass courses by the platform's users and the platform's database is not accessible by anyone  
</li>
<li>Also check the platform's version that is already installed in your server, by following the link 'Platform's Information' in the home page. In order to the upgrade process to be possible the already installed platform should be either version >=1.7. For previous versions (1.5, 1.6) you need to upgrade first in 1.7 following the instructions at the last section of this manual 'Upgrade to Open eClass 2.2 from older versions 1.5, 1.6'.
</li>
<li>
Before the upgrade procedure please backup both the eCourses files and the platform's Database.
</li>
</ul>
</div>
<p>
Then, follow the steps below.
</p>
<ul>
  <li class="head">1st Step</li>
  <li><a href="#unix">For Unix / Linux computers (e.g. Solaris, Redhat, CentOS, Deliian, Suse, Uliuntu etc)</a></li>
  <li><a href="#win">For Ms Windows computers (Windows2000, WindowsXP, Windows2003, Windows Vista, Windows 2007)</a></li>
</ul>
<ul>
  <li class="head">2nd Step</li>
  <li><a href="#dliase">Dataliase Upgrade</a></li>
</ul>
<ul>
  <li class="head">3rd Step</li>
  <li><a href="#after">Successful Upgrade Check</a></li>
</ul>
<ul>
  <li class="head">Optional Further Configuration</li>
  <li><a href="#other">Optional Further Configuration</a></li>
</ul>
<ul>
  <li class="head">Upgrading from previous versions (1.5, 1.6)</li>
  <li><a href="#oldest_unix">For Unix / Linux computers</a></li>
  <li><a href="#oldest_win">For Ms Windows computers</a></li>
</ul>
	
<br>
<hr width='80%'><br>

<h3 id="unix">1st Step: Upgrading in Unix / Linux computers</h3>
<p>All operations presuppose you have the administrator's rights (root) on your computer.</p>
<p>The following example presumes that the eClass platform is already installed on directory <code>/var/www/html</code>.</p>
<p>Due to some changes included by the new (2.3) Open eClass version you will have to delete the old and install the new one. To make sure that you old configuration remain intact you must do the following actions:</p>
<p>We consider that you have downloaded <b>openeclass-2.2.tar.gz</b> on the <code>/tmp
  </code> directory. </p>
<ul>
  <li>Go to the directory you have installed in eClass. e.g.
    <pre>cd /var/www/html</pre>
  <li> Move the configuration file (<em>eclass/config/config.php</em>)
    in another temporary directory (e.g. to directory <em>/tmp</em>)
        <pre>mv /var/www/html/eclass/config/config.php /tmp</pre>
  </li>
  <li>If you have courses in which you have used the <em>Chat</em> subsystem then move the appropriate chat files as well.
    These are found in the directory eclass/modules/chat/ with the form course_code.chat.txt
    e.g.
    <pre>mv /var/www/html/eclass/modules/chat/*.txt /tmp</pre>
  </li>
  <li>Delete all the directories except courses and config
     e.g.
    <pre>cd /var/www/html/eclass/
rm -rf images/ include/ info/ install/ manuals/ template/ modules/ </pre>
  </li>
  <li>Untar openeclass-2.3.tar.gz in a temporary directory (/tmp) e.g.
    <pre>tar xzvf /tmp/openeclass-2.2.tar.gz</pre>

	Then copy from the temporary directory /tmp/openeclass21 all of its contents in the installation directory e.g.
	<pre>cp -a /tmp/openeclass22/*  /var/www/html/eclass/</pre>
	
	So with the above steps you have replaced directory eclass, with the new one.
  </li>
  <li>Then move file <em>config.php</em> to directory <em>config</em>.
    e.g.
    <pre>mv /tmp/config.php /var/www/html/eclass/config/</pre>
  <li>Move to the original place the chat files. e.g.
    <pre>mv /tmp/*.txt /var/www/html/eclass/modules/chat/</pre>
  </li>
  <li>Correct (if necessary) the files and sub-directories permissions: (supposing that the apache is running as www-data) 

    <pre>cd /opt/eclass
chown -R www-data *
find ./ -type f -exec chmod 664 {} \;
find ./ -type d -exec chmod 775 {} \;
</pre>
</li>
</ul>

<p>Having completed the previous steps, you will have installed the new eClass version (eClass 2.3) files successfully. Then, move on to  <a href="#dbase">the second step</a> in order to upgrade the platform databases.</p>

<h3 id="win">1st Step: Upgrading in Ms Windows Computers</h3>
<p>The following example presupposes that eClass has already been installed to directory <code>C:\Program Files\Apache\htdocs\</code> and that you have downloaded <b>openeclass-2.3.zip</b>.</p>  
<p>Due to several changes included by the new (2.3) Open eClass version you will have to delete the old and install the new one. To make sure that you old configuration remain intact you must do the following actions:</p>
<ul>
  <li>Move to the eClass installed folder. e.g.<code>C:\Program
    Files\Apache\htdocs</code></li>
  <li>Move the configuration file (<tt>C:\Program Files\Apache\htdocs\eclass\config\config.php</tt>)
    into another temporary folder in your desktop (e.g. from <tt>C:\Program
    Files\Apache\htdocs\eclass\config\</tt> to <tt>C:\Documents
    and Settings\Administrator\Desktop\</tt>).</li>
  <li>If you have courses in which you have used subsystem 'Chat' then move the appropriate chat files as well.
    These are found in the folder <tt>C:\Program Files\Apache\htdocs\eclass\modules\chat\</tt> in files of the form <em>course_tt</em>.chat.txt 
  </li>
  <li>Go to the <em>eclass</em> folder (e.g.<tt>C:\Program Files\Apache\htdocs\eclass\</tt>) and delete all folders except <em>courses</em>, <em>video</em> and <em>config</em>.</li>
  <li>Unzip openeclass-2.3.zip to a temporary folder on the desktop. e.g.
    <code>C:\Documents and Settings\Administrator\Desktop\eclass17</code>
	After that rename the temporary folder eclass21 to eclass and copy it along with its contents (files and documents). Then, open the file that includes eClass installation, e.g. 
 <code>C:\Program
    Files\Apache\htdocs\</code> 
	and paste it. In that way, the eclass files is replaced by the new ones. 
  </li>
  <li>Move to the original place the chat files. e.g. in <code>C:\Program Files\Apache\htdocs\eclass\modules\chat\</code></li>
  <li>Delete the desktop temporary folder.</li>
</ul>
<p>As soon as the above have been completed, you will have installed the new eClass version files (eClass 2.3) successfully. Then, follow the  <a href="#dbase">second step</a> so as to upgrade the platform database.
</p>

<h3 id="dbase">2nd Step: Database Upgrade</h3>
<div class="note">
<p>Before running the database upgrade script, make sure that MySQL is not operating in strict mode. In order to establish that, check if the parameter <tt>--sql-mode</tt> (in file <em>my.cnf</em> ή <em>my.ini</em> for UNIX / Windows correspondingly) has been set. If it is set (e.g. <tt>--sql-mode=STRICT_TRANS_TABLES</tt>
    or <tt>--sql-mode=STRICT_ALL_TABLES</tt>) then you need to modify it to null (<tt>--sql-mode=""</tt>).
    </p>
</div>
<div class="note">
  <p><b>For Unix/Linux systems only: </b>The process of platform database upgrade includes changes to <em> config.php</em>. As a result, you will temporarily need to change your access rights on the <em>config.php</em> file and the /config directory to read-write (chmod 664).
  </div>
<p>Then enter the following URL on your browser:</p>
<code>http://(url of eclass)/upgrade/</code>
<p>You will be prompted for the username and password of the platform administrator. After providing your personal details, you will be asked to change/correct contact details, as well as students' registration mode on the platform (free or applied registration). The database upgrade will begin afterwards. You will see several messages concerning your working progress. Probably you will not see any false messages. Note that depending on the number and content of courses, it is possible that the process will last for a long time. 
</p>
<p>In the opposite case (namely if error messages occur), then it is possible for a course not to be operating properly. Such error messages may occur if you have altered the structure of an eclass database table. Note (if possible) the accurate error message you saw.</p>
<p>If you face any problems with any course after the upgrade, contact us (<a href="info@openeclass.org">mailto:info@openeclass.org</a>).</p>

<h3 id="after">3rd Step: Successful Upgrade Check</h3>
<p>In order to make sure that the platform has been upgraded, login as administrator and click to "Admin tools". Among other things, version 2.3 has to be indicated. You can alternatively click on the "Platform Information" link on the homepage. Version 2.3 of the platform will be indicated.

<p>You are ready! The upgrade process has been completed successfully. </p>
<p>If you want to see the new features of the new version, go to <a href="CHANGES_en.txt">CHANGES.txt</a> text file. Read forward for further additional regulations (HTTPS, Latex etc).</p>

<h3 id="other">Optional Further Configurations</h3>
<ul>
<li>In the <em>config.php</em> file, the <em>close_user_registration</em> variable, which is FALSE by definition, will be defined. Changing the value to <em>TRUE</em>, registration of users with 'student' rights will not be free anymore. Users will have to follow a process similar to the teacher account creation process, namely filling in a student account application form, in order to obtain a platform account. The application will be examined by the administrator who either approves of it and opens an account, or rejects it.  
</li>
<li>
<p>If you want to modify any message of platform then proceed with the following actions:
Create a file of type .php with name <em>english.inc.php</em> (or <em>greek.inc.php</em>) and place it in directory <em>(eclass path)/config/</em>. Find the varible name which contains the message you wish to change and assing it the new message. e.g. If you want to change message <tt>$langAboutText = "The platform version is";</tt> create the file <em>english.inc.php</em> in the directory (eclass path)/config/ with the following contents:</p>
<pre>
&lt;?php
$langAboutText = "Version is";
</pre>
<p>This way, you will preserve custom messages in future upgrades of the platform.</p>
<p>
 You can modify the names of the basic roles of the users of the platform by redefining in these files the message variables found in (eClass_path)/modules/lang/greek/common.inc.php.
</p>
<p>
You can add text (e.g. information, links, etc.) to the left and right sidebars of the platform homepage by assigning the variables <em>$langExtrasLeft</em> and <em>$langExtrasRight</em> respectively.
</p>
</li>
<li>Open eClass supports mathematical symbols in the <em>Exercises</em>, <em>Forums</em> and <em>Announcements</em> subsystems. In <em>Exercises</em> you can add math symbols in the "Exercise Description" field when creating or modyfying a new exercise, and in "Comment" when adding or modifying a question, in <em>Forums</em> when you compose a new message or reply to an existing one, and in <em>Announcements</em> when a new announcement is created. Math symbols must be enclosed in <em>[m]</em> and <em>[/m]</em> tags.
E.g. when you type 
<pre>
[m]sqrt{x-1}[/m]
</pre>
the square root of x-1 will appear. Mathematical symbols syntax can be found in <a href="../manuals/PhpMathPublisherHelp.pdf"><em>the PhpMathPublisher help file</em></a>. Older versions used the <em>&lt;m></em> and <em>&lt;/m></em> which are still supported, although using the newer tags in brackets is recommended.
<li>
To use the platform with a web server with SSL support (e.g. https://eclass.gunet.gr), you define the <em>urlSecure</em> variable in <em>config.php</em>. For example: <code>$urlSecure = "https://eclass.gunet.gr"</code> 
</li>
</ul>
<p>Further information for these actions can be found in the Administrator's manual (linked from the platform Admin Tool).
</p>

<h3>Upgrading from Older Versions (1.5,  1.6) </h3>
<p>For upgrading from older versions 1.5, 1.6 you have to do some changes manually. Starting from the course directories is now stored in a new directory named <em>courses</em>. Also the configurations file location (<code>config.php</code>) has changed, and is now included in a new directory named <em>config</em>. So, in order not to lose your previous configuration and all your eCourses files, please follow the instructions below. 
</p>

<h4 id="oldest_unix">For Unix / Linux Computers</h4>
<ul>
<li>Go to the directory you have installed in eClass. e.g.
    <pre>cd /var/www/html</pre>
<li> Move the configuration file (<em>eclass/claroline/include/config.php</em>)
    in another temporary directory (e.g. to directory <em>/tmp</em>)
    <pre>mv /var/www/html/eclass/claroline/include/config.php /tmp</pre>
  </li>
<li>If you have courses in which you have used subsystem 'Chat' then move the appropriate chat files as well.
    These are found in directory  eclass/claroline/chat/ with the form course_code.chat.txt
    e.g.
    <pre>mv /var/www/html/eclass/claroline/chat/*.txt /tmp</pre>
  </li>
  <li>Delete the directory claroline with all its subdirectories. e.g.
    <pre>cd /var/www/html/eclass/
rm -rf claroline/</pre>
  </li>
  <li>Unzip eclass-1.7.3.tar.gz to a temporary directory (/tmp) e.g.
    <pre>tar xzvf /tmp/eclass-1.7.3.tar.gz
	</pre>
Then from the directory /tmp/eclass17 copy all of its contents to the installation directory e.g..
<pre>cp -a  /tmp/eclass17/*  /var/www/html/eclass/</pre>
That way, the eclass directory is substituted by the new version.
  </li>
  <li>Go the installation directory of eClass and make the following directories
     <em>config</em> και <em>courses</em>. e.g.
    <pre>cd /var/www/html/eclass
mkdir config
mkdir courses</pre>
  </li>
  <li>Move file <em>config.php</em> to directory <em>config</em>.
    π.χ.
    <pre>mv /tmp/config.php /var/www/html/eclass/config/</pre>
  <li>Move the course directories in directory <em>courses</em>. (e.g. in case there are courses with codes
     TMA100, TMA101)
    <pre>cd /var/www/html/eclass
	mv TMA* ./courses/</pre>
  </li>
  <li>Restore the chat files to their initial position. e.g. 
    <pre>mv /tmp/*.txt /var/www/html/eclass/modules/chat/</pre>
  </li>
  <li>Correct (if needed) the permissions of files and directories (e.g. if apache user is www-data)
    <pre>cd /opt/eclass
chown -R www-data *
find ./ -type f -exec chmod 664 {} \;
find ./ -type d -exec chmod 775 {} \;
</pre>
  </li>
</ul>

<h4 id="oldest_win">For Ms Windows Computers</h4>
<ul>
  <li>Go to eClass folder e.g. <code>C:\Program
    Files\Apache\htdocs</code></li>
  <li>Move configuration file <code>C:\Program Files\Apache\htdocs\eclass\claroline\include\config.php</code>
    to another temporary folder in your desktop e.g. from <code>C:\Program
    Files\Apache\htdocs\eclass\claroline\include\</code> to folder <code>C:\Documents
    and Settings\Administrator\Desktop\</code></li>
  <li>If you have courses in which you have used the <em>Chat</em> subsystem, then move the appropriate chat files as well. These are found in <code>C:\Program Files\Apache\htdocs\eclass\claroline\chat\</code> with names in the form <em>course_code</em>.chat.txt</li>
  <li>Go to folder of eclass <code>C:\Program
    Files\Apache\htdocs\eclass\</code> and delete folder <em>claroline</em>
    with all of its subdirectories.</li>
  <li>Unzip eclass-1.7.3.zip to a temporary folder in your desktop.
    e.g. <code>C:\Documents and Settings\Administrator\Desktop\eclass17</code>.
    Then rename temporary folder eclass17 to eclass and copy with all of its subfolders.
	Then go to folder of eclass, e.g. <code>C:\Program
    Files\Apache\htdocs\</code> and paste them. With that way eclass folder is replaced with the new version.</li>
  <li>Go to folder of eClass and make two new folders 
    <em>config</em> and <em>courses</em>.</li>
  <li>Move config.php file, in newly created folder config 
    e.g. <code>C:\Program Files\Apache\htdocs\eclass\config\</code></li>
  <li>Move courses directories to newly created folder <em>courses</em>
    e.g. <code>C:\Program Files\Apache\htdocs\eclass\courses\</code>
  </li>
  <li>Restore previous chat files in its initial folder
     <code>C:\Program Files\Apache\htdocs\eclass\modules\chat\</code></li>
  <li>Finally delete from your desktop the temporary created folder.</li>
</ul>

</body>
</html>
