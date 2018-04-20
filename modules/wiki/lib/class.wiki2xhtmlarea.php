<?php

/* ========================================================================
 * Open eClass 3.7
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2018  Greek Universities Network - GUnet
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
  @file class.wiki2xhtmlarea.php
  @author: Frederic Minne <zefredz@gmail.com>
           Open eClass Team <eclass@gunet.gr>
 */


/**
 * Wiki2xhtml editor textarea
 */
class Wiki2xhtmlArea {

    var $content;
    var $attributeList;

    /**
     * Constructor
     * @param string content of the area
     * @param string name name of the area
     * @param int cols number of cols
     * @param int rows number of rows
     * @param array extraAttributes extra html attributes for the area
     */
    public function __construct(
    $content = ''
    , $name = 'wiki_content'
    , $cols = 80
    , $rows = 30
    , $extraAttributes = null) {
        $this->setContent($content);

        $attributeList = array();
        $attributeList['class'] = "form-control";
        $attributeList['name'] = $name;
        $attributeList['id'] = $name;
        $attributeList['rows'] = $rows;

        $this->attributeList = ( is_array($extraAttributes) ) ? array_merge($attributeList, $extraAttributes) : $attributeList
        ;
    }

    /**
     * Set area content
     * @param string content
     */
    function setContent($content) {
        $this->content = $content;
    }

    /**
     * Get area content
     * @return string area content
     */
    function getContent() {
        return $this->content;
    }

    /**
     * Get area wiki syntax toolbar
     * @return string toolbar javascript code
     */
    function getToolbar() {

        global $wiki_toolbar, $langWikiUrl, $langWikiUrlImage, $urlAppend;
        $toolbar = '';

        $toolbar .= '<script type="text/javascript" src="'
                . $urlAppend . 'modules/wiki/lib/javascript/toolbar.js"></script>'
                . "\n"
        ;
        $toolbar .= "<script type=\"text/javascript\">if (document.getElementById) {
        var tb = new dcToolBar(document.getElementById('" . $this->attributeList['id'] . "'),
        'wiki','" . $urlAppend . "modules/wiki/toolbar/');

        tb.btStrong('" . $wiki_toolbar['Strongemphasis'] . "');
        tb.btEm('" . $wiki_toolbar['Emphasis'] . "');
        tb.btIns('" . $wiki_toolbar['Inserted'] . "');
        tb.btDel('" . $wiki_toolbar['Deleted'] . "');
        tb.btQ('" . $wiki_toolbar['Inlinequote'] . "');
        tb.btCode('" . $wiki_toolbar['Code'] . "');
        tb.btHr('" . $wiki_toolbar['Hr'] . "');
        tb.addSpace(10);
        tb.btH1('" . $wiki_toolbar['H1'] . "');
        tb.btH2('" . $wiki_toolbar['H2'] . "');
        tb.btH3('" . $wiki_toolbar['H3'] . "');
        tb.btH4('" . $wiki_toolbar['H4'] . "');
        tb.addSpace(10);
        tb.btBr('" . $wiki_toolbar['Linebreak'] . "');
        tb.addSpace(10);
        tb.btBquote('" . $wiki_toolbar['Blockquote'] . "');
        tb.btPre('" . $wiki_toolbar['Preformatedtext'] . "');
        tb.btList('" . $wiki_toolbar['Unorderedlist'] . "','ul');
        tb.btList('" . $wiki_toolbar['Orderedlist'] . "','ol');
        tb.addSpace(10);
        tb.btLink('" . $wiki_toolbar['Link'] . "','" . $langWikiUrl . "');
        tb.btImgLink('" . $wiki_toolbar['Externalimage'] . "','" . $langWikiUrlImage . "');
        tb.draw('');
    }
    </script>\n";

        return $toolbar;
    }

    /**
     * paint (ie echo) area
     */
    function paint() {
        echo $this->toHTML();
    }

    /**
     * get area html code for string inclusion
     * @return string area html code
     */
    function toHTML() {
        $wikiarea = '';

        $attr = '';

        foreach ($this->attributeList as $attribute => $value) {
            $attr .= ' ' . $attribute . '="' . $value . '"';
        }

        $wikiarea .= '<textarea' . $attr . '>' . q($this->getContent()) . '</textarea>' . "\n";

        $wikiarea .= $this->getToolbar();

        return $wikiarea;
    }

}
