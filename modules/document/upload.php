<?php
 
$require_current_course = TRUE;  // flag ασφάλειας
$langFiles = 'document';  // αρχείο μηνυμάτων
 
//θέλουμε help
$require_help = FALSE;
//$helpTopic = 'User';
 
// αλλάζουμε το include 'init.php ' και αντικαθιστάται από το baseTheme.php
//include "../../include/init.php";
 
// το API: αρχείο με μεθόδους για τη δημιουργία τμημάτων της διεπαφής (baseTheme.php)
include "../../include/baseTheme.php";
 
$nameTools = $langDownloadFile;
 
// ΔΕΝ καλείται η συνάρτηση begin_page(). Η συνάρτηση αυτή παράγει μέρος του presentation layer.
// Αυτό πλέον έχει ενσωματωθεί και αυτοματοποιηθεί στις συναρτήσεις του baseTheme. Πιο κάτω στον κώδικα
// θα δείτε πως το κάνετε αυτό.
// begin_page();
 
// ξεκινάει το κυρίως μέρος του κάθε υποσυστήματος
// φεύγουν τα $tool_content .=  και ο κώδικας HTML γίνεται concat στο $tool_content

$tool_content .= "<i>$langNotRequired</i><br><br><br>
<form action=\"document.php\" method=\"post\" enctype=\"multipart/form-data\">
    <input type=\"hidden\" name=\"uploadPath\" value=\"$curDirPath\">
    $langDownloadFile&nbsp;:<br><input type=\"file\" name=\"userFile\"><br><hr>
    $langTitle&nbsp;:<br><input type=\"text\" name=\"file_title\" value=\"\" size=\"40\"><br>
    $langComment&nbsp;:<br><input type=\"text\" name=\"file_comment\" value=\"\" size=\"40\"><br>
    $langCategory&nbsp;:
    <br>
    <select name=\"file_category\">
		<option selected=\"selected\" value=\"0\">$langCategoryOther<br>
		<option value=\"1\">$langCategoryExcercise<br>
		<option value=\"2\">$langCategoryLecture<br>
		<option value=\"3\">$langCategoryEssay<br>
		<option value=\"4\">$langCategoryDescription<br>
		<option value=\"5\">$langCategoryExample<br>
		<option value=\"6\">$langCategoryTheory<br>
    </select><br>
			

        
    
    <input type=\"hidden\" name=\"file_creator\" value=\"$prenom $nom\" size=\"40\"><br>
    $langSubject&nbsp;:<br><input type=\"text\" name=\"file_subject\" value=\"\" size=\"40\"><br>
    $langDescription&nbsp;:<br><input type=\"text\" name=\"file_description\" value=\"\" size=\"40\"><br>
    $langAuthor&nbsp;:<br><input type=\"text\" name=\"file_author\" value=\"\" size=\"40\"><br>
    <input type=\"hidden\" name=\"file_date\" value=\"\" size=\"40\">
    <input type=\"hidden\" name=\"file_format\" value=\"\" size=\"40\">
    $langLanguage&nbsp;:<br>
    

    
    
		<select name=\"file_language\">
			<option selected=\"selected\" value=\"en\">English
			</option><option value=\"da\">Danish
			</option><option value=\"fi\">Finnish
			</option><option value=\"is\">Icelandic
			</option><option value=\"no\">Norwegian
			</option><option value=\"no-nyn\">No: Nynorsk
			</option><option value=\"no-bok\">No: Bokmaal
			</option><option value=\"sv\">Swedish
			</option><option value=\"i-sami-no\">Northern Sαmi
			</option><option value=\"ab\">Abkhazian
			</option><option value=\"aa\">Afar
			
			</option><option value=\"af\">Afrikaans
			</option><option value=\"sq\">Albanian
			</option><option value=\"am\">Amharic
			</option><option value=\"ar\">Arabic
			</option><option value=\"hy\">Armenian
			</option><option value=\"as\">Assamese
			</option><option value=\"ay\">Aymara
			</option><option value=\"az\">Azerbaijani
			</option><option value=\"ba\">Bashkir
			</option><option value=\"eu\">Basque
			</option><option value=\"bn\">Bengali; Bangla
			</option><option value=\"dz\">Bhutani
			</option><option value=\"bh\">Bihari
			</option><option value=\"bi\">Bislama
			</option><option value=\"br\">Breton
			</option><option value=\"bg\">Bulgarian
			</option><option value=\"my\">Burmese
			
			</option><option value=\"be\">Byelorussian
			</option><option value=\"km\">Cambodian
			</option><option value=\"ca\">Catalan
			</option><option value=\"zh\">Chinese
			</option><option value=\"kw\">Cornish
			</option><option value=\"co\">Corsican
			</option><option value=\"hr\">Croatian
			</option><option value=\"cs\">Czech
			</option><option value=\"nl\">Dutch
			</option><option value=\"eo\">Esperanto
			</option><option value=\"et\">Estonian
			</option><option value=\"fo\">Faroese
			</option><option value=\"fj\">Fiji
			</option><option value=\"fr\">French
			</option><option value=\"fy\">Frisian
			</option><option value=\"gl\">Galician
			</option><option value=\"ka\">Georgian
			
			</option><option value=\"de\">German
			</option><option value=\"el\">Greek
			</option><option value=\"kl\">Greenlandic
			</option><option value=\"gn\">Guarani
			</option><option value=\"gu\">Gujarati
			</option><option value=\"ha\">Hausa
			</option><option value=\"he\">Hebrew
			</option><option value=\"hi\">Hindi
			</option><option value=\"hu\">Hungarian
			</option><option value=\"id\">Indonesian
			</option><option value=\"ia\">Interlingua
			</option><option value=\"ie\">Interlingue
			</option><option value=\"iu\">Inuktitut
			</option><option value=\"ik\">Inupiak
			</option><option value=\"ga\">Irish (Irish Gaelic)
			</option><option value=\"it\">Italian
			</option><option value=\"ja\">Japanese
			
			</option><option value=\"jw\">Javanese
			</option><option value=\"kn\">Kannada
			</option><option value=\"ks\">Kashmiri
			</option><option value=\"kk\">Kazakh
			</option><option value=\"rw\">Kinyarwanda
			</option><option value=\"ky\">Kirghiz
			</option><option value=\"rn\">Kirundi
			</option><option value=\"ko\">Korean
			</option><option value=\"ku\">Kurdish
			</option><option value=\"lo\">Laothian (Laotian)
			</option><option value=\"la\">Latin
			</option><option value=\"lv\">Latvian; Lettish
			</option><option value=\"ln\">Lingala
			</option><option value=\"lt\">Lithuanian
			</option><option value=\"lb\">Luxemburgish
			</option><option value=\"mk\">Macedonian
			</option><option value=\"mg\">Malagasy
			
			</option><option value=\"ms\">Malay
			</option><option value=\"ml\">Malayalam
			</option><option value=\"mt\">Maltese
			</option><option value=\"gv\">Manx Gaelic
			</option><option value=\"mi\">Maori
			</option><option value=\"mr\">Marathi
			</option><option value=\"mo\">Moldavian
			</option><option value=\"mn\">Mongolian
			</option><option value=\"na\">Nauru
			</option><option value=\"ne\">Nepali
			</option><option value=\"oc\">Occitan
			</option><option value=\"or\">Oriya
			</option><option value=\"om\">Oromo (Afan) 
			</option><option value=\"ps\">Pashto; Pushto
			</option><option value=\"fa\">Persian
			</option><option value=\"pl\">Polish
			</option><option value=\"pt\">Portuguese
			
			</option><option value=\"pa\">Punjabi
			</option><option value=\"qu\">Quechua
			</option><option value=\"rm\">Rhaeto-Romance
			</option><option value=\"ro\">Romanian
			</option><option value=\"ru\">Russian
			</option><option value=\"sm\">Samoan
			</option><option value=\"sg\">Sangho
			</option><option value=\"sa\">Sanskrit
			</option><option value=\"gd\">Scots Gaelic (Scottish Gaelic)
			</option><option value=\"sr\">Serbian
			</option><option value=\"sh\">Serbo-Croatian
			</option><option value=\"st\">Sesotho
			</option><option value=\"tn\">Setswana
			</option><option value=\"sn\">Shona
			</option><option value=\"sd\">Sindhi
			</option><option value=\"si\">Singhalese
			</option><option value=\"ss\">Siswati
			
			</option><option value=\"sk\">Slovak
			</option><option value=\"sl\">Slovenian
			</option><option value=\"so\">Somali
			</option><option value=\"es\">Spanish
			</option><option value=\"su\">Sudanese
			</option><option value=\"sv\">Swedish
			</option><option value=\"sw\">Swahili
			</option><option value=\"tl\">Tagalog
			</option><option value=\"tg\">Tajik
			</option><option value=\"ta\">Tamil
			</option><option value=\"tt\">Tatar
			</option><option value=\"te\">Telugu
			</option><option value=\"th\">Thai
			</option><option value=\"bo\">Tibetan
			</option><option value=\"ti\">Tigrinya
			</option><option value=\"to\">Tonga
			</option><option value=\"ts\">Tsonga
			
			</option><option value=\"tr\">Turkish
			</option><option value=\"tk\">Turkmen
			</option><option value=\"tw\">Twi
			</option><option value=\"ug\">Uigur
			</option><option value=\"uk\">Ukrainian
			</option><option value=\"ur\">Urdu
			</option><option value=\"uz\">Uzbek
			</option><option value=\"vi\">Vietnamese
			</option><option value=\"vo\">Volapόk
			</option><option value=\"cy\">Welsh
			</option><option value=\"wo\">Wolof
			</option><option value=\"xh\">Xhosa
			</option><option value=\"yi\">Yiddish
			</option><option value=\"yo\">Yoruba
			</option><option value=\"za\">Zhuang
			</option><option value=\"zu\">Zulu
			</option>
		
		</select>";

    

    $tool_content .=  "<br>
    $langCopyrighted&nbsp;:<br><input name=\"file_copyrighted\" type=\"radio\" value=\"0\" checked=\"checked\" /> $langCopyrightedUnknown
    					   <input name=\"file_copyrighted\" type=\"radio\" value=\"2\" /> $langCopyrightedFree
  						   <input name=\"file_copyrighted\" type=\"radio\" value=\"1\" /> $langCopyrightedNotFree
    <br><br>

   
    <input type=\"checkbox\" name=\"uncompress\" value=\"1\">$langUncompress<br><br>
    <input type=\"submit\" value=\"$langDownload\"><br>";
    
    $tool_content .=  "<small>".$langNoticeGreek."</small>";
    $tool_content .=  "</form>";
 

 
draw($tool_content, '2');
 
?>