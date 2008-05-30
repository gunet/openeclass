<?php
/**=============================================================================
       	GUnet eClass 2.0 
        E-learning and Course Management Program  
================================================================================
       	Copyright(c) 2003-2006  Greek Universities Network - GUnet
        A full copyright notice can be read in "/info/copyright.txt".
        
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
	create_table_indexes.php
	@last update: 18-07-2006 by Sakis Agorastos
	@authors list: Agorastos Sakis <th_agorastos@hotmail.com>
==============================================================================        
        @Description: MySQL queries that create FULL TEXT indexes in certain
        tables in the main DB and the current course DB. The indexes are
        required for the search function to operate.

==============================================================================*/

mysql_select_db("$mysqlMainDb");
mysql_query("ALTER TABLE `annonces` ADD FULLTEXT `annonces` (`contenu` ,`code_cours`)");
mysql_query("ALTER TABLE `cours` ADD FULLTEXT `cours` (`code` ,`description` ,`intitule` ,`course_objectives` ,`course_prerequisites` ,`course_keywords` ,`course_references`)");

mysql_select_db("$dbname");
mysql_query("ALTER TABLE `agenda` ADD FULLTEXT `agenda` (`titre` ,`contenu`)");
mysql_query("ALTER TABLE `course_description` ADD FULLTEXT `course_description` (`title` ,`content`)");
mysql_query("ALTER TABLE `document` ADD FULLTEXT `document` (`filename` ,`comment` ,`title`,`creator`,`subject`,`description`,`author`,`language`)");
mysql_query("ALTER TABLE `exercices` ADD FULLTEXT `exercices` (`titre`,`description`)");
mysql_query("ALTER TABLE `posts_text` ADD FULLTEXT `posts_text` (`post_text`)");
mysql_query("ALTER TABLE `liens` ADD FULLTEXT `liens` (`url` ,`titre` ,`description`)");
mysql_query("ALTER TABLE `video` ADD FULLTEXT `video` (`url` ,`titre` ,`description`)");
mysql_query("ALTER TABLE `videolinks` ADD FULLTEXT `videolinks` (`url` ,`titre` ,`description`)");

?>