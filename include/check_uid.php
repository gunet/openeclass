<?

// just make sure that the $uid variable isn't faked
if (isset($_SESSION['uid']))
	$uid = $_SESSION['uid'];
else
	unset($uid);

if ($require_valid_uid and !isset($uid)) {
	header("Location: $urlServer");
	exit;

}

?>
