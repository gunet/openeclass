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

/**
 * LaTeX Rendering Class - Simple Usage Example
 * Copyright (C) 2003  Benjamin Zeiss <zeiss@math.uni-goettingen.de>
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 * --------------------------------------------------------------------
 * @author Benjamin Zeiss <zeiss@math.uni-goettingen.de>
 * @version v0.6
 * @package latexrender
 *
 */
    // --------------------------------------------------------------------------------------------------
    // adjust this to match your system configuration    
    $picture_cache_path = "/opt/eclass/claroline/latexrender/pictures";	
    $picture_cache_httpd_path = "pictures";

    // --------------------------------------------------------------------------------------------------
    require("class.latexrender.php");
    $latex = new LatexRender($picture_cache_path, $picture_cache_httpd_path);

    echo "<html><title>Latex Render Demo</title><body bgcolor='lightgrey'><h3>Latex Render Demo</h3>";
    echo "<form method='post'>";
    echo "<textarea name='latex_formula' rows=10 cols=50>";

    if (isset($_POST['latex_formula'])) {
        echo stripslashes($_POST['latex_formula']);
    } else {
        echo "\frac {43}{12} \sqrt {43}";
    }

    echo "</textarea>";
    echo "<br><br><input type='submit' value='Render Formula'>";
    echo "</form>";

    if (isset($_POST['latex_formula'])) {
        $url = $latex->getFormulaURL(stripslashes($_POST['latex_formula']));
        if ($url != false)
           echo "<u>Formula:</u><br><br><img src='".$url."'>";
        else
            echo "unparseable or potentially dangerous formula !";
    }

    echo "</body></html>";
?>
