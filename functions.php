<?php


	$db = new PDO('mysql:host=localhost;dbname=salt;charset=utf8', 'salt', 'saltybets');
	$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);



	function getweb($addr, $time = null) {

		$c	= curl_init($addr);
		curl_setopt($c, CURLOPT_TIMEOUT, 3);
		//curl_setopt($c, CURLOPT_VERBOSE, true);
		curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($c, CURLOPT_FILETIME, true);

		if ($time !== null) {
			curl_setopt($c, CURLOPT_TIMEVALUE, $time);
			curl_setopt($c, CURLOPT_TIMECONDITION, CURL_TIMECOND_IFMODSINCE);
			//print "derp: $time\n";
		}

		$data	= curl_exec($c);
		$cdata	= curl_getinfo($c);

		curl_close($c);

		return array('data' => $data, 'cdata' => $cdata);

	}



