<?php

/*
 * Session Management for PHP3
 *
 * (C) Copyright 1999-2000 NetUSE GmbH
 *                    Kristian Koehntopp
 *
 * $Id$
 *
 */

/*
 * Change log since version 7.2c
 *
 * Bug fixes to version 7.2c compiled by Richard Archer <rha@juggernaut.com.au>:
 * (credits given to first person to post a diff to phplib mailing list)
 *
 * Normalised all comments and whitespace (rha)
 * replaced "$handle" with "$varname" and "$h" with "$v" throughout (from phplib-devel)
 * added braces around all one-line if statements in: get_undefined, loadfile and halt (rha)
 * set_var was missing two sets of braces (rha)
 * added a couple of "return true" statements (rha)
 * set_unknowns had "keep" as default instead of "remove" (from phplib-devel)
 * set_file failed to check for empty strings if passed an array of filenames (phplib-devel)
 * remove @ from call to preg_replace in subst -- report errors if there are any (NickM)
 * set_block unnecessarily required a newline in the template file (Marc Tardif)
 * pparse now calls this->finish to replace undefined vars (Layne Weathers)
 * get_var now checks for unset varnames (NickM & rha)
 * get_var when passed an array used the array key instead of the value (rha)
 * get_vars now uses a call to get_var rather than this->varvals to prevent undefined var warning (rha)
 * in finish, the replacement string referenced an unset variable (rha)
 * loadfile would try to load a file if the varval had been set to "" (rha)
 * in get_undefined, only match non-whitespace in variable tags as in finish (Layne Weathers & rha)
 * more elegant fix to the problem of subst stripping '$n', '\n' and '\\' strings (rha)
 *
 *
 * Changes in functionality which go beyond bug fixes:
 *
 * changed debug handling so set, get and internals can be tracked separately (rha)
 * added debug statements throughout to track most function calls (rha)
 * debug output contained raw HTML -- is now escaped with htmlentities (rha)
 * Alter regex in set_block to remove more whitespace around BEGIN/END tags to improve HTML layout (rha)
 * Add "append" option to set_var, works just like append in parse (dale at linuxwebpro.com, rha)
 * Altered parse so that append is honored if passed an array (Brian)
 * Converted comments and documentation to phpdoc style (rha)
 * Added clear_var to set the value of variables to "" (rha)
 * Added unset_var to usset variables (rha)
 *
 */

/**
 * The template class allows you to keep your HTML code in some external files
 * which are completely free of PHP code, but contain replacement fields.
 * The class provides you with functions which can fill in the replacement fields
 * with arbitrary strings. These strings can become very large, e.g. entire tables.
 *
 * Note: If you think that this is like FastTemplates, read carefully. It isn't.
 *
 */
class Template {

    /**
     * Serialization helper, the name of this class.
     *
     * @var       string
     * @access    public
     */
    var $classname = "Template";

    /**
     * Determines how much debugging output Template will produce.
     * This is a bitwise mask of available debug levels:
     * 0 = no debugging
     * 1 = debug variable assignments
     * 2 = debug calls to get variable
     * 4 = debug internals (outputs all function calls with parameters).
     *
     * Note: setting $this->debug = true will enable debugging of variable
     * assignments only which is the same behaviour as versions up to release 7.2d.
     *
     * @var       int
     * @access    public
     */
    var $debug = false;

    /**
     * The base directory from which template files are loaded.
     *
     * @var       string
     * @access    private
     * @see       set_root
     */
    var $root = ".";

    /**
     * A hash of strings forming a translation table which translates variable names
     * into names of files containing the variable content.
     * $file[varname] = "filename";
     *
     * @var       array
     * @access    private
     * @see       set_file
     */
    var $file = array();

    /**
     * A hash of strings forming a translation table which translates variable names
     * into regular expressions for themselves.
     * $varkeys[varname] = "/varname/"
     *
     * @var       array
     * @access    private
     * @see       set_var
     */
    var $varkeys = array();

    /**
     * A hash of strings forming a translation table which translates variable names
     * into values for their respective varkeys.
     * $varvals[varname] = "value"
     *
     * @var       array
     * @access    private
     * @see       set_var
     */
    var $varvals = array();

    /**
     * Determines how to output variable tags with no assigned value in templates.
     *
     * @var       string
     * @access    private
     * @see       set_unknowns
     */
    var $unknowns = "remove";

    /**
     * Determines how Template handles error conditions.
     * "yes"      = the error is reported, then execution is halted
     * "report"   = the error is reported, then execution continues by returning "false"
     * "no"       = errors are silently ignored, and execution resumes reporting "false"
     *
     * @var       string
     * @access    public
     * @see       halt
     */
    var $halt_on_error = "yes";

    /**
     * The last error message is retained in this variable.
     *
     * @var       string
     * @access    public
     * @see       halt
     */
    var $last_error = "";

    /*     * ****************************************************************************
     * Class constructor. May be called with two optional parameters.
     * The first parameter sets the template directory the second parameter
     * sets the policy regarding handling of unknown variables.
     *
     * usage: Template([string $root = "."], [string $unknowns = "remove"])
     *
     * @param     $root        path to template directory
     * @param     $string      what to do with undefined variables
     * @see       set_root
     * @see       set_unknowns
     * @access    public
     * @return    void
     */

    public function __construct($root = ".", $unknowns = "remove") {
        if ($this->debug & 4) {
            echo "<p><b>Template:</b> root = $root, unknowns = $unknowns</p>\n";
        }
        $this->set_root($root);
        $this->set_unknowns($unknowns);
    }

    /*     * ****************************************************************************
     * Checks that $root is a valid directory and if so sets this directory as the
     * base directory from which templates are loaded by storing the value in
     * $this->root. Relative filenames are prepended with the path in $this->root.
     *
     * Returns true on success, false on error.
     *
     * usage: set_root(string $root)
     *
     * @param     $root         string containing new template directory
     * @see       root
     * @access    public
     * @return    boolean
     */

    function set_root($root) {
        if ($this->debug & 4) {
            echo "<p><b>set_root:</b> root = $root</p>\n";
        }
        if (!is_dir($root)) {
            $this->halt("set_root: $root is not a directory.");
            return false;
        }

        $this->root = $root;
        return true;
    }

    /*     * ****************************************************************************
     * Sets the policy for dealing with unresolved variable names.
     *
     * unknowns defines what to do with undefined template variables
     * "remove"   = remove undefined variables
     * "comment"  = replace undefined variables with comments
     * "keep"     = keep undefined variables
     *
     * Note: "comment" can cause unexpected results when the variable tag is embedded
     * inside an HTML tag, for example a tag which is expected to be replaced with a URL.
     *
     * usage: set_unknowns(string $unknowns)
     *
     * @param     $unknowns         new value for unknowns
     * @see       unknowns
     * @access    public
     * @return    void
     */

    function set_unknowns($unknowns = "remove") {
        if ($this->debug & 4) {
            echo "<p><b>unknowns:</b> unknowns = $unknowns</p>\n";
        }
        $this->unknowns = $unknowns;
    }

    /*     * ****************************************************************************
     * Defines a filename for the initial value of a variable.
     *
     * It may be passed either a varname and a file name as two strings or
     * a hash of strings with the key being the varname and the value
     * being the file name.
     *
     * The new mappings are stored in the array $this->file.
     * The files are not loaded yet, but only when needed.
     *
     * Returns true on success, false on error.
     *
     * usage: set_file(array $filelist = (string $varname => string $filename))
     * or
     * usage: set_file(string $varname, string $filename)
     *
     * @param     $varname      either a string containing a varname or a hash of varname/file name pairs.
     * @param     $filename     if varname is a string this is the filename otherwise filename is not required
     * @access    public
     * @return    boolean
     */

    function set_file($varname, $filename = "") {
        if (!is_array($varname)) {
            if ($this->debug & 4) {
                echo "<p><b>set_file:</b> (with scalar) varname = $varname, filename = $filename</p>\n";
            }
            if ($filename == "") {
                $this->halt("set_file: For varname $varname filename is empty.");
                return false;
            }
            $this->file[$varname] = $this->filename($filename);
        } else {
            reset($varname);
            while (list($v, $f) = each($varname)) {
                if ($this->debug & 4) {
                    echo "<p><b>set_file:</b> (with array) varname = $v, filename = $f</p>\n";
                }
                if ($f == "") {
                    $this->halt("set_file: For varname $v filename is empty.");
                    return false;
                }
                $this->file[$v] = $this->filename($f);
            }
        }
        return true;
    }

    /*     * ****************************************************************************
     * A variable $parent may contain a variable block defined by:
     * &lt;!-- BEGIN $varname --&gt; content &lt;!-- END $varname --&gt;. This function removes
     * that block from $parent and replaces it with a variable reference named $name.
     * The block is inserted into the varkeys and varvals hashes. If $name is
     * omitted, it is assumed to be the same as $varname.
     *
     * Blocks may be nested but care must be taken to extract the blocks in order
     * from the innermost block to the outermost block.
     *
     * Returns true on success, false on error.
     *
     * usage: set_block(string $parent, string $varname, [string $name = ""])
     *
     * @param     $parent       a string containing the name of the parent variable
     * @param     $varname      a string containing the name of the block to be extracted
     * @param     $name         the name of the variable in which to store the block
     * @access    public
     * @return    boolean
     */

    function set_block($parent, $varname, $name = "") {
        if ($this->debug & 4) {
            echo "<p><b>set_block:</b> parent = $parent, varname = $varname, name = $name</p>\n";
        }
        if (!$this->loadfile($parent)) {
            $this->halt("set_block: unable to load $parent.");
            return false;
        }
        if ($name == "") {
            $name = $varname;
        }

        $str = $this->get_var($parent);
        $reg = "/[ \t]*<!--\s+BEGIN $varname\s+-->\s*?\n?(\s*.*?\n?)\s*<!--\s+END $varname\s+-->\s*?\n?/sm";
        preg_match_all($reg, $str, $m);
        $str = preg_replace($reg, "{%" . "$name%}", $str);
        if (isset($m[1][0])) {
            $this->set_var($varname, $m[1][0]);
        } else {
            $this->halt("set_block: unable to load $varname from $parent.");
        }
        $this->set_var($parent, $str);
        return true;
    }

    /*     * ****************************************************************************
     * This functions sets the value of a variable.
     *
     * It may be called with either a varname and a value as two strings or an
     * an associative array with the key being the varname and the value being
     * the new variable value.
     *
     * The function inserts the new value of the variable into the $varkeys and
     * $varvals hashes. It is not necessary for a variable to exist in these hashes
     * before calling this function.
     *
     * An optional third parameter allows the value for each varname to be appended
     * to the existing variable instead of replacing it. The default is to replace.
     * This feature was introduced after the 7.2d release.
     *
     *
     * usage: set_var(string $varname, [string $value = ""], [boolean $append = false])
     * or
     * usage: set_var(array $varname = (string $varname => string $value), [mixed $dummy_var], [boolean $append = false])
     *
     * @param     $varname      either a string containing a varname or a hash of varname/value pairs.
     * @param     $value        if $varname is a string this contains the new value for the variable otherwise this parameter is ignored
     * @param     $append       if true, the value is appended to the variable's existing value
     * @access    public
     * @return    void
     */

    function set_var($varname, $value = "", $append = false) {
        if (!is_array($varname)) {
            if (!empty($varname)) {
                if ($this->debug & 1) {
                    printf("<b>set_var:</b> (with scalar) <b>%s</b> = '%s'<br>\n", $varname, htmlentities($value));
                }
                $this->varkeys[$varname] = "/" . $this->varname($varname) . "/";
                if ($append && isset($this->varvals[$varname])) {
                    $this->varvals[$varname] .= $value;
                } else {
                    $this->varvals[$varname] = $value;
                }
            }
        } else {
            reset($varname);
            while (list($k, $v) = each($varname)) {
                if (!empty($k)) {
                    if ($this->debug & 1) {
                        printf("<b>set_var:</b> (with array) <b>%s</b> = '%s'<br>\n", $k, htmlentities($v));
                    }
                    $this->varkeys[$k] = "/" . $this->varname($k) . "/";
                    if ($append && isset($this->varvals[$k])) {
                        $this->varvals[$k] .= $v;
                    } else {
                        $this->varvals[$k] = $v;
                    }
                }
            }
        }
    }

    /*     * ****************************************************************************
     * This functions clears the value of a variable.
     *
     * It may be called with either a varname as a string or an array with the
     * values being the varnames to be cleared.
     *
     * The function sets the value of the variable in the $varkeys and $varvals
     * hashes to "". It is not necessary for a variable to exist in these hashes
     * before calling this function.
     *
     *
     * usage: clear_var(string $varname)
     * or
     * usage: clear_var(array $varname = (string $varname))
     *
     * @param     $varname      either a string containing a varname or an array of varnames.
     * @access    public
     * @return    void
     */

    function clear_var($varname) {
        if (!is_array($varname)) {
            if (!empty($varname)) {
                if ($this->debug & 1) {
                    printf("<b>clear_var:</b> (with scalar) <b>%s</b><br>\n", $varname);
                }
                $this->set_var($varname, "");
            }
        } else {
            reset($varname);
            while (list($k, $v) = each($varname)) {
                if (!empty($v)) {
                    if ($this->debug & 1) {
                        printf("<b>clear_var:</b> (with array) <b>%s</b><br>\n", $v);
                    }
                    $this->set_var($v, "");
                }
            }
        }
    }

    /*     * ****************************************************************************
     * This functions unsets a variable completely.
     *
     * It may be called with either a varname as a string or an array with the
     * values being the varnames to be cleared.
     *
     * The function removes the variable from the $varkeys and $varvals hashes.
     * It is not necessary for a variable to exist in these hashes before calling
     * this function.
     *
     *
     * usage: unset_var(string $varname)
     * or
     * usage: unset_var(array $varname = (string $varname))
     *
     * @param     $varname      either a string containing a varname or an array of varnames.
     * @access    public
     * @return    void
     */

    function unset_var($varname) {
        if (!is_array($varname)) {
            if (!empty($varname)) {
                if ($this->debug & 1) {
                    printf("<b>unset_var:</b> (with scalar) <b>%s</b><br>\n", $varname);
                }
                unset($this->varkeys[$varname]);
                unset($this->varvals[$varname]);
            }
        } else {
            reset($varname);
            while (list($k, $v) = each($varname)) {
                if (!empty($v)) {
                    if ($this->debug & 1) {
                        printf("<b>unset_var:</b> (with array) <b>%s</b><br>\n", $v);
                    }
                    unset($this->varkeys[$v]);
                    unset($this->varvals[$v]);
                }
            }
        }
    }

    /*     * ****************************************************************************
     * This function fills in all the variables contained within the variable named
     * $varname. The resulting value is returned as the function result and the
     * original value of the variable varname is not changed. The resulting string
     * is not "finished", that is, the unresolved variable name policy has not been
     * applied yet.
     *
     * Returns: the value of the variable $varname with all variables substituted.
     *
     * usage: subst(string $varname)
     *
     * @param     $varname      the name of the variable within which variables are to be substituted
     * @access    public
     * @return    string
     */

    function subst($varname) {
        $varvals_quoted = array();
        if ($this->debug & 4) {
            echo "<p><b>subst:</b> varname = $varname</p>\n";
        }
        if (!$this->loadfile($varname)) {
            $this->halt("subst: unable to load $varname.");
            return false;
        }

        // quote the replacement strings to prevent bogus stripping of special chars
        reset($this->varvals);
        while (list($k, $v) = each($this->varvals)) {
            $varvals_quoted[$k] = preg_replace(array('/\\\\/', '/\$/'), array('\\\\\\\\', '\\\\$'), $v);
        }

        $str = $this->get_var($varname);
        $str = preg_replace($this->varkeys, $varvals_quoted, $str);
        return $str;
    }

    /*     * ****************************************************************************
     * This is shorthand for print $this->subst($varname). See subst for further
     * details.
     *
     * Returns: always returns false.
     *
     * usage: psubst(string $varname)
     *
     * @param     $varname      the name of the variable within which variables are to be substituted
     * @access    public
     * @return    false
     * @see       subst
     */

    function psubst($varname) {
        if ($this->debug & 4) {
            echo "<p><b>psubst:</b> varname = $varname</p>\n";
        }
        print $this->subst($varname);

        return false;
    }

    /*     * ****************************************************************************
     * The function substitutes the values of all defined variables in the variable
     * named $varname and stores or appends the result in the variable named $target.
     *
     * It may be called with either a target and a varname as two strings or a
     * target as a string and an array of variable names in varname.
     *
     * The function inserts the new value of the variable into the $varkeys and
     * $varvals hashes. It is not necessary for a variable to exist in these hashes
     * before calling this function.
     *
     * An optional third parameter allows the value for each varname to be appended
     * to the existing target variable instead of replacing it. The default is to
     * replace.
     *
     * If $target and $varname are both strings, the substituted value of the
     * variable $varname is inserted into or appended to $target.
     *
     * If $handle is an array of variable names the variables named by $handle are
     * sequentially substituted and the result of each substitution step is
     * inserted into or appended to in $target. The resulting substitution is
     * available in the variable named by $target, as is each intermediate step
     * for the next $varname in sequence. Note that while it is possible, it
     * is only rarely desirable to call this function with an array of varnames
     * and with $append = true. This append feature was introduced after the 7.2d
     * release.
     *
     * Returns: the last value assigned to $target.
     *
     * usage: parse(string $target, string $varname, [boolean $append])
     * or
     * usage: parse(string $target, array $varname = (string $varname), [boolean $append])
     *
     * @param     $target      a string containing the name of the variable into which substituted $varnames are to be stored
     * @param     $varname     if a string, the name the name of the variable to substitute or if an array a list of variables to be substituted
     * @param     $append      if true, the substituted variables are appended to $target otherwise the existing value of $target is replaced
     * @access    public
     * @return    string
     * @see       subst
     */

    function parse($target, $varname, $append = false) {
        if (!is_array($varname)) {
            if ($this->debug & 4) {
                echo "<p><b>parse:</b> (with scalar) target = $target, varname = $varname, append = $append</p>\n";
            }
            $str = $this->subst($varname);
            if ($append) {
                $this->set_var($target, $this->get_var($target) . $str);
            } else {
                $this->set_var($target, $str);
            }
        } else {
            reset($varname);
            while (list($i, $v) = each($varname)) {
                if ($this->debug & 4) {
                    echo "<p><b>parse:</b> (with array) target = $target, i = $i, varname = $v, append = $append</p>\n";
                }
                $str = $this->subst($v);
                if ($append) {
                    $this->set_var($target, $this->get_var($target) . $str);
                } else {
                    $this->set_var($target, $str);
                }
            }
        }

        if ($this->debug & 4) {
            echo "<p><b>parse:</b> completed</p>\n";
        }
        return $str;
    }

    /*     * ****************************************************************************
     * This is shorthand for print $this->parse(...) and is functionally identical.
     * See parse for further details.
     *
     * Returns: always returns false.
     *
     * usage: pparse(string $target, string $varname, [boolean $append])
     * or
     * usage: pparse(string $target, array $varname = (string $varname), [boolean $append])
     *
     * @param     $target      a string containing the name of the variable into which substituted $varnames are to be stored
     * @param     $varname     if a string, the name the name of the variable to substitute or if an array a list of variables to be substituted
     * @param     $append      if true, the substituted variables are appended to $target otherwise the existing value of $target is replaced
     * @access    public
     * @return    false
     * @see       parse
     */

    function pparse($target, $varname, $append = false) {
        if ($this->debug & 4) {
            echo "<p><b>pparse:</b> passing parameters to parse...</p>\n";
        }
        print $this->finish($this->parse($target, $varname, $append));
        return false;
    }

    /*     * ****************************************************************************
     * This function returns an associative array of all defined variables with the
     * name as the key and the value of the variable as the value.
     *
     * This is mostly useful for debugging. Also note that $this->debug can be used
     * to echo all variable assignments as they occur and to trace execution.
     *
     * Returns: a hash of all defined variable values keyed by their names.
     *
     * usage: get_vars()
     *
     * @access    public
     * @return    array
     * @see       $debug
     */

    function get_vars() {
        if ($this->debug & 4) {
            echo "<p><b>get_vars:</b> constructing array of vars...</p>\n";
        }
        reset($this->varkeys);
        while (list($k, $v) = each($this->varkeys)) {
            $result[$k] = $this->get_var($k);
        }
        return $result;
    }

    /*     * ****************************************************************************
     * This function returns the value of the variable named by $varname.
     * If $varname references a file and that file has not been loaded yet, the
     * variable will be reported as empty.
     *
     * When called with an array of variable names this function will return a a
     * hash of variable values keyed by their names.
     *
     * Returns: a string or an array containing the value of $varname.
     *
     * usage: get_var(string $varname)
     * or
     * usage: get_var(array $varname)
     *
     * @param     $varname     if a string, the name the name of the variable to get the value of, or if an array a list of variables to return the value of
     * @access    public
     * @return    string or array
     */

    function get_var($varname) {
        if (!is_array($varname)) {
            if (isset($this->varvals[$varname])) {
                $str = $this->varvals[$varname];
            } else {
                $str = "";
            }
            if ($this->debug & 2) {
                printf("<b>get_var</b> (with scalar) <b>%s</b> = '%s'<br>\n", $varname, htmlentities($str));
            }
            return $str;
        } else {
            reset($varname);
            while (list($k, $v) = each($varname)) {
                if (isset($this->varvals[$v])) {
                    $str = $this->varvals[$v];
                } else {
                    $str = "";
                }
                if ($this->debug & 2) {
                    printf("<b>get_var:</b> (with array) <b>%s</b> = '%s'<br>\n", $v, htmlentities($str));
                }
                $result[$v] = $str;
            }
            return $result;
        }
    }

    /*     * ****************************************************************************
     * This function returns a hash of unresolved variable names in $varname, keyed
     * by their names (that is, the hash has the form $a[$name] = $name).
     *
     * Returns: a hash of varname/varname pairs or false on error.
     *
     * usage: get_undefined(string $varname)
     *
     * @param     $varname     a string containing the name the name of the variable to scan for unresolved variables
     * @access    public
     * @return    array
     */

    function get_undefined($varname) {
        if ($this->debug & 4) {
            echo "<p><b>get_undefined:</b> varname = $varname</p>\n";
        }
        if (!$this->loadfile($varname)) {
            $this->halt("get_undefined: unable to load $varname.");
            return false;
        }

        preg_match_all("/{%([^ \t\r\n}]+)%}/", $this->get_var($varname), $m);
        $m = $m[1];
        if (!is_array($m)) {
            return false;
        }

        reset($m);
        while (list($k, $v) = each($m)) {
            if (!isset($this->varkeys[$v])) {
                if ($this->debug & 4) {
                    echo "<p><b>get_undefined:</b> undefined: $v</p>\n";
                }
                $result[$v] = $v;
            }
        }

        if (count($result)) {
            return $result;
        } else {
            return false;
        }
    }

    /*     * ****************************************************************************
     * This function returns the finished version of $str. That is, the policy
     * regarding unresolved variable names will be applied to $str.
     *
     * Returns: a finished string derived from $str and $this->unknowns.
     *
     * usage: finish(string $str)
     *
     * @param     $str         a string to which to apply the unresolved variable policy
     * @access    public
     * @return    string
     * @see       set_unknowns
     */

    function finish($str) {
        switch ($this->unknowns) {
            case "keep":
                break;

            case "remove":
                $str = preg_replace('/{%[^ \t\r\n}]+%}/', "", $str);
                break;

            case "comment":
                $str = preg_replace('/{%([^ \t\r\n}]+)%}/', "<!-- Template variable \\1 undefined -->", $str);
                break;
        }

        return $str;
    }

    /*     * ****************************************************************************
     * This function prints the finished version of the value of the variable named
     * by $varname. That is, the policy regarding unresolved variable names will be
     * applied to the variable $varname then it will be printed.
     *
     * usage: p(string $varname)
     *
     * @param     $varname     a string containing the name of the variable to finish and print
     * @access    public
     * @return    void
     * @see       set_unknowns
     * @see       finish
     */

    function p($varname) {
        print $this->finish($this->get_var($varname));
    }

    /*     * ****************************************************************************
     * This function returns the finished version of the value of the variable named
     * by $varname. That is, the policy regarding unresolved variable names will be
     * applied to the variable $varname and the result returned.
     *
     * Returns: a finished string derived from the variable $varname.
     *
     * usage: get(string $varname)
     *
     * @param     $varname     a string containing the name of the variable to finish
     * @access    public
     * @return    void
     * @see       set_unknowns
     * @see       finish
     */

    function get($varname) {
        return $this->finish($this->get_var($varname));
    }

    /*     * ****************************************************************************
     * When called with a relative pathname, this function will return the pathname
     * with $this->root prepended. Absolute pathnames are returned unchanged.
     *
     * Returns: a string containing an absolute pathname.
     *
     * usage: filename(string $filename)
     *
     * @param     $filename    a string containing a filename
     * @access    private
     * @return    string
     * @see       set_root
     */

    function filename($filename) {
        if ($this->debug & 4) {
            echo "<p><b>filename:</b> filename = $filename</p>\n";
        }
        if (substr($filename, 0, 1) != "/") {
            $filename = $this->root . "/" . $filename;
        }

        if (!file_exists($filename)) {
            $this->halt("filename: file $filename does not exist.");
        }
        return $filename;
    }

    /*     * ****************************************************************************
     * This function will construct a regexp for a given variable name with any
     * special chars quoted.
     *
     * Returns: a string containing an escaped variable name.
     *
     * usage: varname(string $varname)
     *
     * @param     $varname    a string containing a variable name
     * @access    private
     * @return    string
     */

    function varname($varname) {
        return preg_quote("{%" . $varname . "%}");
    }

    /*     * ****************************************************************************
     * If a variable's value is undefined and the variable has a filename stored in
     * $this->file[$varname] then the backing file will be loaded and the file's
     * contents will be assigned as the variable's value.
     *
     * Note that the behaviour of this function changed slightly after the 7.2d
     * release. Where previously a variable was reloaded from file if the value
     * was empty, now this is not done. This allows a variable to be loaded then
     * set to "", and also prevents attempts to load empty variables. Files are
     * now only loaded if $this->varvals[$varname] is unset.
     *
     * Returns: true on success, false on error.
     *
     * usage: loadfile(string $varname)
     *
     * @param     $varname    a string containing the name of a variable to load
     * @access    private
     * @return    boolean
     * @see       set_file
     */

    function loadfile($varname) {
        if ($this->debug & 4) {
            echo "<p><b>loadfile:</b> varname = $varname</p>\n";
        }

        if (!isset($this->file[$varname])) {
            // $varname does not reference a file so return
            if ($this->debug & 4) {
                echo "<p><b>loadfile:</b> varname $varname does not reference a file</p>\n";
            }
            return true;
        }

        if (isset($this->varvals[$varname])) {
            // will only be unset if varname was created with set_file and has never been loaded
            // $varname has already been loaded so return
            if ($this->debug & 4) {
                echo "<p><b>loadfile:</b> varname $varname is already loaded</p>\n";
            }
            return true;
        }
        $filename = $this->file[$varname];

        /* use @file here to avoid leaking filesystem information if there is an error */
        $str = implode("", @file($filename));
        if (empty($str)) {
            $this->halt("loadfile: While loading $varname, $filename does not exist or is empty.");
            return false;
        }

        if ($this->debug & 4) {
            printf("<b>loadfile:</b> loaded $filename into $varname<br>\n");
        }
        $this->set_var($varname, $str);

        return true;
    }

    /*     * ****************************************************************************
     * This function is called whenever an error occurs and will handle the error
     * according to the policy defined in $this->halt_on_error. Additionally the
     * error message will be saved in $this->last_error.
     *
     * Returns: always returns false.
     *
     * usage: halt(string $msg)
     *
     * @param     $msg         a string containing an error message
     * @access    private
     * @return    void
     * @see       $halt_on_error
     */

    function halt($msg) {
        $this->last_error = $msg;

        if ($this->halt_on_error != "no") {
            $this->haltmsg($msg);
        }

        if ($this->halt_on_error == "yes") {
            die("<b>Halted.</b>");
        }

        return false;
    }

    /*     * ****************************************************************************
     * This function prints an error message.
     * It can be overridden by your subclass of Template. It will be called with an
     * error message to display.
     *
     * usage: haltmsg(string $msg)
     *
     * @param     $msg         a string containing the error message to display
     * @access    public
     * @return    void
     * @see       halt
     */

    function haltmsg($msg) {
        printf("<b>Template Error:</b> %s<br>\n", $msg);
    }

}
