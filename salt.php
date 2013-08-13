#!/usr/bin/php
<?php

	include "functions.php";

	$lasttime	= null;
	$match		= null;
	$laststate	= null;

	try {

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

				// Ignore duplicated states
				if ($laststate == $data['status']) continue;
				$laststate = $data['status'];

				switch ($data['status']) {

					case "open":
						// Match starting, create bets/characters
						$match	= new Match(array(1 => $data['p1name'], 2 => $data['p2name']));
						break;

					case "locked":
						// Match started, update bets
						if ($match) $match->startMatch(array(1 => $data['p1total'], 2 => $data['p2total']), stripdata($data));
						break;

					case "1":
						if ($match) $match->completeMatch(1, stripdata($data));
						break;
					case "2":
						if ($match) $match->completeMatch(2, stripdata($data));
						break;



				}




			} elseif ($lasttime !== $json['cdata']['filetime']) {
				print "X";

			} else {
				print ".";
			}

			sleep(1);

		}


	} catch (Exception $e) {
		print "WELP. SOMETHING BROKE.\n";
		print "Exception:\n$e\n\n";
		print "Dumping data...\n";

		$dumpfile	= "dump-". time() .".log";
		file_put_contents($dumpfile, $json['data']);

		print "Saved data to $dumpfile\nPlease restart me so I can track stats again\n\n";

	}




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