<?php
// $require_current_course = true;

// require_once '../../include/baseTheme.php';
require_once "h5p-php-library/h5p.classes.php";
// require_once 'config.php';

class H5PClass implements H5PFrameworkInterface {

	private $messages = array('error' => array(), 'info' => array());

  public function setErrorMessage($message, $code = NULL) {
    if (true/*current_user_can('edit_h5p_contents')*/) {
      $this->messages['error'][] = (object)array(
        'code' => $code,
        'message' => $message
      	);
    }
  }


  public function getPlatformInfo(){
  	$info = array();
  	$info['name'] = "GUNET Openeclass";
  	$info['version'] = "4.0";
  	$info['h5pVersion'] = "1.0";
    return $info;
  }


  public function fetchExternalData($url, $data = NULL, $blocking = TRUE, $stream = NULL){
  	//not yet 
  }

  public function setLibraryTutorialUrl($machineName, $tutorialUrl){
  	//not yet/important
  }

  public function setInfoMessage($message) {
    if (true/*current_user_can('edit_h5p_contents')*/) {
      $this->messages['info'][] = $message;
    }
  }

  public function getMessages($type) {
    if (empty($this->messages[$type])) {
      return NULL;
    }
    $messages = $this->messages[$type];
    $this->messages[$type] = array();
    return $messages;
  }

  public function t($message, $replacements = array()) {
    // Insert !var as is, escape @var and emphasis %var.
    // foreach ($replacements as $key => $replacement) {
    //   if ($key[0] === '@') {
    //     $replacements[$key] = esc_html($replacement);
    //   }
    //   elseif ($key[0] === '%') {
    //     $replacements[$key] = '<em>' . esc_html($replacement) . '</em>';
    //   }
    // }
    // $message = preg_replace('/(!|@|%)[a-z0-9-]+/i', '%s', $message);
    // $plugin = H5P_Plugin::get_instance();
    // $this->plugin_slug = $plugin->get_plugin_slug();
    // // Assumes that replacement vars are in the correct order.
    // return vsprintf(__($message, $this->plugin_slug), $replacements);
    return ($message); //proswrina den to exw ftia3ei
  }
  // mono to wordpress to exei to getH5pPath() den einai aparaithto
  private function getH5pPath() {
    	
    $dir = "h5p";
    return $dir;
  }

  public function getLibraryFileUrl($libraryFolderName, $fileName) {
    	
    $dir = "libraries" . $libraryFolderName . "/" . $filename;
    return $dir;
  }

  public function getUploadedH5pFolderPath() {
    global $webDir;
    global $course_code;
    	
    $dir = $webDir . "/courses/temp/h5p/" . $course_code;
    return $dir;
  }

	public function getUploadedH5pPath() {
    global $webDir;
    global $course_code;
    $path = $webDir . '/courses/temp/h5p/' . $course_code . "/*.h5p"; 
    $path = (glob($path));
    $path = implode("",$path);
    return $path;
  }

  public function loadAddons() {
    // global $wpdb;
    // Load addons
    // If there are several versions of the same addon, pick the newest one
    /*return $wpdb->get_results(
      "SELECT l1.id as libraryId, l1.name as machineName,
        l1.major_version as majorVersion, l1.minor_version as minorVersion,
        l1.patch_version as patchVersion, l1.add_to as addTo,
        l1.preloaded_js as preloadedJs, l1.preloaded_css as preloadedCss
      FROM {$wpdb->prefix}h5p_libraries AS l1
      LEFT JOIN {$wpdb->prefix}h5p_libraries AS l2
      ON l1.name = l2.name AND
        (l1.major_version < l2.major_version OR
        (l1.major_version = l2.major_version AND
        l1.minor_version < l2.minor_version))
      WHERE l1.add_to IS NOT NULL AND l2.name IS NULL", ARRAY_A
    );
    // NOTE: These are treated as library objects but are missing the following properties:
    // title, embed_types, drop_library_css, fullscreen, runnable, semantics, has_icon*/
  }

  public function getLibraryConfig($libraries = NULL) {
    return defined('H5P_LIBRARY_CONFIG') ? H5P_LIBRARY_CONFIG : NULL;
  }


  public function loadLibraries() { 
    	
    //global $link;
    $libraries = array();
    $sql=Database::get()->queryArray("SELECT * FROM h5p_library ORDER BY machine_name, major_version ASC, minor_version ASC");
    //$sql="SELECT * FROM h5p_library ORDER BY machine_name ASC, major_version ASC, minor_version ASC"; 
		//$result = mysqli_query($link,$sql);
		// while($row = mysqli_fetch_array($result)) {
		// 	$libraries[$row['machine_name']][] = $row['machine_name'];	
		// }
    foreach($sql as $lib){
      $libraries[$lib->machine_name][] = $lib->machine_name;
    } 
		return $libraries;
  }

  public function getAdminUrl() {

  }


  public function getLibraryId($name, $majorVersion = NULL, $minorVersion = NULL) {
    	
  // global $link;
    if($majorVersion !== NULL){
      if($minorVersion !== NULL){
        $sql = Database::get()->querySingle("SELECT * FROM h5p_library WHERE machine_name = ?s AND major_version = ?d AND minor_version = ?d ORDER BY major_version DESC, minor_version DESC, patch_version DESC LIMIT ?d", $name, $majorVersion, $minorVersion,1);
      }else{
        $sql = Database::get()->querySingle("SELECT * FROM h5p_library WHERE machine_name = ?s AND major_version = ?d ORDER BY major_version DESC, minor_version DESC, patch_version DESC LIMIT ?d", $name, $majorVersion,1);
      }
    }else{
      $sql = Database::get()->querySingle("SELECT * FROM h5p_library WHERE machine_name = ?s ORDER BY major_version DESC, minor_version DESC, patch_version DESC LIMIT ?d", $name,1);
    }
    if($sql){
      $id = $sql->id;
    }else{
      return false;
    }

  //   $sql = "SELECT * FROM h5p_library WHERE machine_name = '$name'";

  //   if($majorVersion !== NULL){
  //     $sql = $sql . " AND major_version = '$majorVersion'";
  //     if($minorVersion !== NULL){
  //       $sql = $sql . " AND minor_version = '$minorVersion'";
  //     }
  //   } 
  //   $sql = $sql . " ORDER BY major_version DESC, minor_version DESC, patch_version DESC LIMIT 1";
  //   $result = mysqli_query($link,$sql);
  //   $id = 0;
  //   while($row = mysqli_fetch_array($result)) {
  //     $id = $row['id'];
  //   } 
    return $id;
  }	

  public function getWhitelist($isLibrary, $defaultContentWhitelist, $defaultLibraryWhitelist) {

    $whitelist = $defaultContentWhitelist;
    if ($isLibrary) {
      $whitelist .= ' ' . $defaultLibraryWhitelist;
    }
    return $whitelist;
 	}

 	public function isPatchedLibrary($library) {
    	
    return TRUE;
	}

	public function isInDevMode() {
    return true;
  }

  public function mayUpdateLibraries(){
  	return true;
  }


  public function saveLibraryData(&$library, $new = TRUE) {
  		
    // global $link;
    if($new){
      if(isset($library['title'])){
        $title = $library['title'];
      }else {$title = NULL;}
      if(isset($library['machineName'])){
        $machine_name = $library['machineName'];
      }else {$machine_name = NULL;}
      if(isset($library['majorVersion'])){
        $major_version = $library['majorVersion'];
      }else {$major_version = NULL;}
      if(isset($library['minorVersion'])){
        $minor_version = $library['minorVersion'];
      }else {$minor_version = NULL;}
      if(isset($library['patchVersion'])){
        $patch_version = $library['patchVersion'];
      }else {$patch_version = NULL;}
      if(isset($library['runnable'])){
        $runnable = $library['runnable'];
      }else {$runnable = 0;}
        if(isset($library['fullscreen'])){
          $fullscreen = $library['fullscreen'];
        }else {$fullscreen = 0;}
      if(isset($library['embedTypes'])){
        $embed_types = implode(",", $library['embedTypes'] );
      }else {$embed_types = "";}
      if(isset($library['preloadedJs'])){
        $array = array();
      foreach($library['preloadedJs'] as $libjs){
        $array[] = $libjs['path'];
      }
      $array = implode(",", $array);
        $preloaded_js = $array;
      }else {$preloaded_js = "";}
      if(isset($library['preloadedCss'])){
        $array = array();
      foreach($library['preloadedCss'] as $libcss){
        $array[] = $libcss['path'];
      }
      $array = implode(",", $array);
        $preloaded_css = $array;
      }else {$preloaded_css = "";}
      // if(isset($library['dropLibraryCss'])){
      //   $array = array();
      // foreach($library['dropLibraryCss'] as $libdrop){
      //   $array[] = $libdrop['machineName'];
      // }
      // $array = implode(",", $array);
      //   $dropLibraryCss = $array;
      // }else {$dropLibraryCss = "";}
      // if(isset($library['semantics'])){
      //   $semantics = mysqli_real_escape_string ($library['semantics']);
      // }else {$semantics = "";}


      $sql = Database::get()->query("INSERT INTO h5p_library(machine_name, title, major_version, minor_version, patch_version, runnable, fullscreen, embed_types, preloaded_js, preloaded_css) VALUES (?s, ?s, ?d, ?d, ?d, ?d, ?d, ?s, ?s, ?s)", function ($errormsg) {
        echo "An error has occured: " . $errormsg;
    },$machine_name, $title, $major_version, $minor_version, $patch_version, $runnable, $fullscreen, $embed_types, $preloaded_js, $preloaded_css);

      // $sql = "INSERT INTO h5p_library(machine_name, title, major_version, minor_version, patch_version, runnable, fullscreen, embed_types, preloaded_js, preloaded_css,dropLibraryCss, semantics) VALUES ('$machine_name', '$title', '$major_version', '$minor_version', '$patch_version', '$runnable','$fullscreen', '$embed_types', '$preloaded_js', '$preloaded_css','$dropLibraryCss', '$semantics')";
      // if ($link->query($sql) === TRUE) {
      //   echo "<br>library inserted into database";
      // } else {
      //   echo "Error: " . $sql . "<br>" . $link->error;
      // }
    }else{
        //not ready yet for update
        echo "<br>NOT YET UPDATE";
    }

  }
                 

  public function insertContent($content, $contentMainId = NULL) {
    return $this->updateContent($content);
    //update anti gia insert, to idio pragma
  }

  public function updateContent($content, $contentMainId = NULL) {
    	
    // global $link;
    global $course_id;
    if(isset($content['id'])){
      $id = $content['id'];
    }else{
      $sql = Database::get()->querySingle("SELECT * FROM h5p_content ORDER BY id DESC LIMIT ?d",1);
      if(isset($sql->id)){
        $id = $sql->id + 1;
      }else{
        $id = 1;
      }
      // $sql = "SELECT * FROM h5p_content ORDER BY id DESC LIMIT 1";
      // $result = mysqli_query($link,$sql);
      // $count = mysqli_num_rows($result);
      // if($count == 0){
      //   $id = 1;
      // }else{
      //   while($row = mysqli_fetch_array($result)) {
      //     $id = $row['id'] + 1;            
      //   }
      // }
    }
    $contentdata = $content['params'];// mysqli_real_escape_string ($content['params'] );
    $libraryId = $content['library']['libraryId'];

    $sql = Database::get()->query("INSERT INTO h5p_content(id, main_library_id, params, course_id) VALUES (?d, ?d, ?s, ?d)",function ($errormsg) {
        echo "An error has occured: " . $errormsg;
    },$id, $libraryId, $contentdata, $course_id);
    // $sql = "INSERT INTO h5p_content(id,main_library_id,params) VALUES ( '$id', '$libraryId', '$contentdata')";
    // if(mysqli_query($link,$sql)){
    //   echo "content inserted to database";
    // }else{
    //   echo "Error: " . $sql . "<br>" . $link->error;
    // }
    return $id;
  }


  public function resetContentUserData($contentId) {
    // only for results saving, not important

    /*global $wpdb;
    // Reset user datas for this content
    $wpdb->update(
      $wpdb->prefix . 'h5p_contents_user_data',
      	array(
        'updated_at' => current_time('mysql', 1),
        'data' => 'RESET'
        ),
      	array(
        	'content_id' => $contentId,
        	'invalidate' => 1
      	),
      array('%s', '%s'),
      array('%d', '%d')
    ); */

  }

  public function saveLibraryDependencies($id, $dependencies, $dependencyType) {
    	
    // global $link;
    foreach($dependencies as $dependency){
      $machine_name = $dependency['machineName'];
      $major_version = $dependency['majorVersion'];
      $minor_version = $dependency['minorVersion'];
      $sqlselect = Database::get()->querySingle("SELECT * FROM h5p_library WHERE machine_name = ?s AND major_version = ?d AND minor_version = ?d LIMIT ?d", $machine_name, $major_version, $minor_version, 1);
      // $sqlselect = "SELECT id FROM h5p_library WHERE machine_name = '$machine_name' AND major_version = '$major_version' AND minor_version = '$minor_version' LIMIT 1";
      // $result = mysqli_query($link,$sqlselect);
      // while($row = mysqli_fetch_array($result)) {
      //   $required_library_id = $row['id'];            
      // } 
      $required_library_id = $sqlselect->id;


      $sql = Database::get()->query("INSERT INTO h5p_library_dependency (library_id, required_library_id, dependency_type) VALUES (?d, ?d, ?s)",function ($errormsg) {
        echo "An error has occured: " . $errormsg;
    },$id, $required_library_id, $dependencyType);
      // $sql = "INSERT INTO h5p_library_dependency (library_id,required_library_id,dependency_type) VALUES ('$id', '$required_library_id', '$dependencyType')";
      // if(mysqli_query($link,$sql)){
      //   echo "library dependency inserted to database";
      // }else{
      //   echo "Error: " . $sql . "<br>" . $link->error;
      // }

    }
  }


  public function copyLibraryUsage($contentId, $copyFromId, $contentMainId = NULL) { 
	    
    // global $link;
    $sql = Database::get()->query("INSERT INTO h5p_content_dependency (content_id,library_id,dependency_type) SELECT ?d,library_id,dependency_type FROM h5p_content_dependency WHERE content_id = ?d",function ($errormsg) {
        echo "An error has occured: " . $errormsg;
    },$contentId, $copyFromId);
	   // $sql = "INSERT INTO h5p_content_dependency (content_id,library_id,dependency_type) SELECT '$contentId',library_id,dependency_type FROM h5p_content_dependency WHERE content_id = '$copyFromId'";
    // if(mysqli_query($link,$sql)){
    //   echo "library dependency inserted to database";
    // }else{
    //   echo "Error: " . $sql . "<br>" . $link->error;
    // }
  }

  public function deleteContentData($id) {
    	
    // global $link;
    $sql = Database::get()->query("DELETE FROM h5p_content WHERE id = ?d",function ($errormsg) {
        echo "An error has occured: " . $errormsg;
    },$id);
  //   $sql = "DELETE FROM h5p_content WHERE id = '$id'";
  //   if ($link->query($sql) === TRUE) {
  //   	echo "<br>content deleted from database";
		// } else {
  //   	echo "Error: " . $sql . "<br>" . $link->error;
		// }

  }

  public function deleteLibraryUsage($contentId) {
	    
    // global $link;
    $sql = Database::get()->query("DELETE FROM h5p_content_dependency WHERE content_id = ?d",function ($errormsg) {
        echo "An error has occured: " . $errormsg;
    },$contentId);
  //   $sql = "DELETE FROM h5p_content_dependency WHERE content_id = '$contentId'";
  //   if ($link->query($sql) === TRUE) {
  //   	echo "<br>dependency deleted from database";
		// } else {
  //   	echo "Error: " . $sql . "<br>" . $link->error;
		// }
  }

  public function saveLibraryUsage($contentId, $librariesInUse) {
    // global $link;
    foreach($librariesInUse as $library){
      $libraryId = $library['library']['libraryId'];
      $dependencyType = $library['type'];
      $sql = Database::get()->query("INSERT INTO h5p_content_dependency(content_id, library_id, dependency_type) VALUES (?d, ?d, ?s)",function ($errormsg) {
        echo "An error has occured: " . $errormsg;
    },$contentId, $libraryId, $dependencyType);
      // $sql = "INSERT INTO h5p_content_dependency(content_id, library_id, dependency_type) VALUES ('$contentId', '$libraryId', '$dependencyType')";
      // if ($link->query($sql) === TRUE) {
      //   echo "<br>dependency inserted into the database";
      // } else {
      //   echo "Error: " . $sql . "<br>" . $link->error;
      // }
    }
  }

  public function getLibraryUsage($id, $skipContent = FALSE) {
    /*  global $wpdb;
    return array('content' => $skipContent ? -1 : intval($wpdb->get_var($wpdb->prepare("SELECT COUNT(distinct c.id)FROM {$wpdb->prefix}h5p_libraries l JOIN {$wpdb->prefix}h5p_contents_libraries cl ON l.id = cl.library_id JOIN {$wpdb->prefix}h5p_contents c ON cl.content_id = c.id WHERE l.id = %d", $id))),'libraries' => intval($wpdb->get_var($wpdb->prepare("SELECT COUNT(*)FROM {$wpdb->prefix}h5p_libraries_libraries WHERE required_library_id = %d", $id))));  */
    //NA TO DW : 8elei na epistrefw se array() ton ari8mo twn content kai libraries pou xrhsimopoioune th sugkekrimenh library
    // global $link;
    // pros to parwn den xrhsimopoieitai pou8ena ! 
  }

  public function loadLibrary($machineName, $majorVersion, $minorVersion){ // PERIMENW NA MA8W PWS 8A ELENXW AN TO QUERY EPISTREFEI APOTELESMA
    // global $link;
    $sql = Database::get()->querySinge("SELECT * FROM h5p_library WHERE machine_name = ?s && major_version = ?s && minor_version = ?s", $machineName, $majorVersion, $minorVersion);
    // $sql = "SELECT * FROM h5p_library WHERE machine_name = '$machineName' && major_version = '$majorVersion' && minor_version = '$minorVersion'";
    // $result = mysqli_query($link,$sql);
    // $count = mysqli_num_rows($result);
    // if($count == 0){ // returning false in case of the library not existing
    //   return FALSE;
    // }
    // while($row = mysqli_fetch_array($result)) {
    //   $libraryId = $row['id'];            // checking to see if the library exists in the database
    // } 
    $libraryId = $sql->id;
    $library = array();
    $library['libraryId'] = $libraryId;

    $path = 'libraries/' . $machineName . '-' . $majorVersion . '.' . $minorVersion;
    $json = $path . '/' . "library.json";

    $string = file_get_contents($json); // getting the library setings from the json file
    $json = json_decode($string,true);
    //    - title: The library's name
    $library['title'] = $json['title'];
    //    - machineName: The library machineName
    $library['machineName'] = $json['machineName'];
    //    - majorVersion: The library's majorVersion
    $library['majorVersion'] = $json['majorVersion'];
    //    - minorVersion: The library's minorVersion
    $library['minorVersion'] = $json['minorVersion'];
    //    - patchVersion: The library's patchVersion
    $library['patchVersion'] = $json['patchVersion'];
    //    - runnable: 1 if the library is a content type, 0 otherwise
    $library['runnable'] = $json['runnable'];
    //    - fullscreen(optional): 1 if the library supports fullscreen, 0 otherwise
    if(isset($json['fullscreen'])){
      $library['fullscreen'] = $json['fullscreen'];
    }
    //    - embedTypes(optional): list of supported embed types
    if(isset($json['embedTypes'])){
      $library['embedTypes'] = $json['embedTypes'];
    }
    //    - preloadedJs(optional): comma separated string with js file paths
    
    //    - preloadedCss(optional): comma separated sting with css file paths
    if(isset($json['preloadedCss'])){
      $preloadedCss = '';
      $count = count($json['preloadedCss']);
      if($count == 1){
        $preloadedCss =$preloadedCss . $path  . $json['preloadedCss'][0]['path'];
      }else{
        for($i = 0; $i < $count; $i++){
          if(isset($json['preloadedCss'][$i+1])){
            $preloadedCss =$preloadedCss . $path . $json['preloadedCss'][$i]['path'] . "," ;
          }else{
            $preloadedCss =$preloadedCss . $path . $json['preloadedCss'][$i]['path'];
          }
        }
      }
      $library['preloadedCss'] = $preloadedCss;
    }
    //    - dropLibraryCss(optional): list of associative arrays containing:
    //      - machineName: machine name for the librarys that are to drop their css
    if(isset($json['dropLibraryCss'])){
       $$library['dropLibraryCss'] = $json['dropLibraryCss'];
    }


    //    - semantics(optional): Json describing the content structure for the library
    $semantics = $path . "/" . "semantics.json";
    if(file_exists($semantics)){
      $decode = file_get_contents($semantics);
      $library['semantics'] = json_decode($decode,true);
    }
    //    - preloadedDependencies(optional): list of associative arrays containing:
    //      - machineName: Machine name for a library this library is depending on
    //      - majorVersion: Major version for a library this library is depending on
    //      - minorVersion: Minor for a library this library is depending on
    if(isset($json['preloadedDependencies'])){
      $library['preloadedDependencies'] = $json['preloadedDependencies'];
    }
    //    - dynamicDependencies(optional): list of associative arrays containing:
    //      - machineName: Machine name for a library this library is depending on
    //      - majorVersion: Major version for a library this library is depending on
    //      - minorVersion: Minor for a library this library is depending on
    if(isset($json['dynamicDependencies'])){
      $library['dynamicDependencies'] = $json['dynamicDependencies'];
    }
    //    - editorDependencies(optional): list of associative arrays containing:
    //      - machineName: Machine name for a library this library is depending on
    //      - majorVersion: Major version for a library this library is depending on
    //      - minorVersion: Minor for a library this library is depending on
    if(isset($json['editorDependencies'])){
      $library['editorDependencies'] = $json['editorDependencies'];
    }
    return $library;
  }

  public function loadLibrarySemantics($machineName, $majorVersion, $minorVersion){
      
    $path = 'libraries/' . $machineName . '-' . $majorVersion . '.' . $minorVersion;
    $semantics = $path . '/semantics.json';
    $string = file_get_contents($semantics);
    return $string;

  	//epistrefei to semantics.json
  }

  public function alterLibrarySemantics(&$semantics, $machineName, $majorVersion, $minorVersion){
    global $link;

  	//den einai xrhsimo gia to view, gia to edit einai
  	// HOW IT WORKS??!
  }

  public function deleteLibraryDependencies($libraryId){
    // global $link;
    $sql = Database::get()->query("DELETE FROM h5p_library_dependency WHERE library_id = ?d",function ($errormsg) {
        echo "An error has occured: " . $errormsg;
    },$libraryId);
  // 	$sql = "DELETE FROM h5p_library_dependency WHERE library_id = '$libraryId'";
  //   if ($link->query($sql) === TRUE) {
  //   	echo "<br>dependency deleted from database";
		// } else {
  //   	echo "Error: " . $sql . "<br>" . $link->error;
		// }
  }


  public function lockDependencyStorage(){
  //apla ta bazw, den ta exei to wordpress, isws ta dw sto moodle
  }


  public function unlockDependencyStorage(){
  //apla ta bazw, den ta exei to wordpress, isws ta dw sto moodle
  }

  /* Dikia mou custom sunarthsh gia na ginetai delete folder me arxeia mesa */

  function deleteDirectory($dir) { 
    if (!file_exists($dir)) {
      return true;
    }

    if (!is_dir($dir)) {
		  return unlink($dir);
		}

		foreach (scandir($dir) as $item) {
		  if ($item == '.' || $item == '..') {
		    continue;
		  }

		  if (!deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
		    return false;
		    }

		}

		return rmdir($dir);
	}


	public function deleteLibrary($library) {
	   
    // global $link;
    $library->id = $id;
    $sql = Database::get()->query("DELETE FROM h5p_library WHERE id = ?d",function ($errormsg) {
        echo "An error has occured: " . $errormsg;
    },$id);
    // $sql = "DELETE FROM h5p_library WHERE id = '$id'";
    $dir = "libraries/" . $library->name . "-" . $library->major_version . "." . $library->minor_version;
    deleteDirectory($dir);
	}

  public function loadContent($id){
    // global $link;
    $content = array();
    $sql = Database::get()->querySingle("SELECT * FROM h5p_content WHERE id = ?d",$id);
    // $sql = "SELECT * FROM h5p_content WHERE id = '$id'";
    // $result = mysqli_query($link,$sql);
    // while($row = mysqli_fetch_array($result)) {
    //   $content['id'] = $row['id'];
    //   $content['params'] = $row['params'];
    //   $content['libraryId'] = $row['main_library_id'];            
    // }
    $content['id'] = $sql->id;
    $content['params'] = $sql->params;
    $content['libraryId'] = $sql->main_library_id;

    $path = 'h5p/content/' . $content['id'] . '/h5p.json';
    $json = file_get_contents($path);
    $json = json_decode($json,true);

    $embedTypes = '';
    $count = count($json['embedTypes']);
    if($count == 1){
      $embedTypes = $json['embedTypes'][0];
    }else{
      for($i = 0; $i < $count; $i++){
        if(isset($json['embedTypes'][$i+1])){
          $embedTypes = $embedTypes . $json['embedTypes'][$i] . ",";
        }else{
          $embedTypes = $embedTypes . $json['embedTypes'][$i];
        }
      }
    }
    $content['embedTypes'] = $embedTypes;

    $content['title'] = $json['title'];

    $content['language'] = $json['language'];

    $content['libraryName'] = $json['mainLibrary'];

    foreach($json['preloadedDependencies'] as $jsondep){
      if(strcmp($content['libraryName'], $jsondep['machineName']) == 0){
        $content['libraryName'] = $jsondep['machineName'];
        $content['libraryMajorVersion'] = $jsondep['majorVersion'];
        $content['libraryMinorVersion'] = $jsondep['minorVersion'];
      }
    }

    $librarypath = 'h5p/libraries/' . $content['libraryName'] . '-' . $content['libraryMajorVersion'] . '.' . $content['libraryMinorVersion'] . '/library.json';
    $libjson = file_get_contents($librarypath);
    $libjson = json_decode($libjson,true);

    $libraryembedTypes = '';
    $libcount = count($libjson['embedTypes']);
    if($libcount == 1){
      $libraryembedTypes = $libjson['embedTypes'][0];
    }else{
      for($i = 0; $i < $libcount; $i++){
        if(isset($libjson['embedTypes'][$i+1])){
          $libraryembedTypes = $libraryembedTypes . $libjson['embedTypes'][$i] . ",";
        }else{
          $libraryembedTypes = $libraryembedTypes . $libjson['embedTypes'][$i];
        }
      }
    }
    $content['libraryEmbedTypes'] = $libraryembedTypes;

    if(isset($libjson['fullscreen'])){
      $content['libraryFullscreen'] = $libjson['fullscreen'];
    }else{
      $content['libraryFullscreen'] = 0;
    }
    return $content;
  }
  /**
   * Custom : saves the dependencies of a content into the database
   * gets the data from the h5p.json file and the database(lirbrary table)
   *
   * @param int $id
   *   Content identifier
   */
  public function saveContentDependencies($id){
    // global $link;

    $path = 'courses/h5p/content/' . $id . "/h5p.json";
    $file = file_get_contents($path);
    $json = json_decode($file,true);
    foreach($json['preloadedDependencies'] as $json){
      $machinename = $json['machineName'];
      $majorVersion = $json['majorVersion'];
      $minorVersion = $json['minorVersion'];
      $sqllib = Database::get()->querySingle("SELECT * FROM h5p_library WHERE machine_name = ?s AND major_version = ?d AND minor_version = ?d ORDER BY major_version DESC, minor_version DESC, patch_version DESC LIMIT ?d", $machinename, $majorVersion, $minorVersion, 1);
      // $sqllib = "SELECT * FROM h5p_library WHERE machine_name = '$machinename' AND major_version = '$majorVersion' AND minor_version = '$minorVersion' ORDER BY major_version DESC, minor_version DESC, patch_version DESC LIMIT 1";
      // $result = mysqli_query($link,$sqllib);
      // while($row = mysqli_fetch_array($result)) {
      //   $libraryid = $row['id'];
      // } 
      $libraryid = $sqllib->id;
      $sql = Database::get()->query("INSERT INTO h5p_content_dependency(content_id, library_id) VALUES(?d, ?d)",function ($errormsg) {
        echo "An error has occured: " . $errormsg;
    },$id, $libraryid);
      // $sql = "INSERT INTO h5p_content_dependency(content_id, library_id) VALUES('$id', '$libraryid')";
      // if ($link->query($sql) === TRUE) {
      //   echo "<br>content dependency inserted into database";
      // } else {
      //   echo "Error: " . $sql . "<br>" . $link->error;
      // }
    }
  }

  public function loadContentDependencies($id, $type = NULL){
    // global $link;

    $content = array();
    if ($type){
      $sql = Database::get()->queryArray("SELECT * FROM h5p_content_dependency WHERE content_id = ?d AND dependency_type = ?s",$id,$type);
    }else{
      $sql = Database::get()->queryArray("SELECT * FROM h5p_content_dependency WHERE content_id = ?d",$id);
    }
    // $sql = "SELECT * FROM h5p_content_dependency WHERE content_id = '$id'";
    // if ($type){
    //   $sql = $sql . " AND dependency_type = '$type'";
    // }
    // $result = mysqli_query($link,$sql);
    // while($row = mysqli_fetch_array($result)) {
    //   $library[]['libraryId'] = $row['library_id'];
    // } 
    $library[]['libraryId'] = $sql->library_id;

    foreach($library as $lib){
      $libraryId = $lib['libraryId'];
      $sql = Database::get()->querySingle("SELECT * FROM h5p_library WHERE id = ?d", $libraryId);
      // $sql = "SELECT * FROM h5p_library WHERE id = '$libraryId'";
      // $result = mysqli_query($link,$sql);
      // while($row = mysqli_fetch_array($result)) {
      //   $lib['libraryId'] = $row['id'];
      //   $lib['machineName'] = $row['machine_name'];
      //   $lib['majorVersion'] = $row['major_version'];
      //   $lib['minorVersion'] = $row['minor_version'];
      //   $lib['patchVersion'] = $row['patch_version'];
      //   $lib['preloadedJs'] = $row['preloaded_js'];
      //   $lib['preloadedCss'] = $row['preloaded_css'];
      //   $lib['dropCss'] = $row['dropLibraryCss'];
      //   $content[] = $lib;
      // } 
      $lib['libraryId'] = $row['id'];
        $lib['machineName'] = $sql->machine_name;
        $lib['majorVersion'] = $sql->major_version;
        $lib['minorVersion'] = $sql->minor_version;
        $lib['patchVersion'] = $sql->patch_version;
        $lib['preloadedJs'] = $sql->preloaded_js;
        $lib['preloadedCss'] = $sql->preloaded_css;
        $lib['dropCss'] = $sql->dropLibraryCss;
        $content[] = $lib;

    }

    return $content;
  }

  public function getOption($name, $default = NULL){
    global $link;

  	//NA TO DW STO moodle h drupal, sto wordpress den to exei kala
    // pi8ano na 8elei neo table me options
  }

  public function setOption($name, $value){
    global $link;

  	//same me getOption()
  }

  public function updateContentFields($id, $fields){
    global $link;

  	//exei douleia na to dw!
  	//isws einai gia editor
  }

  public function clearFilteredParameters($library_id){
  	//gia rebuilt einai , oxi akoma
  }

  public function getNumNotFiltered(){
  	//gia rebuild einai, oxi akoma
  }

  public function getNumContent($library_id, $skip = NULL) {
    global $link;
	 /*global $wpdb;
	 $skip_query = empty($skip) ? '' : " AND id NOT IN ($skip)";
	 return (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(id)FROM {$wpdb->prefix}h5p_contents WHERE library_id = %d {$skip_query}", $library_id )); */
	 //NA TO DW !!!!
  }

  public function isContentSlugAvailable($slug){
    global $link;

  	//NA TO DW!!!
  	//MALLON 8A PREPEI NA FTIA3W KALUTERA TO DATABASE
  }

  public function getLibraryStats($type){
    global $link;
  	//not important
  }

  public function getNumAuthors(){
    global $link;
  	//not important
  }

  public function saveCachedAssets($key, $libraries){
    global $link;
  	//not important right now
  }

  public function deleteCachedAssets($library_id){
    global $link;
  	//not important right now
  }

  public function getLibraryContentCount(){
    global $link;
  	//not important right now
    // may be done later(only used for statistics)
  }

  public function afterExportCreated($content, $filename){
  	//not important
    //triggers when export is created
  }

  public function hasPermission($permission, $id = NULL){
  	return TRUE; //not important right now
  }

  public function replaceContentTypeCache($contentTypeCache){
  	//not important right now
  }

  public function libraryHasUpgrade($library){
  	//not important right now
  	return false;
  }

}

?>