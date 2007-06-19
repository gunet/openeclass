<?
die("Run this script to batch-compress the current Xinha snapshot. To run the script, open the file and uncomment the die() command");

error_reporting(E_ALL);
ini_set('show_errors',1);

$return = array();
function scan($dir, $durl = '',$min_size="3000")
{
	static $seen = array();
	global $return;
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

			if(preg_match("/.svn|lang/",$path)) continue;
			
			if(is_dir($path))
			{
				scan($path);
			}
			elseif(is_file($path))
			{
				if(!preg_match("/\.js$/",$path) || filesize($path) < $min_size) continue;
				$return[] =  $path;
			}

		}
	}
	@closedir($dh);

	return $files;
}
scan("../");
$cwd = getcwd();
print "Processing ".count($return)." files<br />";
foreach ($return as $file)
{
	set_time_limit ( 60 ); 
	print "Processed $file<br />";
	flush();
	copy($file,$file."_uncompr.js");
	exec("java -jar ${cwd}/dojo_js_compressor.jar -c ${file}_uncompr.js > $file 2>&1");
	unlink($file."_uncompr.js");
}
print "Operation complete."
?>