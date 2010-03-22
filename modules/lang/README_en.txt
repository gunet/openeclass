----------------------------------------------------------
How to modify messages
----------------------------------------------------------

If you want to modify any message of platform then proceed with the following actions:
Create a file of type .php with name english.inc.php (or greek.inc.php) 
and place it in directory (eclass path)/config/. 
Find the varible name which contains the message you wish to change 
and assing it the new message.

For example if you want to change message 
$langAboutText = "The platform version is"; 
create english.inc.php in directory (eclass path)/config/ like this:

<?
$langAboutText = "Version is";
?>

With the above way, you preserve custom messages from future upgrades of platform.

Also note that you can modify the names of the basic roles of the users of the platform 
by modifying the message file (eClass_path)/modules/lang/english/common.inc.php

You can add a text (e.g. informative) on the left and right of the platform homepage. 
For that reason, assign the value - message in variables $langExtrasLeft και $langExtrasRight,
correspondingly in file (path του eClass)/modules/lang/english/common.inc.php 
