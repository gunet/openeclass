<?php header('Content-Type: text/html; charset=UTF-8');
?><!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<META http-equiv=Content-Type content="text/html; charset=UTF-8">
<title>Upgrade Instructions for Open eClass 2.5</title>
<link href="../template/classic/theme.css" rel="stylesheet" type="text/css" />
<style type="text/css">
p {
 text-align: justify;
}
</style>
  </head>
  <body>
  
  <div id="container" style="padding: 30px;">
  <div id="header"> 

<a href="http://www.openeclass.org/" title="Open eClass" class="logo"></a></div>
    
<p class="title1">Upgrade Instructions for Open eClass 2.5</p>

<p>The new version of Open eClass (2.5) retains backward compatibility (with previous versions).
For that reason, you can upgrade an already installed platform from prior versions to the current 2.5 easily and quickly,
following the upgrade instructions provided below.
Keeping a safety record of the course contents and the database before beginning to upgrade is recommended.</p>
<div class="alert1">
<ul>
<li>Please ensure that during the platform's upgrade process there is no access to Open eClass courses by the platform's users and the platform's database is not accessible by anyone  
</li>
<li>Also check the platform's version that is already installed in your server, by following the link 'Platform Identity'
in the home page. In order to the upgrade process (which is described below) to be possible,
the already installed platform should be either version &gt;=2.0.
For previous versions follow the instructions at the last section of this manual.
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
  1st Step
  <li><a href="#unix">For Unix / Linux computers (e.g. Solaris, Redhat, CentOS, Debian, Suse, Ubuntu etc)</a></li>
  <li><a href="#win">For Ms Windows computers (Windows2000, WindowsXP, Windows2003, Windows Vista, Windows 7, Windows2008)</a></li>
</ul>
<ul>
  <p>2nd Step</p>
  <li><a href="#dbase">Database Upgrade</a></li>
</ul>
<ul>
  <p>3rd Step</p>
  <li><a href="#after">Successful Upgrade Check</a></li>
</ul>
<ul>
  <p>4th Step</p>
  <li><a href="#other">Optional Further Configuration</a></li>
</ul>
<p><a href="#oldest">Upgrading from previous versions (&lt;=0)</a></p>
<br />

<p class="title1" id="unix">1st Step: Upgrading in Unix / Linux computers</p>
<p>All operations presuppose you have the administrator's rights (root) on your computer.
The following example presumes that the eClass platform is already installed on directory <code>/var/www/html</code>.</p>
<p>Due to some changes introduced by the new version (2.5) you will have to delete the old and install the new one.
To make sure that you old configuration remain intact you must do the following actions:</p>
<p>We consider that you have downloaded <b>openeclass-2.5.tar.gz</b> on the <code>/tmp
  </code> directory. </p>
<ul>
  <li>Go to the directory you have installed in eClass. e.g.
    <pre>cd /var/www/html</pre>
  <li> Move the configuration file (<em>eclass/config/config.php</em>)
    in another temporary directory (e.g. to directory <em>/tmp</em>)
        <pre>mv /var/www/html/eclass/config/config.php /tmp</pre>
  </li>
  <li>Delete all the directories except courses and config
     e.g.
    <pre>cd /var/www/html/eclass/
rm -rf images/ include/ info/ install/ manuals/ template/ modules/ </pre>
  </li>
  <li>Unzip and untar openeclass-2.5.tar.gz in a temporary directory (/tmp) e.g.
    <pre>tar xzvf /tmp/openeclass-2.5.tar.gz</pre>

	Then copy from the temporary directory /tmp/openeclass-2.5 all of its contents in the installation directory e.g.
	<pre>cp -a /tmp/openeclass-2.5/*  /var/www/html/eclass/</pre>
	
	So with the above steps you have replaced directory eclass, with the new one.
  </li>
  <li>Then move file <em>config.php</em> to directory <em>config</em>.
    e.g.
    <pre>mv /tmp/config.php /var/www/html/eclass/config/</pre>
  <li>Correct (if necessary) the files and sub-directories permissions: (supposing that the apache is running as user www-data) 

    <pre>cd /opt/eclass
chown -R www-data *
find ./ -type f -exec chmod 664 {} \;
find ./ -type d -exec chmod 775 {} \;
</pre>
</li>
</ul>

<p>Having completed the previous steps, you will have installed the new eClass version (eClass 2.5) files successfully.
Then, move on to  <a href="#dbase">the second step</a> in order to upgrade the platform databases.</p>
<br />
<p class="title1" id="win">1st Step: Upgrading in Ms Windows Computers</h3>
<p>The following example presupposes that eClass has already been installed to directory <code>C:\Program Files\Apache\htdocs\</code> and that you have downloaded <b>openeclass-2.5.zip</b>.</p>  
<p>Due to several changes included by the new version (2.5) you will have to delete the old and install the new one.
To make sure that you old configuration remain intact you must do the following actions:</p>
<ul>
  <li>Move to the eClass installed folder. e.g.<code>C:\Program
    Files\Apache\htdocs</code></li>
  <li>Move the configuration file (<tt>C:\Program Files\Apache\htdocs\eclass\config\config.php</tt>)
    into another temporary folder in your desktop (e.g. from <tt>C:\Program
    Files\Apache\htdocs\eclass\config\</tt> to <tt>C:\Documents
    and Settings\Administrator\Desktop\</tt>).</li>
  <li>Go to the <em>eclass</em> folder (e.g.<tt>C:\Program Files\Apache\htdocs\eclass\</tt>) and delete all folders except <em>courses</em>, <em>video</em> and <em>config</em>.</li>
  <li>Unzip openeclass-2.5.zip to a temporary folder on the desktop. e.g.
    <code>C:\Documents and Settings\Administrator\Desktop\eclass25</code>
	After that rename the temporary folder eclass25 to eclass and copy it along with its contents (files and documents). Then, open the file that includes eClass installation, e.g. 
 <code>C:\Program
    Files\Apache\htdocs\</code> 
	and paste it. In that way, the eclass files is replaced by the new ones. 
  </li>
  <li>Delete the desktop temporary folder.</li>
</ul>
<p>As soon as the above have been completed, you will have installed the new eClass version files (eClass 2.5) successfully. 
        Then, follow the  <a href="#dbase">second step</a> so as to upgrade the platform database.
</p>
<br />
<p class="title1" id="dbase">2nd Step: Database Upgrade</p>
<div class="info">
  <p><b>For Unix/Linux systems only: </b>The process of platform database upgrade includes changes to <em> config.php</em>. As a result, you will temporarily need to change your access rights on the <em>config.php</em> file and the /config directory to read-write (chmod 664).
  </div>
<p>Enter the following URL on your browser:</p>
<code>http://(url of eclass)/upgrade/</code>
<p>You will be prompted for the username and password of the platform administrator.
After providing them, you will be asked to change/correct contact details,
as well as students' registration mode on the platform (free or applied registration).
The database upgrade will begin afterwards. You will see several messages concerning your working progress.
Probably you will not see any false messages.
Note that depending on the number and content of courses, it is possible that the process will last for a long time. 
</p>
<p>In the opposite case (namely if error messages occur), then it is possible for a course not to be operating properly.
Such error messages may occur if you have altered the structure of an eclass database table.
Note (if possible) the accurate error message you saw.</p>
<p>If you face any problems with any course after the upgrade, contact us (<a href="info@openeclass.org">mailto:info@openeclass.org</a>).</p>
<br />
<p class="title1" id="after">3rd Step: Successful Upgrade Check</p>
<p>In order to make sure that the platform has been upgraded, login as administrator and click to "Admin tools".
Among other things, version 2.5 has to be indicated. You can alternatively click on the "Platform Identity" link
on the homepage. Version 2.5 of the platform will be indicated.

<p>You are ready! The upgrade process has been completed successfully. </p>
<p>If you want to see the new features of the new version, go to <a href="CHANGES_en.txt">CHANGES.txt</a> text file.
Read forward for further additional configuration options.</p>
<br />
<p class="title1" id="other">4th Step: Optional Further Configurations</a>
<ul>
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
<li>
<p> If you have SSL support activated and you want to enforce it between the platform and native mobile clients for increased security, 
you can do it by defining the <em>urlMobile</em> variable on <em>config.php</em>. e.g.<code>$urlMobile = "https://eclass.gunet.gr"</code>
</p>
</li>
<li>
<p>The default theme of platform is 'classic'. You can change it later from the admin tool to 'modern'.
Note the change will be visible to users after next login to platform.</p>
</li>
</ul>

<div class='sub_title1'><a name="after_tbl_config">Basic settings</a></div>
<p>
   You can configure several options of platform. After logging as admin user, 
        click in "Admin Tools" and after that, click in "Configuration File".
        Basic options are stored in file <em>config.php</em>. 
        Also you can change the following options below:</p>
<ul><li><em>Theme</em>: The default theme is «classic». 
        You can change it with something else (e.g. «modern» or «ocean»). 
        Theme change will be visible to users in their next login.</li>
        <li><em>Available languages</em>: Available languages are English, German and Spanish.</li>
</ul>
<ul><li><em>disable_eclass_stud_reg</em>: Student registration is disabled</li>
     <li><em>disable_eclass_prof_reg</em>: Teacher registration is disabled</li>
     <li><em>close_user_registration</em>: Registration of users with 'student' rights will not be free anymore. 
             Users will have to follow a process similar to the teacher account creation process, 
             namely filling in a student account application form, in order to obtain a platform account. 
             The application will be examined by the administrator who either approves of it and opens an account, 
             or rejects it.</li>
     <li><em>durationAccount</em>: Duration of users account.</li>
     <li><em>alt_auth_student_req</em>: Activation of users account request through alternative authentication methods.</li>
</ul>

<ul>
 <li><em>email_required</em>: During user registration, email is required.</li>
 <li><em>email_verification_required</em>: Email must be verified during registration and if user changes it.</li>
 <li><em>dont_mail_unverified_mails</em>: Don't send emails to users with unverified email address.</li>
 <li><em>email_from</em>: Emails will be sent with sender email address. Otherwise will be sent with platform administrator email address.</li>
 <li><em>am_required</em>: During user registration student ID is required.</li>
 <li><em>dropbox_allow_student_to_student</em>: In dropbox module, users can send files to each other.</li>
 <li><em>dont_display_login_form</em>: Login form will not be visible in home page. A link to it, will be appeared.</li>
 <li><em>block_username_change</em>: Users will not be able to change their usernames.</li>
 <li><em>display_captcha</em>: Display a `captcha` code during users registration.</li>
 <li><em>insert_xml_metadata</em>: Allow teachers to upload metadata in files in 'Documents'.</li>
</ul>
By default none of them is enabled.
<ul>
 <li><em>doc_quota</em>: Defines the default quota in 'Documents'. 
 <li><em>video_quota</em>: Defines the default quota in 'Video'. 
 <li><em>dropbox_quota</em>: Defines the default quota in 'Dropbox'. 
 <li><em>group_quota</em>: Defines the default quota in 'Groups'. 
</ul>

<p>Further information for these actions can be found in the Administrator's manual (linked from the platform Admin Tool).
</p>
<br />
<p class="title1" id="oldest">Upgrading from older versions (&lt;= 2.0)</p>
<ul>
<li>If platform version is &lt;= 1.7 then you have to first upgrade it to version 1.7
following the instructions <a href="http://www.openeclass.org/downloads/files/docs/1.7/Upgrade.pdf" target=_blank>here</a>
and then upgrade it to version 2.0.</li>
<li>If platform version is 1.7 then you have to upgrade id first in version 2.0
following the instructions <a href="http://www.openeclass.org/downloads/files/docs/2.0/Upgrade.pdf" target=_blank>here</a>.</li>
</ul>
</p>
</div>
</body>
</html>
