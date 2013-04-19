<?php
/* ========================================================================
 * Open eClass 2.6
* E-learning and Course Management System
* ========================================================================
* Copyright 2003-2011  Greek Universities Network - GUnet
* A full copyright notice can be read in "/info/copyright.txt".
* For a full list of contributors, see "credits.txt".
*
* Open eClass is an open platform distributed in the hope that it will
* be useful (without any warranty), under the terms of the GNU (General
		* Public License) as published by the Free Software Foundation.
* The full license can be read in "/info/license/license_gpl.txt".
*
* Contact address: GUnet Asynchronous eLearning Group,
*                  Network Operations Center, University of Athens,
*                  Panepistimiopolis Ilissia, 15784, Athens, Greece
*                  e-mail: info@openeclass.org
* ======================================================================== */

/**
 * Katsika Generate Password wrapper.
 * This will reflect the current generator that is in use, or combine different
 * generators.
 *
 * @return string
 */
function genPass() {
    
    $rand = genPassRandom(rand(2,3));
    
    if($flag = rand(0, 1)) {
        return genPassPronouncable().$rand;
    } else {
        return $rand.genPassPronouncable();
    }
}

/**
 * Nice function that generates an easy-to-pronounce string.
 * @author Stefanos Stamatis <stef@noc.uoa.gr>
 * 
 * @return string
 */
function genPassPronouncable() {
    
    $makepass="";
    $syllables="er,in,tia,wol,fe,pre,vet,jo,nes,al,len,son,cha,ir,ler,bo,ok,tio,nar,sim,ple,bla,ten,toe,cho,co,lat,spe,ak,er,po,co,lor,pen,cil,li,ght,wh,at,the,he,ck,is,mam,bo,no,fi,ve,any,way,pol,iti,cs,ra,dio,sou,rce,sea,rch,pa,per,com,bo,sp,eak,st,fi,rst,gr,oup,boy,ea,gle,tr,ail,bi,ble,brb,pri,dee,kay,en,be,se";
    
    $syllable_array = explode(",", $syllables);
    srand((double)microtime() * 1000000);
    
    while(strlen($makepass) < 8) {
        if (rand() %10 == 1) {
            $makepass .= sprintf("%0.0f", (rand() % 50) + 1);
        } else {
            $makepass .= sprintf("%s", $syllable_array[rand() % 62]);
        }
    }
    
    return(substr(str_replace("\n", '', $makepass), 0, 8));
}

/**
 * Generate random password.
 *
 * @return string
 */
function genPassRandom($length = 8)    {
    
    $allowable_characters = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890";

    mt_srand((double)microtime() * 1000000);

    $pass = "";

    for($i = 0; $i < $length; $i++) {
        $pass .= $allowable_characters[mt_rand(0, strlen($allowable_characters) - 1)];
    }

    return $pass;
}


// creating passwords automatically
function create_pass() {

	$parts = array('a', 'ba', 'fa', 'ga', 'ka', 'la', 'ma', 'xa',
			'e', 'be', 'fe', 'ge', 'ke', 'le', 'me', 'xe',
			'i', 'bi', 'fi', 'gi', 'ki', 'li', 'mi', 'xi',
			'o', 'bo', 'fo', 'go', 'ko', 'lo', 'mo', 'xo',
			'u', 'bu', 'fu', 'gu', 'ku', 'lu', 'mu', 'xu',
			'ru', 'bur', 'fur', 'gur', 'kur', 'lur', 'mur',
			'sy', 'zy', 'gy', 'ky', 'tri', 'kro', 'pra');
	$max = count($parts) - 1;
	$num = rand(10,499);
	return $parts[rand(0,$max)] . $parts[rand(0,$max)] . $num;
}