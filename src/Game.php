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
	
	public function addUserToRoom($user, $roomId){
		if($roomId == Team::$purple){
			$this->purpleRoom->addUser($user);
		} elseif ($roomId == Team::$blue) {
			$this->blueRoom->addUser($user);
		}
	}
}