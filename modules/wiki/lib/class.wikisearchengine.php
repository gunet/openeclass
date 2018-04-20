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
 * ========================================================================
 */

/**
 @file class.wikisearchengine.php
 @author: Frederic Minne <zefredz@gmail.com>
           Open eClass Team <eclass@gunet.gr>
*/

!defined ( "CLWIKI_SEARCH_ANY" ) && define ( "CLWIKI_SEARCH_ANY", "CLWIKI_SEARCH_ANY" );
!defined ( "CLWIKI_SEARCH_ALL" ) && define ( "CLWIKI_SEARCH_ALL", "CLWIKI_SEARCH_ALL" );
!defined ( "CLWIKI_SEARCH_EXP" ) && define ( "CLWIKI_SEARCH_EXP", "CLWIKI_SEARCH_EXP" );

/**
 * Search engine for the Wiki
 */
class WikiSearchEngine {
    /**
     * Constructor
     */
    public function __construct() {

    }

    /**
     * Search for a given pattern in Wiki pages in a given Wiki
     * @param int wikiId
     * @param String pattern
     * @param Const mode
     * @return Array of Wiki pages
     */
    function searchInWiki($pattern, $wikiId, $mode = CLWIKI_SEARCH_ANY) {
        $searchArr = $this->makePageSearchQuery( $pattern, null, $mode );

        $sql =  "
            SELECT
                p.`id`, p.`wiki_id`, p.`title`, c.`content`
            FROM
                `wiki_properties` AS w,
                `wiki_pages` AS p,
                `wiki_pages_content` AS c
            WHERE
                p.`wiki_id` = ?d
            AND "
                .$searchArr[0];

		return Database::get()->queryArray($sql, $wikiId, $searchArr[1]);
    }

    // utility functions

    /**
     * Split a search pattern for the given search mode
     * @param String pattern
     * @param Const mode
     * @return Array ( keywords, implode_word )
     */
    function splitPattern($pattern, $mode = CLWIKI_SEARCH_ANY) {
        $pattern = str_replace('_', '\_', $pattern);
        $pattern = str_replace('%', '\%', $pattern);
        $pattern = str_replace('?', '_' , $pattern);
        $pattern = str_replace('*', '%' , $pattern);

        switch( $mode )
        {
            case CLWIKI_SEARCH_ALL:
            {
                $impl = "AND";
                $keywords = preg_split('~\s~', $pattern);
                break;
            }
            case CLWIKI_SEARCH_EXP:
            {
                $impl = "";
                $keywords = array($pattern);
                break;
            }
            case CLWIKI_SEARCH_ANY:
            default:
            {
                $impl = "OR";
                $keywords = preg_split('~\s~', $pattern);
                break;
            }
        }

        $ret = array($keywords, $impl);

        return $ret;
    }

    /**
     * Generate search string for a given pattern in wiki pages
     * @param String pattern
     * @param Const mode
     * @return Array, first position searchstr, second position array of search query arguments
     */
    function makePageSearchQuery($pattern, $groupId = null, $mode = CLWIKI_SEARCH_ANY) {
        list($keywords, $impl) = $this->splitPattern($pattern, $mode);

        $searchTitleArr = array();
        $searchPageArr = array();
        $ret_array = array();

        $ret_array[0] = ''; //search string
        $ret_array[1] = array(); //search query arguments

        //helper arrays to store arguments before putting them in correct order
        $groupstr_args = array();
        $searchTitleArr_args = array();
        $searchPageArr_args = array();

        if (!is_null($groupId)) {
            $groupstr = "( w.`group_id` = ?d  AND w.`id` = p.`wiki_id`)";
            $groupstr[] = $groupId;
        } else {
            $groupstr = "(w.`id` = p.`wiki_id`)";
        }

        foreach ($keywords as $keyword) {
            $searchTitleArr[] = " p.`title` LIKE ?s ";
            $searchTitleArr_args[] = '%'.$keyword.'%';
            $searchPageArr[] = " c.`content` LIKE ?s ";
            $searchPageArr_args[] = '%'.$keyword.'%';
        }

        $searchTitle = implode($impl, $searchTitleArr);

        if (count($searchTitleArr) > 1) {
            $searchTitle = " ( ".$searchTitle.") ";
        }

        $searchPage = implode($impl, $searchPageArr);

        if (count($searchPageArr ) > 1) {
            $searchPage = " ( ".$searchPage.") ";
        }

        $searchStr = "( ".$groupstr." AND c.`id` = p.`last_version` AND " . $searchTitle . " ) OR "
            . "( ".$groupstr." AND c.`id` = p.`last_version` AND " . $searchPage . " )"
            ;

        $ret_array[0] = "($searchStr)";
        $ret_array[1] = array_merge($groupstr_args, $searchTitleArr_args, $groupstr_args, $searchPageArr_args);

        return $ret_array;
    }

}
