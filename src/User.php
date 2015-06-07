<?php
namespace LoLCompanion;

class User {
    protected $connection;
	public $gameId;
	public $teamId;
	public $championIconId;
	public $passphrase;
	public $isNewInRoom;
	
	public function __construct($conn){
		$this->connection = $conn;
		$this->gameId = -1;
		$this->teamId = -1;
		$this->championIconId = -1;
		$this->passphrase = '';
		$this->isNewInRoom = false;
	}
	
	public function getConnectionID(){
		return $this->connection->resourceId;
	}
	
	public function getConnection(){
		return $this->connection;
	}
	
	public function requestPickedChampion($gameId, $teamId, $championIconId, $passphrase){
		$this->gameId = $gameId;
		$this->teamId = $teamId;
		$this->championIconId = $championIconId;
		$this->passphrase = $passphrase;
	}
	
	public static function getUsersChampionsIconsId($users){
		$championsIconsId = array();
		
		foreach($users as $user){
			$championsIconsId[] = $user->championIconId;
		}
		
		return $championsIconsId;
	}
}