    // $Id$

    /**
     * CLAROLINE
     *
     * @version 1.7 $Revision$
     *
     * @copyright 2001-2005 Universite catholique de Louvain (UCL)
     *
     * @license GENERAL PUBLIC LICENSE (GPL)
     * This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
     * as published by the FREE SOFTWARE FOUNDATION. The GPL is available
     * through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
     *
     * @author Frederic Minne <zefredz@gmail.com>
     *
     * @package Wiki
     */
     
    function disableBoxes( level, privilege )
    {
        if ( privilege == 'read' )
        {
            var sId = level + '_edit';
            unCheck( sId );
            disable( sId );
            sId = level + '_create';
            unCheck( sId );
            disable( sId );
        }
        else if ( privilege == 'edit' )
        {
            var sId = level + '_create';
            unCheck( sId );
            disable( sId );
        }
    }
    
    function enableBoxes( level, privilege )
    {
        if ( privilege == 'read' )
        {
            var sId = level + '_edit';
            enable( sId );
        }
        else if ( privilege == 'edit' )
        {
            var sId = level + '_create';
            enable( sId );
        }
    }
    
    function unCheck( sId )
    {
        var oElem = document.getElementById( sId );
        if ( oElem.checked )
            oElem.checked = false;
    }
    
    function disable( sId )
    {
        var oElem = document.getElementById( sId );
        if ( ! oElem.disabled )
            oElem.disabled = true;
    }
    
    function check( sId )
    {
        var oElem = document.getElementById( sId );
        if ( ! oElem.checked )
            oElem.checked = true;
    }

    function enable( sId )
    {
        var oElem = document.getElementById( sId );
        if ( oElem.disabled )
            oElem.disabled = false;
    }
    
    function updateBoxes( level, privilege )
    {
        var sId = level + '_' + privilege;
        var oElem = document.getElementById( sId );
        
        if ( oElem.checked )
        {
            enable( sId );
            check( sId );
            enableBoxes( level, privilege );
        }
        else
        {
            disableBoxes( level, privilege );
        }
    }
    
    function initBoxes()
    {
        var sId = 'course_read';
        var oElem = document.getElementById( sId )

        if ( ! oElem.checked )
        {
            sId = 'course_edit';
            disable( sId );
            sId = 'course_create';
            disable( sId );
        }

        sId = 'course_edit';
        oElem = document.getElementById( sId )

        if ( ! oElem.checked && ! oElem.disabled )
        {
            sId = 'course_create';
            disable( sId );
        }
        
        sId = 'group_read';
        oElem = document.getElementById( sId )
        
        if ( oElem != null )
        {

            if ( ! oElem.checked )
            {
                sId = 'group_edit';
                disable( sId );
                sId = 'group_create';
                disable( sId );
            }

            sId = 'group_edit';
            oElem = document.getElementById( sId )

            if ( ! oElem.checked && ! oElem.disabled )
            {
                sId = 'group_create';
                disable( sId );
            }
        }
        
        sId = 'other_read';
        oElem = document.getElementById( sId )

        if ( ! oElem.checked )
        {
            sId = 'other_edit';
            disable( sId );
            sId = 'other_create';
            disable( sId );
        }

        sId = 'other_edit';
        oElem = document.getElementById( sId )

        if ( ! oElem.checked && ! oElem.disabled )
        {
            sId = 'other_create';
            disable( sId );
        }
    }