----------------------------------------------------------
How to modify messages
----------------------------------------------------------

If you want to modify any of the platform messages, the recommended way is the
following: Create a file of type inc.php with name en.inc.php (for English) or
el.inc.php (for Greek) and place it in the directory (eclass path)/config/. 
Find the variable name which contains the message you wish to change 
and assign the new message to it.

For example, if you want to change the message:
$langAboutText = "The platform version is";

Create the file en.inc.php in (eclass path)/config/ with the following
contents:

<?php
$langAboutText = "System version:";

This way, custom messages are preserved during future platform upgrades.

Note that you can modify the names of the basic roles of the platform users 
by setting in this file the variables found in (eClass_path)/lang/en/common.inc.php

You can add text (e.g. a notice or banner) to the left and right hand spaces
of the platform homepage, by assigning the message in HTML format to the
variables $langExtrasLeft και $langExtrasRight respectively.
