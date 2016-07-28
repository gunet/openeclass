<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2014  Greek Universities Network - GUnet
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

/* ============================================================================
  class.wiki2xhtmlrenderer.php
  @last update: 15-05-2007 by Thanos Kyritsis
  @authors list: Thanos Kyritsis <atkyritsis@upnet.gr>

  based on Claroline version 1.7.9 licensed under GPL, updated from version 1.11
  copyright (c) 2001, 2007 Universite catholique de Louvain (UCL)

  original file: class.wiki2xhtmlrenderer Revision: 1.8.2.6

  Claroline authors: Frederic Minne <zefredz@gmail.com>
  ==============================================================================
  @Description:

  @Comments:

  @todo:
  ==============================================================================
 */




require_once dirname(__FILE__) . '/wiki2xhtml/class.wiki2xhtml.php';
require_once dirname(__FILE__) . '/class.wikistore.php';
require_once dirname(__FILE__) . '/class.wikipage.php';

define("WIKI_WORD_PATTERN", '((?<![A-Za-z0-9])([A-Z][a-z]+){2,}(?![A-Za-z0-9]))');

/**
 * Wiki2xhtml rendering engine
 *
 * @see wiki2xhtml
 */
class Wiki2xhtmlRenderer extends wiki2xhtml {

    var /* % Wiki */ $wiki;
    var $addAtEnd = array();

    /**
     * Constructor
     * @param Wiki wiki
     */
    public function __construct(&$wiki) {
        parent::__construct();

        $this->wiki = & $wiki;

        // set wiki rendering options
        // use wikiwords to link wikipages
        $this->setOpt('active_wikiwords', 0);
        // auto detect images
        $this->setOpt('active_auto_img', 1);
        // set first wiki title level
        $this->setOpt('first_title_level', 2);
        // use setext title syntax ie ===== and ----- instead of !!! and !!
        $this->setOpt('active_setext_title', 1);
        // set acronyms file
        $this->setOpt('acronyms_file', dirname(__FILE__) . '/wiki2xhtml/acronyms.txt');
        // set wiki word pattern
        $this->setOpt('words_pattern', WIKI_WORD_PATTERN);
        // set footnotes patten
        $this->setOpt('note_str', '<div class="footnotes"><a name="footNotes"></a><h2>Notes</h2>%s</div>');
        // use urls to link wikipages
        $this->setOpt('active_wiki_urls', 1);
        // use macros
        $this->setOpt('active_macros', 1);
        // use tables
        $this->setOpt('active_tables', 1);
    }
	
    /**
     * Overwrite wiki2xhtml __getLine method
     * @access private
     * @see class.wiki2xhtml.php
     */
    function __getLine($i, &$type, &$mode) {
        $pre_type = $type;
        $pre_mode = $mode;
        $type = $mode = NULL;

        if (empty($this->T[$i])) {
            return false;
        }

        $line = htmlspecialchars($this->T[$i], ENT_NOQUOTES);

        # Ligne vide
        if (empty($line)) {
            $type = NULL;
        }
        elseif ($this->getOpt('active_empty') && preg_match('/^øøø(.*)$/',$line,$cap)) {
            $type = NULL;
            $line = trim($cap[1]);
        }
        # Titre
        elseif ($this->getOpt('active_title') && preg_match('/^([!]{1,4})(.*)$/', $line, $cap)) {
            $type = 'title';
            $mode = strlen($cap[1]);
            $line = trim($cap[2]);
        }
        # Ligne HR
        elseif ($this->getOpt('active_hr') && preg_match('/^[-]{4}[- ]*$/', $line)) {
            $type = 'hr';
            $line = NULL;
        }
        # Blockquote
        elseif ($this->getOpt('active_quote') && preg_match('/^(&gt;|;:)(.*)$/', $line, $cap)) {
            $type = 'blockquote';
            $line = trim($cap[2]);
        }
        # Liste
        elseif ($this->getOpt('active_lists') && preg_match('/^([*#]+)(.*)$/', $line, $cap)) {
            $type = 'list';
            $mode = $cap[1];
            $valid = true;

            # V�rification d'int�grit�
            $dl = ($type != $pre_type) ? 0 : strlen($pre_mode);
            $d = strlen($mode);
            $delta = $d - $dl;

            if ($delta < 0 && strpos($pre_mode, $mode) !== 0) {
                $valid = false;
            }
            if ($delta > 0 && $type == $pre_type && strpos($mode, $pre_mode) !== 0) {
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
                $line = '<br />' . $line;
            } else {
                $line = trim($cap[2]);
            }
        }
        # Pr�format�
        elseif ($this->getOpt('active_pre') && preg_match('/^[ ]{1}(.*)$/', $line, $cap)) {
            $type = 'pre';
            $line = $cap[1];
        }
		# tables
        elseif ($this->getOpt('active_tables')) {
            # table start
            if (preg_match('/^\s*{\|(.+)\s*/', $line, $cap)) {
                $type = null;
                $line = '<table class="wikitable">';
                $caption = trim($cap[1]);
                $line .= '<caption>'.$caption.'</caption>';
            }
            elseif (preg_match('/^\s*{\|\s*/', $line, $cap)) {
                $type = null;
                $line = '<table class="wikitable">';
            }
            # table end
            elseif (preg_match('/^\s*\|}\s*$/', $line, $cap)) {
                $type = null;
                $line = '</table>';
            }
            # table row
            elseif( preg_match('/^\s*\|\|(.*)\|\|\s*$/', $line, $cap) ) {
                $type = null;
                
                $line = trim($cap[1]);

                $line = $this->__inlineWalk($line);
                
                $line = $this->_parseTableLine($line);
            } 
            else {
                $type = 'p';
                $line = trim($line);
            }
        }
        # Paragraphe
        else {
            $type = 'p';
            $line = trim($line);
        }

        return $line;
    }
	
    function _parseTableLine($line) {
        $cell = array();
        $offset = 0;
        $th = false;
        
        while (strlen($line) > 0)
        {
            if (false !== ($pos = strpos($line,'|', $offset))) {
                if (($pos-1 >= 0) &&  $line[$pos-1] == '\\') {
                    $offset = $pos+1;
                    continue;
                }
                else{
                    $r = substr($line, 0, $pos);
                    
                    if (strpos($r,'!') === 0) {
                        $th = true;
                        $cell[] = substr($r,1);
                    }
                    else{
                        $cell[] = $r;
                    }
                    
                    $line = substr($line, $pos+1);
                    $offset = 0;
                }
            }
            else{
                $r = $line;
                
                if (strpos($r,'!') === 0) {
                    $th = true;
                    $cell[] = substr($r,1);
                }
                else {
                    $cell[] = $r;
                }

                $line = '';
                $offset = 0;
            }
        }
        
        $ret = '';
        
        if (true === $th) {
            $ret = '<tr><th>';
            $ret .= implode('</th><th>', $cell);
            $ret .= '</th></tr>';
        }
        else{
            $ret = '<tr><td>';
            $ret .= implode('</td><td>', $cell);
            $ret .= '</td></tr>';
        }
        
        return $ret;
    }
	
    /**
     * Parse WikiWords and create hypertext reference to wiki page
     *
     * @access private
     * @see class.wiki2xhtml.php
     * @return string hypertext reference to wiki page
     */
    function parseWikiWord($str, &$tag, &$attr, &$type) {
        global $course_code;
        $tag = 'a';
        $attr = ' href="' . $str . '"';
        if ($this->wiki->pageExists($str)) {
            return "<a href=\"" . $_SERVER['SCRIPT_NAME'] . "?course=" . $course_code
                    . "&amp;action=show&amp;title=" . rawurlencode($str)
                    . "&amp;wikiId=" . $this->wiki->getWikiId()
                    . "\" class=\"wikiShow\">"
                    . $str
                    . "</a>"
            ;
        } else {
            return "<a href=\"" . $_SERVER['SCRIPT_NAME'] . "?course=" . $course_code
                    . "&amp;action=edit&amp;title=" . rawurlencode($str)
                    . "&amp;wikiId=" . $this->wiki->getWikiId()
                    . "\" class=\"wikiEdit\">"
                    . $str
                    . "</a>"
            ;
        }
    }
	
    /**
     * Parse and execute wiki2xhtml macros
     *
     *  enabled macros are :
     *      - """home""" or """main""" : link to Main page
     * @access private
     * @see class.wiki2xhtml.php
     * @return string macro execution result
     */
    function parseMacro($str,&$tag,&$attr,&$type) { 
        $tag = '';
        $attr = '';
        
        $trimmedStr = trim($str,'"');
        
        $matches = array();
        
        if (preg_match('/^color([0-9])/',$trimmedStr,$matches)) {
            $colorCodeList = array(
                0 => '#DD0000',
                1 => '#006600',
                2 => '#0000DD',
                3 => '#660099',
                4 => '#008888',
                5 => '#55AA22',
                6 => '#888800',
                7 => '#DE8822',
                8 => '#804020',
                9 => '#990022',
            );
            
            $colorCode = isset($colorCodeList[(int)$matches[1]]) ? $colorCodeList[(int)$matches[1]] : '#000000';
            $trimmedStr = 'color';
        }
        elseif(preg_match('/^color\(([a-zA-Z]+|#[a-fA-F0-9]{3}|#[a-fA-F0-9]{6})\)/',$trimmedStr,$matches)) {
            $colorCode = $matches[ 1 ];
            $trimmedStr = 'color';
        }
        
        switch($trimmedStr)
        {
            // link to main page
            case 'home':
            case 'main':
            {
			    global $langWikiMainPage,$course_code;
                $str = "<a href=\"".$_SERVER['PHP_SELF']
                    ."?course=".$course_code."&amp;action=show&amp;title=".rawurlencode('__MainPage__')
                    . "&amp;wikiId=" . $this->wiki->getWikiId()
                    . "\" class=\"wikiShow\">"
                    . $langWikiMainPage
                    . "</a>"
                    ;
                break;
            }
            // toc
            case 'toc':
            {
                $str = '';
                $this->addAtEnd[] = '<script type="text/javascript" src="lib/javascript/toc.js"></script>';
                $this->addAtEnd[] = '<script type="text/javascript">createTOC();</script>';
                break;
            }
            case 'color':
            {
                $str = '<span style="color: ' . $colorCode . ';">';
                break;
            }
            case '/color':
            {
                $str = '</span>';
                break;
            }
        }
        
        return $str;
    }

    /**
     * Parse links in pages
     *
     * @see class.wiki2xhtml.php#__parseLink($str, &$tag, &$attr, &$type)
     */
    function __parseLink($str, &$tag, &$attr, &$type) {
        $n_str = $this->__inlineWalk($str, array('acronym', 'img'));
        $data = $this->__splitTagsAttr($n_str);
        $no_image = false;

        if (count($data) == 1) {
            $url = trim($str);
            $content = $str;
            $lang = '';
            $title = '';
        } elseif (count($data) > 1) {
            $url = trim($data[1]);
            $content = $data[0];
            $lang = (!empty($data[2]) ) ? $this->protectAttr($data[2], true) :
                    '';
            $title = (!empty($data[3]) ) ? $data[3] :
                    '';
            $no_image = (!empty($data[4]) ) ? (boolean) $data[4] :
                    false;
        }

        $array_url = $this->__specialUrls();
        $url = preg_replace(array_flip($array_url), $array_url, $url);

        # On vire les &nbsp; dans l'url
        $url = str_replace('&nbsp;', ' ', $url);

        if (preg_match('/^(.+)[.](gif|jpg|jpeg|png)$/', $url) && !$no_image && $this->getOpt('active_auto_img')) {
            # On ajoute les dimensions de l'image si locale
            # Idée de Stephanie
            $img_size = NULL;
            if (!preg_match('|[a-zA-Z0-9]+://|', $url)) {
                if (preg_match('|^/|', $url)) {
                    $path_img = $_SERVER['DOCUMENT_ROOT'] . $url;
                } else {
                    $path_img = $url;
                }

                $img_size = @getimagesize($path_img);
            }

            $attr = ' src="' . $this->protectAttr($this->protectUrls($url)) . '"'; 
			$attr .= (count($data) > 1 ) ? ' alt="' . $this->protectAttr($content) . '"' : ' alt=""';
            $attr .= ($lang ) ? ' lang="' . $lang . '"' : '';
            $attr .= ($title ) ? ' title="' . $this->protectAttr($title) . '"' : '';
            $attr .= (is_array($img_size) ) ? ' ' . $img_size[3] : '';

            $tag = 'img';
            $type = 'close';
            return NULL;
        } else {
            if ($this->getOpt('active_antispam') && preg_match('/^mailto:/', $url)) {
                $url = 'mailto:' . $this->__antiSpam(substr($url, 7));
            }

            if ((!preg_match('|[a-zA-Z0-9]+://|', $url) && !preg_match('~^#~', $url) && !preg_match('~^\.*/~', $url) && !preg_match('~^mailto:~', $url)) && $this->getOpt('active_wiki_urls')) {
                $attr = $this->_getWikiPageLink($url);
            } else {
                $attr = ' href="' . $this->protectAttr($this->protectUrls($url)) . '"' . ' rel="nofollow"';
            }

            $attr .= ($lang) ? ' hreflang="' . $lang . '"' :
                    '';
            $attr .= ($title) ? ' title="' . $this->protectAttr($title) . '"' :
                    '';

            return $content;
        }
    }

    /**
     * Render the given string using the wiki2xhtml renderer
     * @param string txt wiki syntax string
     * @return string xhtml-rendered string
     */
    function render($txt) {
        $ret = preg_replace( '/\\\\((\!|\|)+)/', '$1', $this->transform($txt ) );
        
        foreach ( $this->addAtEnd as $line )
        {
            $ret .= $line . "\n";
        }
        
        return $ret;
    }

    /**
     * Parse page names in URLS and create hypertext reference to wiki page
     *
     * @access private
     * @param string pageName name of the page
     * @return string hypertext reference to wiki page
     */
    function _getWikiPageLink($pageName) {
        global $course_code, $langWikiMainPage;

		if ($langWikiMainPage == $pageName){
            $pageName = '__MainPage__';
        }
		
        // allow links to use wikiwords for wiki page locations
        if ($this->getOpt('active_wikiwords') && $this->getOpt('words_pattern')) {
            $pageName = preg_replace('/¶¶¶'.$this->getOpt('words_pattern').'¶¶¶/msU', '$1', $pageName);
        }

		$fragment = '';

        /*if (preg_match('/(#\w+)$/', $pageName, $matches))
        {
            $fragment = $matches[1];
            $pageName = preg_replace( '/(#\w+)$/', '', $pageName );
        }*/
		
        if ($this->wiki->pageExists($pageName)) {
            return ' href="' . $_SERVER['SCRIPT_NAME'] . '?course=' . $course_code
                    . '&amp;action=show&amp;title=' . rawurlencode($pageName)
                    . '&amp;wikiId=' . $this->wiki->getWikiId() . $fragment
                    . '" class="wikiShow"'
            ;
        } else {
            return ' href="' . $_SERVER['SCRIPT_NAME'] . '?course=' . $course_code
                    . '&amp;action=edit&amp;title=' . rawurlencode($pageName)
                    . '&amp;wikiId=' . $this->wiki->getWikiId() . $fragment
                    . '" class="wikiEdit"'
            ;
        }
    }

    function __initTags() {
        $this->tags = array(
            'strong' => array("'''", "'''"),
            'em' => array("''", "''"),
            'acronym' => array('??', '??'),
            'a' => array('[', ']'),
            'img' => array('((', '))'),
            'q' => array('{{', '}}'),
            'code' => array('@@', '@@'),
            'anchor' => array('~', '~'),
            'del' => array('--', '--'),
            'stroke' => array('--', '--'),
            'ins' => array('++', '++'),
            'u' => array('__', '__'),
            'note' => array('$$', '$$'),
            'word' => array('¶¶¶','¶¶¶'),
            'macro' => array('"""', '"""'),
		    'color' => array('//','//')
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
        array_walk($this->escape_table, create_function('&$a', '$a = \'\\\\\'.$a;'));
    }
	
    function __parseColor($str, &$tag, &$attr, &$type) {
        $n_str = $this->__inlineWalk($str);
        $data = $this->__splitTagsAttr($n_str );
        
        $tag = "span";
        $type= "open";
        
        if (count($data) == 1) {
            $content = $str;
            $attr = ' style="color: #000000"';
        }
        elseif (count($data) > 1) {
            $attr = ' style="color: ' . trim( $data[ 0 ] ) .'"';
            $content = $data[ 1 ];
        }
        
        return $content;
    }

}

?>
