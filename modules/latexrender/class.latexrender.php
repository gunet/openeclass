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

class LatexRender {

    // ====================================================================================
    // Variable Definitions
    // ====================================================================================
    var $_picture_path = 'latex_picture_path';
    var $_picture_path_httpd = 'latex_picture_path_httpd';
    var $_tmp_dir = 'latex_tmp_dir'; // without trailing slash! replace with a custom directory if necessary!
    // i was too lazy to write mutator functions for every single program used
    // just access it outside the class or change it here if nescessary
    var $_latex_path = 'latex_path';
    var $_dvips_path = 'dvips_path';
    var $_convert_path = 'convert_path';
    var $_identify_path = 'identify_path';
    var $_formula_density = 120;
    var $_xsize_limit = 500;
    var $_ysize_limit = 500;
	var $_font_size = 12;
    var $_tmp_filename;
	var $_image_format = "png";
    // this most certainly needs to be extended. in the long term it is planned to use
    // a positive list for more security. this is hopefully enough for now. i'd be glad
    // to receive more bad tags !
    var $_latex_tags_blacklist = array(
        "include","def","command","loop","repeat","open","toks","output","line","input",
        "catcode","mathcode","name","item","section","%","^^","\$\$"
        );

    // ====================================================================================
    // constructor
    // ====================================================================================
    
    /**
     * Initializes the class
     *
     * @param string path where the rendered pictures should be stored
     * @param string same path, but from the httpd chroot
     */
    function LatexRender($picture_path,$picture_path_httpd) {
        $this->_picture_path = $picture_path;
        $this->_picture_path_httpd = $picture_path_httpd;
        $this->_tmp_filename = md5(rand());
    }
    
    // ====================================================================================
    // public functions
    // ====================================================================================

    /**
     * Picture path Mutator function
     *
     * @param string sets the current picture path to a new location
     */
    function setPicturePath($name) {
        $this->_picture_path = $name;
    }

    /**
     * Picture path Mutator function
     *
     * @returns the current picture path
     */
    function getPicturePath() {
        return $this->_picture_path;
    }

    /**
     * Picture path HTTPD Mutator function
     *
     * @param string sets the current httpd picture path to a new location
     */
    function setPicturePathHTTPD($name) {
        $this->_picture_path_httpd = $name;
    }

    /**
     * Picture path HTTPD Mutator function
     *
     * @returns the current picture path
     */
    function getPicturePathHTTPD() {
        return $this->_picture_path_httpd;
    }

    /**
     * Tries to match the LaTeX Formula given as argument against the 
     * formula cache. If the picture has not been rendered before, it'll
     * try to render the formula and drop it in the picture cache directory.
     *
     * @param string formula in LaTeX format
     * @returns the webserver based URL to a picture which contains the 
     * requested LaTeX formula. If anything fails, the resultvalue is false.
     */
    function getFormulaURL($latex_formula) {
        // circumvent certain security functions of web-software which
        // is pretty pointless right here
        $latex_formula = preg_replace("/&gt;/i", ">", $latex_formula);
        $latex_formula = preg_replace("/&lt;/i", "<", $latex_formula);

        $formula_hash = md5($latex_formula);

        $filename = $formula_hash.".".$this->_image_format;
        $full_path_filename = $this->getPicturePath()."/".$filename;

        if (is_file($full_path_filename)) {
            return $this->getPicturePathHTTPD()."/".$filename;
        } else {
            // security filter: reject too long formulas
            if (strlen($latex_formula) > 500) {
                return false;
            }

            // security filter: try to match against LaTeX-Tags Blacklist
            for ($i=0;$i<sizeof($this->_latex_tags_blacklist);$i++) {
                if (stristr($latex_formula,$this->_latex_tags_blacklist[$i])) {
                    return false;
                }
            }

            // security checks assume correct formula, let's render it
            if ($this->renderLatex($latex_formula)) {
                return $this->getPicturePathHTTPD()."/".$filename;
            } else {
                return false;
            }
        }
    }

    // ====================================================================================
    // private functions
    // ====================================================================================

    /**
     * wraps a minimalistic LaTeX document around the formula and returns a string
     * containing the whole document as string. Customize if you want other fonts for
     * example.
     *
     * @param string formula in LaTeX format
     * @returns minimalistic LaTeX document containing the given formula
     */
    function wrap_formula($latex_formula) {
        $string  = "\documentclass[".$this->_font_size."pt]{article}\n";
        $string .= "\usepackage[latin1]{inputenc}\n";
//        $string .= "\usepackage{amsmath}\n";
//        $string .= "\usepackage{amsfonts}\n";
//        $string .= "\usepackage{amssymb}\n";
        $string .= "\pagestyle{empty}\n";
        $string .= "\begin{document}\n";
        $string .= "$".$latex_formula."$\n";
        $string .= "\end{document}\n";

        return $string;
    }

    /**
     * returns the dimensions of a picture file using 'identify' of the
     * imagemagick tools. The resulting array can be adressed with either
     * $dim[0] / $dim[1] or $dim["x"] / $dim["y"]
     *
     * @param string path to a picture
     * @returns array containing the picture dimensions
     */
    function getDimensions($filename) {
        $output=exec($this->_identify_path." ".$filename);
        $result=explode(" ",$output);
        $dim=explode("x",$result[2]);
        $dim["x"] = $dim[0];
        $dim["y"] = $dim[1];

        return $dim;
    }

    /**
     * Renders a LaTeX formula by the using the following method:
     *  - write the formula into a wrapped tex-file in a temporary directory
     *    and change to it
     *  - Create a DVI file using latex (tetex)
     *  - Convert DVI file to Postscript (PS) using dvips (tetex)
     *  - convert, trim and add transparancy by using 'convert' from the
     *    imagemagick package.
     *  - Save the resulting image to the picture cache directory using an
     *    md5 hash as filename. Already rendered formulas can be found directly
     *    this way.
     *
     * @param string LaTeX formula
     * @returns true if the picture has been successfully saved to the picture 
     *          cache directory
     */
    function renderLatex($latex_formula) {
        $latex_document = $this->wrap_formula($latex_formula);

        $current_dir = getcwd();

        chdir($this->_tmp_dir);

        // create temporary latex file
        $fp = fopen($this->_tmp_dir."/".$this->_tmp_filename.".tex","a+");
        fputs($fp,$latex_document);
        fclose($fp);

        // create temporary dvi file
        $command = $this->_latex_path." --interaction=nonstopmode ".$this->_tmp_filename.".tex";
        $status_code = exec($command);

        if (!$status_code) { $this->cleanTemporaryDirectory(); chdir($current_dir); return false; }

        // convert dvi file to postscript using dvips
        $command = $this->_dvips_path." -E ".$this->_tmp_filename.".dvi"." -o ".$this->_tmp_filename.".ps";
        $status_code = exec($command);

        // imagemagick convert ps to image and trim picture
        $command = $this->_convert_path." -density ".$this->_formula_density.
                    " -trim -transparent '#FFFFFF' ".$this->_tmp_filename.".ps ".
                    $this->_tmp_filename.".".$this->_image_format;
        
        $status_code = exec($command);

        // test picture for correct dimensions
        $dim = $this->getDimensions($this->_tmp_filename.".".$this->_image_format);

        if ( ($dim["x"] > $this->_xsize_limit) or ($dim["y"] > $this->_ysize_limit)) {
            $this->cleanTemporaryDirectory(); 
            chdir($current_dir); 
            return false;
        }

        // copy temporary formula file to cahed formula directory
        $latex_hash = md5($latex_formula);
        $filename = $this->getPicturePath()."/".$latex_hash.".".$this->_image_format;

        $status_code = copy($this->_tmp_filename.".".$this->_image_format,$filename);

        $this->cleanTemporaryDirectory();	

        if (!$status_code) { chdir($current_dir); return false; }
        chdir($current_dir);

        return true;
    }

    /**
     * Cleans the temporary directory
     */
    function cleanTemporaryDirectory() {
        $current_dir = getcwd();
        chdir($this->_tmp_dir);

        unlink($this->_tmp_dir."/".$this->_tmp_filename.".tex");
        unlink($this->_tmp_dir."/".$this->_tmp_filename.".aux");
        unlink($this->_tmp_dir."/".$this->_tmp_filename.".log");
        unlink($this->_tmp_dir."/".$this->_tmp_filename.".dvi");
        unlink($this->_tmp_dir."/".$this->_tmp_filename.".ps");
        unlink($this->_tmp_dir."/".$this->_tmp_filename.".".$this->_image_format);

        chdir($current_dir);
    }

}

?>
