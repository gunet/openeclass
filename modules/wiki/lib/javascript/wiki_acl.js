/*
 *  ========================================================================
 *  * Open eClass
 *  * E-learning and Course Management System
 *  * ========================================================================
 *  * Copyright 2003-2024, Greek Universities Network - GUnet
 *  *
 *  * Open eClass is an open platform distributed in the hope that it will
 *  * be useful (without any warranty), under the terms of the GNU (General
 *  * Public License) as published by the Free Software Foundation.
 *  * The full license can be read in "/info/license/license_gpl.txt".
 *  *
 *  * Contact address: GUnet Asynchronous eLearning Group
 *  *                  e-mail: info@openeclass.org
 *  * ========================================================================
 *
 */

/**===========================================================================
 wiki_acl.js
 @last update: 15-05-2007 by Thanos Kyritsis
 @authors list: Thanos Kyritsis <atkyritsis@upnet.gr>

 based on Claroline version 1.7.9 licensed under GPL
 copyright (c) 2001, 2007 Universite catholique de Louvain (UCL)

 original file: wiki_acl Revision: 1.2

 Claroline authors: Frederic Minne <zefredz@gmail.com>
 ==============================================================================
 @Description:

 @Comments:

 @todo:
 ==============================================================================
 */

function disableBoxes(level, privilege)
{
    if (privilege == 'read')
    {
        var sId = level + '_edit';
        unCheck(sId);
        disable(sId);
        sId = level + '_create';
        unCheck(sId);
        disable(sId);
    }
    else if (privilege == 'edit')
    {
        var sId = level + '_create';
        unCheck(sId);
        disable(sId);
    }
}

function enableBoxes(level, privilege)
{
    if (privilege == 'read')
    {
        var sId = level + '_edit';
        enable(sId);
    }
    else if (privilege == 'edit')
    {
        var sId = level + '_create';
        enable(sId);
    }
}

function unCheck(sId)
{
    var oElem = document.getElementById(sId);
    if (oElem.checked)
        oElem.checked = false;
}

function disable(sId)
{
    var oElem = document.getElementById(sId);
    if (!oElem.disabled)
        oElem.disabled = true;
}

function check(sId)
{
    var oElem = document.getElementById(sId);
    if (!oElem.checked)
        oElem.checked = true;
}

function enable(sId)
{
    var oElem = document.getElementById(sId);
    if (oElem.disabled)
        oElem.disabled = false;
}

function updateBoxes(level, privilege)
{
    var sId = level + '_' + privilege;
    var oElem = document.getElementById(sId);

    if (oElem.checked)
    {
        enable(sId);
        check(sId);
        enableBoxes(level, privilege);
    }
    else
    {
        disableBoxes(level, privilege);
    }
}

function initBoxes()
{
    var sId = 'course_read';
    var oElem = document.getElementById(sId)

    if (!oElem.checked)
    {
        sId = 'course_edit';
        disable(sId);
        sId = 'course_create';
        disable(sId);
    }

    sId = 'course_edit';
    oElem = document.getElementById(sId)

    if (!oElem.checked && !oElem.disabled)
    {
        sId = 'course_create';
        disable(sId);
    }

    sId = 'group_read';
    oElem = document.getElementById(sId)

    if (oElem != null)
    {

        if (!oElem.checked)
        {
            sId = 'group_edit';
            disable(sId);
            sId = 'group_create';
            disable(sId);
        }

        sId = 'group_edit';
        oElem = document.getElementById(sId)

        if (!oElem.checked && !oElem.disabled)
        {
            sId = 'group_create';
            disable(sId);
        }
    }

    sId = 'other_read';
    oElem = document.getElementById(sId)

    if (!oElem.checked)
    {
        sId = 'other_edit';
        disable(sId);
        sId = 'other_create';
        disable(sId);
    }

    sId = 'other_edit';
    oElem = document.getElementById(sId)

    if (!oElem.checked && !oElem.disabled)
    {
        sId = 'other_create';
        disable(sId);
    }
}
