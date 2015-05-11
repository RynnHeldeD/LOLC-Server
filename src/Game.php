<?php
namespace LoLCompanion;

class Game {
    public $id;
	public $purpleRoom;
	public $blueRoom;
	
	public function __construct($gameId){
		$this->id = $gameId;
		$this->purpleRoom = new Room($gameId, Team::$purple);
		$this->blueRoom = new Room($gameId, Team::$blue);
	}
	
	public function vardump(){
		echo "PURPLE\r\n";
		foreach($this->purpleRoom->getUsers() as $user){
			echo $user->championIconId ."\r\n";
		}
		echo "BLUE\r\n";
		foreach($this->blueRoom->getUsers() as $user){
			echo $user->championIconId ."\r\n";
		}
	}
	
	public function addUserToRoom($user, $roomId){
		if($roomId == Team::$purple){
			$this->purpleRoom->addUser($user);
		} elseif ($roomId == Team::$blue) {
			$this->blueRoom->addUser($user);
		}
	}
	
	public function removeUser($user){
		$room = $this->getRoom($user->teamId)->removeUser($user);
		$allies = $this->getUsersFromRoom($user);

		Message::sendJSON(
			$allies, 
			array(
				'action' => 'playerList',
				'error' => false,
				'allies' => User::getUsersChampionsIconsId($allies)
			)
		);
	}
	
	public function getRoom($teamId){
		$room = null;
		
		if($teamId == Team::$purple){
			$room = $this->purpleRoom; 
		} elseif ($teamId == Team::$blue) {
			$room = $this->blueRoom;
		}
		
		return $room;
	}
	
	public function getUsersFromRoom($user, $selfExclude = false){
		$users = array();

		if(!empty($user->passphrase)){
			foreach($this->getRoom($user->teamId)->getUsers() as $u){
				if(!empty($u->passphrase) && $user->passphrase == $u->passphrase){
					if($u == $user){
						if(!$selfExclude){
							$users[] = $u;
						}
					} else {
						$users[] = $u;
					}
				}
			}
		} else {
			if(!$selfExclude){
				$users[] = $user;
			}
		}
		
		return $users;
	}
}