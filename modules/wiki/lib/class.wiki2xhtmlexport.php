<?php 

/* ========================================================================
 * Open eClass
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
 * ========================================================================
 */

/* * ===========================================================================
 class.wiki2xhtmlexport.php

 based on Claroline version 1.11 licensed under GPL
 copyright (c) 2001-2012 Universite catholique de Louvain (UCL)

 original file: class.wiki2xhtmlexport.php Revision: 14585

 Claroline authors: Frederic Minne <zefredz@gmail.com>
==============================================================================
*/

require_once dirname(__FILE__) . '/class.wiki2xhtmlrenderer.php';
require_once dirname(__FILE__) . '/class.wikistore.php';
require_once dirname(__FILE__) . '/class.wikipage.php';
require_once dirname(__FILE__) . '/class.wiki.php';

/**
 * Export a Wiki to a single HTML formated string
 * @todo    some refatoring
 */
class WikiToSingleHTMLExporter extends Wiki2xhtmlRenderer
{
    var $wiki;
    var $style = '';

    /**
     * Constructor
     * @param   $wiki Wiki, Wiki to export
     */
    public function __construct($wiki)
    {
        Wiki2xhtmlRenderer::Wiki2xhtmlRenderer($wiki);
        $this->setOpt('first_title_level', 3);
        $this->setOpt('note_str','<div class="footnotes"><h5>Notes</h5>%s</div>');
        $this->wiki =&$wiki;
    }

    /**
     * Export a whole Wiki to a single HTML String
     * @return  string Wiki content in HTML
     */
    public function export()
    {
		global $langWikiMainPage, $langWikiPageNotLoaded;
		
        $pageList = $this->wiki->allPagesByCreationDate();

        $result = $this->_htmlHeader();

        $result .= '<h1>' . $this->wiki->getTitle() . '</h1>' . "\n";

        foreach ( $pageList as $page )
        {
            $wikiPage = new WikiPage($this->wiki->getWikiId());

            $wikiPage->loadPage($page->title);

            $this->setOpt('note_prefix', $page->title);

            if ( $wikiPage->hasError() )
            {
                $result .= '<h2><a name="'
                    . $this->_makePageTitleAnchor($page->title).'">'
                    . $page->title
                    . '</a></h2>'
                    . "\n"
                    ;

                $result .= sprintf($langWikiPageNotLoaded,$page->title);
                $wikiPage = null;
            }
            else
            {
                $pgTitle = $wikiPage->getTitle();

                if ( '__MainPage__' === $pgTitle )
                {
                    $pgTitle = $langWikiMainPage;
                }

                $result .= '<h2><a name="'
                    . $this->_makePageTitleAnchor($page->title) .'">'
                    . $pgTitle
                    .'</a></h2>'
                    . "\n"
                    ;

                $content = $wikiPage->getContent();
                $result .= $this->render($content) . "\n";

                $wikiPage = null;
            }
        }

        $result .= $this->_htmlFooter();

        return $result;
    }

    // private methods

    /**
     * Make HTML anchor name from page title
     * @access  private
     * @return  string anchor name
     * @todo    implement...
     */
    function _makePageTitleAnchor( $pageTitle )
    {
        return $pageTitle;
    }

    /**
     * Get Wiki style sheet
     * @access  private
     * @return  string CSS style to insert in HTML (style tags already added)
     * @todo    remove style tags and add support for multiple media
     */
    function _getWikiStyle()
    {
        $style = '<style type="text/css" media="screen">
h1{
    color: Black;
    background: none;
    font-size: 200%;
    font-weight: bold;
    border-bottom: 2px solid #aaaaaa;
}
h2,h3,h4{
    color: Black;
    background: none;
}
h2{
    border-bottom: 1px solid #aaaaaa;
    font-size:175%;
    font-weight:bold;
}
h3{
    border-bottom: 1px groove #aaaaaa;
    font-size:150%;
    font-weight:bold;
}
h4{
    font-size:125%;
    font-weight:bold;
}
h5{
    font-size: 100%;
    font-style: italic;
    border-bottom: 1px groove #aaaaaa;
}

a.wikiEdit{
    color: red;
}

table {
    border: black outset 1px;
}
td {
    border: black inset 1px;
}
</style>' . "\n";

        return $style;
    }

    /**
     * Generate HTML page header
     * @access  private
     * @return  string HTML header
     */
    function _htmlHeader()
    {
        $header = '<html>' . "\n" . '<head>' . "\n"
            . '<meta charset="utf-8">' . "\n"
            . '<title>' . $this->wiki->getTitle() . '</title>' . "\n"
            . $this->_getWikiStyle()
            . '</head>' . "\n" . '<body>' . "\n"
            ;

        return $header;
    }

    /**
     * Generate HTML page footer
     * @access  private
     * @return  string HTML footer
     */
    function _htmlFooter()
    {
        $footer = '</body>' . "\n" . '</html>' . "\n";

        return $footer;
    }

    // Wiki2XHTML private methods

    /**
     * @see Wiki2xhtmlRenderer
     */
    function parseWikiWord( $str, &$tag, &$attr, &$type )
    {
        $tag = '';
        $attr = '';

        if ( $this->wiki->pageExists( $str ) )
        {
            return '<a href="#'.$this->_makePageTitleAnchor( $str )
                . '" class="wikiShow">'
                . $str
                . '</a>'
                ;
        }
        else
        {
            return '<span class="wikiEdit">'
                . $str
                . '</span>'
                ;
        }
    }

    /**
     * @see Wiki2xhtmlRenderer
     */
    function _getWikiPageLink( $pageName, &$tag, &$attr, &$type )
    {
        // allow links to use wikiwords for wiki page locations
        if ($this->getOpt('active_wikiwords') && $this->getOpt('words_pattern'))
        {
            $pageName = preg_replace('/¶¶¶'.$this->getOpt('words_pattern').'¶¶¶/msU', '$1', $pageName);
        }

        if ($this->wiki->pageExists( $pageName ) )
        {
            $attr = ' href="#' . $this->_makePageTitleAnchor( $pageName )
                . '" class="wikiShow"'
                ;
        }
        else
        {
            # FIXME
            $attr = ' class="wikiEdit"';
            $tag = 'span';
        }
    }
}
