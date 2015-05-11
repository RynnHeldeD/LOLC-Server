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
	
	public function getRoom($teamId){
		$room = null;
		
		if($teamId == Team::$purple){
			$room = $this->purpleRoom; 
		} elseif ($teamId == Team::$blue) {
			$room = $this->blueRoom;
		}
		
		return $room;
	}
	
	public function getUsersFromRoom($teamId, $passphrase = null, $exclude = null){
		$users = array();
		
		if($passphrase != null){
			foreach($this->getRoom($teamId)->getUsers() as $user){
				if($user->passphrase == $passphrase){
					if($user != $exclude){
						$users[] = $user;
					}
				}
			}
		} else {
			$users = $this->getRoom($teamId)->getUsers();
		}
		
		return $users;
	}
}