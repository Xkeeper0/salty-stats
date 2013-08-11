<?php


	class Match {

		protected	$_db			= null;
		protected	$_matchId		= null;
		protected	$_characters	= array();

		/**
		 * Set up a new match between two characters
		 * @param [type] $char1
		 * @param [type] $char2
		 */
		public function __construct($char1, $char2) {
			global $db;
			$this->_db	= $db;


		}

		/**
		 * [startMatch description]
		 * @param  [type] $data
		 * @param  [type] $players
		 * @return [type]
		 */
		public function startMatch($data, $players) {
			// Update match data (total bet, wagers)

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



	}


