<?php


	class Match {

		protected	$_db			= null;
		protected	$_matchId		= null;
		protected	$_characters	= array();

		/**
		 * Set up a new match between two characters
		 * @param [type] $char1
		 * @param [type] $char2
		 * @todo  Maybe consider setting them via array
		 */
		public function __construct($char1, $char2) {
			global $db;
			$this->_db	= $db;

			// Store characters
			print "Storing characters...";
			$this->_characters[1]	= $this->_getCharacterId($char1);
			$this->_characters[2]	= $this->_getCharacterId($char2);
			print " stored ". count($this->_characters) ."\n";

			// Create new match row
			$this->_createMatch();


		}

		/**
		 * Actual match begins (fighting part), log bets
		 * @param  [type] $data
		 * @param  [type] $players
		 * @return [type]
		 */
		public function startMatch($data, $players) {
			// Update match data (total bet, wagers)


			/*
			Player data example:
			(Shoudl probably turn off autoindex)
			"64303":{"n":"cookiemonstar",
					"p":"1",
					"w":"2",
					"b":"32",
					"r":"1",
					"g":"0"},

			 */
			foreach ($players as $id => $player) {

				printf("[%6d] %20s - P=%2d  Wager=%6d  Total=%7d  Rank=%2d  Gold=%2d\n",
					$id,
					$player['n'],
					$player['p'],
					$player['w'],
					$player['b'],
					$player['r'],
					$player['g']
					);
			}

		}

		/**
		 * [completeMatch description]
		 * @param  [type] $data
		 * @param  [type] $players
		 * @return [type]
		 */
		public function completeMatch($data, $players) {

		}

		/**
		 * [_updatePlayerData description]
		 * @param  [type] $players
		 * @return [type]
		 */
		protected function _updatePlayerData($players) {
			// Loop through all players and REPLACE INTO as needed ...
		}


		/**
		 * Get the character id (or create a new one)
		 * @param  [type] $name
		 * @return [type]
		 */
		protected function _getCharacterId($name) {

			// Check if character already exists
			$char	= $this->_db->prepare("
					SELECT	`character_id`
					FROM	`characters`
					WHERE	`name` = ?
					");

			$char->execute(array($name));
			$info	= $char->fetch();

			if ($info) {
				// Character exists, return id
				return $info['character_id'];

			} else {
				// Create new character.

				$newChar	= $this->_db->prepare("
					INSERT INTO	`characters`
					SET			`name`	= ?
					");
				$newChar->execute(array($name));
				return $this->_db->lastInsertId();
			}

		}


		/**
		 * Create a new match row (currently blank)
		 * @return [type]
		 */
		protected function _createMatch() {

			$this->_db->beginTransaction();

			print "Creating match\n";
			// Creathe empty match row
			$match		= $this->_db->query("
					INSERT INTO	`matches` (
						/* intentionally left blank */
					) VALUES (
						/* intentionally left blank */
					)
				");
			$this->_matchId	= $this->_db->lastInsertId();

			// Insert character_matches rows
			$chars	= $this->_db->prepare("
					INSERT INTO	`character_matches`
					SET			`match_id`		= :match_id,
								`character_id`	= :character_id,
								`position`		= :position
				");
			print "Inserting characters: ". count($this->_characters) ."\n";
			foreach ($this->_characters as $position => $characterId) {
				print "$position: ". $this->_matchId .", ". $characterId ."\n";
				$chars->execute(array(
						':match_id'		=> $this->_matchId,
						':character_id'	=> $characterId,
						':position'		=> $position,
					));
			}

			$this->_db->commit();

		}

	}


