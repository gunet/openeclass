<?php
    // /home/username/foo/public_html/
    $dir          = dirname(__FILE__)."/../..";
    $include      = '/\.(php|shtml|html|htm|shtm|cgi|txt|doc|pdf|rtf|xls|csv)$/';
    $exclude      = '';
    $dirinclude   = '';
    $direxclude   = '/(^|\/)[._]|htmlarea/'; // Exclude the htmlarea tree by default

    // New backend config data passing
    //  if data was passed using xinha_pass_to_backend() we extract and use it
    //  as the items above    
    require_once(realpath(dirname(__FILE__) . '/../../contrib/php-xinha.php'));
    if($passed_data = xinha_read_passed_data())
    {
      extract($passed_data);      
    }

    // Old deprecated backend config data passing
    //  not described because you shouldn't use it.
    //------------------------------------------------------------------------    
    $hash = '';
    foreach(explode(',', 'dir,include,exclude,dirinclude,direxclude') as $k)
    {
      if(isset($_REQUEST[$k]))
      {
        if(get_magic_quotes_gpc())
        {
          $_REQUEST[$k] = stripslashes($_REQUEST[$k]);
        }
        $hash .= $k . '=' . $_REQUEST[$k];
        $$k = $_REQUEST[$k];
      }
    }

    if($hash)
    {
      session_start();
      if(!isset($_SESSION[sha1($hash)]))
      {
        ?>
        [ ];
        <?php
        exit;
      }
    }
    //------------------------------------------------------------------------


    function scan($dir, $durl = '')
    {
      global $include, $exclude, $dirinclude, $direxclude;
      static $seen = array();

      $files = array();

      $dir = realpath($dir);
      if(isset($seen[$dir]))
      {
        return $files;
      }
      $seen[$dir] = TRUE;
      $dh = @opendir($dir);


      while($dh && ($file = readdir($dh)))
      {
        if($file !== '.' && $file !== '..')
        {
          $path = realpath($dir . '/' . $file);
          $url  = $durl . '/' . $file;

          if(($dirinclude && !preg_match($dirinclude, $url)) || ($direxclude && preg_match($direxclude, $url))) continue;
          if(is_dir($path))
          {
            if($subdir = scan($path, $url))
            {
              $files[] = array('url'=>$url, 'children'=>$subdir);
            }
          }
          elseif(is_file($path))
          {
            if(($include && !preg_match($include, $url)) || ($exclude && preg_match($exclude, $url))) continue;
            $files[] = array('url'=>$url);
          }

        }
      }
      @closedir($dh);
      return dirsort($files);
    }

    function dirsort($files)
    {
      usort($files, 'dircomp');
      return $files;
    }

    function dircomp($a, $b)
    {
      if(is_array($a)) $a = array_shift($a);
      if(is_array($b)) $b = array_shift($b);
      return strcmp(strtolower($a), strtolower($b));
    }
   
    echo xinha_to_js(scan($dir));
?>
