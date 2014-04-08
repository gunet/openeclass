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
 * ======================================================================== 
 */

/* * ===========================================================================
 toc.js

 based on Claroline version 1.11 licensed under GPL
 copyright (c) 2001-2012 Universite catholique de Louvain (UCL)

 Claroline authors: Frederic Minne <zefredz@gmail.com>
==============================================================================
*/

function createTOC()
{
    // to do : gracefully handle if h2 is top level id and not h1

    // configuration options
    var page_block_id = 'mainContent'; // this is the id which contains our h1's etc
    var toc_page_position =-1; // used later to remember where in the page to put the final TOC
    var top_level ="H1";// default top level.. shouldn't matter what is here it is set at line 50 anyway
    var skip_first = true;

    var w = document.getElementById(page_block_id);
    var x = w.childNodes;
    
    //build our table tbody tr td - structure
    y = document.createElement('table');
    y.id='toc';
    mytablebody = document.createElement('TBODY');
    myrow = document.createElement('TR');
    mycell = document.createElement('TD');
    myrow.appendChild(mycell);
    mytablebody.appendChild(myrow);
    y.appendChild(mytablebody);

    // create the two title strings so we can switch between the two later via the id
    var a = mycell.appendChild(document.createElement('span'));
    a.id = 'toc_hide';
    a.innerHTML = '<b>Table of Contents</b> <small>[<a href="" onclick="javascript:showhideTOC();return false;">hide</a>]</small>';
    a.style.textAlign='center';
    var a = mycell.appendChild(document.createElement('span'));
    a.id = 'toc_show';
    a.style.display='none'

    a.innerHTML = '<b>Table of Contents</b> <small>[<a href="" onclick="javascript:showhideTOC();return false;">show</a>]</small>';
    a.style.textAlign='center';

    var z = mycell.appendChild(document.createElement('div'));

    // set the id so we can show/hide this div block later
    z.id ='toc_contents';

    var toBeTOCced = new Array();
    for (var i=0;i<x.length;i++)
    {
        if (x[i].nodeName.indexOf('H') != -1 && x[i].nodeName != "HR") // added check for hr tags
        {
            toBeTOCced.push(x[i])
            if (toc_page_position == -1)
            {
                // get the first one.. don't care which level it is
                toc_page_position = 0;
                // we should also remember which level is top of the page
                top_level = x[i].nodeName;
            }
            else if (toc_page_position == 0)
            {
                toc_page_position = i-1; // we want the toc before the first subheading
            }
        }
    }
    // array to store numeric toc prefixes
    var counterArray = new Array();
    for (var i=0;i<=7;i++)
        {counterArray[i]=0;}

    // quit if it is a small toc
    if (toBeTOCced.length <= 2) return;

    for (var i=0;i<toBeTOCced.length;i++)
    {
        // put the link item in the toc
        var tmp_indent =0;
        // tmp is link in toc
        var tmp = document.createElement('a');
        // tmp2 is name link for this heading ancor
        var tmp2 = document.createElement('a');

        // we need to prefix with a number
        var level = toBeTOCced[i].nodeName.charAt(1);
        // we need to put in the upper numbers ie: 4.2 etc.
        ++counterArray[level];

        tmp.href = '#header_' + i;
        tmp2.id = 'header_' + i;

        for (var j=2;j<=level;j++)
            if (counterArray[j] > 0)
            {
                tmp.innerHTML += counterArray[j]+'.' // add numbering before this toc entry
                tmp_indent +=10;
            }
        tmp.innerHTML +=  ' ' + toBeTOCced[i].innerHTML;

        // if counterArray[+1] != 1 .. reset it and all the above
        level++; // counterArray[level+1] was giving me issues... stupid javascript
        if (counterArray[level] > 0) // if we dropped back down, clear out the upper numbers
        {
            for (var j=level; j < 7; j++)
            {counterArray[j]=0;}
        }

        if (tmp_indent > 10)
            tmp.style.paddingLeft=tmp_indent -10+'px';

        // if NOT h1 tag, add to toc
        if (!skip_first)
        {
            z.appendChild(tmp);
            // put in a br tag after the link
            var tmp_br = document.createElement('br');
            z.appendChild(tmp_br);
        }
        else // else, act as if this item was never created.
        {
            skip_first=false;
            // this is so the toc prefixes stay proper if the page starts with a h2 instead of a h1... we just reset the first heading to 0
            --level;
            --counterArray[level];
        }



        if (toBeTOCced[i].nodeName == 'H1')
        {
            tmp.innerHTML = 'Top';
            tmp.href = '#top';
            tmp2.id = 'top';
        }
        // put the a name tag right before the heading
        toBeTOCced[i].parentNode.insertBefore(tmp2,toBeTOCced[i]);
    }
    w.insertBefore(y,w.childNodes[toc_page_position+2]); // why is this +2 and not +1?
}

var TOCstate = 'block';

function showhideTOC()
{
    TOCstate = (TOCstate == 'none') ? 'block' : 'none';
    // flip the toc contents
    document.getElementById('toc_contents').style.display = TOCstate;
    // now flip the headings
    if (TOCstate == 'none')
    {
        document.getElementById('toc_show').style.display = 'inline';
        document.getElementById('toc_hide').style.display = 'none';
    }
    else
    {
        document.getElementById('toc_show').style.display = 'none';
        document.getElementById('toc_hide').style.display = 'inline';
    }
}