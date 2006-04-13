<?  session_start(); ?>
<?php
/***************************************************************************
                          faq.php  -  description
                             -------------------
    begin                : Fri November 3, 2000
    copyright            : (C) 2001 The phpBB Group
    email                : support@phpbb.com

    $Id$

 ***************************************************************************/

/***************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 ***************************************************************************/
/***************************************************************************
 * Created by: Steven Cunningham (defender@webinfractions.com) for phpBB
 * *************************************************************************/
include('extention.inc');
include('functions.'.$phpEx);
include('config.'.$phpEx);
require('auth.'.$phpEx);
$pagetitle = "FAQ";
$pagetype = "other";
include('page_header.'.$phpEx);
?>

<div align="center"><center>
<table border="0" width="<?php echo $tablewidth?>" bgcolor="<?php echo $table_bgcolor?>">
  <TR><TD>
<table border="0" width="100%" bgcolor=>
    <tr bgcolor="<?php echo $color1?>">
        <td><font size="<?php echo $FontSize4?>" face="<?php echo $FontFace?>" color="<?php echo $textcolor?>"><b>Frequently
          Asked Questions</font></b></td>
    </tr>
    <tr bgcolor="<?php echo $color2?>">
        <td>
          <font size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>" color="<?php echo $linkcolor?>">
          <a href="#register">Do I have to register?</a><br>
          <a href="#smilies">Can I use smilies?</a><br>
          <a href="#html">Using HTML</a><br>
          <a href="#bbcode">Using BB Code</a><br>
          <a href="#mods">What are moderators?</a><br>
	  <a href="#profile">Can I change my profile?</a><br>
          <a href="#prefs">Can I customize the bulletin board in any way?</a><br>
          <a href="#cookies">Do you use cookies?</a><br>
          <a href="#edit">Can I edit my own posts?</a><br>
          <a href="#attach">Can I attach files?</a><br>
          <a href="#search">Can I search?</a><br>
          <a href="#signature">Can I add a signature to the end of my posts?</a><br>
          <a href="#announce">What are announcements?</a><br>
          <a href="#pw">Is there a username/password retrieval system?</a><br>
          <a href="#notify">Can I be notified by email if someone responds to my topic?</a><br>
          <a href="#searchprivate">Can I search private forums?</a><br>
          <a href="#ranks">What are the ranks in the <?php echo $sitename?> Forums?</a><br>
          <a href="#rednumbers">Why are icons flaming in the topic view?</a></p></font>
        </td>
    </tr>
    <tr bgcolor="<?php echo $color1?>">
        <td>
        <font color="<?php echo $textcolor?>" size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>">
        <a name="register"><b><br>Registering</b></font></a>
        </td>
    </tr>
    <tr bgcolor="<?php echo $color2?>">
        <td>
	<font size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>" color="<?php echo $textcolor?>">
	Registration is only required on a per forum basis. Depending on the how the administrator has setup his/her forums some may require you to register in order to post, where
        some may allow you to post anonymously. If anonymous posting is allowed you can do so by simply not entering
        a username and password when prompted.
        Registration is free, and you are not
        required to post your real name. You are required to post
        your actual email address, however it will only be used to email you a new password if you have forgotten yours. You also have the option to hide
        you email address from everyone except the administrator, it option is selected by default but you can allow others to see your email address
        by selecting the 'Allow other users to view my email address' checkbox on the registration form. You can register by clicking
	<a href="<?php echo $url_phpbb?>/bb_register.<?php echo $phpEx?>?mode=agreement">here</a></font>
	</td>
    </tr>
    <tr bgcolor="<?php echo $color1?>">
        <td>
	<a name="smilies">
	<font color="<?php echo $textcolor?>" size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>">
        <b>Smilies</b></font></a>
	</td>
    </tr>
    <tr bgcolor="<?php echo $color2?>">
        <td>
	<font size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>" color="<?php echo $textcolor?>">
	You've probably seen others use smilies before in email messages or other
        bulletin board posts. Smilies are keyboard characters
        used to convey an emotion, such as a smile
	:)
	or a frown
	:(.
	This bulletin board
        automatically converts certain smilies to a graphical
        representation.
        The following smilies are currently supported: </font><BR>
	<table width="50%" ALIGN="CENTER" BGCOLOR="<?php echo $table_bgcolor?>" CELLSPACEING=1 BORDER="0">
	  <TR><TD>
	  <TABLE WIDTH="100%" BORDER="0">
		 <TR BGCOLOR="<?php echo $color1?>">
		 <TD width="100">
		 	<FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">
		 		<?php echo $l_smilesym?>
		 	</FONT>
		 </td>
		 <td width="50%">
		 	<FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">
				<?php echo $l_smileemotion?>
			</FONT>
		</td>
		<td width="55">
		 	<FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">
				<?php echo $l_smilepict?>
			</FONT>
		</td></tr>
 <?php

	  if ($getsmiles = mysql_query("SELECT * FROM smiles")) {
	     while ($smile = mysql_fetch_array($getsmiles)) {
?>
		 <TR BGCOLOR="<?php echo $color2?>">
		 <TD width="100">
		 	<FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">
		 		<?php echo stripslashes($smile[code])?>
		 	</FONT>
		 </td>
		 <td width="50%">
		 	<FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">
				<?php echo stripslashes($smile[emotion])?>&nbsp;
			</FONT>
		</td>
		<td width="55">
			<IMG SRC="<?php echo "$url_smiles/$smile[smile_url]";?>">
		</td></tr>
<?php
	     }
	  } else
	     echo "Could not retrieve from the smile database.";
?>
    </TABLE></TABLE>
    </div>
	</td>
    </tr>
	<tr bgcolor="<?php echo $color1?>">
	<td>
		<p align="left"><a name="html">
		<font color="<?php echo $textcolor?>" size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>">
		<b>Using HTML</b></font></a></p>
	</td>
	</tr>
	<tr bgcolor="<?php echo $color2?>">
	<td>
	<font size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>" color="<?php echo $textcolor?>">
	You may be able to use HTML in your posts, if your
	administrators and moderators have this option turned on.
	Every time you post a new note, you will be told whether BB Code and/or HTML
	is enabled. If HTML is on, you may use any HTML tags, but please be very
	careful that you proper HTML syntax. If you do not, your moderator or
	administrator may have to edit your post.
	</td>
	<tr bgcolor="<?php echo $color1?>">
	<td>
		<p align="left"><a name="bbcode">
		<font color="<?php echo $textcolor?>" size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>">
		<b>Using BB Code</b></font></a></p>
	</td>
	</tr>
	<tr bgcolor="<?php echo $color2?>">
	<td>

BBCode is a variation on the HTML tags you may already be familiar with.  Basically, it allows you to add functionality or style to your message that would normally require HTML.  You can use BBCode even if HTML is not enabled for the forum you are using.  You may want to use BBCode as opposed to HTML, even if HTML is enabled for your forum, because there is less coding required and it is safer to use (incorrect coding syntax will not lead to as many problems).
<P>

<table border=0 cellpadding=0 cellspacing=0 width="<?php echo $tablewidth?>" align="CENTER"><TR><td bgcolor="#FFFFFF">
<table border=0 cellpadding=4 border=0 cellspacing=1 width=100%>
<TR bgcolor="<?php echo $color1?>">
<TD>
<FONT SIZE="2" FACE="Verdana, Arial">
URL Hyperlinking</FONT></td></tr>
<TR bgcolor="<?php echo $color2?>"><TD><FONT SIZE="2" FACE="Verdana, Arial">
If BBCode is enabled in a forum, you no longer need to use the [URL] code to create a hyperlink.  Simply type the complete URL in either of the following manners and the hyperlink will be created automatically:
<UL><FONT SIZE="2" FACE="Verdana, Arial" color="silver">
<LI> http://www.yourURL.com
<LI> www.yourURL.com
</font>

Notice that you can either use the complete http:// address or shorten it to the www domain.  If the site does not begin with "www", you must use the complete "http://" address.  Also, you may use https and ftp URL prefixes in auto-link mode (when BBCode is ON).
<P>
The old [URL] code will still work, as detailed below.

Just encase the link as shown in the following example (BBCode is in <FONT COLOR="#FF0000">red</FONT>).
<P><center>
<FONT COLOR="#FF0000">[url]</FONT>www.totalgeek.org<FONT COLOR="#FF0000">[/url]</FONT>
<P></center>
You can also have true hyperlinks using the [url] code.  Just use the following format:
<BR><center>
<FONT COLOR="#FF0000">[url=http://www.totalgeek.org]</font>totalgeek.org<FONT COLOR="#FF0000">[/url]</font>
</center><p>
In the examples above, the BBCode automatically generates a hyperlink to the URL that is encased.  It will also ensure that the link is opened in a new window when the user clicks on it. Note that the "http://" part of the URL is completely optional. In the second example above, the URL will hypelink the text to whatever URL you provide after the equal sign.  Also note that you should NOT use quotation marks inside the URL tag.
</font>
</td>
<tr bgcolor="<?php echo $color1?>"><td>
<FONT SIZE="2" FACE="Verdana, Arial">
Email Links</FONT></td></tr>
<TR bgcolor="<?php echo $color2?>"><TD>
<FONT SIZE="2" FACE="Verdana, Arial">
To add a hyperlinked email address within your message, just encase the email address as shown in the following example (BBCode is in <FONT COLOR="#FF0000">red</FONT>).
<P>
<CENTER>
<FONT COLOR="#FF0000">[email]</FONT>james@totalgeek.org<FONT COLOR="#FF0000">[/email]</FONT>
</CENTER>
<P>
In the example above, the BBCode automatically generates a hyperlink to the email address that is encased.
</FONT>
</td></tr>
<tr bgcolor="<?php echo $color1?>"><td>
<FONT SIZE="2" FACE="Verdana, Arial">
Bold and Italics</FONT></td></tr>
<TR bgcolor="<?php echo $color2?>"><TD>
<FONT SIZE="2" FACE="Verdana, Arial">
You can make italicized text or make text bold by encasing the applicable sections of your text with either the [b] [/b] or [i] [/i] tags.
<P>
<CENTER>
Hello, <FONT COLOR="#FF0000">[b]</FONT><B>James</B><FONT COLOR="#FF0000">[/b]</FONT><BR>
Hello, <FONT COLOR="#FF0000">[i]</FONT><I>Mary</I><FONT COLOR="#FF0000">[/i]</FONT>
</CENTER>
</FONT>
</td></tr>
<tr bgcolor="<?php echo $color1?>"><td>
<FONT SIZE="2" FACE="Verdana, Arial">
Bullets/Lists</FONT></td></tr>
<TR bgcolor="<?php echo $color2?>"><TD>
<FONT SIZE="2" FACE="Verdana, Arial">
You can make bulleted lists or ordered lists (by number or letter).
<P>
Unordered, bulleted list:
<P>
<FONT COLOR="#FF0000">[list]</FONT>
<BR>
<FONT COLOR="#FF0000">[*]</font> This is the first bulleted item.<BR>
<FONT COLOR="#FF0000">[*]</font> This is the second bulleted item.<BR>
<FONT COLOR="#FF0000">[/list]</font>
<P>
This produces:
<ul>
<LI> This is the first bulleted item.
<LI> This is the second bulleted item.
</ul>
Note that you must include a closing [/list] when you end each list.

<P>
Making ordered lists is just as easy.  Just add either [LIST=A] or [LIST=1].  Typing [List=A] will produce a list from A to Z.  Using [List=1] will produce numbered lists.
<P>
Here's an example:
<P>

<FONT COLOR="#FF0000">[list=A]</FONT>
<BR>
<FONT COLOR="#FF0000">[*]</font> This is the first bulleted item.<BR>
<FONT COLOR="#FF0000">[*]</font> This is the second bulleted item.<BR>
<FONT COLOR="#FF0000">[/list]</font>
<P>
This produces:
<ol type=A>
<LI> This is the first bulleted item.
<LI> This is the second bulleted item.
</ul>


</FONT>
</td></tr>
<TR bgcolor="<?php echo $color1?>"><TD>
<FONT SIZE="2" FACE="Verdana, Arial">
Adding Images</font></td></tr>
<TR bgcolor="<?php echo $color2?>"><TD>
<FONT SIZE="2" FACE="Verdana, Arial">
To add a graphic within your message, just encase the URL of the graphic image as shown in the following example (BBCode is in <FONT COLOR="#FF0000">red</FONT>).
<P>
<CENTER>
<FONT COLOR="#FF0000">[img]</FONT>http://www.totalgeek.org/images/tline.gif<FONT COLOR="#FF0000">[/img]</FONT>
</CENTER>
<P>
In the example above, the BBCode automatically makes the graphic visible in your message.  Note: the "http://" part of the URL is REQUIRED for the <FONT COLOR="#FF0000">[img]</FONT> code.
</FONT>
</td></tr>
<TR bgcolor="<?php echo $color1?>"><TD>
<FONT SIZE="2" FACE="Verdana, Arial">
Quoting Other Messages</font></td></tr>
<TR bgcolor="<?php echo $color2?>"><TD>
<FONT SIZE="2" FACE="Verdana, Arial">
To reference something specific that someone has posted, just cut and paste the applicable verbiage and enclose it as shown below (BBCode is in <FONT COLOR="#FF0000">red</FONT>).
<P>
<CENTER>
<FONT COLOR="#FF0000">[QUOTE]</FONT>Ask not what your country can do for you....<BR>ask what you can do for your country.<FONT COLOR="#FF0000">[/QUOTE]</FONT>
</CENTER>
<P>
In the example above, the BBCode automatically blockquotes the text you reference.</FONT>
</td>
</tr>
<TR bgcolor="<?php echo $color1?>"><TD>
<FONT SIZE="2" FACE="Verdana, Arial">
Code Tag</FONT></td></tr>
<TR bgcolor="<?php echo $color2?>"><TD>
<FONT SIZE="2" FACE="Verdana, Arial">
Similar to the Quote tage, the Code tag adds some &lt;PRE&gt; tags to preserve formatting.  This useful for displaying programming code, for instance.
<P>

<FONT COLOR="#FF0000">[CODE]</FONT>#!/usr/bin/perl
<P>
print "Content-type: text/html\n\n";
<BR>
print "Hello World!";
<FONT COLOR="#FF0000">[/CODE]</FONT>

<P>
In the example above, the BBCode automatically blockquotes the text you reference and preserves the formatting of the coded text.</FONT>
</td>
</tr>
</table>
</td></tr></table>
</blockquote>
<BR>
You must not use both HTML and BBCode to do the same function.  Also note that the BBCode is not case-sensitive (thus, you could use <FONT COLOR="#FF0000">[URL]</FONT> or <FONT COLOR="#FF0000">[url]</FONT>).
<P>
<FONT COLOR="silver">Incorrect BBCode Usage:</FONT>
<P>
<FONT COLOR="#FF0000">[url]</FONT> www.totalgeek.org <FONT COLOR="#FF0000">[/url]</FONT> - don't put spaces between the bracketed code and the text you are applying the code to.
<P>
<FONT COLOR="#FF0000">[email]</FONT>james@totalgeek.org<FONT COLOR="#FF0000">[email]</FONT> - the end brackets must include a forward slash (<FONT COLOR="#FF0000">[/email]</FONT>)

<P>
</FONT>
</B>

	</td>


    <tr bgcolor="<?php echo $color1?>">
        <td nowrap>
	<p align="left"><a name="mods">
	<font color="<?php echo $textcolor?>" size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>">
	<b>Moderators</b></font></a></p>
        </td>
    </tr>
    <tr bgcolor="<?php echo $color2?>">
        <td>
          <p>
	    <font size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>" color="<?php echo $textcolor?>">
	    Moderators control individual
            forums. They can edit, delete, or prune any posts in their forums.
            If you have a question about a particular forum, you should direct
            it to your forum moderator.</p>
          <p>Admins and forum moderators reserve the right to close or delete any post that does not provide
            a clear and purposefull topic. There are many members who still use
            28.8 and 56k modems that do not have the time to wade through useless
            and senseless topics. </p>
          <p>Anyone who posts just to increase their <?php echo $sitename?> Forums stats or post topics out of
	    boredom risk having there topics closed, removed and/or membership revoked. </p>
          <p>Try to make the topic wording mirror what is inside the thread. Topics like "Check this out!" and
            "~~\\You have to see this!//~~" only attract members to a topic they
            may not want to read.</font></p>
        </td>
    </tr>
    <tr bgcolor="<?php echo $color1?>">
        <td>
	<p align="left"><a name="profile">
	<font color="<?php echo $textcolor?>" size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>">
	<b>Changing Your Profile</b></font></a></p>
        </td>
    </tr>
    <tr bgcolor="<?php echo $color2?>">
        <td>
	<font size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>" color="<?php echo $textcolor?>">
	You may easily change any info stored in your registration profile,
        using the &quot;profile&quot; link located near the top
        of each page. Simply identify yourself by typing your
        username and password, or by logging in, and all of your profile information
        will appear on screen.</font>
	</td>
    </tr>
    <tr bgcolor="<?php echo $color1?>">
        <td>
	<a name="prefs">
	<font color="<?php echo $textcolor?>" size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>">
	<b>Customizing Using Preferences</b></font></a>
	</td>
    </tr>
    <tr bgcolor="<?php echo $color2?>">
        <td>
	<font size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>" color="<?php echo $textcolor?>">
	As a registered
        bulletin board user, you may store your username in memory for up to one year at a time.
	By doing this we create a way to keep track of who you are when you visit the forum, therefor you can customize the look of the forum
	by selecting from the themes that the administration has provided. Also, if the administrator allows it you may have the option of
	creating new themes for the fourms. In creating a new theme you will be able to set the colors, fonts and font sizes on the board, however
	at this time only the administrator may change the images for each theme. When a user creates a theme the images from the board's default theme
	will be selected.
	<br>*NOTE: In order to use themes you MUST have cookies enabled.</font>
	</td>
    </tr>
    <tr bgcolor="<?php echo $color1?>">
        <td><a name="cookies">
	<font color="<?php echo $textcolor?>" size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>">
	<b>Cookies</b></font></a></td>
    </tr>
    <tr bgcolor="<?php echo $color2?>">
        <td>
	<font size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>" color="<?php echo $textcolor?>">
	This bulletin board uses cookies to store the following information:
        the last time you visited the forums, your username,
        and a unique session ID number when you login. These cookies are stored on your browser.
        If your browser does not support cookies,
        or you have not enabled cookies on your browser, none of
        these time-saving features will work properly. </font>
	</td>
    </tr>
    <tr bgcolor="<?php echo $color1?>">
        <td><a name="edit">
	<font color="<?php echo $textcolor?>" size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>">
	<b>Editing Your Posts</b></font></a>
        </td>
    </tr>
    <tr bgcolor="<?php echo $color2?>">
        <td>
	<font size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>" color="<?php echo $textcolor?>">
	You may edit your own posts at any time. Just go to the thread where
        the post to be edited is located and you will see an edit
        icon on the line under your message.
        Click on this icon and edit the post. No one else can
        edit your post, except for the forum moderator or the
        bulletin board administrator. Also, for up to 30 mins after you have posted you message the edit post screen will give you the option
	of deleteing that post. After 30 mins however only the moderator and/or administrator can remove the post.</font>
	</td>
    </tr>
    <tr bgcolor="<?php echo $color1?>">
        <td><a name="signature">
	<font color="<?php echo $textcolor?>" size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>">
	<b>Adding Signatures</b></font></a>
	</td>
    </tr>
    <tr bgcolor="<?php echo $color2?>">
        <td>
	<font size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>" color="<?php echo $textcolor?>">
	You may use a signature on your posts.
        If you click on the profile link at the top of most
        pages, you will be able to edit your profile, including your standard signature. Once you have
        a signature stored, you can choose to include it any post
        you make by checking the &quot;include signature&quot;
        box when you create your post. This bulletin board's
        administrator may elect to turn the signature feature off
        at any time, however. If that is the case, the &quot;include
        signature&quot; option will not appear when you post a
        note, even if you have stored a signature. You may also
        change your signature at any time by changing your
        profile. <p>Note: You may use HTML or <a href="#bbcode">BB Code</a> if the admin has enabled
        these options.
	    </font>
        </p>
        </td>
    </tr>
    <tr bgcolor="<?php echo $color1?>">
        <td>
	<a name="attach">
	<font color="<?php echo $textcolor?>" size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>">
	<b>Attaching Files</b></font></a>
	</td>
    </tr>
    <tr bgcolor="<?php echo $color2?>">
        <td>
	<font size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>" color="<?php echo $textcolor?>">
	For security reasons, you may not attach files to any posts. You may
        cut and paste text into your post, however, or use HTML
        and/or BB Code (if enabled) to provide hyperlinks to
        outside documents. File attachements will be included in a future version of phpBB.</font>
	</td>
    </tr>
    <tr bgcolor="<?php echo $color1?>">
        <td>
	<a name="search">
	<font color="<?php echo $textcolor?>" size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>">
	<b>Searching For Specific Posts</b></font></a>
	</td>
    </tr>
    <tr bgcolor="<?php echo $color2?>">
        <td>
	<font size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>" color="<?php echo $textcolor?>">
	You may search for specific posts based on a word or words found in the
        posts, a user name, a date, and/or a particular forum(s). Just
        click on the &quot;search&quot; link at the top of most
        pages.</font>
	</td>
    </tr>
    <tr bgcolor="<?php echo $color1?>">
        <td>
	<a name="announce">
	<font color="<?php echo $textcolor?>" size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>">
	<b>Announcements</b></font></a>
	</td>
    </tr>
    <tr bgcolor="<?php echo $color2?>">
        <td>
	<font size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>" color="<?php echo $textcolor?>">
	Announcements have not been implemented, but are planned in a future release.
	However, the administrator can create a forum where only other administrators and moderators can post. This type
	of forum can easly be used as an announcement forum.
	</td>
    </tr>
    <tr bgcolor="<?php echo $color1?>">
        <td>
	<a name="pw">
	<font color="<?php echo $textcolor?>" size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>">
	<b>Lost User Name and/or Password</b></font></a>
	</td>
    </tr>
    <tr bgcolor="<?php echo $color2?>">
        <td>
	<font size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>" color="<?php echo $textcolor?>">
 	In the even that you lose your password you can click on the &quot;Forgotten your password?&quot; link provided in the
	message posting screens next to the password field. This link will take you to a page where you can fill in your username and email address.
	The system will then email a new, randomly generated, password to the email address listed in your profile, assuming you supplied the correct email address.</FONT>
	</td>
    </tr>
    <tr bgcolor="<?php echo $color1?>">
        <td>
	<a name="notify">
	<font color="<?php echo $textcolor?>" size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>">
	<b>Email Notification</b></font></a>
	</td>
    </tr>
    <tr bgcolor="<?php echo $color2?>">
        <td>
	<font size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>" color="<?php echo $textcolor?>">
	If you create a new topic, you have the option of receiving an email
        notification every time someone posts a reply to your
        topic. Just check the email notification box on the
        &quot;New Topic&quot; forum when you create your new
        topic if you want to use this feature. </font>
	</td>
    </tr>
    <tr bgcolor="<?php echo $color1?>">
        <td>
	<a name="searchprivate">
	<font color="<?php echo $textcolor?>" size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>">
	<b>Can I search private forums?</b>
	</td>
    </tr>
    <tr bgcolor="<?php echo $color2?>">
        <td>
	<font size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>" color="<?php echo $textcolor?>">
	Yes, but you cannot read any of the posts unless you have the password to the private forum. </font></p>
        </td>
    </tr>
    <tr bgcolor="<?php echo $color1?>">
        <td>
	<a name="ranks">
	<font color="<?php echo $textcolor?>" size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>">
	<b>What are the ranks for the <?php echo $sitename?> Forums?</b></font></a>
	</td>
    </tr>
    <tr bgcolor="<?php echo $color2?>">
        <td>
	<font size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>" color="<?php echo $textcolor?>">
	The <?php echo $sitename?> Forums have established
	methods to classify their users by activity through the number of posts.</p>
	<br>
	The current ranks are as follows:<br>

	<?php
	$sql = "SELECT * FROM ranks WHERE rank_special = 0";
	if(!$r = mysql_query($sql, $db)) {
	echo "Error connecting to the database";
	include('page_tail.'.$phpEx);
	exit();
	}
	?>
	<br><TABLE BORDER="0" WIDTH="<?php echo $TableWidth?>" CELLPADDING="1" CELLSPACING="0" ALIGN="CENTER" VALIGN="TOP"><TR><TD BGCOLOR="<?php echo $table_bgcolor?>">
	<TABLE BORDER="0" CELLPADDING="1" CELLSPACING="1" WIDTH="100%">
	<TR BGCOLOR="<?php echo $color1?>" ALIGN="CENTER">
	<TD><font face="<?php echo $FontFace?>" size="<?php echo $FontSize2?>" color="<?php echo $textcolor?>">&nbsp;Rank Title&nbsp;</font></TD>
	<TD><font face="<?php echo $FontFace?>" size="<?php echo $FontSize2?>" color="<?php echo $textcolor?>">&nbsp;Minimum Posts&nbsp;</font></TD>
	<TD><font face="<?php echo $FontFace?>" size="<?php echo $FontSize2?>" color="<?php echo $textcolor?>">&nbsp;Maximum Posts&nbsp;</font></TD>
        <TD><font face="<?php echo $FontFace?>" size="<?php echo $FontSize2?>" color="<?php echo $textcolor?>">&nbsp;Rank Image&nbsp;</font></TD>
	</TR>
	<?php
	if($m = mysql_fetch_array($r)) {
	do {
	echo "<TR BGCOLOR=\"$color2\" ALIGN=\"CENTER\">";
	echo "<TD><font face=\"<?php echo $FontFace?>\" size=\"2\" color=\"$textcolor\">$m[rank_title]</font></TD>";
	echo "<TD><font face=\"<?php echo $FontFace?>\" size=\"2\" color=\"$textcolor\">$m[rank_min]</font></TD>";
	echo "<TD><font face=\"<?php echo $FontFace?>\" size=\"2\" color=\"$textcolor\">$m[rank_max]</font></TD>";
	if($m[rank_image] != '')
	   echo "<TD><img src=\"$url_images/$m[rank_image]\"></TD>";
	else
	   echo "<TD>&nbsp;</TD>";
	echo "</TR>";
	} while($m = mysql_fetch_array($r));
	}
	else {
	echo "<TR BGCOLOR=\"$color2\" ALIGN=\"CENTER\">";
	echo "<TD COLSPAN=\"4\">No Ranks in the database</TD>";
	echo "</TR>";
	}
	?>
	</TABLE></TABLE></font>
	<br>
	<font size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>" color="<?php echo $textcolor?>">
	The adminstrator also has the option of assigning special ranks to any user they choose. The above table does not list these special ranks.
	</font>
        </td>
    </tr>
    <tr bgcolor="<?php echo $color1?>">
        <td>
	<a name="rednumbers">
	<font color="<?php echo $textcolor?>" size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>">
	<b>Why are some post icons </b>
	</font>
	<font color="#FF0033" size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>">
	<b>flaming</b>
	</font>
	<font color="<?php echo $textcolor?>" size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>">
	<b> in the forum view?</b></font></a>
	</td>
    </tr>
    <tr bgcolor="<?php echo $color2?>">
        <td>
	<font size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>" color="<?php echo $textcolor?>">
	Flaming icons signify that there are <?php echo $hot_threshold?> or more posts in that
        thread. It is a warning to slower connections that the
        thread may take some time to load.</font></p>
        </td>
    </tr>
</table>
</TABLE>
</center>
</div>

<?php
include('page_tail.'.$phpEx);
?>
