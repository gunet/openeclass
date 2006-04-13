<?

if (@$ezboowebstats != "Admin") {
    $To = $PHP_SELF;
    $_SERVER['REMOTE_HOST'] = @getHostByAddr($_SERVER['REMOTE_ADDR']);
    $servertime = time();
    $second = date("s", ($servertime));
    $minute = date("i", ($servertime));
    $hour = date("G", ($servertime));
    $day = date("j", ($servertime));
    $month = date("n", ($servertime));
    $year = date("y", ($servertime))+2000;
    $logdate = $year.'-'.$month.'-'.$day.' '.$hour.':'.$minute.':'.$second ;
	if (!isset($_SERVER['HTTP_REFERER'])) { 
		$_SERVER['HTTP_REFERER'] = ''; 
	}
    $res = db_query("INSERT INTO $table (id, request, host, address, agent, date, referer, country, provider, os, wb) VALUES ('', '$To', '$_SERVER[REMOTE_HOST]', '$_SERVER[REMOTE_ADDR]', '$_SERVER[HTTP_USER_AGENT]', '$logdate', '$_SERVER[HTTP_REFERER]', '', '', '', '')", $currentCourseID);
}

?>
