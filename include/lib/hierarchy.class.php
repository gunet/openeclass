<?php
/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2012  Greek Universities Network - GUnet
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

class hierarchy {

    public $dbtable;
    
    /**
     * Constructor
     *
     * @param string $dbtable - Name of table with tree nodes
     */    
    public function hierarchy($dbtable = 'hierarchy')
    {
        $this->dbtable = $dbtable;
    }
    
    /**
     * Add a node to the tree
     * 
     * @param string $name - The new node name
     * @param int $parentlft - The new node's parent lft
     * @param string $code - The new node code
     * @param int $allow_course
     * @param int $allow_user
     * 
     * @return int - the new node id
     */
    public function addNode($name, $parentlft, $code, $allow_course, $allow_user)
    {
        $ret = null;
        
        if ($this->useProcedures())
        {
            db_query("CALL add_node(".autoquote($name).", $parentlft, ".autoquote($code).", $allow_course, $allow_user)");
            $r = mysql_fetch_array(db_query("SELECT LAST_INSERT_ID()"));
            $ret = $r[0];
        }
        else {
            $lft = $parentlft + 1;
            $rgt = $parentlft + 2;

            $this->shiftRight($parentlft);

            $query = "INSERT INTO ". $this->dbtable ." (name, lft, rgt, code, allow_course, allow_user) "
                    ."VALUES (".autoquote($name).", $lft, $rgt, ".autoquote($code).", $allow_course, $allow_user)";
            db_query($query);
            $ret = mysql_insert_id();
        }
        
        return $ret;
    }
    
    public function addNodeExt($name, $parentlft, $code, $number, $generator, $allow_course, $allow_user)
    {
        $ret = null;
        
        if ($this->useProcedures())
        {
            db_query("CALL add_node_ext(".autoquote($name).", $parentlft, ".autoquote($code).", $number, $generator, $allow_course, $allow_user)");
            $r = mysql_fetch_array(db_query("SELECT LAST_INSERT_ID()"));
            $ret = $r[0];
        }
        else {
            $lft = $parentlft + 1;
            $rgt = $parentlft + 2;

            $this->shiftRight($parentlft);

            $query = "INSERT INTO ". $this->dbtable ." (name, lft, rgt, code, number, generator, allow_course, allow_user) "
                    ."VALUES (".autoquote($name).", $lft, $rgt, ".autoquote($code).", $number, $generator, $allow_course, $allow_user)";
            db_query($query);
            $ret = mysql_insert_id();
        }
        
        return $ret;
    }
    
    /**
     * Update a tree node
     * 
     * @param int $id
     * @param string $name
     * @param int $nodelft
     * @param int $lft
     * @param int $rgt
     * @param int $parentlft
     * @param string $code
     * @param int $allow_course
     * @param int $allow_user
     */
    public function updateNode($id, $name, $nodelft, $lft, $rgt, $parentlft, $code, $allow_course, $allow_user)
    {
        if ($this->useProcedures())
        {
            db_query("CALL update_node($id, ".autoquote($name).", $nodelft, $lft, $rgt, $parentlft, ".autoquote($code).", $allow_course, $allow_user)");
        }
        else
        {
            $query = "UPDATE ". $this->dbtable ." SET name = ".autoquote($name).",  lft = $lft, rgt = $rgt,
                    code = ".autoquote($code).", allow_course = $allow_course, allow_user = $allow_user WHERE id = $id";
            db_query($query);

            if($nodelft != $parentlft)
            {
                $this->moveNodes($nodelft, $lft, $rgt);
            }
        }
    }
    
    /**
     * Delete a node from the tree
     * 
     * @param int $id - The id of the node to delete
     */
    public function deleteNode($id)
    {
        if ($this->useProcedures())
        {
            db_query("CALL delete_node($id)");
        }
        else
        {
            $result = db_query("SELECT lft, rgt FROM ". $this->dbtable ." WHERE id = $id");

            $row = mysql_fetch_assoc($result);

            $lft = $row['lft'];
            $rgt = $row['rgt'];

            db_query("DELETE FROM ". $this->dbtable ." WHERE id = $id");

            $this->delete($lft, $rgt);
        }
    }
    
    /**
     * Shift tree nodes to the right
     *
     * @param int $node - This is the left of the node after which we want to shift
     * @param int $shift - Length of shift
     * @param int $maxrgt - Maximum rgt value in the tree                       
     */    
    public function shiftRight($node, $shift = 2, $maxrgt = 0)
    {
        $this->shift('+', $node, $shift, $maxrgt);     
    }
      
    /**
     * Shift tree nodes to the left
     * 
     * @param int $node - This is the left of the node after which we want to shift
     * @param int $shift - Length of shift
     * @param int $maxrgt - Maximum rgt value in the tree
     */    
    public function shiftLeft($node, $shift = 2, $maxrgt = 0)
    {
        $this->shift('-', $node, $shift, $maxrgt);     
    }
    
    //shift nodes to the end    
    public function shiftEnd($lft, $rgt, $maxrgt)
    {
        $query = "UPDATE ". $this->dbtable ." SET  lft = (lft - ". ($lft-1) .")+". $maxrgt .", rgt = (rgt - ". ($lft-1) .")+". $maxrgt ." WHERE lft BETWEEN ". $lft ." AND ". $rgt;
        db_query($query);
    }
    
    /**
     * Shift tree nodes 
     * 
     * @param string $action - '+' for shift to the right, '-' for shift to the left
     * @param int $node - This is the left of the node after which we want to shift
     * @param int $shift - Length of shift
     * @param int $maxrgt - Maximum rgt value in the tree
     */
    public function shift($action, $node, $shift = 2, $maxrgt = 0)
    {
        $query = "UPDATE ". $this->dbtable ." SET rgt = rgt ". $action ." ". $shift ." WHERE rgt > ". $node . ($maxrgt>0 ? " AND rgt<=" . $maxrgt : '');
        db_query($query);
      
        $query = "UPDATE ". $this->dbtable ." SET lft = lft ". $action ." ". $shift ." WHERE lft > ". $node . ($maxrgt>0 ? " AND lft<=" . $maxrgt : '');
        db_query($query);
    } 
    
    /**
     * Get maximum rgt value in the tree
     * 
     * @return int 
     */
    public function getMaxRgt()
    {
        $result = db_query("SELECT rgt FROM ". $this->dbtable ." ORDER BY rgt desc limit 1");
        $row = mysql_fetch_assoc($result);
        
        return $row['rgt'];
    }
    
    /**
     * Get child's parent
     *
     * @param int $lft - left node of child
     * @param int $rgt - right node of child
     * 
     * @return array
     */
    public function getParent($lft, $rgt)
    {
        $query = "SELECT * FROM ". $this->dbtable ." WHERE lft < ". $lft ." AND rgt > ". $rgt ." ORDER BY lft DESC LIMIT 1";
        $result = db_query($query);
        
        return mysql_fetch_assoc($result);
    }
    
    public function getNodeLft($id)
    {
        $query = "SELECT lft FROM ". $this->dbtable ." WHERE id = ". $id;
        $res = mysql_fetch_assoc(db_query($query));
        
        return intval($res['lft']);
    }
    
    /**
     * Delete nodes
     * 
     * @param int $lft - left node of child
     * @param int $rgt - right node of child
     */
    public function delete($lft, $rgt)
    {
        $nodeWidth = $rgt - $lft + 1;
              
        $query = "DELETE FROM ". $this->dbtable ."  WHERE lft BETWEEN ". $lft ." AND ". $rgt;
        db_query($query);
        
        $query = "UPDATE ". $this->dbtable ."  SET rgt = rgt - ". $nodeWidth ." WHERE rgt > ". $rgt;
        db_query($query);
        
        $query = "UPDATE ". $this->dbtable ."  SET lft = lft - ". $nodeWidth ." WHERE lft > ". $lft;
        db_query($query);
    }
    
    //move nodes
    public function moveNodes($nodelft, $lft, $rgt)
    {
        $nodeWidth = $rgt - $lft + 1;
        $maxrgt = $this->getMaxRgt();
        
        $this->shiftEnd($lft, $rgt, $maxrgt);
        
        if($nodelft==0)
        {
            $this->shiftLeft($rgt, $nodeWidth);
        }
        else
        {
            $this->shiftLeft($rgt, $nodeWidth, $maxrgt);
          
            if($lft<$nodelft)
            {
                $nodelft = $nodelft - $nodeWidth;
            }
          
            $this->shiftRight($nodelft, $nodeWidth, $maxrgt);
          
            $query = "UPDATE ". $this->dbtable ." SET rgt = (rgt - ". $maxrgt .") + ". $nodelft ." WHERE rgt > ". $maxrgt;
            db_query($query);
        
            $query = "UPDATE ". $this->dbtable ." SET lft = (lft - ". $maxrgt .") + ". $nodelft ." WHERE lft > ". $maxrgt;
            db_query($query);
        }    
    }
    
    /**
     * Build tree array
     *
     * @param string $useKey - key for return array, can be 'left' node or 'id'
     * @param int $exclude - the id of the subtree parent node we want to exclude from the dropdown select
     */       
    public function build($tree_array = array('0' => 'Top'), $useKey = 'lft', $exclude = null, $where = "")
    {
        if ($exclude != null)
        {
            $result = db_query("SELECT * FROM ". $this->dbtable ." WHERE id = $exclude");
            $row = mysql_fetch_assoc($result);
            
            $query = "SELECT node.id, node.lft AS lft, node.name AS name, COUNT(parent.id) - 1 AS depth
                FROM ". $this->dbtable ." AS node, ". $this->dbtable ." AS parent 
                    WHERE node.lft BETWEEN parent.lft AND parent.rgt 
                    AND (node.lft < ". $row['lft'] ." OR node.lft > ". $row['rgt'] .")
                    $where
                    GROUP BY node.id 
                    ORDER BY node.lft";
        }
        else
        {
            $query = "SELECT node.id, node.lft AS lft, node.name AS name, COUNT(parent.id) - 1 AS depth
                FROM ". $this->dbtable ." AS node, ". $this->dbtable ." AS parent 
                    WHERE node.lft BETWEEN parent.lft AND parent.rgt 
                    $where
                    GROUP BY node.id 
                    ORDER BY node.lft";
        }
        
        $result = db_query($query);
              
        while($row = mysql_fetch_assoc($result))
        {
            $prefix = '';
            for ($i = 0; $i < $row['depth']; $i++)
                $prefix .= '&nbsp;-&nbsp;';
            
            switch($useKey)
            {
                case 'lft':
                    $tree_array[$row['lft']] = $prefix . self::unserializeLangField($row['name']);
                    break;
                case 'id':
                    $tree_array[$row['id']] = $prefix . self::unserializeLangField($row['name']);
                    break;
            }
        }  
        
        return $tree_array;  
    }
    
    /**
     * Build tree using <ul><li> html tags
     *
     * @param string $params - for any html params for tag <ul>
     * 
     * @return string $html - html output
     */
    public function buildHtmlUl($params = "")
    {
        $html = '<ul ' . $params . '>' . "\n";
        $current_depth = 0;
        
        $query = "SELECT node.name AS name, (COUNT(parent.id) - 1) AS depth FROM ". $this->dbtable ." AS node,  ". $this->dbtable ." AS parent WHERE node.lft BETWEEN parent.lft AND parent.rgt GROUP BY node.id ORDER BY node.lft";
        $result = db_query($query);
        
        while($row = mysql_fetch_assoc($result))
        {
            if($row['depth'] > $current_depth)
            {
                $html = substr($html,0,-6);
                $html .= '<ul>' . "\n";

                $current_depth = $row['depth'];
            }
          
            if($row['depth'] < $current_depth)
            {
                for($i=$current_depth; $i>$row['depth']; $i--)
                {
                    $html .= '</ul></li>' . "\n";
                }

                $current_depth = $row['depth'];
            }
        
            $html .= '<li>' . self::unserializeLangField($row['name']) . '</li>' . "\n";
        }
        
        $html .= '</ul>';
        
        return $html;
    }
    
    /**
     * Build tree using <select> html tags
     *
     * @param string $params     - parameters for <select> tag
     * @param array  $defaults   - the ids of the selected nodes. it can also be a single integer value
     * @param int    $exclude    - the id of the subtree parent node we want to exclude from the dropdown select
     * @param array  $tree_array - array with values to prepend the node values
     * @param string $useKey     - node property to use for html select id
     * @param string $where      - extra SQL where clause to use
     * 
     * @return string $html - html output
     */
    public function buildHtmlSelect($params = '', $defaults = '', $exclude = null, $tree_array = array('0' => 'Top'), $useKey = 'lft', $where = '', $multiple = false)
    {
        $defs = (is_array($defaults)) ? $defaults : array(intval($defaults));
        $multi = ($multiple) ? ' multiple = "multiple"' : '';
        $html = '<select '. $params . $multi .'>'. "\n";
        $tree_array = $this->build($tree_array, $useKey, $exclude, $where);
        
        foreach($tree_array as $key => $value)
        {
            $html .= '<option value="'. $key .'" '. (in_array($key, $defs) ? 'selected' : '') .'>'. $value .'</option>';
        }
        
        $html .= '</select>'. "\n";
        return $html;
    }
    
    public function buildCourseHtmlSelect($params = '', $defaults = '', $exclude = null, $tree_array = array(), $useKey = 'id', $where = 'AND node.allow_course = true')
    {
        return $this->buildHtmlSelect($params, $defaults, $exclude, $tree_array, $useKey, $where, get_config('course_multidep'));
    }
    
    public function buildUserHtmlSelect($params = '', $defaults = '', $exclude = null, $tree_array = array(), $useKey = 'id', $where = 'AND node.allow_user = true')
    {
        return $this->buildHtmlSelect($params, $defaults, $exclude, $tree_array, $useKey, $where, get_config('user_multidep'));
    }
    
    /**
     * Build simple tree array
     *
     * @param string $where
     */
    public function buildSimple($where = null)
    {
        $result = db_query("SELECT id, name FROM $this->dbtable ". $where ." order by id");
        
        $nodes = array();
        
        while($row = mysql_fetch_assoc($result))
        {
            $nodes[$row['id']] = self::unserializeLangField($row['name']);
        }  
        
        return $nodes;  
    }
    
    /**
     * Get a node's full breadcrump-style path
     * 
     * @param int     $nodeid    - the child's id whose full path we want
     * @param boolean $skipfirst - whether we skip the first parent in the full path or not
     * 
     * @return string 
     */
    public function getFullPath($nodeid, $skipfirst = true)
    {
        $ret = "";
        
        if ($nodeid == null)
            return $ret;
        
        $node = mysql_fetch_assoc(db_query("SELECT * FROM $this->dbtable WHERE id = ". $nodeid));
        if (!$node)
            return $ret;
        
        $result = db_query("SELECT * FROM $this->dbtable WHERE lft < ". $node['lft'] ." AND rgt > ". $node['rgt'] ." ORDER BY lft ASC");
        
        $c = 0;
        $skip = 0;
        while ($parent = mysql_fetch_assoc($result))
        {
            if ($skipfirst && $skip == 0)
            {
                $skip++;
                continue;
            }
            
            $ret .= ($c == 0) ? '' : '» ';
            $ret .= self::unserializeLangField($parent['name']) .' ';
            $c++;
        }
        
        $ret .= ($c == 0) ? '' : '» ';
        $ret .= self::unserializeLangField($node['name']) .' ';
        
        return $ret;
    }
    
    public static function unserializeLangField($value)
    {
        global $language;
        
        $values = @unserialize($value);
        
        if ($values !== false)
        {
            if (isset($values[langname_to_code($language)]) && !empty($values[langname_to_code($language)]))
                return $values[langname_to_code($language)];
            else
                return array_shift($values);
        } else {
            return $value;
        }
    }
    
    /**
     * Check if we can use Stored Procedures
     * 
     * @return boolean 
     */
    private function useProcedures() {
        if (version_compare(mysql_get_server_info(), '5.0') >= 0)
            return true;
        else
            return false;
    }
    
}
?>
