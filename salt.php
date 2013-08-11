#!/usr/bin/php
<?php

	include "functions.php";

	$lasttime	= null;
	$match		= null;

	while (true) {

		//$json	= getweb("http://saltybet.com/betdata.json");
		$json	= getweb("http://saltybet.com/betdata.json", $lasttime);

		//$lasttime	= $json['cdata']['filetime'];
		if ($json['data']) {

			$lasttime       = $json['cdata']['filetime'];

			$data	= json_decode($json['data'], true);
			$data['p1total']	= str_replace(",", "", $data['p1total']);
			$data['p2total']	= str_replace(",", "", $data['p2total']);

			print "\n";
			printf("%30s : %8d\n%30s : %8d\n\nstatus: %s\nalert: %s\n",
				$data['p1name'],
				$data['p1total'],
				$data['p2name'],
				$data['p2total'],
				$data['status'],
				$data['alert']
				);

			switch ($data['status']) {


				case "open":
					// Match starting, create bets/characters
					$match	= new Match(array(1 => $data['p1name'], 2 => $data['p2name']));
					break;

				case "locked":
					// Match started, update bets
					if ($match) $match->startMatch($data, stripdata($data));
					break;

				case "1":
				case "2":
					// Match over, set winners
					//$players	= stripdata($data);
					//print_r($players);
					break;



			}




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




	/**
	 * Remove non-player keys from array
	 * @param  array $a
	 * @return array
	 */
	function stripdata($a) {

		$removekeys	= array('p1name', 'p2name', 'p1total', 'p2total', 'status', 'alert');
		foreach ($removekeys as $key) {
			unset($a[$key]);
		}

		return $a;


	}