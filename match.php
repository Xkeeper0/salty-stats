<?php


	class Match {

		protected	$_db			= null;
		protected	$_matchId		= null;
		protected	$_characters	= array();
		protected	$_complete	= false;

		/**
		 * Set up a new match between some characters
		 * @param array $characters
		 */
		public function __construct($characters) {
			global $db;
			$this->_db	= $db;

			// Store characters
			print "Storing characters...";

			foreach($characters as $position => $character) {
				$this->_characters[$position]	= array(
						'characterId'		=> $this->_getCharacterId($character),
						'characterMatchId'	=> null,
					);
			}
			print " stored ". count($this->_characters) ."\n";

			// Create new match row
			$this->_createMatch();


		}


		public function __destruct() {
			if (!$this->_complete) {
				$this->completeMatch();
			}
		}

		/**
		 * Actual match begins (fighting part), log bets
		 * @param  [type] $totalBet
		 * @param  [type] $players
		 * @return [type]
		 */
		public function startMatch($totalBet, $players) {
			// Update match data (total bet, wagers)

			$this->_updatePlayerData($players, true);


			$this->_db->beginTransaction();
			$betUpdate	= $this->_db->prepare("
					UPDATE	`character_matches`
					SET		`total_bet`	= :total_bet
					WHERE	`character_match_id` = :character_match_id
				");
			foreach ($totalBet as $position => $bet) {
				$betUpdate->execute(array(
						':total_bet'			=> $bet,
						':character_match_id'	=> $this->_characters[$position]['characterMatchId'],
					));
			}
			$this->_db->commit();


		}

		/**
		 * [completeMatch description]
		 * @param  [type] $winner
		 * @param  [type] $players
		 * @return [type]
		 */
		public function completeMatch($winner = null, $players = null) {
			$matchUpdate	= $this->_db->prepare("
					UPDATE	`matches`
					SET		`duration_seconds`	= TIMESTAMPDIFF(SECOND, `match_time`, NOW())
					WHERE	`match_id` = :match_id
				");
			$matchUpdate->execute(array(':match_id' => $this->_matchId));

			if ($players !== null) {
				$this->_updatePlayerData($players);
			}

			if ($winner !== null) {
				$winnerQuery	= $this->_db->prepare("
					UPDATE	`character_matches`
					SET		`winner`	= IF(`position` = :winner, 1, 0)
					WHERE	`match_id`	= :match_id
					");

				$winnerQuery->execute(array(
					':winner'	=> $winner,
					':match_id'	=> $this->_matchId
					));
			}

			$this->_complete	= true;

		}

		/**
		 * [_updatePlayerData description]
		 * @param  [type] $players
		 * @return [type]
		 */
		protected function _updatePlayerData($players, $doBets = false) {
			// Loop through all players and REPLACE INTO as needed ...

			$this->_db->beginTransaction();

			$playerUpdate	= $this->_db->prepare("
					INSERT INTO	`players` (
						`player_id`,
						`name`,
					". ($doBets ? "`rank`, " : "") ."
						`last_total`
					) VALUES (
						:player_id,
						:name,
					". ($doBets ? ":rank, " : "") ."
						:last_total
					) ON DUPLICATE KEY UPDATE
						`name`			= :name,
						". ($doBets ? "`rank`			= :rank, " : "") ."
						`last_total`	= :last_total
				");

			if ($doBets) {
				$betUpdate	= $this->_db->prepare("
						INSERT INTO	`bets` (
							`match_id`,
							`player_id`,
							`player_bet`,
							`player_total`,
							`character_match_id`
						) VALUES (
							:match_id,
							:player_id,
							:player_bet,
							:player_total,
							:character_match_id
						)
					");

			}

			foreach ($players as $playerId => $player) {

				try {
					$updateArray	= array(
							':player_id'	=> $playerId,
							':name'			=> $player['n'],
							':last_total'	=> $player['b'],
						);

					if ($doBets) {
						$updateArray[':rank']	= $player['r'];
					}
					$playerUpdate->execute($updateArray);

					if ($doBets) {
						try {
							$betUpdate->execute(array(
									':match_id'				=> $this->_matchId,
									':player_id'			=> $playerId,
									':player_bet'			=> $player['w'],
									':player_total'			=> $player['b'],
									':character_match_id'	=> $this->_characters[$player['p']]['characterMatchId'],
								));
						} catch (Exception $e) {
							print "Failed to add betting data!\n". $e->getMessage() ."\n";
							print "Match ". $this->_matchId ." - Player: [$playerId] ". $player['n'] ." - Wager: ". $player['w'] ." - Total: ". $player['b'] ." - Bet on: ". $player['p'] ." - Rank: ". $player['r'] ."\n";
						}
					}
				} catch (Exception $e) {
					print "Failed to add player data!\n". $e->getMessage() ."\n";
					print "Match ". $this->_matchId ." - Player: [$playerId] ". $player['n'] ." - Wager: ". $player['w'] ." - Total: ". $player['b'] ." - Bet on: ". $$player['p'] ." - Rank: ". $player['r'] ."\n";

				}

			}

			$this->_db->commit();
			print "Updated ". count($players) ." players\n";


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

			foreach ($this->_characters as $position => $character) {
				$chars->execute(array(
						':match_id'		=> $this->_matchId,
						':character_id'	=> $character['characterId'],
						':position'		=> $position,
					));
				$this->_characters[$position]['characterMatchId']	= $this->_db->lastInsertId();
			}

			$this->_db->commit();

		}


	}


