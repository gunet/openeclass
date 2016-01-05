<?php
namespace install;
use Composer\Script\Event;
use Composer\Installer\PackageEvent;

/* The fix is imported from https://www.drupal.org/files/issues/57-64-interdiff.txt */

class Installer
{

	protected static $packageToCleanup = [
    	'phpids/phpids' => ['tests'],
    	'pragmarx/google2fa' => ['tests'],
  	];

	public static function ensureHtaccess(Event $event) {

	    // The current working directory for composer scripts is where you run
	    // composer from.
	    $vendor_dir = $event->getComposer()->getConfig()->get('vendor-dir');

	    // Prevent access to vendor directory on Apache servers.
	    $htaccess_file = $vendor_dir . '/.htaccess';
	    if (!file_exists($htaccess_file)) {
	    	$lines = <<<EOT
			<IfModule mod_authz_core.c>
			  Require all denied
			</IfModule>

			# Deny all requests from Apache 2.0-2.2.
			<IfModule !mod_authz_core.c>
			  Deny from all
			</IfModule>

			# If we know how to do it safely, disable the PHP engine entirely.
			<IfModule mod_php5.c>
			  php_flag engine off
			</IfModule>
EOT;
	      file_put_contents($htaccess_file, $lines . "\n");
	    }

	    // Prevent access to vendor directory on IIS servers.
	    $webconfig_file = $vendor_dir . '/web.config';
	    if (!file_exists($webconfig_file)) {
	     	$lines = <<<EOT
			<configuration>
			  <system.webServer>
			    <authorization>
			      <deny users="*">
			    </authorization>
			  </system.webServer>
			</configuration>
EOT;
	      file_put_contents($webconfig_file, $lines . "\n");
	    }
	  }

	  
	public static function vendorTestCodeCleanup(PackageEvent $event) {
	    $vendor_dir = $event->getComposer()->getConfig()->get('vendor-dir');
	    $op = $event->getOperation();
	    if ($op->getJobType() == 'update') {
	      $package = $op->getTargetPackage();
	    }
	    else {
	      $package = $op->getPackage();
	    }
	    $package_key = static::findPackageKey($package->getName());
	    if ($package_key) {
	      foreach (static::$packageToCleanup[$package_key] as $path) {
	        $dir_to_remove = $vendor_dir . '/' . $package_key . '/' . $path;
	        if (is_dir($dir_to_remove)) {
	          if (!static::deleteRecursive($dir_to_remove)) {
	            $io = $event->getIO();
        		if ($io->askConfirmation(sprintf("Failure removing directory '%s' in package '%s'. Are you sure you want to proceed? ",$path, $package->getPrettyName()), false)) {
        		
        		}else{
        			die('An error occured.');
        		}
	          }
	        }
	        else {
	          	$io = $event->getIO();
	    		if ($io->askConfirmation(sprintf("The directory '%s' in package '%s' does not exist. Are you sure you want to proceed? ",$path, $package->getPrettyName()), false)) {
	    		
	    		}else{
	    			die('An error occured.');
	    		}
	        }
	      }
	    }
	  }

	protected static function findPackageKey($package_name) {
	    $package_key = NULL;
	    // In most cases the package name is already used as the array key.
	    if (isset(static::$packageToCleanup[$package_name])) {
	      $package_key = $package_name;
	    }
	    else {
	      // Handle any mismatch in case between the package name and array key.
	      // For example, the array key 'mikey179/vfsStream' needs to be found
	      // when composer returns a package name of 'mikey179/vfsstream'.
	      foreach (static::$packageToCleanup as $key => $dirs) {
	        if (strtolower($key) === $package_name) {
	          $package_key = $key;
	          break;
	        }
	      }
	    }
	    return $package_key;
	  }


	protected static function deleteRecursive($path) {
	    if (is_file($path) || is_link($path)) {
	      return unlink($path);
	    }
	    $success = TRUE;
	    $dir = dir($path);
	    while (($entry = $dir->read()) !== FALSE) {
	      if ($entry == '.' || $entry == '..') {
	        continue;
	      }
	      $entry_path = $path . '/' . $entry;
	      $success = static::deleteRecursive($entry_path) && $success;
	    }
	    $dir->close();

	    return rmdir($path) && $success;
	  }



}