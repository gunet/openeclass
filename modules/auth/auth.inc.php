<?php
/**=============================================================================
       	GUnet e-Class 2.0 
        E-learning and Course Management Program  
================================================================================
       	Copyright(c) 2003-2006  Greek Universities Network - GUnet
        Α full copyright notice can be read in "/info/copyright.txt".
        
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

/**===========================================================================
	auth.inc.php
	@last update: 31-05-2006 by Stratos Karatzidis
	@authors list: Karatzidis Stratos <kstratos@uom.gr>
		       Vagelis Pitsioygas <vagpits@uom.gr>
==============================================================================        
        @Description: Functions Library for authentication purposes

 	This library includes all the functions for authentication
	and their settings.

 		

==============================================================================
*/

// pop3 class
require("methods/pop3.php");

/****************************************************************
find/return the id of the default authentication method
return $auth_id (a value between 1 and 5: 1-eclass,2-pop3,3-imap,4-ldap,5-db)
****************************************************************/
function get_auth_id()
{
	global $db;
	$sql = "SELECT auth_id FROM auth WHERE auth_default=1";
  $auth_method = mysql_query($sql,$db);
  if($auth_method)
  {
		$authrow = mysql_fetch_row($auth_method);
		if(mysql_num_rows($auth_method)==1)
		{
	    $auth_id = $authrow[0];
	    return $auth_id;
		}
		else
		{
	    return 0;
		}
	}
  else
  {
		return 0;
	}
}

/****************************************************************
find/return the ids of the default authentication methods
return $auth_methods (array with all the values of the defined/active methods)
****************************************************************/
function get_auth_active_methods()
{
	global $db;
	$sql = "SELECT auth_id,auth_settings FROM auth WHERE auth_default=1";
  $auth_method = mysql_query($sql,$db);
  if($auth_method)
  {
		$auth_methods = array();
		while($authrow = mysql_fetch_row($auth_method))
		{
			// get only those with valid,not empty settings
			if(($authrow[0]!=1) && (empty($authrow[1])))
			{
				continue;
			}
			else
			{
				$auth_methods[] = $authrow[0];
			}
		}
		if(!empty($auth_methods))
		{
			return $auth_methods;
		}
		else
		{
	    return 0;
		}
	}
  else
  {
		return 0;
	}
}

/****************************************************************
find if the eclass method is the only one active in the platform
return $is_eclass_unique (integer)
****************************************************************/
function is_eclass_unique()
{
	global $db;
	$is_eclass_unique = 0;
	$sql = "SELECT auth_id,auth_settings FROM auth WHERE auth_default=1";
  $auth_method = mysql_query($sql,$db);
  if($auth_method)
  {
		$count_methods = 0;
		$is_eclass = 0;
		while($authrow = mysql_fetch_row($auth_method))
		{
			if($authrow[0]==1)
			{
				$is_eclass = 1;
				$count_methods++;
			}
			else
			{
				if(empty($authrow[1]))
				{
					continue;
				}
				else
				{
					$count_methods++;
				}
			}
		}
		if(($is_eclass==1) && ($count_methods==1))
		{
			$is_eclass_unique = 1;
		}
		else
		{
			$is_eclass_unique = 0;
		}
	}
  else
  {
		$is_eclass_unique = 0;
	}
	
	return $is_eclass_unique;
	
}

/****************************************************************
find/return the string, describing in words the default authentication method
return $m (string)
****************************************************************/
function get_auth_info($auth)
{
	if(!empty($auth))
	{
		switch($auth)
		{
			case '1': $m = "Πιστοποίηση μέσω ECLASS";
				break;
			case '2': $m = "Πιστοποίηση μέσω POP3";
				break;
			case '3': $m = "Πιστοποίηση μέσω IMAP";
				break;
			case '4':	$m = "Πιστοποίηση μέσω LDAP";
				break;
			case '5': $m = "Πιστοποίηση μέσω External DB";
				break;
			default:	$m = 0;
				break;
		}
		return $m;
	}
	else
	{
		return 0;
	}
}

/****************************************************************
find/return the settings of the default authentication method

$auth : integer a value between 1 and 5: 1-eclass,2-pop3,3-imap,4-ldap,5-db)
return $auth_row : an associative array
****************************************************************/
function get_auth_settings($auth)
{
	$qry = "SELECT * FROM auth WHERE auth_id = ".$auth;
  $result = db_query($qry);
  if($result)
  {
		if(mysql_num_rows($result)==1)
		{
	    $auth_row = mysql_fetch_array($result,MYSQL_ASSOC);
	    return $auth_row;
		}
		else
		{
	    return 0;
		}	
	}
	else
	{
		return 0;
	}
}

/****************************************************************
Try to authenticate the user with the admin-defined auth method
true (the user is authenticated) / false (not authenticated)

$auth an integer-value for auth method(1:eclass, 2:pop3, 3:imap, 4:ldap, 5:db)
$test_username
$test_password
return $testauth (boolean: true-is authenticated, false-is not)
****************************************************************/
function auth_user_login ($auth,$test_username, $test_password) 
{
    global $mysqlMainDb;
    switch($auth)
    {
	case '1':
	    // Returns true if the username and password work and false if they don't
	    $sql = "SELECT user_id FROM user WHERE username='".$test_username."' AND password='".$test_password."'";
	    $result = db_query($sql);
	    if(mysql_num_rows($result)==1)
	    {
    		$testauth = true;
	    }
	    else
	    {
		$testauth = false;
	    }
	break;	
    
    
	case '2':
	    $pop3host = $GLOBALS['pop3host'];
	    $pop3=new pop3_class;
	    $pop3->hostname = $pop3host;	/* POP 3 server host name                      */
	    $pop3->port=110;				/* POP 3 server host port                      */
	    $user = $test_username;                       	/* Authentication user name                    */
	    $password = $test_password;                   	/* Authentication password                     */
	    $pop3->realm="";                         	/* Authentication realm or domain              */
	    $pop3->workstation="";			/* Workstation for NTLM authentication         */
	    $apop = 0;			/* Use APOP authentication                     */
	    $pop3->authentication_mechanism="USER";  /* SASL authentication mechanism               */
	    $pop3->debug=0;                          /* Output debug information                    */
	    $pop3->html_debug=1;                     /* Debug information is in HTML                */
	    $pop3->join_continuation_header_lines=1; /* Concatenate headers split in multiple lines */

	    if(($error=$pop3->Open())=="")
	    {
		if(($error=$pop3->Login($user,$password,$apop))=="")
		{
		    if($error=="" && ($error=$pop3->Close())=="")
		    {
		    $testauth = true;
		    }
		    else
		    {
		    $testauth = false;
		    }
		}
		else
		{
		    $testauth = false;
		}
	    }
	    else
	    {
		$testauth = false;
	    }
	    if($error!="")
	    {
		$testauth = false;
	    }
	    break;
	
	case '3':
	    $imaphost = $GLOBALS['imaphost'];
	    $imapauth = imap_auth($imaphost, $test_username, $test_password);
	    if($imapauth)
	    {
		$testauth = true;
	    }
	    else
	    {
		$testauth = false;
	    }
	    break;
	    
	case '4':
			$ldaphost = $GLOBALS['ldaphost'];
			$basedn = $GLOBALS['ldapbind_dn'];
			$ldap_uid = $test_username;
			$ldap_passwd = $test_password;
			// anonymous account:
			$a_user = $GLOBALS['ldapbind_user'];
			$a_pass = $GLOBALS['ldapbind_pw'];
			$testauth = "false";
			
			//$ds=ldap_connect($ldaphost);  //get the ldapServer, baseDN from the db
			
			    // suppose user has provided a pair: $user, $pass
    $ldap_host = $ldaphost;
    $ldap_base_dn = $basedn;
    $ldap_user_attrib = 'uid';
    $user = $ldap_uid;
    $pass = $ldap_passwd;
    $all_ldap_base_dn     = array();
    $all_ldap_user_attrib = array();

    $all_ldap_base_dn = array($ldap_base_dn);

    // Transfer the array of user attributes to a new value. Create an array of the user attributes to match
    // the number of base dn's if a single user attribute has been passed.
    $all_ldap_user_attrib[] = $ldap_user_attrib;

    $ldap = ldap_connect($ldap_host);

    if($ldap)		// Check that connection was established
    {
        // now process all base dn's until authentication is achieved or fail
        foreach( $all_ldap_base_dn as $idx => $base_dn)
        {
            // construct dn for user
            $dn = $all_ldap_user_attrib[$idx] . "=" . $user . "," . $base_dn;

            // try an authenticated bind. use this to confirm that the user/password pair
            if(ldap_bind($ldap, $dn, $pass))
            {
		
							$testauth = true;

            } // end if
            else
            {
            	$testauth = false;
            }
        } // foreach
        @ldap_unbind($ldap);
    } // if($ldap)
    else
    {
    	$testauth = false;
    }

			break;    

	case '5':
	    
	    //$dbtype = $GLOBALS['dbtype'];
	    $dbhost = $GLOBALS['dbhost'];
	    $dbname = $GLOBALS['dbname'];
	    $dbuser = $GLOBALS['dbuser'];
	    $dbpass = $GLOBALS['dbpass'];
	    $dbtable = $GLOBALS['dbtable'];
	    $dbfielduser = $GLOBALS['dbfielduser'];
	    $dbfieldpass = $GLOBALS['dbfieldpass'];
	    $newlink = true;
	    mysql_close($GLOBALS['db']);			// close the previous link
	    $link = mysql_connect($dbhost,$dbuser,$dbpass,$newlink);
	    if($link)
	    {
				$db_ext = mysql_select_db($dbname,$link);
				if($db_ext)
				{
		    	$qry = "SELECT * FROM ".$dbname.".".$dbtable." WHERE ".$dbfielduser."='".$test_username."' AND ".$dbfieldpass."='".$test_password."'";
		    	$res = mysql_query($qry,$link);
		    	if($res)
		    	{
						if(mysql_num_rows($res)>0)
						{
			     		$testauth = true;
			    		mysql_close($link);
							//mysql_select_db($mysqlMainDb,$GLOBALS['db']);
							// Connect to database
							$GLOBALS['db'] = mysql_connect($GLOBALS['mysqlServer'], $GLOBALS['mysqlUser'], $GLOBALS['mysqlPassword']);
							if (mysql_version()) mysql_query("SET NAMES greek");
							mysql_select_db($mysqlMainDb, $GLOBALS['db']);


						}
		    	}
		    	else
		    	{
						$testauth = false;
		    	}
		    	
				}
				else
				{
		    	$testauth = false;
				}
	    }
	    else
	    {
				$testauth = false;
	    }
	    break;
	    
	default:
	    $testauth = $auth;
	    break;
    }
    
    return $testauth;

}

/********************************************************************
Show a selection box. Taken from main.lib.php
Difference: the return value and not just echo the select box

$entries: an array of (value => label)
$name: the name of the selection element
$default: if it matches one of the values, specifies the default entry
***********************************************************************/
function selection3($entries, $name, $default = '')
{
	$select_box = "<select name='$name'>\n";
	foreach ($entries as $value => $label) 
	{
	    if ($value == $default) 
	    {
		$select_box .= "<option selected value='" . htmlspecialchars($value) . "'>" .
				htmlspecialchars($label) . "</option>\n";
	    } 
	    else 
	    {
		$select_box .= "<option value='" . htmlspecialchars($value) . "'>" .
				htmlspecialchars($label) . "</option>\n";
	    }
	}
	$select_box .= "</select>\n";
	
	return $select_box;
}


/****************************************************************
Check if an account is active or not. Apart from admin, everybody has
a registration unix timestamp and an expiration unix timestamp.
By default is set to last a year

$userid : the id of the account
return $testauth (boolean: true-is authenticated, false-is not)
****************************************************************/
function check_activity($userid)
{
	global $db;
	$qry = "SELECT registered_at,expires_at FROM user WHERE user_id=".$userid;
	$res = mysql_query($qry,$db);
	if(($res) && (mysql_num_rows($res)==1))
	{
		$row = mysql_fetch_row($res);
		if($row[1]>time())
		{
			return 1;
		}
		else
		{
			return 0;
		}
	}
	else
	{
		return 0;
	}
}


 
// *****************************************************************************
// Copyright 2003-2004 by A J Marston <http://www.tonymarston.net>
// Distributed under the GNU General Public Licence
// *****************************************************************************
class Encryption 
{
    var $scramble1;         // 1st string of ASCII characters
    var $scramble2;         // 2nd string of ASCII characters
    var $errors;            // array of error messages
    var $adj;               // 1st adjustment value (optional)
    var $mod;               // 2nd adjustment value (optional)
    
    // ****************************************************************************
    // class constructor
    // ****************************************************************************
    function encryption ()
    {
        $this->errors = array();
        
        // Each of these two strings must contain the same characters, but in a different order.
        // Use only printable characters from the ASCII table.
        // Do not use single quote, double quote or backslash as these have special meanings in PHP.
        // Each character can only appear once in each string EXCEPT for the first character
        // which must be duplicated at the end (this gets round a bijou problemette when the
        // first character of the password is also the first character in $scramble1).
        $this->scramble1 = '! #$%&()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[]^_`abcdefghijklmnopqrstuvwxyz{|}~!';
        $this->scramble2 = 'f^jAE]okIOzU[2&q1{3`h5w_794p@6s8?BgP>dFV=m D<TcS%Ze|r:lGK/uCy.Jx)HiQ!#$~(;Lt-R}Ma,NvW+Ynb*0Xf';
        //$this->scramble1 = '! #$%&()*+,-./0123456789:;\'\\\"<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[]^_`abcdefghijklmnopqrstuvwxyz{|}~!';
        //$this->scramble2 = 'f^jAE]okIOzU[2&q1{3`h5w_794\"\'\\p@6s8?BgP>dFV=m D<TcS%Ze|r:lGK/uCy.Jx)HiQ!#$~(;Lt-R}Ma,NvW+Ynb*0Xf';

        if (strlen($this->scramble1) <> strlen($this->scramble2)) {
            $this->errors[] = '** SCRAMBLE1 is not same length as SCRAMBLE2 **';
        } // if
        
        $this->adj = 1.75;  // this value is added to the rolling fudgefactors
        $this->mod = 3;     // if divisible by this the adjustment is made negative
        
    } // constructor
    
    // ****************************************************************************
    function decrypt ($key, $source) 
    // decrypt string into its original form
    {
        //DebugBreak();
        // convert $key into a sequence of numbers
        $fudgefactor = $this->_convertKey($key);
        if ($this->errors) return;
        
        if (empty($source)) {
            $this->errors[] = 'No value has been supplied for decryption';
            return;
        } // if
        
        $target = null;
        $factor2 = 0;
        
        for ($i = 0; $i < strlen($source); $i++) {
            // extract a character from $source
            $char2 = substr($source, $i, 1);
            
            // identify its position in $scramble2
            $num2 = strpos($this->scramble2, $char2);
            if ($num2 === false) {
                $this->errors[] = "Source string contains an invalid character ($char2)";
                return;
            } // if
            
            if ($num2 == 0) {
                // use the last occurrence of this letter, not the first
                $num2 = strlen($this->scramble1)-1;
            } // if
            
            // get an adjustment value using $fudgefactor
            $adj     = $this->_applyFudgeFactor($fudgefactor);
            
            $factor1 = $factor2 + $adj;                 // accumulate in $factor1
            $num1    = round($factor1 * -1) + $num2;    // generate offset for $scramble1
            $num1    = $this->_checkRange($num1);       // check range
            $factor2 = $factor1 + $num2;                // accumulate in $factor2
            
            // extract character from $scramble1
            $char1 = substr($this->scramble1, $num1, 1);
            
            // append to $target string
            $target .= $char1;

            //echo "char1=$char1, num1=$num1, adj= $adj, factor1= $factor1, num2=$num2, char2=$char2, factor2= $factor2<br />\n";
            
        } // for
        
        return rtrim($target);
        
    } // decrypt
    
    // ****************************************************************************
    function encrypt ($key, $source, $sourcelen = 0) 
    // encrypt string into a garbled form
    {
        //DebugBreak();
        // convert $key into a sequence of numbers
        $fudgefactor = $this->_convertKey($key);
        if ($this->errors) return;

        if (empty($source)) {
            $this->errors[] = 'No value has been supplied for encryption';
            return;
        } // if
        
        // pad $source with spaces up to $sourcelen
        while (strlen($source) < $sourcelen) {
            $source .= ' ';
        } // while
        
        $target = null;
        $factor2 = 0;
        
        for ($i = 0; $i < strlen($source); $i++) {
            // extract a character from $source
            $char1 = substr($source, $i, 1);
            
            // identify its position in $scramble1
            $num1 = strpos($this->scramble1, $char1);
            if ($num1 === false) {
                $this->errors[] = "Source string contains an invalid character ($char1)";
                return;
            } // if
            
            // get an adjustment value using $fudgefactor
            $adj     = $this->_applyFudgeFactor($fudgefactor);
            
            $factor1 = $factor2 + $adj;             // accumulate in $factor1
            $num2    = round($factor1) + $num1;     // generate offset for $scramble2
            $num2    = $this->_checkRange($num2);   // check range
            $factor2 = $factor1 + $num2;            // accumulate in $factor2
            
            // extract character from $scramble2
            $char2 = substr($this->scramble2, $num2, 1);
            
            // append to $target string
            $target .= $char2;

            //echo "char1=$char1, num1=$num1, adj= $adj, factor1= $factor1, num2=$num2, char2=$char2, factor2= $factor2<br />\n";
            
        } // for
        
        return $target;
        
    } // encrypt
    
    // ****************************************************************************
    function getAdjustment () 
    // return the adjustment value
    {
        return $this->adj;
        
    } // setAdjustment
    
    // ****************************************************************************
    function getModulus () 
    // return the modulus value
    {
        return $this->mod;
        
    } // setModulus
    
    // ****************************************************************************
    function setAdjustment ($adj) 
    // set the adjustment value
    {
        $this->adj = (float)$adj;
        
    } // setAdjustment
    
    // ****************************************************************************
    function setModulus ($mod) 
    // set the modulus value
    {
        $this->mod = (int)abs($mod);    // must be a positive whole number
        
    } // setModulus
    
    // ****************************************************************************
    // private methods
    // ****************************************************************************
    function _applyFudgeFactor (&$fudgefactor) 
    // return an adjustment value  based on the contents of $fudgefactor
    // NOTE: $fudgefactor is passed by reference so that it can be modified
    {
        $fudge = array_shift($fudgefactor);     // extract 1st number from array
        $fudge = $fudge + $this->adj;           // add in adjustment value
        $fudgefactor[] = $fudge;                // put it back at end of array
        
        if (!empty($this->mod)) {               // if modifier has been supplied
            if ($fudge % $this->mod == 0) {     // if it is divisible by modifier
                $fudge = $fudge * -1;           // make it negative
            } // if
        } // if
        
        return $fudge;
        
    } // _applyFudgeFactor
    
    // ****************************************************************************
    function _checkRange ($num) 
    // check that $num points to an entry in $this->scramble1
    {
        $num = round($num);         // round up to nearest whole number
        
        // indexing starts at 0, not 1, so subtract 1 from string length
        $limit = strlen($this->scramble1)-1;
        
        while ($num > $limit) {
            $num = $num - $limit;   // value too high, so reduce it
        } // while
        while ($num < 0) {
            $num = $num + $limit;   // value too low, so increase it
        } // while
        
        return $num;
        
    } // _checkRange
    
    // ****************************************************************************
    function _convertKey ($key) 
    // convert $key into an array of numbers
    {
        if (empty($key)) {
            $this->errors[] = 'No value has been supplied for the encryption key';
            return;
        } // if
        
        $array[] = strlen($key);    // first entry in array is length of $key
        
        $tot = 0;
        for ($i = 0; $i < strlen($key); $i++) {
            // extract a character from $key
            $char = substr($key, $i, 1);
            
            // identify its position in $scramble1
            $num = strpos($this->scramble1, $char);
            if ($num === false) {
                $this->errors[] = "Key contains an invalid character ($char)";
                return;
            } // if
            
            $array[] = $num;        // store in output array
            $tot = $tot + $num;     // accumulate total for later
        } // for
        
        $array[] = $tot;            // insert total as last entry in array
        
        return $array;
        
    } // _convertKey
    
// ****************************************************************************
} // end Encryption
// ****************************************************************************

?>