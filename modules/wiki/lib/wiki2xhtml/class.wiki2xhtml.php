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

/**===========================================================================
	class.wiki2xhtml.php
	@last update: 15-05-2007 by Thanos Kyritsis
	@authors list: Thanos Kyritsis <atkyritsis@upnet.gr>
	               
	based on DotClear version 3.1d licensed under GPL
	      copyright (c) 2004 Olivier Meunier and contributors.
	      
	      original file: class.wiki2xhtml Revision: 1.1.2.2
	      
	DotClear contributors: Stephanie Booth
                           Mathieu Pillard
                           Christophe Bonijol
                           Jean-Charles Bagneris
                           Nicolas Chachereau
                           J�r�me Lipowicz
==============================================================================        
    @Description: 

    @Comments:
 
    @todo: 
==============================================================================
*/




class wiki2xhtml
{
	var $__version__ = '3.1d';

	var $T;
	var $opt;
	var $line;
	var $foot_notes;

	var $tags;
	var $open_tags;
	var $close_tags;
	var $all_tags;
	var $tag_pattern;
	var $escape_table;
	var $allowed_inline = array();

	function wiki2xhtml()
	{
		# Mise en place des options
		$this->setOpt('active_title',1);		# Activation des titres !!!
		$this->setOpt('active_setext_title',0);	# Activation des titres setext (EXPERIMENTAL)
		$this->setOpt('active_hr',1);			# Activation des <hr />
		$this->setOpt('active_lists',1);		# Activation des listes
		$this->setOpt('active_quote',1);		# Activation du <blockquote>
		$this->setOpt('active_pre',1);		# Activation du <pre>
		$this->setOpt('active_empty',0);		# Activation du bloc vide ���
		$this->setOpt('active_auto_urls',0);	# Activation de la reconnaissance d'url (inactif)
		$this->setOpt('active_autoemails',0);	# Activation de la reconnaissance des emails (inactif)
		$this->setOpt('active_antispam',1);     # Activation de l'antispam pour les emails
		$this->setOpt('active_urls',1);		# Activation des liens []
		$this->setOpt('active_auto_img',1);	# Activation des images automatiques dans les liens []
		$this->setOpt('active_img',1);		# Activation des images (())
		$this->setOpt('active_anchor',1);		# Activation des ancres ~...~
		$this->setOpt('active_em',1);			# Activation du <em> ''...''
		$this->setOpt('active_strong',1);		# Activation du <strong> __...__
		$this->setOpt('active_br',1);			# Activation du <br /> %%%
		$this->setOpt('active_q',1);			# Activation du <q> {{...}}
		$this->setOpt('active_code',1);		# Activation du <code> @@...@@
		$this->setOpt('active_acronym',1); 	# Activation des acronymes
		$this->setOpt('active_ins',1);		# Activation des ins ++..++
		$this->setOpt('active_del',1);		# Activation des del --..--
		$this->setOpt('active_footnotes',1);	# Activation des notes de bas de page
		$this->setOpt('active_wikiwords',0);	# Activation des mots wiki
		$this->setOpt('active_macros',0);		# Activation des macros ���..���

		$this->setOpt('parse_pre',1);			# Parser l'int�rieur de blocs <pre> ?

		$this->setOpt('active_fix_word_entities',0); # Fixe les caract�res MS
		$this->setOpt('active_fr_syntax',1);	# Corrections syntaxe FR

		$this->setOpt('first_title_level',3);	# Premier niveau de titre <h..>

		$this->setOpt('note_prefix','wiki-footnote');
		$this->setOpt('note_str','<div class="footnotes"><h4>Notes</h4>%s</div>');
		$this->setOpt('words_pattern','((?<![A-Za-z0-9��-��-��-�])([A-Z�-��-�][a-z��-��-�]+){2,}(?![A-Za-z0-9��-��-��-�]))');

		$this->setOpt('mail_pattern','/^([0-9a-zA-Z]([-.\w]*[0-9a-zA-Z])*@([0-9a-zA-Z][-\w]*[0-9a-zA-Z]\.)+[a-zA-Z]{2,9})$/');

		$this->setOpt('acronyms_file',dirname(__FILE__).'/acronyms.txt');

		$this->acro_table = $this->__getAcronyms();
		$this->foot_notes = array();
	}

	function setOpt($option, $value)
	{
		$this->opt[$option] = $value;
	}

	function getOpt($option)
	{
		return (!empty($this->opt[$option])) ? $this->opt[$option] : false;
	}

	function transform($in)
	{
		# Initialisation des tags
		$this->__initTags();
		$this->foot_notes = array();

		# V�rification du niveau de titre
		if ($this->getOpt('first_title_level') > 4) {
			$this->setOpt('first_title_level',4);
		}

		$res = str_replace("\r", '', $in);

		$escape_pattern = array();

		# traitement des titres � la setext
		if ($this->getOpt('active_setext_title') && $this->getOpt('active_title')) {
			$res = preg_replace('/^(.*)\n[=]{5,}$/m','!!!$1',$res);
			$res = preg_replace('/^(.*)\n[-]{5,}$/m','!!$1',$res);
		}

		# Transformation des mots Wiki
		if ($this->getOpt('active_wikiwords') && $this->getOpt('words_pattern')) {
			$res = preg_replace('/'.$this->getOpt('words_pattern').'/ms','���$1���',$res);
		}

		$this->T = explode("\n",$res);
		$this->T[] = '';

		# Parse les blocs
		$res = $this->__parseBlocks();

		# Line break
		if ($this->getOpt('active_br')) {
			$res = preg_replace('/(?<!\\\)%%%/', '<br />', $res);
			$escape_pattern[] = '%%%';
		}

		# Correction des caract�res faits par certains traitement
		# de texte comme Word
		if ($this->getOpt('active_fix_word_entities')) {
			$wR = array(
			'�' => '&#8218;',
			'�' => '&#402;',
			'�' => '&#8222;',
			'�' => '&#8230;',
			'�' => '&#8224;',
			'�' => '&#8225;',
			'�' => '&#710;',
			'�' => '&#8240;',
			'�' => '&#352;',
			'�' => '&#8249;',
			'�' => '&#338;',
			'�' => '&#8216;',
			'�' => '&#8217;',
			'�' => '&#8220;',
			'�' => '&#8221;',
			'�' => '&#8226;',
			'�' => '&#8211;',
			'�' => '&#8212;',
			'�' => '&#732;',
			'�' => '&#8482;',
			'�' => '&#353;',
			'�' => '&#8250;',
			'�' => '&#339;',
			'�' => '&#376;',
			'�' => '&#8364;');

			$res = str_replace(array_keys($wR),array_values($wR),$res);
		}

		# Nettoyage des \s en trop
		$res = preg_replace('/([\s]+)(<\/p>|<\/li>|<\/pre>)/', '$2', $res);
		$res = preg_replace('/(<li>)([\s]+)/', '$1', $res);

		# On vire les escapes
		$res = preg_replace('/\\\('.implode('|',$escape_pattern).')/','$1',$res);

		# On vire les ���MotWiki��� qui sont rest� (dans les url...)
		if ($this->getOpt('active_wikiwords') && $this->getOpt('words_pattern')) {
			$res = preg_replace('/���'.$this->getOpt('words_pattern').'���/msU','$1',$res);
		}

		# On ajoute les notes
		if (count($this->foot_notes) > 0)
		{
			$res_notes = '';
			$i = 1;
			foreach ($this->foot_notes as $k => $v) {
				$res_notes .= "\n".'<p>[<a href="#rev-'.$k.'" name="'.$k.'">'.$i.'</a>] '.$v.'</p>';
				$i++;
			}
			$res .= sprintf("\n".$this->getOpt('note_str')."\n",$res_notes);
		}

		return $res;
	}

	/* PRIVATE
	--------------------------------------------------- */

	function __initTags()
	{
		$this->tags = array(
			'em' => array("''","''"),
			'strong' => array('__','__'),
			'acronym' => array('??','??'),
			'a' => array('[',']'),
			'img' => array('((','))'),
			'q' => array('{{','}}'),
			'code' => array('@@','@@'),
			'anchor' => array('~','~'),
			'del' => array('--','--'),
			'ins' => array('++','++'),
			'note' => array('$$','$$'),
			'word' => array('���','���'),
			'macro' => array('"""','"""')
		);

		# Suppression des tags selon les options
		if (!$this->getOpt('active_urls')) {
			unset($this->tags['a']);
		}
		if (!$this->getOpt('active_img')) {
			unset($this->tags['img']);
		}
		if (!$this->getOpt('active_anchor')) {
			unset($this->tags['anchor']);
		}
		if (!$this->getOpt('active_em')) {
			unset($this->tags['em']);
		}
		if (!$this->getOpt('active_strong')) {
			unset($this->tags['strong']);
		}
		if (!$this->getOpt('active_q')) {
			unset($this->tags['q']);
		}
		if (!$this->getOpt('active_code')) {
			unset($this->tags['code']);
		}
		if (!$this->getOpt('active_acronym')) {
			unset($this->tags['acronym']);
		}
		if (!$this->getOpt('active_ins')) {
			unset($this->tags['ins']);
		}
		if (!$this->getOpt('active_del')) {
			unset($this->tags['del']);
		}
		if (!$this->getOpt('active_footnotes')) {
			unset($this->tags['note']);
		}
		if (!$this->getOpt('active_wikiwords')) {
			unset($this->tags['word']);
		}
		if (!$this->getOpt('active_macros')) {
			unset($this->tags['macro']);
		}

		$this->open_tags = $this->__getTags();
		$this->close_tags = $this->__getTags(false);
		$this->all_tags = $this->__getAllTags();
		$this->tag_pattern = $this->__getTagsPattern();

		$this->escape_table = $this->all_tags;
		array_walk($this->escape_table,create_function('&$a','$a = \'\\\\\'.$a;'));
	}

	function __getTags($open=true)
	{
		$res = array();
		foreach ($this->tags as $k => $v) {
			$res[$k] = ($open) ? $v[0] : $v[1];
		}
		return $res;
	}

	function __getAllTags()
	{
		$res = array();
		foreach ($this->tags as $v) {
			$res[] = $v[0];
			$res[] = $v[1];
		}
		return array_values(array_unique($res));
	}

	function __getTagsPattern($escape=false)
	{
		$res = $this->all_tags;
		array_walk($res,create_function('&$a','$a = preg_quote($a,"/");'));

		if (!$escape) {
			return '/(?<!\\\)('.implode('|',$res).')/';
		} else {
			return '('.implode('|',$res).')';
		}
	}

	/* Blocs
	--------------------------------------------------- */
	function __parseBlocks()
	{
		$mode = $type = NULL;
		$res = '';
		$max = count($this->T);

		for ($i=0; $i<$max; $i++)
		{
			$pre_mode = $mode;
			$pre_type = $type;
			$end = ($i+1 == $max);

			$line = $this->__getLine($i,$type,$mode);

			if ($type != 'pre' || $this->getOpt('parse_pre')) {
				$line = $this->__inlineWalk($line);
			}

			$res .= $this->__closeLine($type,$mode,$pre_type,$pre_mode);
			$res .= $this->__openLine($type,$mode,$pre_type,$pre_mode);

			# P dans les blockquotes
			if ($type == 'blockquote' && trim($line) == '' && $pre_type == $type) {
				$res .= "</p>\n<p>";
			}

			# Correction de la syntaxe FR dans tous sauf pre et hr
			# Sur id�e de Christophe Bonijol
			# Changement de regex (Nicolas Chachereau)
			if ($this->getOpt('active_fr_syntax') && $type != NULL && $type != 'pre' && $type != 'hr') {
				$line = preg_replace('/[ ]+([:?!;�](\s|$))/','&nbsp;$1',$line);
				$line = preg_replace('/(�)[ ]+/','$1&nbsp;',$line);
			}

			$res .= $line;
		}

		return trim($res);
	}

	function __getLine($i,&$type,&$mode)
	{
		$pre_type = $type;
		$pre_mode = $mode;
		$type = $mode = NULL;

		if (empty($this->T[$i])) {
			return false;
		}

		$line = htmlspecialchars($this->T[$i],ENT_NOQUOTES);

		# Ligne vide
		if (empty($line))
		{
			$type = NULL;
		}
		elseif ($this->getOpt('active_empty') && preg_match('/^���(.*)$/',$line,$cap))
		{
			$type = NULL;
			$line = trim($cap[1]);
		}
		# Titre
		elseif ($this->getOpt('active_title') && preg_match('/^([!]{1,4})(.*)$/',$line,$cap))
		{
			$type = 'title';
			$mode = strlen($cap[1]);
			$line = trim($cap[2]);
		}
		# Ligne HR
		elseif ($this->getOpt('active_hr') && preg_match('/^[-]{4}[- ]*$/',$line))
		{
			$type = 'hr';
			$line = NULL;
		}
		# Blockquote
		elseif ($this->getOpt('active_quote') && preg_match('/^(&gt;|;:)(.*)$/',$line,$cap))
		{
			$type = 'blockquote';
			$line = trim($cap[2]);
		}
		# Liste
		elseif ($this->getOpt('active_lists') && preg_match('/^([*#]+)(.*)$/',$line,$cap))
		{
			$type = 'list';
			$mode = $cap[1];
			$valid = true;

			# V�rification d'int�grit�
			$dl = ($type != $pre_type) ? 0 : strlen($pre_mode);
			$d = strlen($mode);
			$delta = $d-$dl;

			if ($delta < 0 && strpos($pre_mode,$mode) !== 0) {
				$valid = false;
			}
			if ($delta > 0 && $type == $pre_type && strpos($mode,$pre_mode) !== 0) {
				$valid = false;
			}
			if ($delta == 0 && $mode != $pre_mode) {
				$valid = false;
			}
			if ($delta > 1) {
				$valid = false;
			}

			if (!$valid) {
				$type = 'p';
				$mode = NULL;
				$line = '<br />'.$line;
			} else {
				$line = trim($cap[2]);
			}
		}
		# Pr�format�
		elseif ($this->getOpt('active_pre') && preg_match('/^[ ]{1}(.*)$/',$line,$cap))
		{
			$type = 'pre';
			$line = $cap[1];
		}
		# Paragraphe
		else {
			$type = 'p';
			$line = trim($line);
		}

		return $line;
	}

	function __openLine($type,$mode,$pre_type,$pre_mode)
	{
		$open = ($type != $pre_type);

		if ($open && $type == 'p')
		{
			return "\n<p>";
		}
		elseif ($open && $type == 'blockquote')
		{
			return "\n<blockquote><p>";
		}
		elseif (($open || $mode != $pre_mode) && $type == 'title')
		{
			$fl = $this->getOpt('first_title_level');
			$fl = $fl+3;
			$l = $fl-$mode;
			return "\n<h".($l).'>';
		}
		elseif ($open && $type == 'pre')
		{
			return "\n<pre>";
		}
		elseif ($open && $type == 'hr')
		{
			return "\n<hr />";
		}
		elseif ($type == 'list')
		{
			$dl = ($open) ? 0 : strlen($pre_mode);
			$d = strlen($mode);
			$delta = $d-$dl;
			$res = '';

			if($delta > 0) {
				if(substr($mode, -1, 1) == '*') {
					$res .= "<ul>\n";
				} else {
					$res .= "<ol>\n";
				}
			} elseif ($delta < 0) {
				$res .= "</li>\n";
				for($j = 0; $j < abs($delta); $j++) {
					if (substr($pre_mode,(0 - $j - 1), 1) == '*') {
						$res .= "</ul>\n</li>\n";
					} else {
						$res .= "</ol>\n</li>\n";
					}
				}
			} else {
				$res .= "</li>\n";
			}

			return $res."<li>";
		}
		else
		{
			return NULL;
		}
	}

	function __closeLine($type,$mode,$pre_type,$pre_mode)
	{
		$close = ($type != $pre_type);

		if ($close && $pre_type == 'p')
		{
			return "</p>\n";
		}
		elseif ($close && $pre_type == 'blockquote')
		{
			return "</p></blockquote>\n";
		}
		elseif (($close || $mode != $pre_mode) && $pre_type == 'title')
		{
			$fl = $this->getOpt('first_title_level');
			$fl = $fl+3;
			$l = $fl-$pre_mode;
			return '</h'.($l).">\n";
		}
		elseif ($close && $pre_type == 'pre')
		{
			return "</pre>\n";
		}
		elseif ($close && $pre_type == 'list')
		{
			$res = '';
			for($j = 0; $j < strlen($pre_mode); $j++) {
				if(substr($pre_mode,(0 - $j - 1), 1) == '*') {
					$res .= "</li>\n</ul>";
				} else {
					$res .= "</li>\n</ol>";
				}
			}
			return $res;
		}
		else
		{
			return "\n";
		}
	}


	/* Inline
	--------------------------------------------------- */
	function __inlineWalk($str,$allow_only=NULL)
	{
		$tree = preg_split($this->tag_pattern,$str,-1,PREG_SPLIT_DELIM_CAPTURE);

		$res = '';
		for ($i=0; $i<count($tree); $i++)
		{
			$attr = '';

			if (in_array($tree[$i],array_values($this->open_tags)) &&
			($allow_only == NULL || in_array(array_search($tree[$i],$this->open_tags),$allow_only)))
			{
				$tag = array_search($tree[$i],$this->open_tags);
				$tag_type = 'open';

				if (($tidy = $this->__makeTag($tree,$tag,$i,$i,$attr,$tag_type)) !== false)
				{
					if ($tag != '') {
						$res .= '<'.$tag.$attr;
						$res .= ($tag_type == 'open') ? '>' : ' />';
					}
					$res .= $tidy;
				}
				else
				{
					$res .= $tree[$i];
				}
			}
			else
			{
				$res .= $tree[$i];
			}
		}

		# Suppression des echappements
		$res = str_replace($this->escape_table,$this->all_tags,$res);

		return $res;
	}

	function __makeTag(&$tree,&$tag,$position,&$j,&$attr,&$type)
	{
		$res = '';
		$closed = false;

		$itag = $this->close_tags[$tag];

		# Recherche fermeture
		for ($i=$position+1;$i<count($tree);$i++)
		{
			if ($tree[$i] == $itag)
			{
				$closed = true;
				break;
			}
		}

		# R�sultat
		if ($closed)
		{
			for ($i=$position+1;$i<count($tree);$i++)
			{
				if ($tree[$i] != $itag)
				{
					$res .= $tree[$i];
				}
				else
				{
					switch ($tag)
					{
						case 'a':
							$res = $this->__parseLink($res,$tag,$attr,$type);
							break;
						case 'img':
							$type = 'close';
							$res = $this->__parseImg($res,$attr);
							break;
						case 'acronym':
							$res = $this->__parseAcronym($res,$attr);
							break;
						case 'q':
							$res = $this->__parseQ($res,$attr);
							break;
						case 'anchor':
							$tag = 'a';
							$res = $this->__parseAnchor($res,$attr);
							break;
						case 'note':
							$tag = '';
							$res = $this->__parseNote($res);
							break;
						case 'word':
							$res = $this->parseWikiWord($res,$tag,$attr,$type);
							break;
						case 'macro':
							$res = $this->parseMacro($res,$tag,$attr,$type);
							break;
						default :
							$res = $this->__inlineWalk($res);
							break;
					}

					if ($type == 'open' && $tag != '') {
						$res .= '</'.$tag.'>';
					}
					$j = $i;
					break;
				}
			}

			return $res;
		}
		else
		{
			return false;
		}
	}

	function __splitTagsAttr($str)
	{
		$res = preg_split('/(?<!\\\)\|/',$str);
		//array_walk($res,create_function('&$v','$v = str_replace("\|","|",$v);'));
		foreach ($res as $k => $v) {
			$res[$k] = str_replace("\|",'|',$v);
		}

		return $res;
	}

	# Antispam (J�r�me Lipowicz)
	function __antiSpam($str)
	{
		$encoded = bin2hex($str);
		$encoded = chunk_split($encoded, 2, '%');
		$encoded = '%'.substr($encoded, 0, strlen($encoded) - 1);
		return $encoded;
	}

	function __parseLink($str,&$tag,&$attr,&$type)
	{
		$n_str = $this->__inlineWalk($str,array('acronym','img'));
		$data = $this->__splitTagsAttr($n_str);
		$no_image = false;

		if (count($data) == 1)
		{
			$url = trim($str);
			$content = $str;
			$lang = '';
			$title = '';
		}
		elseif (count($data) > 1)
		{
			$url = trim($data[1]);
			$content = $data[0];
			$lang = (!empty($data[2])) ? $this->protectAttr($data[2],true) : '';
			$title = (!empty($data[3])) ? $data[3] : '';
			$no_image = (!empty($data[4])) ? (boolean) $data[4] : false;
		}

		$array_url = $this->__specialUrls();
		$url = preg_replace(array_flip($array_url),$array_url,$url);

		# On vire les &nbsp; dans l'url
		$url = str_replace('&nbsp;',' ',$url);

		if (preg_match('/^(.+)[.](gif|jpg|jpeg|png)$/', $url) && !$no_image && $this->getOpt('active_auto_img'))
		{
			# On ajoute les dimensions de l'image si locale
			# Id�e de Stephanie
			$img_size = NULL;
			if (!preg_match('|[a-zA-Z0-9]+://|', $url)) {
				if (preg_match('|^/|',$url)) {
					$path_img = $_SERVER['DOCUMENT_ROOT'] . $url;
				} else {
					$path_img = $url;
				}

				$img_size = @getimagesize($path_img);
			}

			$attr = ' src="'.$this->protectAttr($this->protectUrls($url)).'"'.
			$attr .= (count($data) > 1) ? ' alt="'.$this->protectAttr($content).'"' : ' alt=""';
			$attr .= ($lang) ? ' lang="'.$lang.'"' : '';
			$attr .= ($title) ? ' title="'.$this->protectAttr($title).'"' : '';
			$attr .= (is_array($img_size)) ? ' '.$img_size[3] : '';

			$tag = 'img';
			$type = 'close';
			return NULL;
		}
		else
		{
			if ($this->getOpt('active_antispam') && preg_match('/^mailto:/',$url)) {
				$url = 'mailto:'.$this->__antiSpam(substr($url,7));
			}

			$attr = ' href="'.$this->protectAttr($this->protectUrls($url)).'"';
			$attr .= ($lang) ? ' hreflang="'.$lang.'"' : '';
			$attr .= ($title) ? ' title="'.$this->protectAttr($title).'"' : '';

			return $content;
		}
	}

	function __specialUrls()
	{
		$res['#^google://(.*)$#'] = 'http://www.google.com/search?q=$1&amp;start=0&amp;start=0';
		$res['#^wikipedia://(.*)$#'] = 'http://en.wikipedia.org/wiki/$1';

		return $res;
	}

	function __parseImg($str,&$attr)
	{
		$data = $this->__splitTagsAttr($str);

		$alt = '';
		$url = $data[0];
		if (!empty($data[1])) {
			$alt = $data[1];
		}

		$attr = ' src="'.$this->protectAttr($this->protectUrls($url)).'"';
		$attr .= ' alt="'.$this->protectAttr($alt).'"';

		if (!empty($data[2])) {
			$data[2] = strtoupper($data[2]);
			if ($data[2] == 'G' || $data[2] == 'L') {
				$attr .= ' style="float:left; margin: 0 1em 1em 0;"';
			} elseif ($data[2] == 'D' || $data[2] == 'R') {
				$attr .= ' style="float:right; margin: 0 0 1em 1em;"';
			} elseif ($data[2] == 'C') {
				$attr .= ' style="display:block; margin:0 auto;"';
			}
		}

		if (!empty($data[3])) {
			$attr .= ' longdesc="'.$this->protectAttr($data[3]).'"';
		}

		return NULL;
	}

	function __parseQ($str,&$attr)
	{
		$str = $this->__inlineWalk($str);
		$data = $this->__splitTagsAttr($str);

		$content = $data[0];
		$lang = (!empty($data[1])) ? $this->protectAttr($data[1],true) : '';

		$attr .= (!empty($lang)) ? ' lang="'.$lang.'"' : '';
		$attr .= (!empty($data[2])) ? ' cite="'.$this->protectAttr($data[2]).'"' : '';

		return $content;
	}

	function __parseAnchor($str,&$attr)
	{
		$name = $this->protectAttr($str,true);

		if ($name != '') {
			$attr = ' name="'.$name.'"';
		}
		return null;
	}

	function __parseNote($str)
	{
		$i = count($this->foot_notes)+1;
		$id = $this->getOpt('note_prefix').'-'.$i;
		$this->foot_notes[$id] = $this->__inlineWalk($str);
		return '<sup>\[<a href="#'.$id.'" name="rev-'.$id.'">'.$i.'</a>\]</sup>';
	}

	# Obtenir un acronyme
	function __parseAcronym($str,&$attr)
	{
		$data = $this->__splitTagsAttr($str);

		$acronym = $data[0];
		$title = $lang = '';

		if (count($data) > 1)
		{
			$title = $data[1];
			$lang = (!empty($data[2])) ? $this->protectAttr($data[2],true) : '';
		}

		if ($title == '' && !empty($this->acro_table[$acronym])) {
			$title = $this->acro_table[$acronym];
		}

		$attr = ($title) ? ' title="'.$this->protectAttr($title).'"' : '';
		$attr .= ($lang) ? ' lang="'.$lang.'"' : '';

		return $acronym;
	}

	# D�finition des acronymes, dans le fichier acronyms.txt
	function __getAcronyms()
	{
		$file = $this->getOpt('acronyms_file');
		$res = array();

		if (file_exists($file))
		{
			if (($fc = @file($file)) !== false)
			{
				foreach ($fc as $v)
				{
					$v = trim($v);
					if ($v != '')
					{
						$p = strpos($v,':');
						$K = (string) trim(substr($v,0,$p));
						$V = (string) trim(substr($v,($p+1)));

						if ($K) {
							$res[$K] = $V;
						}
					}
				}
			}
		}

		return $res;
	}

	# Mots wiki (pour h�ritage)
	function parseWikiWord($str,&$tag,&$attr,&$type)
	{
		$tag = $attr = '';
		return $str;
	}

	# Macros (pour h�ritage)
	function parseMacro($str,&$tag,&$attr,&$type)
	{
		$tag = $attr = '';
		return $str;
	}

	/* Protection des attributs */
	function protectAttr($str,$name=false)
	{
		if ($name && !preg_match('/^[A-Za-z][A-Za-z0-9_:.-]*$/',$str)) {
			return '';
		}

		return str_replace(array("'",'"'),array('&#039;','&quot;'),$str);
	}

	/* Protection des urls */
	function protectUrls($str)
	{
		if (preg_match('/^javascript:/',$str)) {
			$str = '#';
		}

		return $str;
	}

	/* Aide et debug
	--------------------------------------------------- */
	function help()
	{
		$help['b'] = array();
		$help['i'] = array();

		$help['b'][] = 'Laisser une ligne vide entre chaque bloc <em>de m�me nature</em>.';
		$help['b'][] = '<strong>Paragraphe</strong> : du texte et une ligne vide';

		if ($this->getOpt('active_title')) {
			$help['b'][] = '<strong>Titre</strong> : <code>!!!</code>, <code>!!</code>, '.
			'<code>!</code> pour des titres plus ou moins importants';
		}

		if ($this->getOpt('active_hr')) {
			$help['b'][] = '<strong>Trait horizontal</strong> : <code>----</code>';
		}

		if ($this->getOpt('active_lists')) {
			$help['b'][] = '<strong>Liste</strong> : ligne d�butant par <code>*</code> ou '.
			'<code>#</code>. Il est possible de m�langer les listes '.
			'(<code>*#*</code>) pour faire des listes de plusieurs niveaux. '.
			'Respecter le style de chaque niveau';
		}

		if ($this->getOpt('active_pre')) {
			$help['b'][] = '<strong>Texte pr�format�</strong> : espace devant chaque ligne de texte';
		}

		if ($this->getOpt('active_quote')) {
			$help['b'][] = '<strong>Bloc de citation</strong> : <code>&gt;</code> ou '.
			'<code>;:</code> devant chaque ligne de texte';
		}

		if ($this->getOpt('active_fr_syntax')) {
			$help['i'][] = 'La correction de ponctuation est active. Un espace '.
						'ins�cable remplacera automatiquement tout espace '.
						'pr�c�dant les marque ";","?",":" et "!".';
		}

		if ($this->getOpt('active_em')) {
			$help['i'][] = '<strong>Emphase</strong> : deux apostrophes <code>\'\'texte\'\'</code>';
		}

		if ($this->getOpt('active_strong')) {
			$help['i'][] = '<strong>Forte emphase</strong> : deux soulign�s <code>__texte__</code>';
		}

		if ($this->getOpt('active_br')) {
			$help['i'][] = '<strong>Retour forc� � la ligne</strong> : <code>%%%</code>';
		}

		if ($this->getOpt('active_ins')) {
			$help['i'][] = '<strong>Insertion</strong> : deux plus <code>++texte++</code>';
		}

		if ($this->getOpt('active_del')) {
			$help['i'][] = '<strong>Suppression</strong> : deux moins <code>--texte--</code>';
		}

		if ($this->getOpt('active_urls')) {
			$help['i'][] = '<strong>Lien</strong> : <code>[url]</code>, <code>[nom|url]</code>, '.
			'<code>[nom|url|langue]</code> ou <code>[nom|url|langue|titre]</code>.';

			$help['i'][] = '<strong>Image</strong> : comme un lien mais avec une extension d\'image.'.
			'<br />Pour d�sactiver la reconnaissance d\'image mettez 0 dans un dernier '.
			'argument. Par exemple <code>[image|image.gif||0]</code> fera un lien vers l\'image au '.
			'lieu de l\'afficher.'.
			'<br />Il est conseill� d\'utiliser la nouvelle syntaxe.';
		}

		if ($this->getOpt('active_img')) {
			$help['i'][] = '<strong>Image</strong> (nouvelle syntaxe) : '.
			'<code>((url|texte alternatif))</code>, '.
			'<code>((url|texte alternatif|position))</code> ou '.
			'<code>((url|texte alternatif|position|description longue))</code>. '.
			'<br />La position peut prendre les valeur L ou G (gauche), R ou D (droite) ou C (centr�).';
		}

		if ($this->getOpt('active_anchor')) {
			$help['i'][] = '<strong>Ancre</strong> : <code>~ancre~</code>';
		}

		if ($this->getOpt('active_acronym')) {
			$help['i'][] = '<strong>Acronyme</strong> : <code>??acronyme??</code> ou '.
			'<code>??acronyme|titre??</code>';
		}

		if ($this->getOpt('active_q')) {
			$help['i'][] = '<strong>Citation</strong> : <code>{{citation}}</code>, '.
			'<code>{{citation|langue}}</code> ou <code>{{citation|langue|url}}</code>';
		}

		if ($this->getOpt('active_code')) {
			$help['i'][] = '<strong>Code</strong> : <code>@@code ici@@</code>';
		}

		if ($this->getOpt('active_footnotes')) {
			$help['i'][] = '<strong>Note de bas de page</strong> : <code>$$Corps de la note$$</code>';
		}

		$res = '<dl class="wikiHelp">';

		$res .= '<dt>Blocs</dt><dd>';
		if (count($help['b']) > 0)
		{
			$res .= '<ul><li>';
			$res .= implode('&nbsp;;</li><li>', $help['b']);
			$res .= '.</li></ul>';
		}
		$res .= '</dd>';

		$res .= '<dt>�l�ments en ligne</dt><dd>';
		if (count($help['i']) > 0)
		{
			$res .= '<ul><li>';
			$res .= implode('&nbsp;;</li><li>', $help['i']);
			$res .= '.</li></ul>';
		}
		$res .= '</dd>';

		$res .= '</dl>';

		return $res;
	}

	/*
	function debug()
	{
		$mode = $type = NULL;
		$max = count($this->T);

		$res =
		'<table border="1">'.
		'<tr><th>p-mode</th><th>p-type</th><th>mode</th><th>type</th><th>chaine</th></tr>';

		for ($i=0; $i<$max; $i++)
		{
			$pre_mode = $mode;
			$pre_type = $type;

			$line = $this->__getLine($i,$type,$mode);

			$res .=
			'<tr><td>'.$pre_mode.'</td><td>'.$pre_type.'</td>'.
			'<td>'.$mode.'</td><td>'.$type.'</td><td>'.$line.'</td></tr>';

		}
		$res .= '</table>';

		return $res;
	}
	//*/
}

?>
