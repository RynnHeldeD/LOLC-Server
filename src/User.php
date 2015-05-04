<?php
namespace LoLCompanion;

class User {
    protected $connection;
	public $gameId;
	public $teamId;
	public $championIconId;
	public $passphrase;
	
	public function __construct($conn){
		$this->connection = $conn;
		$this->gameId = -1;
		$this->teamId = -1;
		$this->championIconId = -1;
		$this->passphrase = '';
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
}