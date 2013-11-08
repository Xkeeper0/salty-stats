#!/usr/bin/php
<?php

	include "functions.php";

	$lasttime	= null;
	$match		= null;
	$laststate	= null;

	try {

		while (true) {

			//$matchData	= getweb("http://saltybet.com/betdata.json");
			$matchData	= getweb("http://saltybet.com/state.json", $lasttime);

			//$lasttime	= $matchData['cdata']['filetime'];
			if ($matchData['data']) {

				$lasttime       = $matchData['cdata']['filetime'];

				$data				= json_decode($matchData['data'], true);
				$data['p1total']	= str_replace(",", "", $data['p1total']);
				$data['p2total']	= str_replace(",", "", $data['p2total']);

				print "\n";
				printf("%30s : %8d\n%30s : %8d\n\nstatus: %s\nalert: %s\nx: %s\n",
					$data['p1name'],
					$data['p1total'],
					$data['p2name'],
					$data['p2total'],
					$data['status'],
					$data['alert'],
					$data['x']
					);

				// Ignore duplicated states
				if ($laststate == $data['status']) {
					print "ReusedState: $laststate, $data[status]\n";
					continue;
				}
				$laststate = $data['status'];

				sleep(1);	// give the site some time to Do Its Thing before we read the player data ...
				$playerData	= getweb("http://saltybet.com/zdata.json", $lasttime);
				$playerDataArray	= json_decode($playerData['data'], true);


				switch ($data['status']) {

					case "open":
						// Match starting, create bets/characters
						$match	= new Match(array(1 => $data['p1name'], 2 => $data['p2name']));
						break;

					case "locked":
						// Match started, update bets
						if ($match) $match->startMatch(array(1 => $data['p1total'], 2 => $data['p2total']), stripdata($playerDataArray));
						break;

					case "1":
						if ($match) $match->completeMatch(1, stripdata($playerDataArray));
						break;
					case "2":
						if ($match) $match->completeMatch(2, stripdata($playerDataArray));
						break;



				}




			} elseif ($lasttime !== $matchData['cdata']['filetime']) {
				print "X ". $matchData['cdata']['http_code'] ."\n";

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
		file_put_contents($dumpfile, $matchData['data']);

		print "Saved data to $dumpfile\nPlease restart me so I can track stats again\n\n";

	}




	/**
	 * Remove non-player keys from array
	 * @param  array $a
	 * @return array
	 */
	function stripdata($a) {

		$removekeys	= array('p1name', 'p2name', 'p1total', 'p2total', 'status', 'alert', 'x');
		foreach ($removekeys as $key) {
			unset($a[$key]);
		}

		return $a;


	}
