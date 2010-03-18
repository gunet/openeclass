<?php
/*========================================================================
*   Open eClass 2.3
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2010  Greek Universities Network - GUnet
*  A full copyright notice can be read in "/info/copyright.txt".
*
*  Developers Group:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*			Yannis Exidaridis <jexi@noc.uoa.gr>
*			Alexandros Diamantidis <adia@noc.uoa.gr>
*			Tilemachos Raptis <traptis@noc.uoa.gr>
*
*  For a full list of contributors, see "credits.txt".
*
*  Open eClass is an open platform distributed in the hope that it will
*  be useful (without any warranty), under the terms of the GNU (General
*  Public License) as published by the Free Software Foundation.
*  The full license can be read in "/info/license/license_gpl.txt".
*
*  Contact address: 	GUnet Asynchronous eLearning Group,
*  			Network Operations Center, University of Athens,
*  			Panepistimiopolis Ilissia, 15784, Athens, Greece
*  			eMail: info@openeclass.org
* =========================================================================*/

/*===========================================================================
        phpbb/functions.php
        @last update: 2006-07-23 by Artemios G. Voyiatzis
        @authors list: Artemios G. Voyiatzis <bogart@upnet.gr>

        based on Claroline version 1.7 licensed under GPL
              copyright (c) 2001, 2006 Universite catholique de Louvain (UCL)

        Claroline authors: Piraux Sebastien <pir@cerdecam.be>
                      Lederer Guillaume <led@cerdecam.be>

	based on phpBB version 1.4.1 licensed under GPL
		copyright (c) 2001, The phpBB Group
==============================================================================
    @Description: This module implements a per course forum for supporting
	discussions between teachers and students or group of students.
	It is a heavily modified adaptation of phpBB for (initially) Claroline
	and (later) eclass. In the future, a new forum should be developed.
	Currently we use only a fraction of phpBB tables and functionality
	(viewforum, viewtopic, post_reply, newtopic); the time cost is
	enormous for both core phpBB code upgrades and migration from an
	existing (phpBB-based) to a new eclass forum :-(

    @Comments:

    @todo:
==============================================================================
*/


/******************************************************************************
 * Actual code starts here
 *****************************************************************************/

/*
 * Gets the total number of topics in a form
 */
function get_total_topics($forum_id, $thedb) {
	global $langError;
	$sql = "SELECT count(*) AS total FROM topics WHERE forum_id = '$forum_id'";
	if(!$result = db_query($sql, $thedb))
		return($langError);
	if(!$myrow = mysql_fetch_array($result))
		return($langError);
	
	return($myrow["total"]);
}

/*
 * Returns the total number of posts in the whole system, a forum, or a topic
 * Also can return the number of users on the system.
 */ 
function get_total_posts($id, $thedb, $type) {
   switch($type) {
    case 'users':
      $sql = "SELECT count(*) AS total FROM users WHERE (user_id != -1) AND (user_level != -1)";
      break;
    case 'all':
      $sql = "SELECT count(*) AS total FROM posts";
      break;
    case 'forum':
      $sql = "SELECT count(*) AS total FROM posts WHERE forum_id = '$id'";
      break;
    case 'topic':
      $sql = "SELECT count(*) AS total FROM posts WHERE topic_id = '$id'";
      break;
   // Old, we should never get this.   
    case 'user':
      error_die("Should be using the users.user_posts column for this.");
   }
   if(!$result = db_query($sql, $thedb))
     return("ERROR");
   if(!$myrow = mysql_fetch_array($result))
     return("0");
   
   return($myrow["total"]);
   
}

/*
 * Returns the most recent post in a forum, or a topic
 */
function get_last_post($id, $thedb, $type) {
   global $langError, $langNoPosts, $langFrom2;
   switch($type) {
    case 'time_fix':
      $sql = "SELECT p.post_time FROM posts p WHERE p.topic_id = '$id' ORDER BY post_time DESC LIMIT 1";   
      break;
    case 'forum':
      $sql = "SELECT p.post_time, p.poster_id FROM posts p WHERE p.forum_id = '$id' ORDER BY post_time DESC LIMIT 1";
      break;
    case 'topic':
      $sql = "SELECT p.post_time FROM posts p WHERE p.topic_id = '$id' ORDER BY post_time DESC LIMIT 1";
      break;
    case 'user':
      $sql = "SELECT p.post_time FROM posts p WHERE p.poster_id = '$id' LIMIT 1";
      break;
   }
   if(!$result = db_query($sql, $thedb))
     return($langError);
   if(!$myrow = mysql_fetch_array($result))
     return($langNoPosts);
   if(($type != 'user') && ($type != 'time_fix'))
     $val = sprintf("%s <br> %s %s", $myrow["post_time"], $langFrom2, $myrow["username"]);
   else
     $val = $myrow["post_time"];
   return($val);
}


/*
 * Checks if a forum or a topic exists in the database. Used to prevent
 * users from simply editing the URL to post to a non-existant forum or topic
 */
function does_exists($id, $thedb, $type) {
	switch($type) {
		case 'forum':
			$sql = "SELECT forum_id FROM forums WHERE forum_id = '$id'";
		break;
		case 'topic':
			$sql = "SELECT topic_id FROM topics WHERE topic_id = '$id'";
		break;
	}
	if(!$result = db_query($sql, $thedb))
		return(0);
	if(!$myrow = mysql_fetch_array($result)) 
		return(0);
	return(1);
}

/**
 * bbdecode/bbencode functions:
 * Rewritten - Nathan Codding - Aug 24, 2000
 * quote, code, and list rewritten again in Jan. 2001.
 * All BBCode tags now implemented. Nesting and multiple occurances should be 
 * handled fine for all of them. Using str_replace() instead of regexps often
 * for efficiency. quote, list, and code are not regular, so they are 
 * implemented as PDAs - probably not all that efficient, but that's the way it is. 
 *
 * Note: all BBCode tags are case-insensitive.
 */

function bbencode($message, $is_html_disabled) {

	// pad it with a space so we can distinguish between FALSE and matching the 1st char (index 0).
	// This is important; bbencode_quote(), bbencode_list(), and bbencode_code() all depend on it.
	$message = " " . $message;
	
	// First: If there isn't a "[" and a "]" in the message, don't bother.
	if (! (strpos($message, "[") && strpos($message, "]")) )
	{
		// Remove padding, return.
		$message = substr($message, 1);
		return $message;	
	}

	// [CODE] and [/CODE] for posting code (HTML, PHP, C etc etc) in your posts.
	$message = bbencode_code($message, $is_html_disabled);

	// [QUOTE] and [/QUOTE] for posting replies with quote, or just for quoting stuff.	
	$message = bbencode_quote($message);

	// [list] and [list=x] for (un)ordered lists.
	$message = bbencode_list($message);
	
	// [b] and [/b] for bolding text.
	$message = preg_replace("/\[b\](.*?)\[\/b\]/si", "<!-- BBCode Start --><B>\\1</B><!-- BBCode End -->", $message);
	
	// [i] and [/i] for italicizing text.
	$message = preg_replace("/\[i\](.*?)\[\/i\]/si", "<!-- BBCode Start --><I>\\1</I><!-- BBCode End -->", $message);
	
	// [img]image_url_here[/img] code..
	$message = preg_replace("/\[img\](.*?)\[\/img\]/si", "<!-- BBCode Start --><IMG SRC=\"\\1\" BORDER=\"0\"><!-- BBCode End -->", $message);
	
	// Patterns and replacements for URL and email tags..
	$patterns = array();
	$replacements = array();
	
	// [url]xxxx://www.phpbb.com[/url] code..
	$patterns[0] = "#\[url\]([a-z]+?://){1}(.*?)\[/url\]#si";
	$replacements[0] = '<!-- BBCode u1 Start --><A HREF="\1\2" TARGET="_blank">\1\2</A><!-- BBCode u1 End -->';
	
	// [url]www.phpbb.com[/url] code.. (no xxxx:// prefix).
	$patterns[1] = "#\[url\](.*?)\[/url\]#si";
	$replacements[1] = '<!-- BBCode u1 Start --><A HREF="http://\1" TARGET="_blank">\1</A><!-- BBCode u1 End -->';
	
	// [url=xxxx://www.phpbb.com]phpBB[/url] code.. 
	$patterns[2] = "#\[url=([a-z]+?://){1}(.*?)\](.*?)\[/url\]#si";
	$replacements[2] = '<!-- BBCode u2 Start --><A HREF="\1\2" TARGET="_blank">\3</A><!-- BBCode u2 End -->';
	
	// [url=www.phpbb.com]phpBB[/url] code.. (no xxxx:// prefix).
	$patterns[3] = "#\[url=(.*?)\](.*?)\[/url\]#si";
	$replacements[3] = '<!-- BBCode u2 Start --><A HREF="http://\1" TARGET="_blank">\2</A><!-- BBCode u2 End -->';
	
	// [email]user@domain.tld[/email] code..
	$patterns[4] = "#\[email\](.*?)\[/email\]#si";
	$replacements[4] = '<!-- BBCode Start --><A HREF="mailto:\1">\1</A><!-- BBCode End -->';
						
	$message = preg_replace($patterns, $replacements, $message);
	
	// Remove our padding from the string..
	$message = substr($message, 1);
	return $message;
	
} // bbencode()



function bbdecode($message) {

		// Undo [code]
		$code_start_html = "<!-- BBCode Start --><TABLE BORDER=0 ALIGN=CENTER WIDTH=85%><TR><TD><font size=-1>Code:</font><HR></TD></TR><TR><TD><FONT SIZE=-1><PRE>";
		$code_end_html = "</PRE></FONT></TD></TR><TR><TD><HR></TD></TR></TABLE><!-- BBCode End -->";
		$message = str_replace($code_start_html, "[code]", $message);
		$message = str_replace($code_end_html, "[/code]", $message);

		// Undo [quote]
		$quote_start_html = "<!-- BBCode Quote Start --><TABLE BORDER=0 ALIGN=CENTER WIDTH=85%><TR><TD><font size=-1>Quote:</font><HR></TD></TR><TR><TD><FONT SIZE=-1><BLOCKQUOTE>";
		$quote_end_html = "</BLOCKQUOTE></FONT></TD></TR><TR><TD><HR></TD></TR></TABLE><!-- BBCode Quote End -->";
		$message = str_replace($quote_start_html, "[quote]", $message);
		$message = str_replace($quote_end_html, "[/quote]", $message);
		
		// Undo [b] and [i]
		$message = preg_replace("#<!-- BBCode Start --><B>(.*?)</B><!-- BBCode End -->#s", "[b]\\1[/b]", $message);
		$message = preg_replace("#<!-- BBCode Start --><I>(.*?)</I><!-- BBCode End -->#s", "[i]\\1[/i]", $message);
		
		// Undo [url] (long form)
		$message = preg_replace("#<!-- BBCode u2 Start --><A HREF=\"([a-z]+?://)(.*?)\" TARGET=\"_blank\">(.*?)</A><!-- BBCode u2 End -->#s", "[url=\\1\\2]\\3[/url]", $message);
		
		// Undo [url] (short form)
		$message = preg_replace("#<!-- BBCode u1 Start --><A HREF=\"([a-z]+?://)(.*?)\" TARGET=\"_blank\">(.*?)</A><!-- BBCode u1 End -->#s", "[url]\\3[/url]", $message);
		
		// Undo [email]
		$message = preg_replace("#<!-- BBCode Start --><A HREF=\"mailto:(.*?)\">(.*?)</A><!-- BBCode End -->#s", "[email]\\1[/email]", $message);
		
		// Undo [img]
		$message = preg_replace("#<!-- BBCode Start --><IMG SRC=\"(.*?)\" BORDER=\"0\"><!-- BBCode End -->#s", "[img]\\1[/img]", $message);
		
		// Undo lists (unordered/ordered)
	
		// <li> tags:
		$message = str_replace("<!-- BBCode --><LI>", "[*]", $message);
		
		// [list] tags:
		$message = str_replace("<!-- BBCode ulist Start --><UL>", "[list]", $message);
		
		// [list=x] tags:
		$message = preg_replace("#<!-- BBCode olist Start --><OL TYPE=([A1])>#si", "[list=\\1]", $message);
		
		// [/list] tags:
		$message = str_replace("</UL><!-- BBCode ulist End -->", "[/list]", $message);
		$message = str_replace("</OL><!-- BBCode olist End -->", "[/list]", $message);

		return($message);
}

/**
 * James Atkinson - Feb 5, 2001
 * This function does exactly what the PHP4 function array_push() does
 * however, to keep phpBB compatable with PHP 3 we had to come up with out own 
 * method of doing it.
 */
function bbcode_array_push(&$stack, $value) {
   $stack[] = $value;
   return(sizeof($stack));
}

/**
 * James Atkinson - Feb 5, 2001
 * This function does exactly what the PHP4 function array_pop() does
 * however, to keep phpBB compatable with PHP 3 we had to come up with out own
 * method of doing it.
 */
function bbcode_array_pop(&$stack) {
   $arrSize = count($stack);
   $x = 1;
   while(list($key, $val) = each($stack)) {
      if($x < count($stack)) {
	 $tmpArr[] = $val;
      }
      else {
	 $return_val = $val;
      }
      $x++;
   }
   $stack = $tmpArr;
   return($return_val);
}

/**
 * Nathan Codding - Jan. 12, 2001.
 * Performs [quote][/quote] bbencoding on the given string, and returns the results.
 * Any unmatched "[quote]" or "[/quote]" token will just be left alone. 
 * This works fine with both having more than one quote in a message, and with nested quotes.
 * Since that is not a regular language, this is actually a PDA and uses a stack. Great fun.
 *
 * Note: This function assumes the first character of $message is a space, which is added by 
 * bbencode().
 */
function bbencode_quote($message)
{
	// First things first: If there aren't any "[quote]" strings in the message, we don't
	// need to process it at all.
	
	if (!strpos(strtolower($message), "[quote]"))
	{
		return $message;	
	}
	
	$stack = Array();
	$curr_pos = 1;
	while ($curr_pos && ($curr_pos < strlen($message)))
	{	
		$curr_pos = strpos($message, "[", $curr_pos);
	
		// If not found, $curr_pos will be 0, and the loop will end.
		if ($curr_pos)
		{
			// We found a [. It starts at $curr_pos.
			// check if it's a starting or ending quote tag.
			$possible_start = substr($message, $curr_pos, 7);
			$possible_end = substr($message, $curr_pos, 8);
			if (strcasecmp("[quote]", $possible_start) == 0)
			{
				// We have a starting quote tag.
				// Push its position on to the stack, and then keep going to the right.
				bbcode_array_push($stack, $curr_pos);
				++$curr_pos;
			}
			else if (strcasecmp("[/quote]", $possible_end) == 0)
			{
				// We have an ending quote tag.
				// Check if we've already found a matching starting tag.
				if (sizeof($stack) > 0)
				{
					// There exists a starting tag. 
					// We need to do 2 replacements now.
					$start_index = bbcode_array_pop($stack);

					// everything before the [quote] tag.
					$before_start_tag = substr($message, 0, $start_index);

					// everything after the [quote] tag, but before the [/quote] tag.
					$between_tags = substr($message, $start_index + 7, $curr_pos - $start_index - 7);

					// everything after the [/quote] tag.
					$after_end_tag = substr($message, $curr_pos + 8);

					$message = $before_start_tag . "<!-- BBCode Quote Start --><TABLE BORDER=0 ALIGN=CENTER WIDTH=85%><TR><TD><font size=-1>Quote:</font><HR></TD></TR><TR><TD><FONT SIZE=-1><BLOCKQUOTE>";
					$message .= $between_tags . "</BLOCKQUOTE></FONT></TD></TR><TR><TD><HR></TD></TR></TABLE><!-- BBCode Quote End -->";
					$message .= $after_end_tag;
					
					// Now.. we've screwed up the indices by changing the length of the string. 
					// So, if there's anything in the stack, we want to resume searching just after it.
					// otherwise, we go back to the start.
					if (sizeof($stack) > 0)
					{
						$curr_pos = bbcode_array_pop($stack);
						bbcode_array_push($stack, $curr_pos);
						++$curr_pos;
					}
					else
					{
						$curr_pos = 1;
					}
				}
				else
				{
					// No matching start tag found. Increment pos, keep going.
					++$curr_pos;	
				}
			}
			else
			{
				// No starting tag or ending tag.. Increment pos, keep looping.,
				++$curr_pos;	
			}
		}
	} // while
	
	return $message;
	
} // bbencode_quote()


/**
 * Nathan Codding - Jan. 12, 2001.
 * Performs [code][/code] bbencoding on the given string, and returns the results.
 * Any unmatched "[code]" or "[/code]" token will just be left alone. 
 * This works fine with both having more than one code block in a message, and with nested code blocks.
 * Since that is not a regular language, this is actually a PDA and uses a stack. Great fun.
 *
 * Note: This function assumes the first character of $message is a space, which is added by 
 * bbencode().
 */
function bbencode_code($message, $is_html_disabled)
{
	// First things first: If there aren't any "[code]" strings in the message, we don't
	// need to process it at all.
	if (!strpos(strtolower($message), "[code]"))
	{
		return $message;	
	}
	
	// Second things second: we have to watch out for stuff like [1code] or [/code1] in the 
	// input.. So escape them to [#1code] or [/code#1] for now:
	$message = preg_replace("/\[([0-9]+?)code\]/si", "[#\\1code]", $message);
	$message = preg_replace("/\[\/code([0-9]+?)\]/si", "[/code#\\1]", $message);
	
	$stack = Array();
	$curr_pos = 1;
	$max_nesting_depth = 0;
	while ($curr_pos && ($curr_pos < strlen($message)))
	{	
		$curr_pos = strpos($message, "[", $curr_pos);
	
		// If not found, $curr_pos will be 0, and the loop will end.
		if ($curr_pos)
		{
			// We found a [. It starts at $curr_pos.
			// check if it's a starting or ending code tag.
			$possible_start = substr($message, $curr_pos, 6);
			$possible_end = substr($message, $curr_pos, 7);
			if (strcasecmp("[code]", $possible_start) == 0)
			{
				// We have a starting code tag.
				// Push its position on to the stack, and then keep going to the right.
				bbcode_array_push($stack, $curr_pos);
				++$curr_pos;
			}
			else if (strcasecmp("[/code]", $possible_end) == 0)
			{
				// We have an ending code tag.
				// Check if we've already found a matching starting tag.
				if (sizeof($stack) > 0)
				{
					// There exists a starting tag. 
					$curr_nesting_depth = sizeof($stack);
					$max_nesting_depth = ($curr_nesting_depth > $max_nesting_depth) ? $curr_nesting_depth : $max_nesting_depth;
					
					// We need to do 2 replacements now.
					$start_index = bbcode_array_pop($stack);

					// everything before the [code] tag.
					$before_start_tag = substr($message, 0, $start_index);

					// everything after the [code] tag, but before the [/code] tag.
					$between_tags = substr($message, $start_index + 6, $curr_pos - $start_index - 6);

					// everything after the [/code] tag.
					$after_end_tag = substr($message, $curr_pos + 7);

					$message = $before_start_tag . "[" . $curr_nesting_depth . "code]";
					$message .= $between_tags . "[/code" . $curr_nesting_depth . "]";
					$message .= $after_end_tag;
					
					// Now.. we've screwed up the indices by changing the length of the string. 
					// So, if there's anything in the stack, we want to resume searching just after it.
					// otherwise, we go back to the start.
					if (sizeof($stack) > 0)
					{
						$curr_pos = bbcode_array_pop($stack);
						bbcode_array_push($stack, $curr_pos);
						++$curr_pos;
					}
					else
					{
						$curr_pos = 1;
					}
				}
				else
				{
					// No matching start tag found. Increment pos, keep going.
					++$curr_pos;	
				}
			}
			else
			{
				// No starting tag or ending tag.. Increment pos, keep looping.,
				++$curr_pos;	
			}
		}
	} // while
	
	if ($max_nesting_depth > 0)
	{
		for ($i = 1; $i <= $max_nesting_depth; ++$i)
		{
			$start_tag = escape_slashes(preg_quote("[" . $i . "code]"));
			$end_tag = escape_slashes(preg_quote("[/code" . $i . "]"));
			
			$match_count = preg_match_all("/$start_tag(.*?)$end_tag/si", $message, $matches);
	
			for ($j = 0; $j < $match_count; $j++)
			{
				$before_replace = escape_slashes(preg_quote($matches[1][$j]));
				$after_replace = $matches[1][$j];
				
				if (($i < 2) && !$is_html_disabled)
				{
					// don't escape special chars when we're nested, 'cause it was already done
					// at the lower level..
					// also, don't escape them if HTML is disabled in this post. it'll already be done
					// by the posting routines.
					$after_replace = htmlspecialchars($after_replace);	
				}
				
				$str_to_match = $start_tag . $before_replace . $end_tag;
				
				$message = preg_replace("/$str_to_match/si", "<!-- BBCode Start --><TABLE BORDER=0 ALIGN=CENTER WIDTH=85%><TR><TD><font size=-1>Code:</font><HR></TD></TR><TR><TD><FONT SIZE=-1><PRE>$after_replace</PRE></FONT></TD></TR><TR><TD><HR></TD></TR></TABLE><!-- BBCode End -->", $message);
			}
		}
	}
	
	// Undo our escaping from "second things second" above..
	$message = preg_replace("/\[#([0-9]+?)code\]/si", "[\\1code]", $message);
	$message = preg_replace("/\[\/code#([0-9]+?)\]/si", "[/code\\1]", $message);
	
	return $message;
	
} // bbencode_code()


/**
 * Nathan Codding - Jan. 12, 2001.
 * Performs [list][/list] and [list=?][/list] bbencoding on the given string, and returns the results.
 * Any unmatched "[list]" or "[/list]" token will just be left alone. 
 * This works fine with both having more than one list in a message, and with nested lists.
 * Since that is not a regular language, this is actually a PDA and uses a stack. Great fun.
 *
 * Note: This function assumes the first character of $message is a space, which is added by 
 * bbencode().
 */
function bbencode_list($message)
{		
	$start_length = Array();
	$start_length[ordered] = 8;
	$start_length[unordered] = 6;
	
	// First things first: If there aren't any "[list" strings in the message, we don't
	// need to process it at all.
	
	if (!strpos(strtolower($message), "[list"))
	{
		return $message;	
	}
	
	$stack = Array();
	$curr_pos = 1;
	while ($curr_pos && ($curr_pos < strlen($message)))
	{	
		$curr_pos = strpos($message, "[", $curr_pos);
	
		// If not found, $curr_pos will be 0, and the loop will end.
		if ($curr_pos)
		{
			// We found a [. It starts at $curr_pos.
			// check if it's a starting or ending list tag.
			$possible_ordered_start = substr($message, $curr_pos, $start_length[ordered]);
			$possible_unordered_start = substr($message, $curr_pos, $start_length[unordered]);
			$possible_end = substr($message, $curr_pos, 7);
			if (strcasecmp("[list]", $possible_unordered_start) == 0)
			{
				// We have a starting unordered list tag.
				// Push its position on to the stack, and then keep going to the right.
				bbcode_array_push($stack, array($curr_pos, ""));
				++$curr_pos;
			}
			else if (preg_match("/\[list=([a1])\]/si", $possible_ordered_start, $matches))
			{
				// We have a starting ordered list tag.
				// Push its position on to the stack, and the starting char onto the start
				// char stack, the keep going to the right.
				bbcode_array_push($stack, array($curr_pos, $matches[1]));
				++$curr_pos;
			}
			else if (strcasecmp("[/list]", $possible_end) == 0)
			{
				// We have an ending list tag.
				// Check if we've already found a matching starting tag.
				if (sizeof($stack) > 0)
				{
					// There exists a starting tag. 
					// We need to do 2 replacements now.
					$start = bbcode_array_pop($stack);
					$start_index = $start[0];
					$start_char = $start[1];
					$is_ordered = ($start_char != "");
					$start_tag_length = ($is_ordered) ? $start_length[ordered] : $start_length[unordered];
					
					// everything before the [list] tag.
					$before_start_tag = substr($message, 0, $start_index);

					// everything after the [list] tag, but before the [/list] tag.
					$between_tags = substr($message, $start_index + $start_tag_length, $curr_pos - $start_index - $start_tag_length);
					// Need to replace [*] with <LI> inside the list.
					$between_tags = str_replace("[*]", "<!-- BBCode --><LI>", $between_tags);
					
					// everything after the [/list] tag.
					$after_end_tag = substr($message, $curr_pos + 7);

					if ($is_ordered)
					{
						$message = $before_start_tag . "<!-- BBCode olist Start --><OL TYPE=" . $start_char . ">";
						$message .= $between_tags . "</OL><!-- BBCode olist End -->";
					}
					else
					{
						$message = $before_start_tag . "<!-- BBCode ulist Start --><UL>";
						$message .= $between_tags . "</UL><!-- BBCode ulist End -->";
					}
					
					$message .= $after_end_tag;
					
					// Now.. we've screwed up the indices by changing the length of the string. 
					// So, if there's anything in the stack, we want to resume searching just after it.
					// otherwise, we go back to the start.
					if (sizeof($stack) > 0)
					{
						$a = bbcode_array_pop($stack);
						$curr_pos = $a[0];
						bbcode_array_push($stack, $a);
						++$curr_pos;
					}
					else
					{
						$curr_pos = 1;
					}
				}
				else
				{
					// No matching start tag found. Increment pos, keep going.
					++$curr_pos;	
				}
			}
			else
			{
				// No starting tag or ending tag.. Increment pos, keep looping.,
				++$curr_pos;	
			}
		}
	} // while
	
	return $message;
	
} // bbencode_list()



/**
 * Nathan Codding - Oct. 30, 2000
 *
 * Escapes the "/" character with "\/". This is useful when you need
 * to stick a runtime string into a PREG regexp that is being delimited 
 * with slashes.
 */
function escape_slashes($input)
{
	$output = str_replace('/', '\/', $input);
	return $output;
}

 /**
 * Rewritten by Nathan Codding - Feb 6, 2001.
 * - Goes through the given string, and replaces xxxx://yyyy with an HTML <a> tag linking
 * 	to that URL
 * - Goes through the given string, and replaces www.xxxx.yyyy[zzzz] with an HTML <a> tag linking
 * 	to http://www.xxxx.yyyy[/zzzz] 
 * - Goes through the given string, and replaces xxxx@yyyy with an HTML mailto: tag linking
 *		to that email address
 * - Only matches these 2 patterns either after a space, or at the beginning of a line
 *
 * Notes: the email one might get annoying - it's easy to make it more restrictive, though.. maybe
 * have it require something like xxxx@yyyy.zzzz or such. We'll see.
 */

function make_clickable($text) {
	
	// pad it with a space so we can match things at the start of the 1st line.
	$ret = " " . $text;
	
	// matches an "xxxx://yyyy" URL at the start of a line, or after a space.
	// xxxx can only be alpha characters.
	// yyyy is anything up to the first space, newline, or comma.
	$ret = preg_replace("#([\n ])([a-z]+?)://([^, \n\r]+)#i", "\\1<!-- BBCode auto-link start --><a href=\"\\2://\\3\" target=\"_blank\">\\2://\\3</a><!-- BBCode auto-link end -->", $ret);
	
	// matches a "www.xxxx.yyyy[/zzzz]" kinda lazy URL thing
	// Must contain at least 2 dots. xxxx contains either alphanum, or "-"
	// yyyy contains either alphanum, "-", or "."
	// zzzz is optional.. will contain everything up to the first space, newline, or comma.
	// This is slightly restrictive - it's not going to match stuff like "forums.foo.com"
	// This is to keep it from getting annoying and matching stuff that's not meant to be a link.
	$ret = preg_replace("#([\n ])www\.([a-z0-9\-]+)\.([a-z0-9\-.\~]+)((?:/[^, \n\r]*)?)#i", "\\1<!-- BBCode auto-link start --><a href=\"http://www.\\2.\\3\\4\" target=\"_blank\">www.\\2.\\3\\4</a><!-- BBCode auto-link end -->", $ret);
	
	// matches an email@domain type address at the start of a line, or after a space.
	// Note: before the @ sign, the only valid characters are the alphanums and "-", "_", or ".".
	// After the @ sign, we accept anything up to the first space, linebreak, or comma.
	$ret = preg_replace("#([\n ])([a-z0-9\-_.]+?)@([^, \n\r]+)#i", "\\1<!-- BBcode auto-mailto start --><a href=\"mailto:\\2@\\3\">\\2@\\3</a><!-- BBCode auto-mailto end -->", $ret);
	
	// Remove our padding..
	$ret = substr($ret, 1);
	
	return($ret);
}


/**
 * Nathan Codding - Feb 6, 2001
 * Reverses the effects of make_clickable(), for use in editpost.
 * - Does not distinguish between "www.xxxx.yyyy" and "http://aaaa.bbbb" type URLs.
 *
 */
 
function undo_make_clickable($text) {
	
	$text = preg_replace("#<!-- BBCode auto-link start --><a href=\"(.*?)\" target=\"_blank\">.*?</a><!-- BBCode auto-link end -->#i", "\\1", $text);
	$text = preg_replace("#<!-- BBcode auto-mailto start --><a href=\"mailto:(.*?)\">.*?</a><!-- BBCode auto-mailto end -->#i", "\\1", $text);
	
	return $text;
	
}



/**
 * Nathan Codding - August 24, 2000.
 * Takes a string, and does the reverse of the PHP standard function
 * htmlspecialchars().
 */
function undo_htmlspecialchars($input) {
	$input = preg_replace("/&gt;/i", ">", $input);
	$input = preg_replace("/&lt;/i", "<", $input);
	$input = preg_replace("/&quot;/i", "\"", $input);
	$input = preg_replace("/&amp;/i", "&", $input);
	
	return $input;
}

/*
 * Check if this is the first post in a topic. Used in editpost.php
 */
function is_first_post($topic_id, $post_id, $thedb) {
   $sql = "SELECT post_id FROM posts WHERE topic_id = '$topic_id' ORDER BY post_id LIMIT 1";
   if(!$r = db_query($sql, $thedb))
     return(0);
   if(!$m = mysql_fetch_array($r))
     return(0);
   if($m["post_id"] == $post_id)
     return(1);
   else
     return(0);
}

/**
 * Checks if the given userid is allowed to log into the given (private) forumid.
 * If the "is_posting" flag is true, checks if the user is allowed to post to that forum.
 */
function check_priv_forum_auth($userid, $forumid, $is_posting, $db)
{
	$sql = "SELECT count(*) AS user_count FROM forum_access WHERE (user_id = $userid) AND (forum_id = $forumid) ";
	
	if ($is_posting)
	{
		$sql .= "AND (can_post = 1)";
	}
	
	if (!$result = mysql_query($sql, $db))
	{
		// no good..
		return FALSE;
	}
	
	if(!$row = mysql_fetch_array($result))
	{
		return FALSE;
	}
   
  	if ($row[user_count] <= 0)
  	{
  		return FALSE;
  	}
  	
  	return TRUE;

}

/**
 * Displays an error message and exits the script. Used in the posting files.
 */
function error_die($msg){
	global $tablewidth, $table_bgcolor;
	global $db, $user_logged_in;
	global $FontFace, $FontSize3, $textcolor, $phpbbversion;
	global $starttime;
	if ( !isset($tool_content) ) {
		$tool_content = "";
	}
	$tool_content .= "<br>
		<TABLE BORDER=\"0\" CELLPADDING=\"1\" CELLSPACING=\"0\" ALIGN=\"CENTER\" VALIGN=\"TOP\" WIDTH=\"$tablewidth\">
		<TR><TD BGCOLOR=\"$table_bgcolor\">
			<TABLE BORDER=\"0\" CALLPADDING=\"1\" CELLSPACEING=\"1\" WIDTH=\"100%\">
			<TR ALIGN=\"LEFT\">
			<TD>
			<p><font face=\"Verdana\" size=\"2\"><ul>$msg</ul></font></P>
			</TD>
			</TR>
			</TABLE>
		</TD></TR>
	 	</TABLE>
	 	<br>";
	draw($tool_content, 2);
	exit();
}

function get_syslang_string($sys_lang, $string) {
	include('language/lang_' . $sys_lang . '.php');
	$ret_string = $$string;
	return($ret_string);
}

function sync($thedb, $id, $type) {
   switch($type) {
   	case 'forum':
   		$sql = "SELECT max(post_id) AS last_post FROM posts WHERE forum_id = $id";
   		if(!$result = db_query($sql, $thedb))
   		{
   			error_die("Could not get post ID");
   		}
   		if($row = mysql_fetch_array($result))
   		{
   			$last_post = $row["last_post"];
   		}
   		
   		$sql = "SELECT count(post_id) AS total FROM posts WHERE forum_id = $id";
   		if(!$result = db_query($sql, $thedb))
   		{
   			error_die("Could not get post count");
   		}
   		if($row = mysql_fetch_array($result))
   		{
   			$total_posts = $row["total"];
   		}
   		
   		$sql = "SELECT count(topic_id) AS total FROM topics WHERE forum_id = $id";
   		if(!$result = db_query($sql, $thedb))
   		{
   			error_die("Could not get topic count");
   		}
   		if($row = mysql_fetch_array($result))
   		{
   			$total_topics = $row["total"];
   		}
   		
   		$sql = "UPDATE forums
			SET forum_last_post_id = '$last_post', forum_posts = $total_posts, forum_topics = $total_topics
			WHERE forum_id = $id";
   		if(!$result = db_query($sql, $thedb))
   		{
   			error_die("Could not update forum $id");
   		}
   	break;

   	case 'topic':
   		$sql = "SELECT max(post_id) AS last_post FROM posts WHERE topic_id = $id";
   		if(!$result = db_query($sql, $thedb))
   		{
   			error_die("Could not get post ID");
   		}
   		if($row = mysql_fetch_array($result))
   		{
   			$last_post = $row["last_post"];
   		}
   		
   		$sql = "SELECT count(post_id) AS total FROM posts WHERE topic_id = $id";
   		if(!$result = db_query($sql, $thedb))
   		{
   			error_die("Could not get post count");
   		}
   		if($row = mysql_fetch_array($result))
   		{
   			$total_posts = $row["total"];
   		}
   		$total_posts -= 1;
   		$sql = "UPDATE topics SET topic_replies = $total_posts, topic_last_post_id = $last_post WHERE topic_id = $id";
   		if(!$result = db_query($sql, $thedb))
   		{
   			error_die("Could not update topic $id");
   		}
   	break;

   	case 'all forums':
   		$sql = "SELECT forum_id FROM forums";
   		if(!$result = db_query($sql, $thedb))
   		{
   			error_die("Could not get forum IDs");
   		}
   		while($row = mysql_fetch_array($result))
   		{
   			$id = $row["forum_id"];
   			sync($thedb, $id, "forum");
   		}
   	break;
   	case 'all topics':
   		$sql = "SELECT topic_id FROM topics";
   		if(!$result = db_query($sql, $thedb))
   		{
   			error_die("Could not get topic ID's");
   		}
   		while($row = mysql_fetch_array($result))
   		{
   			$id = $row["topic_id"];
   			sync($thedb, $id, "topic");
   		}
   	break;
   }
   return(TRUE);
}

/**
 * Less agressive version of stripslashes. Only replaces \\ \' and \"
 * The PHP stripslashes() also removed single backslashes from the string.
 * Expects a string or array as an argument.
 * Returns the result.
 */
function own_stripslashes($string)
{
   $find = array(
            '/\\\\\'/',  // \\\'
            '/\\\\/',    // \\
				'/\\\'/',    // \'
            '/\\\"/');   // \"
   $replace = array(
            '\'',   // \
            '\\',   // \
            '\'',   // '
            '"');   // "
   return preg_replace($find, $replace, $string);
}

// display notification status of link
function toggle_link($notify) {
	
	if ($notify == TRUE) {
		return FALSE;
	} elseif ($notify == FALSE) {
		return TRUE;
	}
}

// display notification status of link and icon
function toggle_icon($notify) {	
	
	if ($notify == TRUE) {
		return '_on';
	} elseif ($notify == FALSE) {
		return '_off';
	}
}

// returns a category id from a forum id
function forum_category($id) {
	
	global $currentCourseID;
	
	if ($r = mysql_fetch_row(db_query("SELECT cat_id FROM forums WHERE forum_id=$id", $currentCourseID))) {
		return $r[0];
	} else {
		return FALSE;
	}
}

// returns a category name from a category id
function category_name($id) {
	
	global $currentCourseID;
	
	if ($r = mysql_fetch_row(db_query("SELECT cat_title FROM catagories WHERE cat_id=$id", $currentCourseID))) {
		return $r[0];
	} else {
		return FALSE;
	}
}

// Apply various transformations to message text for display
function format_message($message)
{
        $message = make_clickable($message);
        return str_replace(
                        array('<w>', '</w>', '<r>', '</r>'),
                        array('<s><font color="red">', '</font></s>', '<font color="#0000FF">', '</font>'),
                        $message);
}
