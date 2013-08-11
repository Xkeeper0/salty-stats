#!/usr/bin/php
<?php

	include "functions.php";

	$lasttime	= null;
	while (true) {

		//$json	= getweb("http://saltybet.com/betdata.json");
		$json	= getweb("http://saltybet.com/betdata.json", $lasttime);

		//$lasttime	= $json['cdata']['filetime'];
		if ($json['data']) {

			$lasttime       = $json['cdata']['filetime'];

			$data	= json_decode($json['data'], true);
			print "\n";
			printf("%30s : %8d\n%30s : %8d\n\nstatus: %s\nalert: %s\n",
				$data['p1name'],
				$data['p1total'],
				$data['p2name'],
				$data['p2total'],
				$data['status'],
				$data['alert']
				);

		} elseif ($lasttime !== $json['cdata']['filetime']) {
			print "X";

		} else {
			print ".";
		}

		sleep(1);

	}

#	print "\n\n";
#	print_r($json['cdata']);

	print "\n\n";
