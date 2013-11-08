<?php

	// Require important files
	require_once("match.php");

	// Set up database connection
	$db = new PDO('mysql:host=localhost;dbname=salt;charset=utf8', 'saltstats', '6tmHuwzf2MF6rYfW');
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);


	/**
	 * Fetch webpage if modified after $time
	 * @param  string $addr
	 * @param  int $time
	 * @return string
	 */
	function getweb($addr, $time = null) {

		static $c;

		if (!$c) $c	= curl_init();
		curl_setopt($c, CURLOPT_URL, $addr);
		curl_setopt($c, CURLOPT_TIMEOUT, 3);
		curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($c, CURLOPT_FILETIME, true);
		curl_setopt($c, CURLOPT_HTTPHEADER, array("Host: www.saltybet.com"));

		if ($time !== null) {
			curl_setopt($c, CURLOPT_TIMEVALUE, $time);
			curl_setopt($c, CURLOPT_TIMECONDITION, CURL_TIMECOND_IFMODSINCE);
			//print "derp: $time\n";
		} else {
			curl_setopt($c, CURLOPT_TIMEVALUE, null);
			curl_setopt($c, CURLOPT_TIMECONDITION, null);

		}

		$data	= curl_exec($c);
		$cdata	= curl_getinfo($c);

		return array('data' => $data, 'cdata' => $cdata);

	}



