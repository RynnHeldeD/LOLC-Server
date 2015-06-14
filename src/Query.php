<?php
namespace LoLCompanion;
use LoLCompanion\Model\User;
use LoLCompanion\Manager\UserManager;
use LoLCompanion\Manager\GameManager;
use LoLCompanion\Model\Message;

class Query {
	public static function pickedChampion($user, $jsonMsg){
		// Update user information
		$user->setGameId($jsonMsg['gameId']);
		$user->setTeamId($jsonMsg['teamId']);
		$user->setChampionIconId($jsonMsg['championIconId']);	
		$user->switchToChannel($jsonMsg['passphrase']);
		
		// Send to the new player his allies
		if($user->isReady()){
			$allies = $user->findAllies();
			$game = GameManager::findOrCreate($jsonMsg['gameId']);
			Message::sendJSON(
				array($user), 
				array(
					'action' => 'playerList',
					'error' => false,
					'allies' => UserManager::getUsersChampionsIconsId($allies),
					'timestamp' => $game->getTimestamp()
				)
			);
			
			// Send his allies an update and to one of them but non non-new a request to share his timers
			if(count($allies) > 0){
				$alliesExceptSelf = $user->findAllies(false);
				Message::sendJSON(
					$alliesExceptSelf, 
					array(
						'action' => 'playerList_toNewAllies',
						'error' => false,
						'allies' => UserManager::getUsersChampionsIconsId($allies)
					),
					true // Add the share param to one of the allies
				);
			}
		} else {
			echo "[ERROR] PickedChampion called on " . $user->getConnectionId() . " but user is not ready after set. Is the jsonMsg correct ?\r\n " . var_dump($jsonMsg);
		}
	}
	
	public static function sentTimers($user, $jsonMsg){
		$allies = $user->findAllies(false);
		$newAllies = array();
		
		foreach($allies as $a){
			if($a->isNewInChannel()){
				$a->setIsNewInChannel(false);
				UserManager::update($a);
				$newAllies[] = $a;
			}
		}
	
		Message::sendJSON(
			$newAllies, 
			array(
				'action' => 'sharedTimers',
				'error' => false,
				'timers' => $jsonMsg['timers'],
				'timestamp' => $jsonMsg['timestamp']
			)
		);
	}
	
	public static function timerActivation($user, $jsonMsg){
		$allies = $user->findAllies(false);
		
		Message::sendJSON(
			$allies, 
			array(
				'action' => 'timer',
				'error' => false,
				'idSortGrille' => $jsonMsg['idSortGrille'],
				'timestampDeclenchement' => $jsonMsg['timestampDeclenchement']
			)
		);
	}
	
	public static function switchChannel($user, $jsonMsg){
		$oldAllies = $user->findAllies(false);
		Message::sendJSON(
			$oldAllies, 
			array(
				'action' => 'playerList_toOldAllies',
				'error' => false,
				'allies' => UserManager::getUsersChampionsIconsId($oldAllies)
			)
		);
		
		$user->switchToChannel($jsonMsg['channel']);

		$newAllies = $user->findAllies();
		Message::sendJSON(
			$newAllies, 
			array(
				'action' => 'playerList_toNewAllies',
				'error' => false,
				'allies' => UserManager::getUsersChampionsIconsId($newAllies)
			),
			true // On switch channel, we want to share timers
		);
	}
	
	public static function timerDelay($user, $jsonMsg){		
		$allies = $user->findAllies(false);
		
		Message::sendJSON(
			$allies, 
			array(
				'action' => 'timerDelay',
				'error' => false,
				'idSortGrille' => $jsonMsg['idSortGrille']
			)
		);
	}
	
	public static function stopTimer($user, $jsonMsg){
		$allies = $user->findAllies(false);

		Message::sendJSON(
			$allies,
			array(
				'action' => 'stopTimer',
				'error' => false,
				'idSortGrille' => $jsonMsg['idSortGrille']
			)
		);
	}
	
	public static function sentCooldown($user, $jsonMsg){
		$allies = $user->findAllies(false);

		Message::sendJSON(
			$allies,
			array(
				'action' => 'sharedCooldown',
				'error' => false,
				'champUlti' => $jsonMsg['champUlti'],
				'cdr' => $jsonMsg['cdr']
			)
		);
	}
}
?>