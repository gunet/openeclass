<? // $Id$
/**
    +-------------------------------------------------------------------+
    | CLAROLINE version $Revision$                                |
    +-------------------------------------------------------------------+
    | Copyright (c) 2001, 2002 Universite catholique de Louvain (UCL)   |
    +-------------------------------------------------------------------+
    | Authors: Thomas Depraetere <depraetere@ipm.ucl.ac.be>             |
    |          Hugues Peeters    <peeters@ipm.ucl.ac.be>                |
    |          Christophe Gesché <gesche@ipm.ucl.ac.be>                 |
    +-------------------------------------------------------------------+
    |   This page is used to launch an event when a user click          |
    |   on a page linked in a cours                                     |
    |   - It gets name of URL                                           |
    |   - It calls the event function                                   |
    |   - It redirects the user to the linked page                      |
    |                                                                   |
    |   Need the liens.id, user.user_id et cours.cours_id               |
    |   when called                                                     |
    |   ?link_id=$myrow[0]&link_url=$myrow[1]                           |
    |   url is given to avoid a new select                              |
    +-------------------------------------------------------------------+
*/

include ('../../include/init.php');

header("Cache-Control: no-store, no-cache, must-revalidate");   // HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");                                     // HTTP/1.0

header("Location: $link_url");

//to be sure that the script stop running after the redirection
exit;

?>
