<?php header('Content-Type: text/html; charset=UTF-8');
?><!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<META http-equiv=Content-Type content="text/html; charset=UTF-8">
<title>Installation Instructions Open eClass 2.6</title>
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

<a href="http://www.openeclass.org/" title="Open eClass" alt="Open eClass" class="logo"></a></div>

  <p class="title1">Installation Instructions Open eClass 2.6</p>
<p>The Open eClass platform is a complete Course Management System. It is the solution offered by the Greek Academic Network GUnet
to support Asynchronous eLearning Services. It is mainly designed, developed and supported by the GUnet Asynchronous eLearning Group
and is distributed for free as open-source software.</p>
    <p>The Asynchronous <b>Open eClass 2.6</b> eLearning platform has been tested and operates well in: 
    </p>
    <ul>
      <li>Ms Windows environments (<b>Windows NT</b>, <b>Windows 2000</b>, <b>Windows XP</b>, <b>Windows 2003</b>, <b>Windows Vista</b>, <b>Windows 7</b>, <b>Windows 2008</b>)
      </li>
      <li>Various Linux distributions (e.g. <b>RedHat</b>, <b>CentOS</b>, <b>Debian</b>, <b>Ubuntu</b>, <b>OpenSuse</b> etc)
      </li>
      <li>Other UNIX environments (e.g. <b>Solaris</b>).
      </li>
    </ul>In the following pages platform's installation instructions are presented analytically:<br>
    <ul>
      <li><a href="#before">Actions before installation - Prerequisities</a></li>
      <li><a href="#unix">Installations in Unix / Linux systems</a> </li>
      <li><a href="#win">Installations in Ms Windows systems</a></li>
      <li><a href="#after">Actions after installation</a></li>
      <li style="list-style:none;">
	<ul>
	<li><a href="#after_f">How to modify test courses</a></li>
	<li><a href="#after_l">How to modify platform logo</a></li>
	<li><a href="#after_theme">How to modify platform theme</a></li>
	<li><a href="#after_m">How to modify messages</a></li>
	<li><a href="#after_math">Support of math symbols</a></li>	
	<li><a href="#after_pma">PhpMyAdmin</a></li>
	<li><a href="#after_tbl_config">Basic Settings</a></li>
	<li><a href="#after_other">Other settings</a></li>
	</ul>
      </li>
    </ul>
    <p class="title1">
      <a name="before" id="before">Actions before installation - prequisities</a>
    </p>
    <p>A series of applications needs to exist and operate in order for the eClass platform to be installed and operate as well.
    These applications are:
    </p>
    <p class="sub_title1">
      1. Web Server (<a href="http://httpd.apache.org/" target="_blank">Apache</a>2.x)
    </p>
    <p>Apache has to be able to control pages of the <em>.php</em> type.
    If you have not set the server yet, adding the following line to the <code>httpd.conf</code> file is enough:  
    </p>
    <pre>AddType application/x-httpd-php .php</pre>

<p>You will also have to define that the default charset of pages sent by the Web Server is <em>UTF-8</em>. In Apache, this can be done by placing the following statement in the <code>httpd.conf</code> file:   
</p>
    <pre>AddDefaultCharset UTF-8</pre>

<p>It is recommended, for security reasons, to deactivate directory indexing.
Add the <em>-Indexes</em> option on the <code>httpd.conf</code> to the list of Options. If eClass is installed on /var/www/html, add the following statement to httpd.conf: </p>
    <pre>
&lt;Directory /var/www/&gt;
................
Options -Indexes
................
&lt;/Directory&gt;
</pre>
    <div class="info">
      <b>For Windows Only</b> 
      <p>If Microsoft Webserver (<em>IIS</em>) runs on your computer, you will have to deactivate it. Follow <em>Start-&gt;Programs-&gt;Administrative
        Tools-&gt;Services</em> and click on 'stop' in order to stop the <em>«World Wide Web Publishing Service»</em>. Click right on the service and click on <em>«Disabled»</em> from the <em>«Startup type»</em> options to disable <em>IIS</em> permanently. Please notice that platform Open eClass operates without any problems under IIS web server, but without being exhaustive tested.   
      </p>
    </div>

<p class="sub_title1"> 2. Scripting Language<a href="http://www.php.net" target="_blank"> PHP</a> (versions &gt;= 5.0) 
</p>
<p>During PHP installation, it is important to activate the Apache support for PHP.
Note that you will need to activate support for <em>mysql, zlib, pcre, mbstring</em> and <em>gd</em> modules in PHP.
If you wish to use an LDAP server to authenticate users, you have to activate the module for <em>ldap</em> support as well.
Most of the PHP distributions have built-in support for these modules (except for the ldap module maybe).
Satisfaction of the above is checked during Open eClass installation.
    </p>
<p>You will also have to define the following parameters on the <code>php.ini</code> file:  
</p>
<ul>
        <li><code>short_open_tag = off</code></li>
        <li><code>magic_quotes_gpc = off</code></li>
        <li><code>magic_quotes_runtime = off</code></li>
</ul>
    <p>As far as the maximum file size allowed to be uploaded on the platform is concerned, you can adjust it to the following lines in the <code>php.ini</code> file: 
    </p>
    <ul>
      <li>
        <code>upload_max_filesize = 80M</code> (predefined value is 2M)
      </li>
      <li>
        <code>memory_limit = 25M</code> (predefined value is 8M)
      </li>
      <li>
        <code>post_max_size = 95M</code> (predefined value is 8M)
      </li>
      <li>
        <code>max_execution_time = 100</code> (predefined value is 30 sec)
      </li>
    </ul>
<p>
What is more, if a PHP notice comes up during the application, search for the <em>display_errors</em> variable in file <code>php.ini</code>
and modify it in: </p>
    <pre>display_errors = Off</pre> 
    <div class="info">
        <b>For Windows only</b>.
      <ul>
        <li>In Windows extensions uncomment (;) from the line <code>extension = php_ldap.dll</code> 
        </li>
        <li>Change the <em>session.save_path</em> variable to an existing path <em>(e.g. session.save_path=c:\winnt\temp\)</em>. Also make sure that the apache has access rights on it. 
        </li>
        <li>Fill in the SMTP server that serves you, e.g. <code>SMTP = mail.gunet.gr</code> 
        </li>
        <li>Fill the field <code>sendmail_from</code> with a valid sender email address 
        </li>
      </ul>
    </div>
    <p>Finally, you are advised to define the default charset again by the following line:
 <code>default_charset = "UTF-8"</code>  
    </p>
    <p>As soon as you have finished with changes, restart Apache Web Server. 
    </p>
<p class="sub_title1"> 3. Database<a href="http://www.mysql.com" target="_blank">MySQL</a> (versions 4.1.x or 5.x) </p>
    <p>Along with the MySQL installation, a user account with database creation rights has to be created.
    Because of the fact, that openeclass creates a new database for each course, make sure that you
    have permissions to create databases through php scripts and not through other tools (e.g. Plesk, cPanel etc.)
    </p>
    <p class="sub_title1">
      4. Mail Servers <a href="http://www.sendmail.org" target="_blank">sendmail</a> or <a href="http://www.postfix.org"
      target="_blank">postfix</a> (optional)
    </p>
    <p>In some of the platform operations (e.g. during users' registration), emails are sent. If any of the email submission applications does not function, platform mails are not sent anywhere. 
    </p>
    <div class="info">
      <p>
        <b>For Windows only:</b> Alternatively, in order to install the above, use the
       <a href="http://www.easyphp.org" target="_blank">EasyPHP</a> package or <a href="http://www.apachefriends.org/en/xampp-windows.html" target="_blank">XAMPP</a> package.
      </p>
    </div>
    <div class="title1">
      <a name="unix" id="unix">Installation in Unix / Linux systems</a>
    </div>
    <div class="sub_title1">
      Installation Process:
    </div>
    <p>
You can decompress the <b>openeclass-2.6.tar.gz</b> file using the <code>tar xzvf openeclass-2.6.tar.gz</code> command.
The sub-directory created during decompression of the packet includes all the application files and has to be placed in an accessible point by the computer web server. 
    </p>
    <p>
To give access rights to web server you can type the following commands (e.g. if the web server runs as a www-data user) 
    </p><pre>
		cd (path of eclass) (e.g. cd /var/www/html/openeclass)
		chown -R www-data *
		find ./ -type f -exec chmod 664 {} \;
		find ./ -type d -exec chmod 775 {} \;
		</pre>
    <p>Administrator's rights (root) are usually necessary for the above commands.  
    </p>
    <p>In order to start installing, visit the /install/ sub-directory address with a web browser.
    If, for example, the main eclass directory is located in http://www.example.gr/openeclass/,
    the address you have to type is <code>http://www.example.gr/openeclass/install/</code>.
    Then follow the platform installation guide steps like the ones presented on your screen.
    Note that during the installation process you will be required the following:
</p>
<ul>
  <li>The name of the computer MySQL is installed to (e.g. eclass.gunet.gr, localhost - if they are on the same computer)  </li>
  <li>A 'username' and a 'password' for a mysql user with database creation and deletion rights. </li>
  <li>Name for the main eClass database (default is eclass). Change it however, if there is a database with the same name already.  
  </li>
  <li>Platform URL (as this appears on the browser after installation
    e.g. http://eclass.gunet.gr/openeclass/) </li>
  <li>The file path on the server. Make sure that the path is right (e.g. /var/www/html/). </li>
  <li>Administrator's Name / Surname and email. </li>
  <li>Administrator's Username and Password.</li>
  <li>The name you would like to give to the platform (e.g. Open eClass).</li>
  <li>Phone number and email helpdesk (several applications meet this email, it could be the same as the administrator's).</li>
  <li>Name and address of your institute.</li>
</ul>
    <div class="title1">
      <a name="win" id="win">Installation in Ms Windows systems</a>
    </div>
    <div class="sub_title1">
      Installation Process
    </div>
    <p>Decompress the openeclass-2.6.zip file in the root directory of the Apache.
    The subdirectory created during the decompression of the package includes all files of the application.
    This sub-directory has to be placed in an accessible path by the web server.
    </p>
    <p>In order to start installation, visit the /install/ sub-directory address with a web browser.
    If, for example, the main eclass directory is located in http://www.example.gr/openeclass/,
    the address you have to type is <code>http://www.example.gr/openeclass/install/</code>.
    Then follow the platform installation guide steps like the ones presented on your screen.
    Note that during the installation process you will be required the following:</p>
<ul>
  <li>Computer hostname, in which MySQL is installed to (e.g. eclass.gunet.gr, localhost - if they are on the same computer) 
 </li>
  <li>A 'username' and a 'password' for MySQL with database creation and deletion rights.  </li>
  <li>Name for the main eClass database (default is eclass). Change it however if there is a database with the same name already.  
  </li>
  <li>Platform URL (as this appears on the browser after installation e.g.http://eclass.gunet.gr/openeclass/). 
 </li>
  <li>The file path on the server. Make sure that the path is right (e.g. C:\Program Files\Apache\htdocs\).</li>
  <li>Administrator's Name / Surname and email.</li>
  <li>Administrator's Username and Password.</li>
  <li>The name you would like to give to the platform (e.g. Open eClass).</li>
  <li>Phone number and email helpdesk (several applications meet this email, it could be the same as the administrator's).</li>
  <li>Name and address of your institute.</li>
</ul>
    <hr>
    <div class='title1'>
      <a name="after" id="after">Actions after installation</a>
    </div>

<div class='sub_title1'><a name="after_f">How to modify test courses</a></div>
<p>eClass introduces 3 tentative / general Schools / Faculties. (Faculty 1-Code TMA, Faculty 2-Code TMB etc).
You will have to change and adjust them to the Schools-Faculties of your own institute.
You can do this through the administrator tool.
You will find more and further information for these actions in the Administrator's manual (included in the administator tool).</p>
<div class='sub_title1'><a name="after_l">How to modify platform logo</a></div>
<p> In case some institutes intend to substitute the initial eClass logo with one of its own, they just have to substitute the picture.</p>
    <pre>(path of Open eClass)/template/classic/img/logo_openeclass.png</pre> 
    <p> with its own. </p>
<p class='sub_title1'><a name="after_theme">How to modify platform theme</a></p>
<p>The default theme of platform is 'classic'. You can change it later from the admin tool to 'modern'.
Note the change will be visible to users after next login to platform.</p>
<div class='sub_title1'><a name="after_m">How to modify messages</a></div>
<p>If you want to modify any message of platform then proceed with the following actions:
Create a file of type .php with name <em>english.inc.php</em> (or <em>greek.inc.php</em>) and place it in directory <em>(eclass path)/config/</em>. Find the varible name which contains the message you wish to change and assing it the new message. e.g. If you want to change message <pre>$langAboutText = "The platform version is";</pre> create <em>english.inc.php</em> in directory (eclass path)/config/ like this:
<pre>
&lt;?
$langAboutText = "Version is";
?&gt;
</pre>
With the above way, you preserve custom messages from future upgrades of platform.
</p>
<p>
You can modify the names of the basic roles of the users of the platform by modifying the message file (eClass_path)/modules/lang/english/common.inc.php
</p>
<p>
You can add a text (e.g. informative) on the left and right of the platform homepage. For that reason, assign the value - message in variables <em>$langExtrasLeft</em> και <em>$langExtrasRight</em>, correspondingly in file <em>(path του eClass)/modules/lang/english/common.inc.php</em> 
</p>
<div class='sub_title1'><a name="after_math">Support of math symbols</a></div>
<p>Open eClass supports mathematical symbols in subsystems "Exercises", "Forums" and "Announcements". In "Exercises" you can add math symbols in fields "Exercise Description" while a new exercise is created (or modified), in field "Comment" when a new question in an exercise is created (or modified). In subsystem "Forums" when you compose a new message or reply to an existing one and in subsystem "Announcements" when a new announcement is created. Math symbols must be enclosed with tags <em>&lt;m&gt;</em> and <em>&lt;/m&gt;</em>.
E.g. when you type 
<pre>
&lt;m&gt;sqrt{x-1}&lt;/m&gt; 
</pre>
square root of x-1 will be drawed. For syntax of all mathematical symbols, read manual in <em>http://(Open eClass url)/manuals/PhpMathPublisherHelp.pdf</em> 
</p>

<div class='sub_title1'><a name="after_pma">PhpMyAdmin</a></div> 
<p>The platform is delivered through the <em>phpMyAdmin</em> management tool. For safety and security reasons, access to phpMyAdmin is done through the browser's cookies. If you want to change it, you can refer to the config.inc.php file of phpMyAdmin.</p>

<div class='sub_title1'><a name="after_tbl_config">Basic settings</a></div>
<p>
   You can configure several options of platform. After logging as admin user, 
        click in "Admin Tools" and after that, click in "Configuration File".
       Among other, you can change the available languages (e.g. greek, italian) etc.       
</p>
<p>
        By default platform supports uploading of all the usual text, audio, video and image file types. 
        If you desire to add a new file type, type its extension in 'Teacher while list' or 'Student while list' textarea
         correspondingly.
</p>       

<div class='sub_title1'><a name="after_other">Other settings</a></div>
<p> 
If you want to use the platform with a Web server which has the SSL support activated (e.g. https://eclass.gunet.gr), you can do it by defining the <em>urlSecure</em> variable on <em>config.php</em>. e.g.<code>$urlSecure = "https://eclass.gunet.gr"</code> 
</p>
<p> If you have SSL support activated and you want to enforce it between the platform and native mobile clients for increased security, 
you can do it by defining the <em>urlMobile</em> variable on <em>config.php</em>. e.g.<code>$urlMobile = "https://eclass.gunet.gr"</code>
</p>

<p>
  Finally, it should be noted to the users of the platform that they need to have javascript activated on their browser.
    <ul>
      <li>For Internet Explorer users, choose consecutively <em>Internet Options/Security/Custom Level/Security Options</em> from the menu and check the <em>"Scripting of java applets"</em> option. </li>
      <li>For users <em>Mozilla Firefox,</em> choose consecutively <em>Edit / Preferences /  Web features</em> from the menu and check the <em>Enable Java script for Navigator</em> option.
      </li>
    </ul>
</p>
    <div class = "alert1"> 
	<b>For Unix / Linux systems only:</b>
        <p>After completing installation, you are advised, for security reasons, to change access rights for
	the <code>/config/config.php</code> and <code>/install/index.php</code><p> files and allow reading only.
	(persmissions must be set to 444) e.g.</p> 
        <pre>chmod 444 /config/config.php /install/index.php</pre>
    </div>
</div>
  </body>
</html>
